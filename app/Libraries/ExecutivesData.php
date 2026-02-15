<?php

namespace App\Libraries;

use App\Models\PersonnelModel;
use App\Models\ProgramModel;
use App\Models\PersonnelProgramModel;

/**
 * Shared data logic for executives/organization structure.
 * Used by Api::executives() and optionally by Pages (for server-side fallback).
 */
class ExecutivesData
{
    /**
     * Position tier: 1=คณบดี, 2=รองคณบดี, 3=ผู้ช่วยคณบดี, 4=อื่นๆ
     */
    public static function personnelPositionTier(string $position): int
    {
        $p = $position ?: '';
        // Tier 4: หัวหน้าสำนักงาน / หัวหน้าหน่วยการจัดการงานวิจัย — ไม่ถือเป็นคณบดี (แล้ว filterHeadOffice/filterHeadResearch จะแยก)
        if (mb_strpos($p, 'หัวหน้าสำนักงาน') !== false
            || mb_strpos($p, 'หัวหน้าหน่วยจัดการ') !== false
            || mb_strpos($p, 'หัวหน้าหน่วยการจัดการงานวิจัย') !== false
            || mb_strpos($p, 'หัวหน้าหน่วยวิจัย') !== false) {
            return 4;
        }
        if (mb_strpos($p, 'ผู้อำนวยการ') !== false) {
            return 4;
        }
        $hasDean = mb_strpos($p, 'คณบดี') !== false;
        $hasVice = mb_strpos($p, 'รอง') !== false;
        $hasAssistant = mb_strpos($p, 'ผู้ช่วย') !== false;

        if ($hasDean && $hasVice && !$hasAssistant) {
            return 2;
        }
        if ($hasDean && $hasAssistant) {
            return 3;
        }
        if ($hasDean) {
            return 1;
        }
        return 4;
    }

    public static function personnelPositionTierEn(string $position): int
    {
        $p = strtolower($position ?: '');
        if (strpos($p, 'head of office') !== false || strpos($p, 'head of research') !== false || strpos($p, 'head of dean') !== false) {
            return 4;
        }
        if (strpos($p, 'director') !== false) {
            return 4;
        }
        if (strpos($p, 'associate dean') !== false || strpos($p, 'vice dean') !== false) {
            return 2;
        }
        if (strpos($p, 'assistant dean') !== false) {
            return 3;
        }
        if (strpos($p, 'dean') !== false) {
            return 1;
        }
        return 4;
    }

    /**
     * Group personnel by tier (1–4).
     *
     * @return array<int, array{label_th: string, label_en: string, personnel: array}>
     */
    public static function groupPersonnelByPositionTier(array $personnel): array
    {
        $groups = [
            1 => ['label_th' => 'คณบดี', 'label_en' => 'Dean', 'personnel' => []],
            2 => ['label_th' => 'รองคณบดี', 'label_en' => 'Associate Dean', 'personnel' => []],
            3 => ['label_th' => 'ผู้ช่วยคณบดี', 'label_en' => 'Assistant Dean', 'personnel' => []],
            4 => ['label_th' => 'อาจารย์และบุคลากรในสังกัด', 'label_en' => 'Faculty & Staff', 'personnel' => []],
        ];
        foreach ($personnel as $p) {
            $posTh = $p['position'] ?? '';
            $posEn = $p['position_en'] ?? '';
            $tier = self::personnelPositionTier($posTh);
            if ($tier === 4 && $posTh === '' && $posEn !== '') {
                $tier = self::personnelPositionTierEn($posEn);
            }
            $groups[$tier]['personnel'][] = $p;
        }
        return $groups;
    }

    /**
     * Filter personnel ที่ตำแหน่ง "หัวหน้าสำนักงาน" (หัวหน้าเจ้าหน้าที่)
     *
     * @param array $personnel
     * @return array
     */
    public static function filterHeadOffice(array $personnel): array
    {
        return array_values(array_filter($personnel, function ($p) {
            $pos = $p['position'] ?? '';
            return mb_strpos($pos, 'หัวหน้าสำนักงาน') !== false;
        }));
    }

    /**
     * Filter personnel ที่ตำแหน่งหัวหน้าหน่วยการจัดการงานวิจัย (รองรับหลายรูปแบบใน DB)
     *
     * @param array $personnel
     * @return array
     */
    public static function filterHeadResearch(array $personnel): array
    {
        return array_values(array_filter($personnel, function ($p) {
            $pos = trim($p['position'] ?? '');
            $posEn = trim($p['position_en'] ?? '');
            if (mb_strpos($pos, 'หัวหน้าหน่วยจัดการงานวิจัย') !== false) {
                return true;
            }
            if (mb_strpos($pos, 'หัวหน้าหน่วยการจัดการ') !== false) {
                return true;
            }
            if (mb_strpos($pos, 'หัวหน้าหน่วยวิจัย') !== false) {
                return true;
            }
            $posEnLower = mb_strtolower($posEn);
            if (strpos($posEnLower, 'head of research') !== false || strpos($posEnLower, 'research unit') !== false || strpos($posEnLower, 'research management') !== false) {
                return true;
            }
            return false;
        }));
    }

    /**
     * Build program chair items from programs (chair_personnel_id / coordinator_id / personnel_programs).
     *
     * @return array<int, array{program_name: string, person: array}>
     */
    public static function buildProgramChairItems(
        PersonnelModel $personnelModel,
        ProgramModel $programModel,
        PersonnelProgramModel $personnelProgramModel
    ): array {
        $programs = $programModel->getWithDepartment();
        $chairIds = [];
        foreach ($programs as $p) {
            $cid = (int) ($p['chair_personnel_id'] ?? 0);
            if ($cid > 0) {
                $chairIds[$cid] = true;
            }
            if ($cid <= 0 && $programModel->db->fieldExists('coordinator_id', 'programs')) {
                $cid = (int) ($p['coordinator_id'] ?? 0);
                if ($cid > 0) {
                    $chairIds[$cid] = true;
                }
            }
        }
        if ($personnelProgramModel->db->tableExists('personnel_programs')) {
            foreach ($programs as $p) {
                if ((int)($p['chair_personnel_id'] ?? 0) > 0) {
                    continue;
                }
                $row = $personnelProgramModel->where('program_id', (int)($p['id'] ?? 0))->like('role_in_curriculum', 'ประธาน')->first();
                if ($row && !empty($row['personnel_id'])) {
                    $chairIds[(int) $row['personnel_id']] = true;
                }
            }
        }
        $chairIds = array_keys($chairIds);
        $personnelMap = [];
        if (!empty($chairIds)) {
            $list = $personnelModel->getActiveByIdsWithUser($chairIds);
            foreach ($list as $p) {
                $personnelMap[(int) ($p['id'] ?? 0)] = $p;
            }
        }

        $items = [];
        foreach ($programs as $program) {
            $programId = (int) ($program['id'] ?? 0);
            $chairId = (int) ($program['chair_personnel_id'] ?? 0);
            if ($chairId <= 0 && $programModel->db->fieldExists('coordinator_id', 'programs')) {
                $chairId = (int) ($program['coordinator_id'] ?? 0);
            }
            if ($chairId <= 0 && $personnelProgramModel->db->tableExists('personnel_programs')) {
                $row = $personnelProgramModel->where('program_id', $programId)->like('role_in_curriculum', 'ประธาน')->first();
                $chairId = $row ? (int) ($row['personnel_id'] ?? 0) : 0;
            }
            if ($chairId <= 0) {
                continue;
            }
            $person = $personnelMap[$chairId] ?? null;
            if (!$person) {
                continue;
            }
            $programName = trim($program['name_th'] ?? $program['name_en'] ?? '');
            if ($programName !== '') {
                $items[] = [
                    'program_name' => $programName,
                    'person' => $person,
                ];
            }
        }
        return $items;
    }

    /**
     * Get full executives data: 6 กลุ่มผู้บริหาร
     * คณบดี, รองคณบดี, ผู้ช่วยคณบดี, ประธานหลักสูตร, หัวหน้าสำนักงาน, หัวหน้าหน่วยจัดการงานวิจัย
     *
     * @return array{tier1: array, tier2: array, tier3: array, headOffice: array, headResearch: array, programChairs: array}
     */
    public static function getExecutivesData(): array
    {
        $personnelModel = new PersonnelModel();
        $programModel = new ProgramModel();
        $personnelProgramModel = new PersonnelProgramModel();

        $personnel = $personnelModel->getActiveWithDepartment();
        $byTier = self::groupPersonnelByPositionTier($personnel);

        $tier1 = $byTier[1]['personnel'] ?? [];
        $tier2 = $byTier[2]['personnel'] ?? [];
        $tier3 = $byTier[3]['personnel'] ?? [];
        $tier4 = $byTier[4]['personnel'] ?? [];

        $headOffice = self::filterHeadOffice($tier4);
        $headResearch = self::filterHeadResearch($tier4);
        $programChairs = self::buildProgramChairItems($personnelModel, $programModel, $personnelProgramModel);

        return [
            'tier1' => $tier1,
            'tier2' => $tier2,
            'tier3' => $tier3,
            'headOffice' => $headOffice,
            'headResearch' => $headResearch,
            'programChairs' => $programChairs,
        ];
    }
}
