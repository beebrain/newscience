<?php

namespace App\Libraries;

use App\Models\CvEntryModel;
use App\Models\CvSectionModel;

/**
 * หารายการ CV ซ้ำในหัวข้อ research/articles ตาม DOI / rr_publication_id / sync_external_key
 */
final class CvPublicationDedupe
{
    /**
     * @param array<string,mixed> $candidateMeta metadata ที่จะบันทึก (มี doi / rr_publication_id / sync_external_key)
     */
    public static function findDuplicateEntryId(int $personnelId, array $candidateMeta, int $ignoreEntryId = 0): ?int
    {
        $cDoi = PublicationIdentity::normalizedDoiFromMetadata($candidateMeta);
        $cRid = (int) ($candidateMeta['rr_publication_id'] ?? 0);
        $cEk  = trim((string) ($candidateMeta['sync_external_key'] ?? ''));
        if ($cEk === '' && $cDoi !== '') {
            $pk = PublicationIdentity::publicationExternalKeyFromNormalizedDoi($cDoi);
            if ($pk !== null) {
                $cEk = $pk;
            }
        }
        if ($cEk === '' && $cRid > 0) {
            $cEk = (string) PublicationIdentity::publicationExternalKeyFromRrId($cRid);
        }
        if ($cDoi === '' && $cRid <= 0 && $cEk === '') {
            return null;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('cv_sections') || ! CvEntryModel::isTablePresent($db)) {
            return null;
        }

        $sectionModel = new CvSectionModel();
        $sections     = $sectionModel->where('personnel_id', $personnelId)
            ->groupStart()
            ->where('type', 'research')
            ->orWhere('type', 'articles')
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        if ($sections === []) {
            return null;
        }

        $entryModel = new CvEntryModel();
        foreach ($sections as $sec) {
            $sid = (int) ($sec['id'] ?? 0);
            if ($sid <= 0) {
                continue;
            }
            foreach ($entryModel->where('section_id', $sid)->findAll() as $row) {
                $eid = (int) ($row['id'] ?? 0);
                if ($eid <= 0 || $eid === $ignoreEntryId) {
                    continue;
                }
                $m = CvEntryModel::decodeMetadata($row['metadata'] ?? null);
                if ($cRid > 0 && (int) ($m['rr_publication_id'] ?? 0) === $cRid) {
                    return $eid;
                }
                $rowDoi = PublicationIdentity::normalizedDoiFromMetadata($m);
                if ($cDoi !== '' && $rowDoi !== '' && $cDoi === $rowDoi) {
                    return $eid;
                }
                $rowEk = trim((string) ($m['sync_external_key'] ?? ''));
                if ($rowEk === '' && $rowDoi !== '') {
                    $p2 = PublicationIdentity::publicationExternalKeyFromNormalizedDoi($rowDoi);
                    if ($p2 !== null) {
                        $rowEk = $p2;
                    }
                }
                if ($rowEk === '' && (int) ($m['rr_publication_id'] ?? 0) > 0) {
                    $rowEk = (string) PublicationIdentity::publicationExternalKeyFromRrId((int) $m['rr_publication_id']);
                }
                if ($cEk !== '' && $rowEk !== '' && $cEk === $rowEk) {
                    return $eid;
                }
            }
        }

        return null;
    }
}
