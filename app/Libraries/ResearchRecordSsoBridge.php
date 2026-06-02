<?php

namespace App\Libraries;

use Config\ResearchRecordSso;

/**
 * สร้าง URL เข้า Research Record ผ่าน /auth/sso-entry (ใช้ร่วมกัน OAuth + go-research-record)
 */
class ResearchRecordSsoBridge
{
    public static function entryUrlForUser(array $user): ?string
    {
        $config = config(ResearchRecordSso::class);
        if (! $config->enabled || $config->baseUrl === '' || $config->sharedSecret === '') {
            return null;
        }

        $entryUrl = rtrim($config->baseUrl, '/') . $config->ssoEntryPath;

        return self::buildSignedUrl($user, $entryUrl, $config->sharedSecret);
    }

    public static function buildSignedUrl(array $user, string $entryUrl, string $secret): string
    {
        $email = $user['email'] ?? '';
        $name  = $user['tf_name'] ?? $user['first_name_th'] ?? $user['first_name_en'] ?? 'User';

        $payload = [
            'email' => $email,
            'name'  => $name,
            'exp'   => time() + 120,
        ];
        $payloadB64 = self::base64UrlEncode(json_encode($payload));
        $sigB64     = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, $secret, true));
        $token      = $payloadB64 . '.' . $sigB64;

        $sep = str_contains($entryUrl, '?') ? '&' : '?';

        // base64url + '.' ปลอดภัยใน query — หลีกเลี่ยง double-encoding จาก proxy/IIS
        return $entryUrl . $sep . 'token=' . $token;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
