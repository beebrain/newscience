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
        'program_id',
        'profile_image',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[user.email,uid,{uid}]',
        'password' => 'permit_empty|min_length[6]',
        'role' => 'required|in_list[super_admin,faculty_admin,user]',
        'program_id' => 'permit_empty|integer',
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
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        return $this->where('role', $role)->findAll();
    }

    /**
     * Get users by program
     */
    public function getByProgram(int $programId): array
    {
        return $this->where('program_id', $programId)->findAll();
    }

    /**
     * Get users by role and program
     */
    public function getByRoleAndProgram(string $role, int $programId): array
    {
        return $this->where('role', $role)->where('program_id', $programId)->findAll();
    }

    /**
     * Get super admins only
     */
    public function getSuperAdmins(): array
    {
        return $this->where('role', 'super_admin')->where('status', 'active')->findAll();
    }

    /**
     * Get faculty admins only
     */
    public function getFacultyAdmins(): array
    {
        return $this->where('role', 'faculty_admin')->where('status', 'active')->findAll();
    }

    /**
     * Get regular users only
     */
    public function getRegularUsers(): array
    {
        return $this->where('role', 'user')->where('status', 'active')->findAll();
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(array $user): bool
    {
        return ($user['role'] ?? '') === 'super_admin';
    }

    /**
     * Check if user is faculty admin
     */
    public function isFacultyAdmin(array $user): bool
    {
        return ($user['role'] ?? '') === 'faculty_admin';
    }

    /**
     * Check if user is admin (รวม admin, editor, faculty_admin)
     */
    public function isAdmin(array $user): bool
    {
        return in_array($user['role'] ?? '', ['admin', 'editor', 'faculty_admin'], true);
    }

    /**
     * Check if user can manage program
     */
    public function canManageProgram(array $user, int $programId): bool
    {
        // Super admin can manage all programs
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Admin, Editor, Faculty admin can manage their own program
        if ($this->isAdmin($user) && (int)($user['program_id'] ?? 0) === $programId) {
            return true;
        }

        return false;
    }

    /**
     * Search users by name or email
     */
    public function searchUsers(string $query, ?int $programId = null): array
    {
        $builder = $this->like('email', $query)
            ->orLike('gf_name', $query)
            ->orLike('gl_name', $query)
            ->orLike('th_name', $query)
            ->orLike('thai_name', $query)
            ->orLike('thai_lastname', $query);

        if ($programId) {
            $builder->where('program_id', $programId);
        }

        return $builder->findAll();
    }

    /**
     * Get users with pagination and filters
     */
    public function getUsersWithFilters(?string $role = null, ?int $programId = null, ?string $status = null, int $limit = 20, int $offset = 0): array
    {
        $builder = $this;

        if ($role) {
            $builder = $builder->where('role', $role);
        }

        if ($programId) {
            $builder = $builder->where('program_id', $programId);
        }

        if ($status) {
            $activeValue = ($status === 'active') ? 1 : 0;
            $builder = $builder->where('active', $activeValue);
        }

        return $builder->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Count users with filters
     */
    public function countUsersWithFilters(?string $role = null, ?int $programId = null, ?string $status = null): int
    {
        $builder = $this;

        if ($role) {
            $builder = $builder->where('role', $role);
        }

        if ($programId) {
            $builder = $builder->where('program_id', $programId);
        }

        if ($status) {
            $activeValue = ($status === 'active') ? 1 : 0;
            $builder = $builder->where('active', $activeValue);
        }

        return $builder->countAllResults();
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(int $uid): bool
    {
        $user = $this->find($uid);
        if (!$user) {
            return false;
        }

        $currentActive = (int) ($user['active'] ?? 0);
        $newActive = $currentActive === 1 ? 0 : 1;
        return $this->update($uid, ['active' => $newActive]);
    }

    /**
     * Bulk update user roles
     */
    public function bulkUpdateRoles(array $uids, string $role): bool
    {
        if (empty($uids)) {
            return false;
        }

        return $this->whereIn('uid', $uids)->update(['role' => $role]);
    }

    /**
     * Bulk update user active status
     */
    public function bulkUpdateStatus(array $uids, string $status): bool
    {
        if (empty($uids)) {
            return false;
        }

        $activeValue = ($status === 'active') ? 1 : 0;
        return $this->whereIn('uid', $uids)->update(['active' => $activeValue]);
    }
}
