<?php

namespace App\Libraries;

use App\Models\AdminImpersonationLogModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\IncomingRequest;

class AdminImpersonation
{
    public const SESSION_ACTIVE = 'impersonation_active';
    public const SESSION_LOG_ID = 'impersonation_log_id';
    public const SESSION_STARTED_AT = 'impersonation_started_at';
    public const SESSION_ACTOR_ID = 'impersonator_admin_id';
    public const SESSION_ACTOR_EMAIL = 'impersonator_admin_email';
    public const SESSION_ACTOR_NAME = 'impersonator_admin_name';
    public const SESSION_ACTOR_ROLE = 'impersonator_admin_role';
    public const SESSION_ACTOR_LOGIN_VIA = 'impersonator_admin_login_via';
    public const SESSION_TARGET_ID = 'impersonation_target_id';
    public const SESSION_TARGET_EMAIL = 'impersonation_target_email';
    public const SESSION_TARGET_NAME = 'impersonation_target_name';
    public const SESSION_TARGET_ROLE = 'impersonation_target_role';

    private const SESSION_KEYS = [
        self::SESSION_ACTIVE,
        self::SESSION_LOG_ID,
        self::SESSION_STARTED_AT,
        self::SESSION_ACTOR_ID,
        self::SESSION_ACTOR_EMAIL,
        self::SESSION_ACTOR_NAME,
        self::SESSION_ACTOR_ROLE,
        self::SESSION_ACTOR_LOGIN_VIA,
        self::SESSION_TARGET_ID,
        self::SESSION_TARGET_EMAIL,
        self::SESSION_TARGET_NAME,
        self::SESSION_TARGET_ROLE,
    ];

    public static function isActive(): bool
    {
        return (bool) session()->get(self::SESSION_ACTIVE);
    }

    public static function start(array $actor, array $target, string $reason, IncomingRequest $request): bool
    {
        $session = session();
        $userModel = new UserModel();
        $actorEmail = self::normalizeEmail((string) ($actor['email'] ?? ''));
        $targetEmail = self::normalizeEmail((string) ($target['email'] ?? ''));
        $targetRole = self::sessionRoleFor($target);
        $targetName = $userModel->getFullName($target);
        $now = date('Y-m-d H:i:s');

        $logId = (new AdminImpersonationLogModel())->logEvent('impersonation_start', [
            'actor_uid'       => (int) ($actor['uid'] ?? 0),
            'actor_email'     => $actorEmail,
            'target_uid'      => (int) ($target['uid'] ?? 0),
            'target_email'    => $targetEmail,
            'target_role'     => $targetRole,
            'reason'          => $reason,
            'status'          => 'started',
            'ip_address'      => $request->getIPAddress(),
            'user_agent'      => substr((string) $request->getUserAgent(), 0, 1000),
            'session_id_hash' => self::sessionHash(),
            'started_at'      => $now,
            'context'         => [
                'actor_role' => $actor['role'] ?? '',
                'target_name' => $targetName,
            ],
        ]);

        if ($logId <= 0) {
            log_message('error', 'AdminImpersonation: audit log insert failed actor_uid=' . (int) ($actor['uid'] ?? 0) . ' target_uid=' . (int) ($target['uid'] ?? 0));
            return false;
        }

        $session->regenerate(true);
        $session->set([
            self::SESSION_ACTIVE       => true,
            self::SESSION_LOG_ID       => $logId,
            self::SESSION_STARTED_AT   => $now,
            self::SESSION_ACTOR_ID     => (int) ($actor['uid'] ?? 0),
            self::SESSION_ACTOR_EMAIL  => $actorEmail,
            self::SESSION_ACTOR_NAME   => $userModel->getFullName($actor),
            self::SESSION_ACTOR_ROLE   => (string) ($actor['role'] ?? 'super_admin'),
            self::SESSION_ACTOR_LOGIN_VIA => (string) ($session->get('admin_login_via') ?? 'uru_portal_oauth'),
            self::SESSION_TARGET_ID    => (int) ($target['uid'] ?? 0),
            self::SESSION_TARGET_EMAIL => $targetEmail,
            self::SESSION_TARGET_NAME  => $targetName,
            self::SESSION_TARGET_ROLE  => $targetRole,
            'admin_logged_in'          => true,
            'admin_id'                 => (int) ($target['uid'] ?? 0),
            'admin_email'              => $targetEmail,
            'admin_name'               => $targetName,
            'admin_role'               => $targetRole,
            'admin_login_via'          => 'super_admin_impersonation',
        ]);
        $session->remove([
            'admin_access_token',
            'admin_refresh_token',
            'admin_token_expires',
            'super_admin_member_portal',
        ]);

        return true;
    }

    public static function stop(string $endedBy = 'manual'): bool
    {
        $session = session();
        if (! self::isActive()) {
            return true;
        }

        $actorId = (int) $session->get(self::SESSION_ACTOR_ID);
        $actorEmail = self::normalizeEmail((string) $session->get(self::SESSION_ACTOR_EMAIL));
        $actorName = (string) $session->get(self::SESSION_ACTOR_NAME);
        $actorRole = (string) $session->get(self::SESSION_ACTOR_ROLE);
        $actorLoginVia = (string) $session->get(self::SESSION_ACTOR_LOGIN_VIA);
        $logId = (int) $session->get(self::SESSION_LOG_ID);

        if ($actorId <= 0 || $actorEmail === '') {
            log_message('error', 'AdminImpersonation: restore failed, missing actor session data');
            if ($logId > 0) {
                (new AdminImpersonationLogModel())->close($logId, 'restore_failed', 'ended_error');
            }
            self::clearSessionKeys();
            $session->destroy();
            return false;
        }

        if ($logId > 0) {
            (new AdminImpersonationLogModel())->close($logId, $endedBy);
        }

        self::clearSessionKeys();
        $session->regenerate(true);

        $session->set([
            'admin_logged_in' => true,
            'admin_id'        => $actorId,
            'admin_email'     => $actorEmail,
            'admin_name'      => $actorName,
            'admin_role'      => $actorRole ?: 'super_admin',
            'admin_login_via' => $actorLoginVia ?: 'uru_portal_oauth',
        ]);
        $session->remove([
            'admin_access_token',
            'admin_refresh_token',
            'admin_token_expires',
        ]);

        return true;
    }

    public static function endIfActive(string $endedBy = 'logout'): void
    {
        if (! self::isActive()) {
            return;
        }

        $logId = (int) session()->get(self::SESSION_LOG_ID);
        if ($logId > 0) {
            (new AdminImpersonationLogModel())->close($logId, $endedBy);
        }
        self::clearSessionKeys();
    }

    public static function logDenied(array $actor, ?array $target, string $reason, IncomingRequest $request): void
    {
        (new AdminImpersonationLogModel())->logEvent('impersonation_denied', [
            'actor_uid'       => (int) ($actor['uid'] ?? 0),
            'actor_email'     => self::normalizeEmail((string) ($actor['email'] ?? '')),
            'target_uid'      => $target ? (int) ($target['uid'] ?? 0) : null,
            'target_email'    => $target ? self::normalizeEmail((string) ($target['email'] ?? '')) : null,
            'target_role'     => $target['role'] ?? null,
            'reason'          => $reason,
            'status'          => 'denied',
            'ip_address'      => $request->getIPAddress(),
            'user_agent'      => substr((string) $request->getUserAgent(), 0, 1000),
            'session_id_hash' => self::sessionHash(),
            'context'         => ['uri' => (string) current_url()],
        ]);
    }

    public static function clearSessionKeys(): void
    {
        session()->remove(self::SESSION_KEYS);
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function sessionRoleFor(array $user): string
    {
        return ! empty($user['admin']) ? 'admin' : (string) ($user['role'] ?? 'user');
    }

    private static function sessionHash(): string
    {
        return hash('sha256', (string) session_id());
    }
}
