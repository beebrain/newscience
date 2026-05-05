<?php

namespace App\Models;

use App\Libraries\CvProfile;
use CodeIgniter\Model;

class PersonnelModel extends Model
{
    protected $table = 'personnel';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        // After normalization, name/name_en/email/image are deprecated
        // Keep for backward compatibility during migration
        'name',
        'name_en',
        'email',
        'image',
        'cv_profile_image',
        'position',
        'position_en',
        'position_detail',
        'academic_title',
        'academic_title_en',
        'orcid_id',
        'organization_unit_id',
        'program_id',
        'user_email',
        'user_uid',
        'phone',
        'bio',
        'bio_en',
        'education',
        'expertise',
        'sort_order',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    /** ปิดการกรองเฉพาะฟิลด์ที่เปลี่ยน เพื่อไม่ให้ update([]) เมื่อค่าที่ส่งเท่ากับค่าเดิมแล้วเกิด DataException */
    protected bool $updateOnlyChanged = false;

    /**
     * Build SELECT statement with user data joined
     * ชื่อที่แสดง (name / name_en): ใช้ข้อมูลใน personnel เป็นหลัก แล้วจึง fallback ไป user
     * ตรวจสอบคอลัมน์ใน user ก่อนใช้ เพื่อกัน Unknown column ถ้า DB ยังไม่มีคอลัมน์
     */
    protected function selectWithUser(): string
    {
        $db = $this->db;
        $hasTitle          = $db->fieldExists('title', 'user');
        $hasTfName         = $db->fieldExists('tf_name', 'user');
        $hasTlName         = $db->fieldExists('tl_name', 'user');
        $hasGfName         = $db->fieldExists('gf_name', 'user');
        $hasGlName         = $db->fieldExists('gl_name', 'user');
        $hasProfileImage   = $db->fieldExists('profile_image', 'user');
        $hasProfilePicture = $db->fieldExists('profile_picture', 'user');
        $hasEmail          = $db->fieldExists('email', 'user');

        $uTitle = $hasTitle  ? 'user.title'   : 'NULL';
        $uTf    = $hasTfName ? 'user.tf_name' : 'NULL';
        $uTl    = $hasTlName ? 'user.tl_name' : 'NULL';
        $uGf    = $hasGfName ? 'user.gf_name' : 'NULL';
        $uGl    = $hasGlName ? 'user.gl_name' : 'NULL';
        $uImg   = $hasProfileImage ? 'user.profile_image' : ($hasProfilePicture ? 'user.profile_picture' : 'NULL');
        $uEmail = $hasEmail  ? 'user.email'   : 'NULL';

        $nameExpr = "COALESCE(
                NULLIF(TRIM(personnel.name), ''),
                NULLIF(TRIM(CONCAT(COALESCE({$uTf}, ''), ' ', COALESCE({$uTl}, ''))), ''),
                NULLIF(TRIM(CONCAT(COALESCE({$uTitle}, ''), ' ', COALESCE({$uTf}, ''), ' ', COALESCE({$uTl}, ''))), ''),
                TRIM(personnel.name)
            )";
        $nameEnExpr = "COALESCE(
                NULLIF(TRIM(personnel.name_en), ''),
                NULLIF(TRIM(CONCAT(COALESCE({$uGf}, ''), ' ', COALESCE({$uGl}, ''))), ''),
                TRIM(personnel.name_en)
            )";

        $extra = "user.email as user_email";
        if ($hasTitle)  $extra .= ", user.title as user_title";
        if ($hasTfName) $extra .= ", user.tf_name as user_tf_name";
        if ($hasTlName) $extra .= ", user.tl_name as user_tl_name";
        if ($hasGfName) $extra .= ", user.gf_name as user_gf_name";
        if ($hasGlName) $extra .= ", user.gl_name as user_gl_name";
        if ($hasProfileImage) {
            $extra .= ", user.profile_image as user_profile_image";
        } elseif ($hasProfilePicture) {
            $extra .= ", user.profile_picture as user_profile_image";
        }

        return "personnel.*,
            {$nameExpr} as name,
            {$nameEnExpr} as name_en,
            COALESCE({$uEmail}, personnel.email) as email,
            COALESCE({$uImg}, personnel.image) as image,
            {$extra}";
    }

    /**
     * ON clause สำหรับ JOIN user — รองรับทั้ง user_email และ user_uid (ถ้ามีคอลัมน์)
     *
     * @return array{0: string, 1: bool|null} [condition, escape]
     */
    protected function userJoinOn(): array
    {
        if ($this->db->fieldExists('user_uid', 'personnel')) {
            return ['(user.email = personnel.user_email OR user.uid = personnel.user_uid)', false];
        }

        return ['user.email = personnel.user_email', null];
    }

    /**
     * ชื่อสำหรับแสดง — ชื่อภาษาไทย ก่อนเสมอ (user_tf_name + user_tl_name จาก user)
     */
    public function getFullName($person)
    {
        $combined = trim($person['name'] ?? '');
        if ($combined !== '') {
            return $combined;
        }
        $first = trim($person['user_tf_name'] ?? '');
        $last  = trim($person['user_tl_name'] ?? '');
        $full  = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }

        return '';
    }

    /**
     * Get full name in English (falls back to Thai name if empty)
     */
    public function getFullNameEn($person)
    {
        $enRow = trim($person['name_en'] ?? '');
        if ($enRow !== '') {
            return $enRow;
        }
        if (! empty($person['user_gf_name']) || ! empty($person['user_gl_name'])) {
            $fname = trim($person['user_gf_name'] ?? '');
            $lname = trim($person['user_gl_name'] ?? '');
            $fullName = trim("{$fname} {$lname}");
            if ($fullName !== '') {
                return $fullName;
            }
        }

        return $this->getFullName($person);
    }

    /**
     * Get email - supports both normalized and legacy data
     */
    public function getEmail($person): string
    {
        return trim($person['email'] ?? '');
    }

    /**
     * Get image/profile photo — ใช้เฉพาะจาก user (profile_image/profile_picture) ไม่ใช้ personnel.image
     * เพื่อให้ข้อมูลเดียวกันเมื่อมีการอัปโหลดรูปของ user
     */
    public function getImage($person): string
    {
        $img = $person['user_profile_image'] ?? $person['image'] ?? '';
        return is_string($img) ? trim($img) : '';
    }

    /**
     * Get active personnel with user data joined
     */
    public function getActive()
    {
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->where('personnel.status', 'active')
            ->orderBy('personnel.sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get active personnel without join (for simple queries)
     */
    public function getActiveSimple()
    {
        return $this->where('status', 'active')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get personnel by organization unit with user data
     */
    public function getByOrganizationUnit($organizationUnitId)
    {
        if (!$this->db->fieldExists('organization_unit_id', 'personnel')) {
            return [];
        }
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->where('personnel.organization_unit_id', $organizationUnitId)
            ->where('personnel.status', 'active')
            ->orderBy('personnel.sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get executives (dean, vice deans) with user data
     */
    public function getExecutives()
    {
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->where('personnel.status', 'active')
            ->where('personnel.position IS NOT NULL')
            ->orderBy('personnel.sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get dean with user data
     */
    public function getDean()
    {
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->like('personnel.position', 'คณบดี')
            ->where('personnel.status', 'active')
            ->first();
    }

    /**
     * Find personnel by ID with user data
     */
    public function findWithUser($id)
    {
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->where('personnel.id', $id)
            ->first();
    }

    /**
     * Find personnel by user email with user data
     */
    public function findByUserEmail(string $email)
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->where('personnel.user_email', $email)
            ->first();
    }

    /**
     * บุคลากร active ที่ผูก user_email แบบ normalize (lowercase + trim) — ใช้สิทธิ์ E-Certificate organizer
     */
    public function findActiveByUserEmailNormalized(string $email): ?array
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }

        return $this->where('status', 'active')
            ->where('LOWER(TRIM(user_email))', $email)
            ->first();
    }

    /**
     * Find personnel by user_uid (legacy) — แปลงเป็น email แล้วเรียก findByUserEmail
     */
    public function findByUserUid($userUid)
    {
        $userModel = new UserModel();
        $user = $userModel->find((int) $userUid);
        return $user && !empty($user['email']) ? $this->findByUserEmail($user['email']) : null;
    }

    /**
     * Find personnel by email (checks both user.email and personnel.email)
     */
    public function findByEmail(string $email)
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->groupStart()
            ->where('personnel.user_email', $email)
            ->orWhere('user.email', $email)
            ->orWhere('personnel.email', $email)
            ->groupEnd()
            ->first();
    }

    /**
     * Get active personnel with organization unit (และ program names)
     */
    public function getActiveWithDepartment()
    {
        if (!$this->db->tableExists('organization_units') || !$this->db->fieldExists('organization_unit_id', 'personnel')) {
            $select = $this->selectWithUser();
            if ($this->db->fieldExists('program_id', 'personnel')) {
                $select .= ', programs.name_th as program_name_th, programs.name_en as program_name_en';
            }
            [$on, $onEsc] = $this->userJoinOn();
            $builder = $this->select($select)
                ->join('user', $on, 'left', $onEsc)
                ->where('personnel.status', 'active');
            if ($this->db->fieldExists('program_id', 'personnel')) {
                $builder->join('programs', 'programs.id = personnel.program_id', 'left');
            }
            return $builder->orderBy('personnel.sort_order', 'ASC')->findAll();
        }
        $select = $this->selectWithUser() . ', organization_units.name_th as department_name_th, organization_units.name_en as department_name_en';
        if ($this->db->fieldExists('program_id', 'personnel')) {
            $select .= ', programs.name_th as program_name_th, programs.name_en as program_name_en';
        }
        [$on, $onEsc] = $this->userJoinOn();
        $builder = $this->select($select)
            ->join('user', $on, 'left', $onEsc)
            ->join('organization_units', 'organization_units.id = personnel.organization_unit_id', 'left')
            ->where('personnel.status', 'active');
        if ($this->db->fieldExists('program_id', 'personnel')) {
            $builder->join('programs', 'programs.id = personnel.program_id', 'left');
        }
        return $builder->orderBy('organization_units.sort_order', 'ASC')
            ->orderBy('personnel.sort_order', 'ASC')
            ->findAll();
    }

    /**
     * โหลด personnel ตาม IDs พร้อม join user (ชื่อจาก tf_name + tl_name)
     * ใช้สำหรับหน้าผู้บริหาร — ประธานหลักสูตร
     */
    public function getActiveByIdsWithUser(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($id) => $id > 0)));
        if ($ids === []) {
            return [];
        }
        [$on, $onEsc] = $this->userJoinOn();

        return $this->select($this->selectWithUser())
            ->join('user', $on, 'left', $onEsc)
            ->where('personnel.status', 'active')
            ->whereIn('personnel.id', $ids)
            ->orderBy('personnel.sort_order', 'ASC')
            ->findAll();
    }

    /**
     * หลักสูตรที่บุคลากรสังกัด (หลายหลักสูตร) จากตาราง personnel_programs
     * คืนค่า [] ถ้าตารางไม่มีหรือยังไม่มีข้อมูล
     */
    public function getProgramsForPersonnel(int $personnelId): array
    {
        if (!$this->db->tableExists('personnel_programs')) {
            return [];
        }
        $model = new \App\Models\PersonnelProgramModel();
        return $model->getByPersonnelId($personnelId);
    }

    /**
     * Determine the tier level of a personnel based on their position
     * Uses OrganizationRoles library for consistent classification
     *
     * @param array $personnel Personnel data array
     * @return int Tier level (1-5)
     */
    public function getTier(array $personnel): int
    {
        return \App\Libraries\OrganizationRoles::getTier($personnel);
    }

    /**
     * Get department ID from personnel's primary program
     *
     * @param int $personnelId Personnel ID
     * @return int|null Department ID or null
     */
    public function getDepartmentFromPrimaryProgram(int $personnelId): ?int
    {
        if (!$this->db->tableExists('personnel_programs')) {
            return null;
        }

        $ppModel = new \App\Models\PersonnelProgramModel();
        $primaryProgram = $ppModel->getPrimaryProgramForPersonnel($personnelId);

        if ($primaryProgram && !empty($primaryProgram['organization_unit_id'])) {
            return (int) $primaryProgram['organization_unit_id'];
        }

        return null;
    }

    /**
     * Get all active personnel grouped by tier
     *
     * @return array Array of [tier => [personnel...]]
     */
    public function getActiveGroupedByTier(): array
    {
        $personnel = $this->getActive();
        $grouped = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];

        foreach ($personnel as $p) {
            $tier = $this->getTier($p);
            $grouped[$tier][] = $p;
        }

        return $grouped;
    }

    /**
     * Link personnel to user by email
     * Used for normalizing data
     */
    public function linkToUserByEmail(int $personnelId): bool
    {
        $personnel = $this->find($personnelId);
        if (!$personnel || empty($personnel['email'])) {
            return false;
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($personnel['email']);

        if ($user) {
            return $this->update($personnelId, ['user_email' => $user['email']]);
        }

        return false;
    }

    /**
     * Get user data for personnel (for when full user data is needed)
     */
    public function getUserData(int $personnelId): ?array
    {
        $personnel = $this->find($personnelId);
        if (!$personnel || empty($personnel['user_email'])) {
            return null;
        }

        $userModel = new UserModel();
        return $userModel->findByEmail(trim($personnel['user_email']));
    }

    /**
     * คำนำหน้าไทยสำหรับแสดงสาธารณะ — personnel.academic_title ก่อน แล้วค่อย user.title
     */
    public static function resolvePublicTitleTh(array $person): string
    {
        $at = trim((string) ($person['academic_title'] ?? ''));
        if ($at !== '') {
            return $at;
        }

        return trim((string) ($person['user_title'] ?? ''));
    }

    /**
     * คำนำหน้าอังกฤษ — personnel.academic_title_en ก่อน แล้วแปลจาก user.title
     */
    public static function resolvePublicTitleEn(array $person): string
    {
        $en = trim((string) ($person['academic_title_en'] ?? ''));
        if ($en !== '') {
            return $en;
        }
        $u = trim((string) ($person['user_title'] ?? ''));
        if ($u === '') {
            return '';
        }

        return trim(CvProfile::mapAcademicTitleThToEn($u));
    }

    /**
     * ชื่อ-นามสกุลไทยสาธารณะ — ชื่อรวมจาก query (เน้น personnel) ก่อน แล้วค่อยแยกจาก user
     */
    public static function resolvePublicNameTh(array $person): string
    {
        $n = trim((string) ($person['name'] ?? ''));
        if ($n !== '') {
            return $n;
        }

        return trim(trim((string) ($person['user_tf_name'] ?? '')) . ' ' . trim((string) ($person['user_tl_name'] ?? '')));
    }

    /**
     * ชื่อ-นามสกุลอังกฤษสาธารณะ — name_en จาก query ก่อน แล้วค่อย user
     */
    public static function resolvePublicNameEn(array $person): string
    {
        $n = trim((string) ($person['name_en'] ?? ''));
        if ($n !== '') {
            return $n;
        }

        return trim(trim((string) ($person['user_gf_name'] ?? '')) . ' ' . trim((string) ($person['user_gl_name'] ?? '')));
    }

    public static function resolvePublicDisplayNameTh(array $person): string
    {
        $t = self::resolvePublicTitleTh($person);
        $n = self::resolvePublicNameTh($person);

        return $t !== '' ? $t . ' ' . $n : $n;
    }

    public static function resolvePublicDisplayNameEn(array $person): string
    {
        $t = self::resolvePublicTitleEn($person);
        $n = self::resolvePublicNameEn($person);
        if ($n === '') {
            return self::resolvePublicDisplayNameTh($person);
        }

        return $t !== '' ? $t . ' ' . $n : $n;
    }
}
