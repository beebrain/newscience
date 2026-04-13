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
        'status' => 'permit_empty|in_list[active,inactive,pending]',
    ];

    /**
     * หาหรือสร้าง student จากข้อมูล URU Portal OAuth (/me endpoint)
     * ใช้ email เป็น key หลักในการ identify user เสมอ
     * — ถ้า student มีอยู่แล้ว (email ตรงกัน) แต่ยังไม่มี login_uid → update login_uid
     * — ถ้าไม่มีเลย → สร้าง student ใหม่ (role=student, status=active)
     * — ถ้าเป็น pending (สร้างจากอีเมลล่วงหน้า) → อัปเดตจาก Portal แล้วตั้ง status=active; ไม่เปลี่ยน role=club
     * — อัปเดตข้อมูลจาก Portal ทุกครั้งที่ login (ไม่ข้ามแม้ student มีอยู่แล้ว)
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

        // ค้นหาด้วย email ก่อนเสมอ (email เป็นเงื่อนไขหลักสำหรับการอัปเดต)
        $student = $this->findByEmail($email);
        $foundByEmail = $student !== null;

        // ถ้าไม่พบด้วย email ลองหาด้วย login_uid
        if (!$student && $loginUid !== '') {
            $student = $this->db->table($this->table)
                ->where('login_uid', $loginUid)
                ->limit(1)
                ->get()
                ->getRowArray() ?: null;
        }

        // Mapping จาก URU Portal /me API (code, prefix_*, first_name_en/th, last_name_en/th, email, ...)
        $updateData = [
            'email'   => $email,
            'title'   => trim($portalUser['title'] ?? $portalUser['prefix_th'] ?? $portalUser['prefix_en'] ?? ''),
            'gf_name' => trim($portalUser['gf_name'] ?? $portalUser['first_name_en'] ?? $portalUser['firstname_en'] ?? ''),
            'gl_name' => trim($portalUser['gl_name'] ?? $portalUser['last_name_en'] ?? $portalUser['lastname_en'] ?? ''),
            'tf_name' => trim($portalUser['tf_name'] ?? $portalUser['first_name_th'] ?? $portalUser['firstname_th'] ?? $portalUser['th_name'] ?? $portalUser['thai_name'] ?? ''),
            'tl_name' => trim($portalUser['tl_name'] ?? $portalUser['last_name_th'] ?? $portalUser['lastname_th'] ?? $portalUser['thai_lastname'] ?? ''),
        ];

        // อัปเดต login_uid เสมอถ้า Portal ส่งมา (รวมถึงกรณี login ครั้งแรกที่ login_uid ยังว่าง)
        if ($loginUid !== '') {
            $updateData['login_uid'] = $loginUid;
        }

        // ไม่อัปเดต profile_image จาก API (ยกเว้นตามข้อกำหนด — ใช้เฉพาะที่ user อัปโหลดหรือมีอยู่แล้ว)

        if ($student) {
            // student มีอยู่แล้ว — อัปเดตทุกครั้งที่ login (sync ข้อมูลจาก Portal)
            $existingLoginUid = trim($student['login_uid'] ?? '');
            if ($existingLoginUid === '' && $loginUid !== '') {
                log_message('info', 'StudentUserModel::findOrCreateFromPortalUser first login, updating login_uid=' . $loginUid . ' for id=' . $student['id']);
            }
            if (($student['status'] ?? '') === 'pending') {
                $updateData['status'] = 'active';
            }
            $onlyAllowed = array_intersect_key($updateData, array_flip($this->allowedFields));
            $id = (int) ($student['id'] ?? 0);
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'StudentUserModel::findOrCreateFromPortalUser [update_student_user] before update | ' . json_encode([
                    'email' => $email,
                    'found_by_email' => $foundByEmail,
                    'id' => $id,
                    'data' => $onlyAllowed,
                ]));
            }
            $ok = false;
            if ($foundByEmail) {
                $ok = $this->db->table($this->table)->where('email', $email)->update($onlyAllowed);
                $updated = $this->findByEmail($email);
            } elseif ($id >= 1) {
                $ok = $this->update($id, $onlyAllowed);
                $updated = $this->find($id);
            } else {
                $ok = $this->db->table($this->table)->where('email', $email)->update($onlyAllowed);
                $updated = $this->findByEmail($email);
            }
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'StudentUserModel::findOrCreateFromPortalUser [update_student_user] after update | ' . json_encode([
                    'email' => $email,
                    'success' => $ok,
                    'updated_id' => $updated['id'] ?? null,
                ]));
            } elseif (!$ok) {
                log_message('error', 'StudentUserModel::findOrCreateFromPortalUser update failed email=' . $email);
            }
            return $updated;
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
        $t = trim($login);
        if ($t === '') {
            return null;
        }
        $emailNorm = strtolower($t);
        if (filter_var($emailNorm, FILTER_VALIDATE_EMAIL)) {
            $byEmail = $this->findByEmail($emailNorm);
            if ($byEmail !== null) {
                return $byEmail;
            }
        }
        $row = $this->where('login_uid', $t)->first();
        return is_array($row) ? $row : null;
    }

    /**
     * สร้างแถวนักศึกษารอเปิดใช้จากอีเมล (สโมสรเพิ่มผู้มีสิทธิ์ก่อนมีบัญชี Portal)
     * ถ้ามีอีเมลนี้แล้ว คืนแถวเดิม
     */
    public function createPendingInviteByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        $existing = $this->findByEmail($email);
        if ($existing !== null) {
            return $existing;
        }
        $this->skipValidation(true);
        $id = $this->insert([
            'email'    => $email,
            'password' => null,
            'role'     => 'student',
            'status'   => 'pending',
        ]);
        $this->skipValidation(false);
        if (! $id) {
            return null;
        }

        return $this->find((int) $id);
    }

    /**
     * รายชื่อนักศึกษา active สำหรับ dropdown (จัดการผู้มีสิทธิ์บาร์โค้ด)
     */
    public function getListForDropdown(): array
    {
        return $this->where('status', 'active')->orderBy('email', 'ASC')->findAll();
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
     * ชื่อสำหรับแสดงในระบบ — ถ้ามีชื่อภาษาไทย (tf_name, tl_name) ให้แสดงชื่อไทยก่อนเสมอ
     * Fallback: ชื่ออังกฤษ (gf_name, gl_name) หรือ email
     */
    public function getFullName(array $row): string
    {
        $first = trim($row['tf_name'] ?? '');
        $last  = trim($row['tl_name'] ?? '');
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
        if (($user['role'] ?? '') === 'super_admin') {
            return true;
        }
        if ($this->isAdminStudent($user) && (int) ($user['program_id'] ?? 0) === $programId) {
            return true;
        }
        if (in_array($user['role'] ?? '', ['admin', 'editor', 'faculty_admin'], true)) {
            $mgrPid = (int) ($user['program_id'] ?? 0);
            if ($programId === 0) {
                return true;
            }

            return $mgrPid === $programId;
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
            ->orLike('tf_name', $query)
            ->orLike('tl_name', $query);

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

        if ($status !== null && $status !== '') {
            $builder = $builder->where('status', $status);
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

        if ($status !== null && $status !== '') {
            $builder = $builder->where('status', $status);
        }

        return $builder->countAllResults();
    }

    /**
     * Toggle student status active <-> inactive (ไม่รวม pending)
     */
    public function toggleStatus(int $id): bool
    {
        $student = $this->find($id);
        if (! $student) {
            return false;
        }

        $s = $student['status'] ?? 'active';
        if ($s === 'pending') {
            return false;
        }
        $newStatus = $s === 'active' ? 'inactive' : 'active';

        return $this->update($id, ['status' => $newStatus]);
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
        if (! in_array($status, ['active', 'inactive', 'pending'], true)) {
            return false;
        }

        return $this->whereIn('id', $ids)->update(['status' => $status]);
    }
}
