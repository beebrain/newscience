<?php

namespace App\Libraries;

use Config\ResearchRecordSso;

/**
 * สร้าง URL เข้า Research Record ผ่าน /auth/sso-entry
 */
class ResearchRecordSsoBridge
{
    public static function entryUrlForUser(array $user): ?string
    {
        $email = CvProfile::normalizeEmail((string) ($user['email'] ?? ''));
        if ($email === '') {
            return null;
        }

        return self::signedEntryUrl($email, self::displayNameFromUser($user));
    }

    /**
     * @param string $email              canonical email (personnel)
     * @param string $displayName        ชื่อแสดงใน RR
     * @param string|null $entryRedirect path ใน RR หลัง SSO เช่น /index.php/publications/create
     * @param string|null $returnAfterSave URL เต็มกลับ NS หลังบันทึก
     */
    public static function signedEntryUrl(
        string $email,
        string $displayName,
        ?string $entryRedirect = null,
        ?string $returnAfterSave = null
    ): ?string {
        $config = config(ResearchRecordSso::class);
        if (! $config->enabled || $config->baseUrl === '' || $config->sharedSecret === '') {
            return null;
        }

        $email = CvProfile::normalizeEmail($email);
        if ($email === '') {
            return null;
        }

        $entryUrl = rtrim($config->baseUrl, '/') . $config->ssoEntryPath;

        return self::buildSignedUrl(self::userPayload($email, $displayName, $entryRedirect, $returnAfterSave), $entryUrl, $config->sharedSecret);
    }

    /** @return array{email:string,name:string,exp:int,entry_redirect?:string,return_after_save?:string} */
    private static function userPayload(string $email, string $displayName, ?string $entryRedirect, ?string $returnAfterSave): array
    {
        $payload = [
            'email' => $email,
            'name'  => $displayName !== '' ? $displayName : 'User',
            'exp'   => time() + 120,
        ];
        if ($entryRedirect !== null && $entryRedirect !== '') {
            $payload['entry_redirect'] = $entryRedirect;
        }
        if ($returnAfterSave !== null && $returnAfterSave !== '') {
            $payload['return_after_save'] = $returnAfterSave;
        }

        return $payload;
    }

    public static function rrEntryPath(string $suffix): string
    {
        $config = config(ResearchRecordSso::class);
        $path   = '/' . ltrim($suffix, '/');

        if (str_contains($config->ssoEntryPath, '/index.php/')) {
            return '/index.php' . $path;
        }

        return $path;
    }

    /**
     * @param array{email:string,name:string,exp:int,entry_redirect?:string,return_after_save?:string}|array<string,mixed> $user
     */
    public static function buildSignedUrl(array $user, string $entryUrl, string $secret): string
    {
        $email = CvProfile::normalizeEmail((string) ($user['email'] ?? ''));
        $name  = (string) ($user['name'] ?? $user['tf_name'] ?? $user['first_name_th'] ?? 'User');

        $payload = [
            'email' => $email,
            'name'  => $name,
            'exp'   => (int) ($user['exp'] ?? (time() + 120)),
        ];
        if (! empty($user['entry_redirect'])) {
            $payload['entry_redirect'] = (string) $user['entry_redirect'];
        }
        if (! empty($user['return_after_save'])) {
            $payload['return_after_save'] = (string) $user['return_after_save'];
        }

        $payloadB64 = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $sigB64     = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, $secret, true));
        $token      = $payloadB64 . '.' . $sigB64;

        $sep = str_contains($entryUrl, '?') ? '&' : '?';

        return $entryUrl . $sep . 'token=' . $token;
    }

    private static function displayNameFromUser(array $user): string
    {
        return (string) ($user['tf_name'] ?? $user['first_name_th'] ?? $user['first_name_en'] ?? 'User');
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
