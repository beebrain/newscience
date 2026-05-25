<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Search NS personnel/users for publication author autocomplete.
 */
final class PublicationAuthorSearch
{
    private const SCIENCE_FACULTY_LABELS = [
        'คณะวิทยาศาสตร์และเทคโนโลยี',
        'คณะวิทยาศาสตร์และเทคโนโลยี',
    ];

    /**
     * @return list<array{personnel_id:int,name:string,email:string,affiliation:string}>
     */
    public static function searchByName(string $q, int $limit = 10): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return [];
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('personnel')) {
            return [];
        }

        $limit = max(1, min($limit, 20));
        $builder = self::baseBuilder($db);
        $likeFields = [
            'personnel.name',
            'personnel.name_en',
            'personnel.email',
            'personnel.user_email',
        ];
        foreach (['tf_name', 'tl_name', 'gf_name', 'gl_name', 'email'] as $field) {
            if ($db->fieldExists($field, 'user')) {
                $likeFields[] = 'user.' . $field;
            }
        }

        $builder->groupStart();
        foreach ($likeFields as $i => $field) {
            if ($i === 0) {
                $builder->like($field, $q);
            } else {
                $builder->orLike($field, $q);
            }
        }
        $builder->groupEnd()
            ->orderBy('personnel.sort_order', 'ASC')
            ->orderBy('personnel.name', 'ASC')
            ->limit($limit);

        return self::uniqueResults($builder->get()->getResultArray());
    }

    /**
     * @return array{personnel_id:int,name:string,email:string,affiliation:string}|null
     */
    public static function resolveByEmail(string $email): ?array
    {
        $email = CvProfile::normalizeEmail($email);
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('personnel')) {
            return null;
        }

        $builder = self::baseBuilder($db);
        $builder->groupStart()
            ->where('LOWER(TRIM(personnel.user_email)) = ' . $db->escape($email), null, false)
            ->orWhere('LOWER(TRIM(personnel.email)) = ' . $db->escape($email), null, false);
        if ($db->tableExists('user') && $db->fieldExists('email', 'user')) {
            $builder->orWhere('LOWER(TRIM(user.email)) = ' . $db->escape($email), null, false);
        }
        $builder->groupEnd()
            ->limit(1);

        $row = $builder->get()->getRowArray();

        return is_array($row) ? self::formatResult($row) : null;
    }

    /**
     * @return array{personnel_id:int,name:string,email:string,affiliation:string}|null
     */
    public static function formatResult(array $row): ?array
    {
        $personnelId = (int) ($row['personnel_id'] ?? $row['id'] ?? 0);
        if ($personnelId <= 0) {
            return null;
        }

        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            $name = trim((string) (($row['user_tf_name'] ?? '') . ' ' . ($row['user_tl_name'] ?? '')));
        }
        if ($name === '') {
            $name = trim((string) ($row['name_en'] ?? ''));
        }
        if ($name === '') {
            $name = trim((string) (($row['user_gf_name'] ?? '') . ' ' . ($row['user_gl_name'] ?? '')));
        }

        $email = CvProfile::normalizeEmail((string) ($row['email'] ?? ''));
        if ($email === '') {
            $email = CvProfile::normalizeEmail((string) ($row['user_email'] ?? $row['personnel_user_email'] ?? ''));
        }

        $affiliation = trim((string) ($row['program_name_th'] ?? ''));
        if ($affiliation === '') {
            $affiliation = 'มหาวิทยาลัยราชภัฏอุตรดิตถ์';
        }

        return [
            'personnel_id' => $personnelId,
            'name'         => $name,
            'email'        => $email,
            'affiliation'  => $affiliation,
        ];
    }

    private static function baseBuilder($db)
    {
        $hasUser = $db->tableExists('user');
        $select = [
            'personnel.id AS personnel_id',
            'personnel.name',
            'personnel.name_en',
            'personnel.email',
            'personnel.user_email AS personnel_user_email',
        ];

        foreach (['tf_name', 'tl_name', 'gf_name', 'gl_name', 'email', 'faculty'] as $field) {
            if ($hasUser && $db->fieldExists($field, 'user')) {
                $select[] = 'user.' . $field . ' AS user_' . $field;
            }
        }
        if ($db->tableExists('programs') && $db->fieldExists('program_id', 'personnel')) {
            $select[] = 'programs.name_th AS program_name_th';
        }

        $builder = $db->table('personnel')->select(implode(', ', $select));

        if ($hasUser) {
            $joinOn = $db->fieldExists('user_uid', 'personnel')
                ? '(user.email = personnel.user_email OR user.uid = personnel.user_uid)'
                : 'user.email = personnel.user_email';
            $builder->join('user', $joinOn, 'left', false);
        }
        if ($db->tableExists('programs') && $db->fieldExists('program_id', 'personnel')) {
            $builder->join('programs', 'programs.id = personnel.program_id', 'left');
        }
        if ($db->fieldExists('status', 'personnel')) {
            $builder->where('personnel.status', 'active');
        }
        if ($hasUser && $db->fieldExists('faculty', 'user')) {
            // รวม: ไม่มี user / ยังไม่ระบุคณะ / คณะวิทยาศาสตร์ (สะกดต่างกันได้)
            $builder->groupStart();
            $builder->where('user.uid IS NULL', null, false);
            $builder->orWhere('user.email IS NULL', null, false);
            $builder->orWhere('TRIM(COALESCE(user.faculty, \'\')) = \'\'', null, false);
            $builder->orLike('user.faculty', 'วิทยาศาสตร์', 'both');
            foreach (array_values(array_unique(self::SCIENCE_FACULTY_LABELS, SORT_STRING)) as $faculty) {
                $builder->orWhere('user.faculty', $faculty);
            }
            $builder->groupEnd();
        }

        return $builder;
    }

    /**
     * @return list<array{personnel_id:int,name:string,email:string,affiliation:string}>
     */
    private static function uniqueResults(array $rows): array
    {
        $out = [];
        $seen = [];
        foreach ($rows as $row) {
            $item = self::formatResult($row);
            if ($item === null) {
                continue;
            }
            $key = $item['email'] !== '' ? 'e:' . $item['email'] : 'p:' . $item['personnel_id'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
        }

        return $out;
    }
}
