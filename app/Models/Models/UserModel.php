<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User model — ตรงกับโครงสร้างตาราง user (newscience)
 * @see database/user_table_structure.sql
 */
class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'uid';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'login_uid',
        'email',
        'password',
        'title',
        'gf_name',
        'gl_name',
        'major',
        'thai_name',
        'thai_lastname',
        'profile_picture',
        'profile_customer',
        'titleThai',
        'active',
        'admin',
        'role',
        'managed_faculties',
        'edoc',
        'curriculum_id',
        'faculty_id',
        'department_id',
        'user_type',
        'degree',
        'gender',
        'nickname',
        'birth_date',
        'nationality',
        'citizen_id',
        'passport_id',
        'bio',
        'expertise',
        'phone',
        'institution',
        'google_scholar',
        'orcid',
        'scopus',
        'researchgate',
        'linkedin',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[user.email,uid,{uid}]',
        'password' => 'permit_empty|min_length[6]',
    ];

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Find user by login_uid (username)
     */
    public function findByLoginUid(string $loginUid)
    {
        return $this->where('login_uid', $loginUid)->first();
    }

    /**
     * Find user by email or login_uid (single fresh query to avoid builder state leaking)
     */
    public function findByIdentifier(string $login): ?array
    {
        $row = $this->db->table($this->table)
            ->where('email', $login)
            ->orWhere('login_uid', $login)
            ->limit(1)
            ->get()
            ->getRowArray();
        return $row !== null ? $row : null;
    }

    /**
     * Verify password (รองรับ hash ว่าง/null จาก DB)
     */
    public function verifyPassword(string $password, $hash): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get admins only — role เป็น faculty_admin/super_admin หรือ admin=1 และ active=1
     */
    public function getAdmins()
    {
        return $this->where('active', 1)
            ->groupStart()
            ->whereIn('role', ['faculty_admin', 'super_admin'])
            ->orWhere('admin', 1)
            ->groupEnd()
            ->findAll();
    }

    /**
     * หาหรือสร้าง user จากข้อมูล API (Portal/Edoc SSO) — เมื่อได้รับ JSON จาก Edoc จะ update ลง table user
     * โครงสร้างตาราง: thai_name, thai_lastname, profile_picture, active, role enum('user','faculty_admin','super_admin')
     */
    public function findOrCreateFromApiUser(array $apiUser): ?array
    {
        $email = trim($apiUser['email'] ?? '');
        $loginUid = trim($apiUser['login_uid'] ?? $apiUser['code'] ?? '');
        if ($email === '') {
            return null;
        }
        $user = $this->findByLoginUid($loginUid);
        if (!$user) {
            $user = $this->findByEmail($email);
        }
        $data = [
            'login_uid'     => $loginUid ?: null,
            'email'         => $email,
            'title'         => trim($apiUser['title'] ?? ''),
            'titleThai'     => trim($apiUser['titleThai'] ?? $apiUser['title_th'] ?? ''),
            'gf_name'       => trim($apiUser['gf_name'] ?? $apiUser['first_name_en'] ?? ''),
            'gl_name'       => trim($apiUser['gl_name'] ?? $apiUser['last_name_en'] ?? ''),
            'major'         => trim($apiUser['major'] ?? ''),
            'thai_name'     => trim($apiUser['thai_name'] ?? $apiUser['first_name_th'] ?? ''),
            'thai_lastname' => trim($apiUser['thai_lastname'] ?? $apiUser['last_name_th'] ?? ''),
        ];
        $profilePicture = trim($apiUser['profile_picture'] ?? $apiUser['profile_image'] ?? '');
        if ($profilePicture !== '') {
            $data['profile_picture'] = $profilePicture;
        }
        if ($user) {
            $this->update($user['uid'], $data);
            return $this->find($user['uid']);
        }
        $data['password'] = null;
        $data['role'] = 'user';
        $data['admin'] = 0;
        $data['active'] = 1;
        if ($profilePicture !== '') {
            $data['profile_picture'] = $profilePicture;
        }
        $uid = $this->insert($data);
        return $uid ? $this->find($uid) : null;
    }

    /**
     * ชื่อ (ไทย) — ตาราง user ใช้ thai_name
     */
    private static function firstNameTh(array $user): string
    {
        return trim($user['thai_name'] ?? '');
    }

    /**
     * นามสกุล (ไทย) — ตาราง user ใช้ thai_lastname
     */
    private static function lastNameTh(array $user): string
    {
        return trim($user['thai_lastname'] ?? '');
    }

    /**
     * Get full name (Thai) — thai_name + thai_lastname, fallback title + gf_name + gl_name
     */
    public function getFullName(array $user): string
    {
        $first = self::firstNameTh($user);
        $last  = self::lastNameTh($user);
        $full  = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }
        $title = $user['title'] ?? $user['titleThai'] ?? '';
        $firstName = $user['gf_name'] ?? '';
        $lastName = $user['gl_name'] ?? '';
        return trim("{$title} {$firstName} {$lastName}");
    }

    /**
     * ชื่อไทยเต็ม (ชื่อ + นามสกุล) สำหรับแสดง — จาก thai_name + thai_lastname
     */
    public function getFullNameThaiForDisplay(array $user): string
    {
        $first = self::firstNameTh($user);
        $last  = self::lastNameTh($user);
        $full  = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }
        $title = trim($user['title'] ?? $user['titleThai'] ?? '');
        return trim($title . ' ' . $first . ' ' . $last) ?: $this->getFullName($user);
    }

    /**
     * คืนค่า status แบบสตริงจาก active (1 = active, 0 = inactive) — สำหรับโค้ดที่ยังใช้ key 'status'
     */
    public static function statusFromActive($active): string
    {
        return ((int) $active) === 1 ? 'active' : 'inactive';
    }

    /**
     * รายการ user สำหรับเลือกเพิ่มเป็นบุคลากร (dropdown)
     */
    public function getListForPersonnel(bool $excludeLinked = true): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('user.*');
        $builder->where('user.active', 1);
        if ($excludeLinked && $this->db->tableExists('personnel') && $this->db->fieldExists('user_uid', 'personnel')) {
            $builder->join('personnel', 'personnel.user_uid = user.uid', 'left');
            $builder->where('personnel.id IS NULL');
        }
        $builder->orderBy('user.email', 'ASC');
        $rows = $builder->get()->getResultArray();
        $list = [];
        foreach ($rows as $u) {
            $uid = (int) ($u['uid'] ?? 0);
            if ($uid <= 0) continue;
            $nameTh = $this->getFullNameThaiForDisplay($u);
            $nameEn = trim(($u['gf_name'] ?? '') . ' ' . ($u['gl_name'] ?? ''));
            if ($nameEn === '') {
                $nameEn = $nameTh;
            }
            $email = trim($u['email'] ?? '');
            $list[] = [
                'uid' => $uid,
                'email' => $email,
                'display_name' => $nameTh !== '' ? $nameTh . ' (' . $email . ')' : $email,
                'name' => $nameTh,
                'name_en' => $nameEn,
            ];
        }
        return $list;
    }
}
