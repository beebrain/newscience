<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AdminImpersonation;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Impersonation extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();

        if (! $this->currentActorIsSuperAdmin() && ! $this->isStopWhileImpersonating()) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function index(): string
    {
        $search = trim((string) $this->request->getGet('search'));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 20;

        $total = $this->countEligibleTargets($search);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $targets = $this->eligibleTargets($search, $perPage, ($page - 1) * $perPage);

        return view('admin/impersonation/index', [
            'page_title'      => 'Login As บุคลากร',
            'targets'         => $targets,
            'search'          => $search,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'total'           => $total,
            'hasPreviousPage' => $page > 1,
            'hasNextPage'     => $page < $totalPages,
            'isImpersonating' => AdminImpersonation::isActive(),
        ]);
    }

    public function start(int $uid)
    {
        $actor = $this->currentActor();
        if ($actor === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->recordStartAttempt();
        if (! $this->startAttemptAllowed()) {
            AdminImpersonation::logDenied($actor, null, 'too_many_attempts', $this->request);
            return redirect()->to(base_url('admin/impersonation'))
                ->with('error', 'มีการเริ่มใช้งานแทนถี่เกินไป กรุณารอสักครู่แล้วลองใหม่');
        }

        if (AdminImpersonation::isActive()) {
            AdminImpersonation::logDenied($actor, null, 'nested_impersonation_blocked', $this->request);
            return redirect()->to(base_url('admin/impersonation'))
                ->with('error', 'ไม่สามารถเริ่ม Login As ซ้อนกันได้ กรุณาหยุด session ปัจจุบันก่อน');
        }

        $target = $this->userModel->find($uid);
        $denyReason = $this->targetDenyReason($target);
        if ($denyReason !== null) {
            AdminImpersonation::logDenied($actor, is_array($target) ? $target : null, $denyReason, $this->request);
            return redirect()->to(base_url('admin/impersonation'))
                ->with('error', $this->denyMessage($denyReason));
        }

        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            $reason = 'Super Admin Login As';
        }

        $ok = AdminImpersonation::start($actor, $target, mb_substr($reason, 0, 1000), $this->request);
        if (! $ok) {
            return redirect()->to(base_url('admin/impersonation'))
                ->with('error', 'ไม่สามารถเริ่ม Login As ได้ เพราะบันทึก audit log ไม่สำเร็จ');
        }

        return redirect()->to(base_url('dashboard'))
            ->with('success', 'เริ่มใช้งานแทน ' . $this->userModel->getFullName($target) . ' แล้ว');
    }

    public function stop()
    {
        if (! AdminImpersonation::stop('manual')) {
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'ไม่สามารถคืน session เดิมได้ ระบบจึงออกจากระบบเพื่อความปลอดภัย');
        }

        return redirect()->to(base_url('admin/impersonation'))
            ->with('success', 'หยุดใช้งานแทนและกลับเป็น Super Admin แล้ว');
    }

    private function currentActor(): ?array
    {
        $uid = (int) session()->get('admin_id');
        if ($uid <= 0) {
            return null;
        }

        $user = $this->userModel->find($uid);
        if (! $user || ($user['role'] ?? '') !== 'super_admin') {
            return null;
        }

        return $user;
    }

    private function currentActorIsSuperAdmin(): bool
    {
        return $this->currentActor() !== null;
    }

    private function isStopWhileImpersonating(): bool
    {
        return AdminImpersonation::isActive()
            && trim(uri_string(), '/') === 'admin/impersonation/stop';
    }

    private function targetDenyReason($target): ?string
    {
        if (! is_array($target)) {
            return 'target_not_found';
        }

        $email = AdminImpersonation::normalizeEmail((string) ($target['email'] ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'target_email_invalid';
        }

        if (($target['role'] ?? '') === 'super_admin') {
            return 'target_super_admin';
        }

        $status = (string) ($target['status'] ?? '');
        $active = (int) ($target['active'] ?? ($status === 'active' ? 1 : 0));
        if ($status !== 'active' && $active !== 1) {
            return 'target_inactive';
        }

        // If needed, we can bypass the personnel database check for regular system users to enable impersonation from user list
        // if (! $this->isActivePersonnel((int) $target['uid'], $email)) {
        //     return 'target_not_personnel';
        // }

        return null;
    }

    private function denyMessage(string $reason): string
    {
        return [
            'target_not_found'      => 'ไม่พบผู้ใช้ที่ต้องการ Login As',
            'target_email_invalid'  => 'บัญชีเป้าหมายไม่มีอีเมลที่ถูกต้อง',
            'target_super_admin'    => 'ไม่อนุญาตให้ Login As บัญชี Super Admin',
            'target_inactive'       => 'ไม่อนุญาตให้ Login As บัญชีที่ปิดใช้งาน',
            'target_not_personnel'  => 'บัญชีนี้ไม่อยู่ในรายชื่อบุคลากร active ของคณะ',
        ][$reason] ?? 'ไม่สามารถเริ่ม Login As ด้วยบัญชีนี้ได้';
    }

    private function eligibleTargets(string $search, int $limit, int $offset): array
    {
        $builder = $this->baseEligibleBuilder($search)
            ->select($this->eligibleUserSelectColumns());
        $db = db_connect();
        if ($db->fieldExists('tf_name', 'user')) {
            $builder->orderBy('user.tf_name', 'ASC');
        }
        if ($db->fieldExists('tl_name', 'user')) {
            $builder->orderBy('user.tl_name', 'ASC');
        }
        $builder->limit($limit, $offset);

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['display_name'] = $this->userModel->getFullNameThaiForDisplay($row);
            $row['normalized_email'] = AdminImpersonation::normalizeEmail((string) ($row['email'] ?? ''));
            $row['is_active'] = ((string) ($row['status'] ?? '') === 'active') || ((int) ($row['active'] ?? 0) === 1);
        }
        unset($row);

        return $rows;
    }

    private function countEligibleTargets(string $search): int
    {
        $row = $this->baseEligibleBuilder($search)
            ->select('COUNT(DISTINCT user.uid) AS total', false)
            ->get()
            ->getRowArray();

        return (int) ($row['total'] ?? 0);
    }

    private function baseEligibleBuilder(string $search)
    {
        $emailExpr = 'LOWER(TRIM(user.email))';
        $builder = db_connect()->table('user')
            ->where('user.role !=', 'super_admin');

        $this->applyEligibleUserActiveFilter($builder);
        $builder->where('EXISTS (SELECT 1 FROM personnel WHERE personnel.status = \'active\' AND ' . $this->personnelMatchesUserSql($emailExpr) . ')', null, false);

        if ($search !== '') {
            $this->applyEligibleUserSearch($builder, $search);
        }

        return $builder;
    }

    private function applyEligibleUserActiveFilter($builder): void
    {
        $db = db_connect();
        if ($db->fieldExists('status', 'user')) {
            $builder->where('user.status', 'active');

            return;
        }
        if ($db->fieldExists('active', 'user')) {
            $builder->where('user.active', 1);
        }
    }

    private function applyEligibleUserSearch($builder, string $search): void
    {
        $db = db_connect();
        $builder->groupStart();
        if ($db->fieldExists('email', 'user')) {
            $builder->like('user.email', $search);
        }
        foreach (['login_uid', 'tf_name', 'tl_name', 'gf_name', 'gl_name'] as $field) {
            if ($db->fieldExists($field, 'user')) {
                $builder->orLike('user.' . $field, $search);
            }
        }
        $builder->groupEnd();
    }

    private function isActivePersonnel(int $uid, string $email): bool
    {
        $db = db_connect();
        $parts  = [];
        $params = ['active'];

        if ($db->fieldExists('user_uid', 'personnel')) {
            $parts[]  = 'user_uid = ?';
            $params[] = $uid;
        }

        $parts[]  = 'LOWER(TRIM(user_email)) = ?';
        $params[] = $email;
        $parts[]  = 'LOWER(TRIM(email)) = ?';
        $params[] = $email;

        $sql = 'SELECT id FROM personnel WHERE status = ? AND (' . implode(' OR ', $parts) . ') LIMIT 1';
        $row = $db->query($sql, $params)->getRowArray();

        return $row !== null;
    }

    /** SELECT คอลัมน์ user ที่มีจริงใน DB (production อาจไม่มี program_id ฯลฯ) */
    private function eligibleUserSelectColumns(): string
    {
        $db   = db_connect();
        $cols = ['user.uid', 'user.email', 'user.role'];

        foreach (['login_uid', 'title', 'tf_name', 'tl_name', 'gf_name', 'gl_name', 'active', 'status', 'program_id'] as $field) {
            if ($db->fieldExists($field, 'user')) {
                $cols[] = 'user.' . $field;
            }
        }

        return implode(', ', $cols);
    }

    /**
     * เงื่อนไขจับคู่ personnel ↔ user (อีเมลเป็นหลัก; user_uid ถ้า DB ยังมีคอลัมน์)
     *
     * @param string $userEmailExpr SQL expression เช่น LOWER(TRIM(user.email))
     */
    private function personnelMatchesUserSql(string $userEmailExpr): string
    {
        $parts = [
            "LOWER(TRIM(personnel.user_email)) = {$userEmailExpr}",
            "LOWER(TRIM(personnel.email)) = {$userEmailExpr}",
        ];

        if (db_connect()->fieldExists('user_uid', 'personnel')) {
            array_unshift($parts, 'personnel.user_uid = user.uid');
        }

        return '(' . implode(' OR ', $parts) . ')';
    }

    private function recordStartAttempt(): void
    {
        $attempts = session()->get('impersonation_start_attempts');
        $attempts = is_array($attempts) ? $attempts : [];
        $cutoff = time() - 600;
        $attempts = array_values(array_filter($attempts, static fn ($ts) => (int) $ts >= $cutoff));
        $attempts[] = time();
        session()->set('impersonation_start_attempts', $attempts);
    }

    private function startAttemptAllowed(): bool
    {
        $attempts = session()->get('impersonation_start_attempts');
        $attempts = is_array($attempts) ? $attempts : [];
        $cutoff = time() - 600;
        $recent = array_values(array_filter($attempts, static fn ($ts) => (int) $ts >= $cutoff));
        return count($recent) <= 5;
    }
}
