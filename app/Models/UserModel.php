<?php

namespace App\Models;

use CodeIgniter\Model;

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
        'tf_name',
        'tl_name',
        'th_name',
        'thai_name',
        'thai_lastname',
        'role',
        'profile_image',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[user.email,uid,{uid}]',
        'password' => 'permit_empty|min_length[6]',
    ];

    /**
     * แปลงค่า active (0/1) หรือ status เป็นข้อความสำหรับแสดง
     */
    public static function statusFromActive($activeOrStatus): string
    {
        if (is_string($activeOrStatus) && $activeOrStatus !== '') {
            return $activeOrStatus;
        }
        return (int) $activeOrStatus === 1 ? 'active' : 'inactive';
    }

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
     * Get admins only
     */
    public function getAdmins()
    {
        return $this->where('role', 'admin')->where('status', 'active')->findAll();
    }

    /**
     * หาหรือสร้าง user จากข้อมูล API (Portal/Edoc SSO) — เมื่อได้รับ JSON จาก Edoc จะ update ลง table user
     * คืน array user หรือ null ถ้าไม่พบและสร้างไม่ได้
     * @param array $apiUser ต้องมี key: email; ควรมี login_uid/code, ชื่อ-นามสกุล (gf_name, gl_name, thai_name, thai_lastname หรือ first_name_th, last_name_th ฯลฯ)
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
        // อัปเดตฟิลด์จาก Edoc JSON ลง table user (รองรับ key หลายแบบตามแบบอย่างใน Edoc)
        $data = [
            'login_uid'     => $loginUid ?: null,
            'email'         => $email,
            'title'         => trim($apiUser['title'] ?? ''),
            'gf_name'       => trim($apiUser['gf_name'] ?? $apiUser['first_name_en'] ?? ''),
            'gl_name'       => trim($apiUser['gl_name'] ?? $apiUser['last_name_en'] ?? ''),
            'tf_name'       => trim($apiUser['tf_name'] ?? $apiUser['first_name_th'] ?? ''),
            'tl_name'       => trim($apiUser['tl_name'] ?? $apiUser['last_name_th'] ?? ''),
            'th_name'       => trim($apiUser['th_name'] ?? $apiUser['thai_name'] ?? $apiUser['first_name_th'] ?? ''),
            'thai_name'     => trim($apiUser['thai_name'] ?? $apiUser['first_name_th'] ?? ''),
            'thai_lastname' => trim($apiUser['thai_lastname'] ?? $apiUser['last_name_th'] ?? ''),
        ];
        $profileImage = trim($apiUser['profile_image'] ?? '');
        if ($profileImage !== '') {
            $data['profile_image'] = $profileImage;
        }
        if ($user) {
            $this->update($user['uid'], $data);
            return $this->find($user['uid']);
        }
        $data['password'] = null;
        $data['role'] = 'user';
        $data['status'] = 'active';
        if ($profileImage !== '') {
            $data['profile_image'] = $profileImage;
        }
        $uid = $this->insert($data);
        return $uid ? $this->find($uid) : null;
    }

    /**
     * ชื่อ (ไทย) จาก field — รองรับทั้ง th_name และ thai_name (ตารางโคลนจาก researchrecord)
     */
    private static function firstNameTh(array $user): string
    {
        return trim($user['th_name'] ?? $user['thai_name'] ?? $user['tf_name'] ?? '');
    }

    /**
     * นามสกุล (ไทย) จาก field — thai_lastname
     */
    private static function lastNameTh(array $user): string
    {
        return trim($user['thai_lastname'] ?? $user['tl_name'] ?? '');
    }

    /**
     * Get full name (Thai) — ใช้ ชื่อ + นามสกุล จาก th_name/thai_name + thai_lastname
     */
    public function getFullName(array $user): string
    {
        $first = self::firstNameTh($user);
        $last  = self::lastNameTh($user);
        $full  = trim($first . ' ' . $last);
        if ($full !== '') return $full;
        $title = $user['title'] ?? '';
        $firstName = $user['tf_name'] ?? $user['gf_name'] ?? '';
        $lastName = $user['tl_name'] ?? $user['gl_name'] ?? '';
        return trim("{$title} {$firstName} {$lastName}");
    }

    /**
     * ชื่อไทยเต็ม (ชื่อ + นามสกุล) สำหรับแสดงใน dropdown — จาก thai_name + thai_lastname
     */
    public function getFullNameThaiForDisplay(array $user): string
    {
        $first = self::firstNameTh($user);
        $last  = self::lastNameTh($user);
        $full  = trim($first . ' ' . $last);
        if ($full !== '') return $full;
        $title = trim($user['title'] ?? '');
        return trim($title . ' ' . $first . ' ' . $last) ?: $this->getFullName($user);
    }

    /**
     * รายการ user สำหรับเลือกเพิ่มเป็นบุคลากร (dropdown) — แสดงทั้งชื่อ และนามสกุล
     */
    public function getListForPersonnel(bool $excludeLinked = true): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('user.*');
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
