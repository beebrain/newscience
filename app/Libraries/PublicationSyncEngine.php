<?php

namespace App\Libraries;

use App\Models\CvSyncLogModel;

class PublicationSyncEngine
{
    /**
     * @return array{success:bool,message:string,rr_to_ns?:array,ns_to_rr?:array,error?:string}
     */
    public static function reconcileForPersonnel(int $personnelId, string $canonicalEmail, string $trigger = 'auto'): array
    {
        $email = CvProfile::normalizeEmail($canonicalEmail);
        if ($email === '') {
            return ['success' => false, 'message' => 'missing email', 'error' => 'EMAIL_REQUIRED'];
        }
        if (! PublicationCatalog::isReady()) {
            return ['success' => false, 'message' => 'publication catalog schema missing', 'error' => 'SCHEMA_MISSING'];
        }

        $rr = ResearchRecordCvSyncClient::fetchPublicationsSyncBundle($email);
        if (! $rr['success']) {
            return ['success' => false, 'message' => $rr['message'] ?? 'fetch RR publications failed', 'error' => $rr['error'] ?? 'FETCH_FAILED'];
        }

        $rrStats = ['inserted' => 0, 'updated' => 0, 'linked' => 0, 'skipped_unchanged' => 0, 'contributors_synced' => 0];
        if (! empty($rr['publications']) && is_array($rr['publications'])) {
            $rrStats = PublicationCatalog::syncFromRrPayload($personnelId, $email, $rr['publications']);
        }

        $payload = PublicationCatalog::buildPayloadForPersonnel($personnelId);
        $push = ['success' => true, 'publications' => [], 'stats' => ['sent' => count($payload)]];
        if ($payload !== []) {
            $push = ResearchRecordCvSyncClient::pushPublicationsSyncBundle($email, $payload);
            if ($push['success'] && ! empty($push['publications']) && is_array($push['publications'])) {
                PublicationCatalog::syncFromRrPayload($personnelId, $email, $push['publications']);
            }
        }

        self::log($personnelId, $trigger, $rr['content_hash'] ?? null, [
            'rr_to_ns' => $rrStats,
            'ns_to_rr' => $push['stats'] ?? [],
            'push_ok'  => $push['success'] ?? false,
        ]);

        if (! ($push['success'] ?? false)) {
            return [
                'success'  => false,
                'message'  => $push['message'] ?? 'push NS publications to RR failed',
                'error'    => $push['error'] ?? 'PUSH_FAILED',
                'rr_to_ns' => $rrStats,
                'ns_to_rr' => $push,
            ];
        }

        return [
            'success'  => true,
            'message'  => 'publications reconciled',
            'rr_to_ns' => $rrStats,
            'ns_to_rr' => $push['stats'] ?? [],
        ];
    }

    /**
     * Push NS publication catalog to RR only (does not pull from RR first).
     *
     * @return array{success:bool,message:string,ns_to_rr?:array,error?:string,skipped?:bool}
     */
    public static function pushToResearchRecord(int $personnelId, string $canonicalEmail, string $trigger = 'push_manual'): array
    {
        $email = CvProfile::normalizeEmail($canonicalEmail);
        if ($email === '') {
            return ['success' => false, 'message' => 'missing email', 'error' => 'EMAIL_REQUIRED'];
        }
        if (! PublicationCatalog::isReady()) {
            return [
                'success' => true,
                'message' => 'publication catalog not ready',
                'skipped' => true,
                'ns_to_rr' => ['sent' => 0],
            ];
        }

        $payload = PublicationCatalog::buildPayloadForPersonnel($personnelId);
        if ($payload === []) {
            return [
                'success'  => true,
                'message'  => 'no publications in catalog',
                'ns_to_rr' => ['sent' => 0, 'inserted' => 0, 'updated' => 0, 'skipped_unchanged' => 0],
            ];
        }

        $push = ResearchRecordCvSyncClient::pushPublicationsSyncBundle($email, $payload);
        if ($push['success'] && ! empty($push['publications']) && is_array($push['publications'])) {
            PublicationCatalog::syncFromRrPayload($personnelId, $email, $push['publications']);
        }

        self::log($personnelId, $trigger, null, [
            'ns_to_rr' => $push['stats'] ?? ['sent' => count($payload)],
            'push_ok'  => $push['success'] ?? false,
        ]);

        if (! ($push['success'] ?? false)) {
            return [
                'success'  => false,
                'message'  => $push['message'] ?? 'push publications to RR failed',
                'error'    => $push['error'] ?? 'PUSH_FAILED',
                'ns_to_rr' => $push,
            ];
        }

        return [
            'success'  => true,
            'message'  => 'publications pushed',
            'ns_to_rr' => $push['stats'] ?? [],
        ];
    }

    private static function log(int $personnelId, string $trigger, ?string $rrHash, array $decisions): void
    {
        $log = new CvSyncLogModel();
        if (! $log->db->tableExists('cv_sync_log')) {
            return;
        }

        $log->insert([
            'personnel_id'    => $personnelId,
            'direction'       => 'publications_reconcile',
            'ns_content_hash' => null,
            'rr_content_hash' => $rrHash,
            'decisions_json'  => json_encode(['trigger' => $trigger] + $decisions, JSON_UNESCAPED_UNICODE),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
