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
        'status',
        'edoc',
        'admin_edoc'
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
     * รายการผู้ใช้สำหรับฟอร์มบุคลากร (autocomplete)
     *
     * @param bool $excludeExisting ตัดผู้ที่ถูกเชื่อมกับ personnel.user_uid แล้วหรือไม่
     * @return array<int, array{uid:int, email:string, name:string, name_en:string, is_linked:bool}>
     */
    public function getListForPersonnel(bool $excludeExisting = true): array
    {
        $columns = ['uid', 'email'];
        $optional = ['title', 'th_name', 'thai_name', 'thai_lastname', 'tf_name', 'tl_name', 'gf_name', 'gl_name'];
        foreach ($optional as $column) {
            if ($this->db->fieldExists($column, $this->table)) {
                $columns[] = $column;
            }
        }

        $builder = $this->select($columns);

        // ดึงรายชื่อ user_uid ที่ถูกผูกกับ personnel แล้ว
        $linkedUids = [];
        if ($this->db->tableExists('personnel')) {
            $existing = $this->db->table('personnel')
                ->select('user_uid')
                ->where('user_uid IS NOT NULL')
                ->get()
                ->getResultArray();
            $linkedUids = array_values(array_filter(array_map(fn($row) => (int) ($row['user_uid'] ?? 0), $existing)));
        }

        if ($excludeExisting && $linkedUids !== []) {
            $builder->whereNotIn('uid', $linkedUids);
        }

        $orderColumns = [];
        foreach (['th_name', 'thai_name', 'tf_name', 'gf_name'] as $column) {
            if ($this->db->fieldExists($column, $this->table)) {
                $orderColumns[] = $column;
            }
        }
        if ($orderColumns === []) {
            $orderColumns[] = 'email';
        }
        foreach ($orderColumns as $column) {
            $builder->orderBy($column);
        }

        $users = $builder->findAll();

        return array_map(function (array $user) use ($linkedUids) {
            $nameTh = trim($this->getFullName($user));
            $nameEn = trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? ''));
            $uid = (int) ($user['uid'] ?? 0);

            return [
                'uid'       => $uid,
                'email'     => $user['email'] ?? '',
                'name'      => $nameTh,
                'name_en'   => $nameEn,
                'is_linked' => in_array($uid, $linkedUids, true),
            ];
        }, $users);
    }

    /**
     * Get admins only
     */
    public function getAdmins()
    {
        return $this->where('role', 'admin')->where('status', 'active')->findAll();
    }

    /**
     * หาหรือสร้าง user จากข้อมูล URU Portal OAuth (/me endpoint)
     * ใช้ email เป็น key หลักในการ identify user เสมอ
     * — ถ้า user มีอยู่แล้วในฐานข้อมูล (email ตรงกัน) แต่ยังไม่มี login_uid → update login_uid
     * — ถ้าไม่มีเลย → สร้าง user ใหม่ (role=user, status=active)
     *
     * @param array $portalUser ข้อมูลจาก /me endpoint ต้องมี email; ควรมี login_uid/username
     * @return array|null user array หรือ null ถ้าล้มเหลว
     */
    public function findOrCreateFromPortalUser(array $portalUser): ?array
    {
        $email    = strtolower(trim($portalUser['email'] ?? ''));
        $loginUid = trim($portalUser['login_uid'] ?? $portalUser['username'] ?? $portalUser['code'] ?? '');

        if ($email === '') {
            log_message('error', 'UserModel::findOrCreateFromPortalUser email empty');
            return null;
        }

        // ค้นหาด้วย email ก่อนเสมอ (email คือ key หลัก)
        $user = $this->findByEmail($email);

        // ถ้าไม่พบด้วย email ลองหาด้วย login_uid (กรณีข้อมูลเก่าที่ email อาจต่างกัน)
        if (!$user && $loginUid !== '') {
            $user = $this->findByLoginUid($loginUid);
        }

        $updateData = [
            'email'         => $email,
            'title'         => trim($portalUser['title'] ?? ''),
            'gf_name'       => trim($portalUser['gf_name'] ?? $portalUser['first_name_en'] ?? $portalUser['firstname_en'] ?? ''),
            'gl_name'       => trim($portalUser['gl_name'] ?? $portalUser['last_name_en'] ?? $portalUser['lastname_en'] ?? ''),
            'tf_name'       => trim($portalUser['tf_name'] ?? $portalUser['first_name_th'] ?? $portalUser['firstname_th'] ?? ''),
            'tl_name'       => trim($portalUser['tl_name'] ?? $portalUser['last_name_th'] ?? $portalUser['lastname_th'] ?? ''),
            'th_name'       => trim($portalUser['th_name'] ?? $portalUser['thai_name'] ?? $portalUser['first_name_th'] ?? $portalUser['firstname_th'] ?? ''),
            'thai_name'     => trim($portalUser['thai_name'] ?? $portalUser['first_name_th'] ?? $portalUser['firstname_th'] ?? ''),
            'thai_lastname' => trim($portalUser['thai_lastname'] ?? $portalUser['last_name_th'] ?? $portalUser['lastname_th'] ?? ''),
        ];

        // อัปเดต login_uid เสมอถ้า Portal ส่งมา (รวมถึงกรณี login ครั้งแรกที่ login_uid ยังว่าง)
        if ($loginUid !== '') {
            $updateData['login_uid'] = $loginUid;
        }

        $profileImage = trim($portalUser['profile_image'] ?? $portalUser['avatar'] ?? $portalUser['picture'] ?? '');
        if ($profileImage !== '') {
            $updateData['profile_image'] = $profileImage;
        }

        if ($user) {
            // user มีอยู่แล้ว — update ข้อมูลและ login_uid (ถ้ายังว่าง)
            $existingLoginUid = trim($user['login_uid'] ?? '');
            if ($existingLoginUid === '' && $loginUid !== '') {
                log_message('info', 'UserModel::findOrCreateFromPortalUser first login, updating login_uid=' . $loginUid . ' for uid=' . $user['uid']);
            }
            $this->update($user['uid'], $updateData);
            return $this->find($user['uid']);
        }

        // ไม่มี user — สร้างใหม่
        $updateData['password'] = null;
        $updateData['role']     = 'user';
        $updateData['status']   = 'active';
        $uid = $this->insert($updateData);
        if (!$uid) {
            log_message('error', 'UserModel::findOrCreateFromPortalUser insert failed email=' . $email);
            return null;
        }
        log_message('info', 'UserModel::findOrCreateFromPortalUser created new user uid=' . $uid . ' email=' . $email);
        return $this->find($uid);
    }

    /**
     * หาหรือสร้าง user จากข้อมูล API (Portal/Edoc SSO) — เมื่อได้รับ JSON จาก Edoc จะ update ลง table user
     * คืน array user หรือ null ถ้าไม่พบและสร้างไม่ได้
     * @param array $apiUser ต้องมี key: email; ควรมี login_uid/code, ชื่อ-นามสกุล (gf_name, gl_name, thai_name, thai_lastname หรือ first_name_th, last_name_th ฯลฯ)
     */
    public function findOrCreateFromApiUser(array $apiUser): ?array
    {
        $email = strtolower(trim($apiUser['email'] ?? ''));
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
