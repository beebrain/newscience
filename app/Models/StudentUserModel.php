<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentUserModel extends Model
{
    protected $table = 'student_user';
    protected $primaryKey = 'id';
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
        'thai_lastname',
        'program_id',
        'profile_image',
        'role',
        'status',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[student_user.email,id,{id}]',
        'password' => 'permit_empty|min_length[6]',
        'role' => 'required|in_list[student,club,admin_student]',
        'program_id' => 'permit_empty|integer',
    ];

    /**
     * หาหรือสร้าง student จากข้อมูล URU Portal OAuth (/me endpoint)
     * ใช้ email เป็น key หลักในการ identify user เสมอ
     * — ถ้า student มีอยู่แล้ว (email ตรงกัน) แต่ยังไม่มี login_uid → update login_uid
     * — ถ้าไม่มีเลย → สร้าง student ใหม่ (role=student, status=active)
     *
     * @param array $portalUser ข้อมูลจาก /me endpoint ต้องมี email; ควรมี login_uid/username
     * @return array|null student array หรือ null ถ้าล้มเหลว
     */
    public function findOrCreateFromPortalUser(array $portalUser): ?array
    {
        $email    = strtolower(trim($portalUser['email'] ?? ''));
        $loginUid = trim($portalUser['login_uid'] ?? $portalUser['username'] ?? $portalUser['code'] ?? '');

        if ($email === '') {
            log_message('error', 'StudentUserModel::findOrCreateFromPortalUser email empty');
            return null;
        }

        // ค้นหาด้วย email ก่อนเสมอ (email คือ key หลัก)
        $student = $this->findByEmail($email);

        // ถ้าไม่พบด้วย email ลองหาด้วย login_uid
        if (!$student && $loginUid !== '') {
            $student = $this->db->table($this->table)
                ->where('login_uid', $loginUid)
                ->limit(1)
                ->get()
                ->getRowArray() ?: null;
        }

        $updateData = [
            'email'         => $email,
            'title'         => trim($portalUser['title'] ?? ''),
            'gf_name'       => trim($portalUser['gf_name'] ?? $portalUser['first_name_en'] ?? $portalUser['firstname_en'] ?? ''),
            'gl_name'       => trim($portalUser['gl_name'] ?? $portalUser['last_name_en'] ?? $portalUser['lastname_en'] ?? ''),
            'tf_name'       => trim($portalUser['tf_name'] ?? $portalUser['first_name_th'] ?? $portalUser['firstname_th'] ?? ''),
            'tl_name'       => trim($portalUser['tl_name'] ?? $portalUser['last_name_th'] ?? $portalUser['lastname_th'] ?? ''),
            'th_name'       => trim($portalUser['th_name'] ?? $portalUser['thai_name'] ?? $portalUser['first_name_th'] ?? $portalUser['firstname_th'] ?? ''),
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

        if ($student) {
            // student มีอยู่แล้ว — update ข้อมูลและ login_uid (ถ้ายังว่าง)
            $existingLoginUid = trim($student['login_uid'] ?? '');
            if ($existingLoginUid === '' && $loginUid !== '') {
                log_message('info', 'StudentUserModel::findOrCreateFromPortalUser first login, updating login_uid=' . $loginUid . ' for id=' . $student['id']);
            }
            $this->update($student['id'], $updateData);
            return $this->find($student['id']);
        }

        // ไม่มี student — สร้างใหม่
        $updateData['password'] = null;
        $updateData['role']     = 'student';
        $updateData['status']   = 'active';
        $id = $this->insert($updateData);
        if (!$id) {
            log_message('error', 'StudentUserModel::findOrCreateFromPortalUser insert failed email=' . $email);
            return null;
        }
        log_message('info', 'StudentUserModel::findOrCreateFromPortalUser created new student id=' . $id . ' email=' . $email . ' login_uid=' . $loginUid);
        return $this->find($id);
    }

    /**
     * Find by email
     */
    public function findByEmail(string $email): ?array
    {
        $row = $this->where('email', $email)->first();
        return is_array($row) ? $row : null;
    }

    /**
     * Find by email or login_uid
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

    public function verifyPassword(string $password, $hash): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }
        return password_verify($password, $hash);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get full name (Thai preferred)
     */
    public function getFullName(array $row): string
    {
        $first = trim($row['th_name'] ?? $row['tf_name'] ?? '');
        $last  = trim($row['thai_lastname'] ?? $row['tl_name'] ?? '');
        $full  = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }
        $firstEn = trim($row['gf_name'] ?? '');
        $lastEn  = trim($row['gl_name'] ?? '');
        return trim($firstEn . ' ' . $lastEn) ?: $row['email'] ?? '';
    }

    /**
     * Get club users (student_user with role=club)
     */
    public function getClubs()
    {
        return $this->where('role', 'club')->where('status', 'active')->findAll();
    }

    /**
     * Get students by role
     */
    public function getByRole(string $role): array
    {
        return $this->where('role', $role)->findAll();
    }

    /**
     * Get students by program
     */
    public function getByProgram(int $programId): array
    {
        return $this->where('program_id', $programId)->findAll();
    }

    /**
     * Get students by role and program
     */
    public function getByRoleAndProgram(string $role, int $programId): array
    {
        return $this->where('role', $role)->where('program_id', $programId)->findAll();
    }

    /**
     * Get admin students only
     */
    public function getAdminStudents(): array
    {
        return $this->where('role', 'admin_student')->where('status', 'active')->findAll();
    }

    /**
     * Get regular students only
     */
    public function getRegularStudents(): array
    {
        return $this->where('role', 'student')->where('status', 'active')->findAll();
    }

    /**
     * Get club members only
     */
    public function getClubMembers(): array
    {
        return $this->where('role', 'club')->where('status', 'active')->findAll();
    }

    /**
     * Check if student is admin student
     */
    public function isAdminStudent(array $student): bool
    {
        return ($student['role'] ?? '') === 'admin_student';
    }

    /**
     * Check if student is club member
     */
    public function isClubMember(array $student): bool
    {
        return ($student['role'] ?? '') === 'club';
    }

    /**
     * Check if student can manage program
     * (ตรวจสอบว่า student มีสิทธิ์จัดการ program นี้หรือไม่)
     */
    public function canManageProgram(array $user, int $programId): bool
    {
        // Admin student can manage their own program
        if ($this->isAdminStudent($user) && (int)($user['program_id'] ?? 0) === $programId) {
            return true;
        }

        // ถ้า user เป็น admin/editor/faculty_admin ก็สามารถจัดการได้
        if (in_array($user['role'] ?? '', ['admin', 'editor', 'faculty_admin', 'super_admin'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Search students by name or email
     */
    public function searchStudents(string $query, ?int $programId = null): array
    {
        $builder = $this->like('email', $query)
            ->orLike('gf_name', $query)
            ->orLike('gl_name', $query)
            ->orLike('th_name', $query)
            ->orLike('thai_lastname', $query);

        if ($programId) {
            $builder->where('program_id', $programId);
        }

        return $builder->findAll();
    }

    /**
     * Get students with pagination and filters
     */
    public function getStudentsWithFilters(?string $role = null, ?int $programId = null, ?string $status = null, int $limit = 20, int $offset = 0): array
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
     * Count students with filters
     */
    public function countStudentsWithFilters(?string $role = null, ?int $programId = null, ?string $status = null): int
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
     * Toggle student active status
     */
    public function toggleStatus(int $id): bool
    {
        $student = $this->find($id);
        if (!$student) {
            return false;
        }

        $currentActive = (int) ($student['active'] ?? 0);
        $newActive = $currentActive === 1 ? 0 : 1;
        return $this->update($id, ['active' => $newActive]);
    }

    /**
     * Bulk update student roles
     */
    public function bulkUpdateRoles(array $ids, string $role): bool
    {
        if (empty($ids)) {
            return false;
        }

        return $this->whereIn('id', $ids)->update(['role' => $role]);
    }

    /**
     * Bulk update student active status
     */
    public function bulkUpdateStatus(array $ids, string $status): bool
    {
        if (empty($ids)) {
            return false;
        }

        $activeValue = ($status === 'active') ? 1 : 0;
        return $this->whereIn('id', $ids)->update(['active' => $activeValue]);
    }
}
