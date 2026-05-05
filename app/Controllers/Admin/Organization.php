<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Models\ProgramModel;
use App\Models\OrganizationUnitModel;
use App\Models\UserModel;
use App\Libraries\SeedEnsure;
use App\Libraries\OrganizationRoles;
use App\Libraries\StaffImageUpload;
use App\Libraries\CvProfile;
use App\Libraries\OrganizationPositionCatalog;
use App\Libraries\PersonnelOrgRoleRules;
use App\Libraries\CertOrganizerAccess;
use App\Models\PersonnelOrgRoleModel;
use Config\Certificate as CertificateConfig;
use CodeIgniter\Database\Exceptions\DataException;

class Organization extends BaseController
{
    protected $personnelModel;
    protected $personnelProgramModel;
    protected $personnelOrgRoleModel;
    protected $programModel;
    protected $userModel;

    public function __construct()
    {
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
        $this->personnelOrgRoleModel = new PersonnelOrgRoleModel();
        $this->programModel = new ProgramModel();
        $this->userModel = new UserModel();
    }

    /**
     * ตำแหน่งในโครงสร้าง: 1=คณบดี, 2=รองคณบดี, 3=ผู้ช่วยคณบดี, 4=หัวหน้าสำนักงาน/หัวหน้าหน่วยวิจัย, 5=ประธานหลักสูตร, 6=อื่นๆ
     * Uses OrganizationRoles library for consistent classification
     */
    private static function getTier(string $position, string $positionEn = ''): int
    {
        return OrganizationRoles::getTier(['position' => $position, 'position_en' => $positionEn]);
    }

    private function getTierForPersonnel(array $p): int
    {
        $eff = PersonnelOrgRoleRules::effectivePositionForTier($p['org_roles'] ?? [], $p['position'] ?? '');

        return self::getTier($eff, $p['position_en'] ?? '');
    }

    /**
     * แนบ org_roles ให้แต่ละแถว personnel (อ้างอิงตาม id)
     */
    private function attachOrgRolesToPersonnelRows(array &$personnel): void
    {
        if (! $this->personnelOrgRoleModel->db->tableExists('personnel_org_roles')) {
            foreach ($personnel as &$p) {
                $p['org_roles']              = [];
                $t                           = trim((string) ($p['position'] ?? ''));
                $p['position_summary_lines'] = $t !== '' ? [$t] : [];
            }
            unset($p);

            return;
        }
        $ids = array_values(array_filter(array_map(fn ($p) => (int) ($p['id'] ?? 0), $personnel), fn ($id) => $id > 0));
        if ($ids === []) {
            return;
        }
        $grouped = $this->personnelOrgRoleModel->getGroupedByPersonnelIds($ids);
        foreach ($personnel as &$p) {
            $pid = (int) ($p['id'] ?? 0);
            $p['org_roles'] = $grouped[$pid] ?? [];
            $lines            = [];
            foreach ($p['org_roles'] as $r) {
                $t = trim((string) ($r['position_title'] ?? ''));
                if ($t !== '') {
                    $lines[] = $t;
                }
            }
            if ($lines === [] && trim((string) ($p['position'] ?? '')) !== '') {
                $lines[] = trim((string) $p['position']);
            }
            $p['position_summary_lines'] = $lines;
        }
        unset($p);
    }

    /**
     * จัดกลุ่มบุคลากรตาม tier โครงสร้างผู้บริหาร:
     * 1 คณบดี, 2 รองคณบดี, 3 ผู้ช่วยคณบดี, 4 หัวหน้าสำนักงาน/หัวหน้าหน่วยวิจัย, 5 ประธานหลักสูตร, 6 อื่นๆ
     */
    private function groupByTier(array $personnel): array
    {
        $groups = [
            1 => ['label_th' => 'คณบดี', 'label_en' => 'Dean', 'personnel' => []],
            2 => ['label_th' => 'รองคณบดี', 'label_en' => 'Associate Dean', 'personnel' => []],
            3 => ['label_th' => 'ผู้ช่วยคณบดี', 'label_en' => 'Assistant Dean', 'personnel' => []],
            4 => ['label_th' => 'หัวหน้าสำนักงานคณบดี / หัวหน้าหน่วยการจัดการงานวิจัย', 'label_en' => "Head of Dean's Office / Head of Research Unit", 'personnel' => []],
            5 => ['label_th' => 'ประธานหลักสูตร', 'label_en' => 'Program Chair', 'personnel' => []],
            6 => ['label_th' => 'อาจารย์และบุคลากรในสังกัด', 'label_en' => 'Faculty & Staff', 'personnel' => []],
        ];
        foreach ($personnel as $p) {
            $tier = $this->getTierForPersonnel($p);
            if (!isset($groups[$tier])) {
                $groups[$tier] = ['label_th' => 'อื่นๆ', 'label_en' => 'Other', 'personnel' => []];
            }
            $groups[$tier]['personnel'][] = $p;
        }
        foreach ($groups as $tier => &$g) {
            PersonnelOrgRoleRules::sortPersonnelWithStaffOfficerLast($g['personnel']);
        }
        unset($g);

        return $groups;
    }

    /**
     * รายการโครงสร้างองค์กร (แบ่งตาม tier)
     * ใช้ batch load personnel_programs + programs เพื่อหลีกเลี่ยง N+1
     */
    public function index()
    {
        $personnel = $this->personnelModel->getActiveWithDepartment();
        if ($this->personnelModel->db->tableExists('personnel_programs')) {
            $personnelIds = array_values(array_filter(array_map(fn($p) => (int) ($p['id'] ?? 0), $personnel), fn($id) => $id > 0));
            $ppRows = $personnelIds !== [] ? $this->personnelProgramModel->getByPersonnelIds($personnelIds) : [];
            $programIds = array_values(array_unique(array_filter(array_map(fn($r) => (int) ($r['program_id'] ?? 0), $ppRows), fn($id) => $id > 0)));
            $programsMap = [];
            if ($programIds !== []) {
                foreach ($this->programModel->whereIn('id', $programIds)->findAll() as $pr) {
                    $programsMap[(int) $pr['id']] = $pr;
                }
            }
            $ppByPersonnel = [];
            foreach ($ppRows as $row) {
                $pid = (int) $row['personnel_id'];
                if (!isset($ppByPersonnel[$pid])) {
                    $ppByPersonnel[$pid] = [];
                }
                $ppByPersonnel[$pid][] = $row;
            }
            foreach ($personnel as &$p) {
                $pid = (int) ($p['id'] ?? 0);
                $ppList = $ppByPersonnel[$pid] ?? [];
                if (empty($ppList)) {
                    $name = $p['program_name_th'] ?? $p['program_name_en'] ?? '';
                    $p['programs_list'] = $name;
                    $p['programs_list_tags'] = $name ? [['name' => $name, 'role' => '']] : [];
                } else {
                    $tags = [];
                    foreach ($ppList as $pp) {
                        $pr = $programsMap[(int) ($pp['program_id'] ?? 0)] ?? null;
                        if ($pr) {
                            $tags[] = [
                                'name' => $pr['name_th'] ?? $pr['name_en'] ?? '',
                                'role' => $pp['role_in_curriculum'] ?? '',
                            ];
                        }
                    }
                    $p['programs_list'] = implode(', ', array_column($tags, 'name'));
                    $p['programs_list_tags'] = $tags;
                }
            }
            unset($p);
        } else {
            foreach ($personnel as &$p) {
                $name = $p['program_name_th'] ?? $p['program_name_en'] ?? '';
                $p['programs_list'] = $name;
                $p['programs_list_tags'] = $name ? [['name' => $name, 'role' => '']] : [];
            }
            unset($p);
        }

        // ลิงก์กับตาราง user ตามอีเมล (personnel.email = user.email)
        $emails = array_values(array_unique(array_filter(array_map(function ($p) {
            $e = trim($p['email'] ?? '');
            return $e !== '' ? $e : null;
        }, $personnel))));
        $userByEmail = [];
        if ($emails !== []) {
            $users = $this->userModel->whereIn('email', $emails)->findAll();
            foreach ($users as $u) {
                $userByEmail[trim($u['email'] ?? '')] = $u;
            }
        }
        foreach ($personnel as &$p) {
            $email = trim($p['email'] ?? '');
            $p['user_link'] = $email !== '' ? ($userByEmail[$email] ?? null) : null;
        }
        unset($p);

        $this->attachOrgRolesToPersonnelRows($personnel);

        // เฉพาะสมาชิกคณะวิทยาศาสตร์และเทคโนโลยี (เกณฑ์เดียวกับ user-faculty / E-Certificate)
        $certCfg   = config(CertificateConfig::class);
        $personnel = array_values(array_filter($personnel, static function ($p) use ($certCfg) {
            $user = $p['user_link'] ?? null;

            return $user !== null && CertOrganizerAccess::userFacultyMatchesOrganizerFaculty($user, $certCfg);
        }));

        // ตัวกรองชื่อ (ค้นหา)
        $filterName = $this->request->getGet('name');
        if ($filterName !== null && trim($filterName) !== '') {
            $q = trim($filterName);
            $personnel = array_values(array_filter($personnel, function ($p) use ($q) {
                $name = trim($p['name'] ?? '');
                $nameEn = trim($p['name_en'] ?? '');
                return stripos($name, $q) !== false || stripos($nameEn, $q) !== false;
            }));
        }

        // โครงสร้าง 5 หน่วยงาน: ผู้บริหาร, สำนักงานคณบดี, หัวหน้าหน่วยวิจัย, หลักสูตรป.ตรี, หลักสูตรบัณฑิต
        $organizationSections = $this->buildOrganizationSectionsForAdmin($personnel);

        // ตัวกรองหน่วยงาน (แสดงเฉพาะหน่วยงานที่เลือก)
        $orgUnitModel = new OrganizationUnitModel();
        $organizationUnits = $orgUnitModel->getOrdered();
        $unitFilterOptions = ['' => 'ทั้งหมด'];
        foreach ($organizationUnits as $u) {
            $unitFilterOptions[$u['code'] ?? ''] = $u['name_th'] ?? $u['code'];
        }
        $unitFilterOptions['personnel_pool'] = 'บุคลากร';
        $filterUnit = $this->request->getGet('unit');
        if ($filterUnit !== null && $filterUnit !== '') {
            $organizationSections = array_values(array_filter($organizationSections, function ($sec) use ($filterUnit) {
                return ($sec['unit']['code'] ?? '') === $filterUnit;
            }));
        }

        $data = [
            'page_title' => 'โครงสร้างองค์กร',
            'organization_sections' => $organizationSections,
            'unit_filter_options' => $unitFilterOptions,
            'filter_unit' => $filterUnit ?? '',
            'filter_name' => $filterName ?? '',
        ];

        return view('admin/organization/index', $data);
    }

    /**
     * สร้าง 5 หน่วยงานสำหรับ Admin: ผู้บริหาร (tier 1–5), สำนักงานคณบดี, หัวหน้าหน่วยวิจัย, หลักสูตรป.ตรี, หลักสูตรบัณฑิต
     * โครงสร้างผู้บริหาร: 1 คณบดี, 2 รองคณบดี, 3 ผู้ช่วยคณบดี, 4 หัวหน้าสำนักงาน/หัวหน้าหน่วยวิจัย, 5 ประธานหลักสูตร
     */
    private function buildOrganizationSectionsForAdmin(array $personnel): array
    {
        $orgUnitModel = new OrganizationUnitModel();
        $units = $orgUnitModel->getOrdered();
        $personnelById = [];
        foreach ($personnel as $p) {
            $personnelById[(int) ($p['id'] ?? 0)] = $p;
        }

        $groups = $this->groupByTier($personnel);
        // ผู้บริหาร = tier 1,2,3,4,5 (คณบดี, รอง, ผู้ช่วย, หัวหน้าสำนักงาน/หัวหน้าหน่วยวิจัย, ประธานหลักสูตร)
        $executivesPersonnel = array_merge(
            $groups[1]['personnel'] ?? [],
            $groups[2]['personnel'] ?? [],
            $groups[3]['personnel'] ?? [],
            $groups[4]['personnel'] ?? [],
            $groups[5]['personnel'] ?? []
        );

        $officeUnitId = null;
        foreach ($units as $u) {
            if (($u['code'] ?? '') === 'office') {
                $officeUnitId = (int) ($u['id'] ?? 0);
                break;
            }
        }
        $researchUnitId = null;
        foreach ($units as $u) {
            if (($u['code'] ?? '') === 'research') {
                $researchUnitId = (int) ($u['id'] ?? 0);
                break;
            }
        }
        // สำนักงานคณบดี: เฉพาะ tier 6 ที่สังกัดหน่วย office หรือตำแหน่งเจ้าหน้าที่ (หัวหน้าสำนักงานไปอยู่ผู้บริหารแล้ว)
        $officePersonnel = [];
        if ($officeUnitId > 0 && $this->personnelModel->db->fieldExists('organization_unit_id', 'personnel')) {
            $byOrgUnit = array_values(array_filter($personnel, function ($p) use ($officeUnitId) {
                return (int) ($p['organization_unit_id'] ?? 0) === $officeUnitId;
            }));
            foreach ($byOrgUnit as $p) {
                $tier = $this->getTierForPersonnel($p);
                if ($tier === 6) {
                    $officePersonnel[] = $p;
                }
            }
        }
        $staffPosition = array_values(array_filter($personnel, function ($p) {
            return $this->personnelHasOfficeStaffTitle($p);
        }));
        $officeIds = array_fill_keys(array_map(fn($p) => (int) ($p['id'] ?? 0), $officePersonnel), true);
        foreach ($staffPosition as $p) {
            $id = (int) ($p['id'] ?? 0);
            if ($id > 0 && empty($officeIds[$id])) {
                $tier = $this->getTierForPersonnel($p);
                if ($tier === 6) {
                    $officePersonnel[] = $p;
                    $officeIds[$id] = true;
                }
            }
        }
        PersonnelOrgRoleRules::sortPersonnelWithStaffOfficerLast($officePersonnel);

        // หัวหน้าหน่วยวิจัย: เฉพาะ tier 6 ที่สังกัดหน่วย research (หัวหน้าหน่วยวิจัยไปอยู่ผู้บริหารแล้ว)
        $headResearch = [];
        if ($researchUnitId > 0 && $this->personnelModel->db->fieldExists('organization_unit_id', 'personnel')) {
            $headResearch = array_values(array_filter($personnel, function ($p) use ($researchUnitId) {
                if ((int) ($p['organization_unit_id'] ?? 0) !== $researchUnitId) {
                    return false;
                }
                $tier = $this->getTierForPersonnel($p);

                return $tier === 6;
            }));
            PersonnelOrgRoleRules::sortPersonnelWithStaffOfficerLast($headResearch);
        }

        $programs = $this->programModel->getActive();
        $hasChairColumn = $this->programModel->db->fieldExists('chair_personnel_id', 'programs');
        $personnelByProgram = [];
        foreach ($programs as $program) {
            $programId = (int) $program['id'];
            $ppRows = $this->personnelProgramModel->getByProgramId($programId);
            $chair = null;
            if ($hasChairColumn) {
                $chairId = (int) ($program['chair_personnel_id'] ?? 0);
                if ($chairId > 0) {
                    $chair = $personnelById[$chairId] ?? null;
                }
            }
            if ($chair === null) {
                foreach ($ppRows as $row) {
                    $pid = (int) ($row['personnel_id'] ?? 0);
                    $role = trim($row['role_in_curriculum'] ?? '');
                    if (mb_strpos($role, 'ประธาน') !== false) {
                        $chair = $personnelById[$pid] ?? null;
                        if ($chair !== null) {
                            break;
                        }
                    }
                }
            }
            if ($chair === null) {
                foreach ($ppRows as $row) {
                    $pid = (int) ($row['personnel_id'] ?? 0);
                    $person = $personnelById[$pid] ?? null;
                    if ($person && $this->personIsChairForProgram($person, $programId)) {
                        $chair = $person;
                        break;
                    }
                }
            }
            $chairId = $chair !== null ? (int) ($chair['id'] ?? 0) : 0;
            $personnelList = [];
            foreach ($ppRows as $row) {
                $pid = (int) ($row['personnel_id'] ?? 0);
                if ($pid === $chairId) {
                    continue;
                }
                $person = $personnelById[$pid] ?? null;
                if ($person !== null) {
                    $personnelList[] = $person;
                }
            }
            PersonnelOrgRoleRules::sortPersonnelWithStaffOfficerLast($personnelList);
            $personnelByProgram[] = [
                'program' => $program,
                'chair' => $chair,
                'personnel' => $personnelList,
            ];
        }

        $bachelorPrograms = [];
        $graduatePrograms = [];
        foreach ($personnelByProgram as $block) {
            $level = $block['program']['level'] ?? 'bachelor';
            if ($level === 'master' || $level === 'doctorate') {
                $graduatePrograms[] = $block;
            } else {
                $bachelorPrograms[] = $block;
            }
        }

        $sections = [];
        foreach ($units as $unit) {
            $code = $unit['code'] ?? '';
            $section = ['unit' => $unit, 'personnel' => [], 'programs' => []];
            if ($code === 'executives') {
                $section['personnel'] = $executivesPersonnel;
            } elseif ($code === 'office') {
                $section['personnel'] = $officePersonnel;
            } elseif ($code === 'research') {
                $section['personnel'] = $headResearch;
            } elseif ($code === 'bachelor') {
                $section['programs'] = $bachelorPrograms;
            } elseif ($code === 'graduate') {
                $section['programs'] = $graduatePrograms;
            }
            $sections[] = $section;
        }

        // กลุ่ม "บุคลากร": ยังไม่อยู่ในรายการผู้บริหาร / สำนักงาน / วิจัย / หลักสูตร (จาก personnel_programs)
        // และยังไม่มีสังกัดหลักสูตรหลัก (program_id หรือแถว personnel_programs)
        $assignedInSections = [];
        foreach ($executivesPersonnel as $p) {
            $assignedInSections[(int) ($p['id'] ?? 0)] = true;
        }
        foreach ($officePersonnel as $p) {
            $assignedInSections[(int) ($p['id'] ?? 0)] = true;
        }
        foreach ($headResearch as $p) {
            $assignedInSections[(int) ($p['id'] ?? 0)] = true;
        }
        foreach ($personnelByProgram as $block) {
            if (! empty($block['chair'])) {
                $assignedInSections[(int) ($block['chair']['id'] ?? 0)] = true;
            }
            foreach ($block['personnel'] ?? [] as $p) {
                $assignedInSections[(int) ($p['id'] ?? 0)] = true;
            }
        }

        $hasProgramAffiliation = [];
        foreach ($personnel as $p) {
            $pid = (int) ($p['id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            if ((int) ($p['program_id'] ?? 0) > 0) {
                $hasProgramAffiliation[$pid] = true;
            }
            foreach ($p['org_roles'] ?? [] as $or) {
                if ((int) ($or['program_id'] ?? 0) > 0) {
                    $hasProgramAffiliation[$pid] = true;
                    break;
                }
            }
        }
        if ($this->personnelModel->db->tableExists('personnel_programs')) {
            $allPersonnelIds = array_values(array_filter(
                array_map(fn ($p) => (int) ($p['id'] ?? 0), $personnel),
                fn ($id) => $id > 0
            ));
            if ($allPersonnelIds !== []) {
                foreach ($this->personnelProgramModel->getByPersonnelIds($allPersonnelIds) as $row) {
                    $hasProgramAffiliation[(int) ($row['personnel_id'] ?? 0)] = true;
                }
            }
        }

        $orgUnitCol = $this->personnelModel->db->fieldExists('organization_unit_id', 'personnel');
        $unassigned = [];
        foreach ($personnel as $p) {
            $pid = (int) ($p['id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            if (! empty($assignedInSections[$pid])) {
                continue;
            }
            if (! empty($hasProgramAffiliation[$pid])) {
                continue;
            }
            if ($orgUnitCol && (int) ($p['organization_unit_id'] ?? 0) > 0) {
                continue;
            }
            $unassigned[] = $p;
        }
        PersonnelOrgRoleRules::sortPersonnelWithStaffOfficerLast($unassigned);

        $sections[] = [
            'unit'      => ['name_th' => 'บุคลากร', 'name_en' => 'Faculty Staff', 'code' => 'personnel_pool', 'sort_order' => 99],
            'personnel' => $unassigned,
            'programs'  => [],
        ];

        return $sections;
    }

    /**
     * ตำแหน่งในโครงสร้าง แบ่งตามฝ่ายงาน (สำหรับ optgroup ใน dropdown)
     * คืนค่า [ 'ชื่อฝ่าย' => [ 'value' => 'label', ... ], ... ]
     */
    private function getPositionOptions(): array
    {
        return OrganizationPositionCatalog::getGroupedOptions();
    }

    /** ค่าตำแหน่งที่อนุญาตใน dropdown (รายการคงที่ใน OrganizationPositionCatalog) */
    private function getAllowedPositionValues(): array
    {
        return OrganizationPositionCatalog::getAllowedTitles();
    }

    private function personnelHasOfficeStaffTitle(array $p): bool
    {
        return PersonnelOrgRoleRules::personnelHasStaffOfficerTitle($p);
    }

    private function personIsChairForProgram(array $person, int $programId): bool
    {
        return PersonnelOrgRoleRules::hasChairCurriculumRoleForProgram(
            $person['org_roles'] ?? [],
            $programId,
            $person['position'] ?? null
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function collectOrgRolesFromPost(): array
    {
        $raw = $this->request->getPost('org_roles');
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $r) {
            if (! is_array($r)) {
                continue;
            }
            $title = trim((string) ($r['position_title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'role_kind'            => trim((string) ($r['role_kind'] ?? '')),
                'position_title'       => $title,
                'program_id'           => (int) ($r['program_id'] ?? 0) ?: null,
                'organization_unit_id' => (int) ($r['organization_unit_id'] ?? 0) ?: null,
                'position_detail'      => trim((string) ($r['position_detail'] ?? '')) ?: null,
                'sort_order'           => (int) ($r['sort_order'] ?? 0),
                'is_primary_program'   => isset($r['is_primary_program']) && (string) $r['is_primary_program'] !== '' && (string) $r['is_primary_program'] !== '0',
            ];
        }

        return $out;
    }

    private function orgRoleKindOptionsTh(): array
    {
        return [
            PersonnelOrgRoleRules::KIND_EXECUTIVE  => 'ผู้บริหาร',
            PersonnelOrgRoleRules::KIND_RESEARCH   => 'หน่วยงานวิจัย',
            PersonnelOrgRoleRules::KIND_OFFICE    => 'สำนักงานคณบดี',
            PersonnelOrgRoleRules::KIND_CURRICULUM => 'หลักสูตร',
        ];
    }

    /**
     * @return string|null ข้อความ error หรือ null ถ้าผ่าน
     */
    private function validatePostedOrganizationPosition(?string $position): ?string
    {
        if ($position === null || trim($position) === '') {
            return null;
        }
        $position = trim($position);
        if (! in_array($position, $this->getAllowedPositionValues(), true)) {
            return 'ตำแหน่งไม่อยู่ในรายการที่อนุญาต — เลือกจากรายการตำแหน่งเท่านั้น';
        }

        return null;
    }

    /**
     * คำนำหน้าใน CvProfile — คืนข้อความ error หรือ null ถ้าผ่าน
     */
    private function validatePostedAcademicTitles(): ?string
    {
        $at = trim((string) $this->request->getPost('academic_title'));
        if ($at !== '' && ! CvProfile::isAllowedUserTitle($at)) {
            return 'คำนำหน้าชื่อ (ไทย) ไม่ตรงกับรายการมาตรฐาน กรุณาเลือกจากรายการ';
        }
        $atEn = trim((string) $this->request->getPost('academic_title_en'));
        if ($atEn !== '' && ! array_key_exists($atEn, CvProfile::academicTitleOptionsEn())) {
            return 'คำนำหน้าชื่อ (English) ไม่ตรงกับรายการมาตรฐาน กรุณาเลือกจากรายการ';
        }

        return null;
    }

    /**
     * เดิมเป็นฟอร์มเพิ่มบุคลากร — ย้ายไปที่ admin/user-faculty (user.faculty) แล้วซิงก์ personnel อัตโนมัติ
     */
    public function create()
    {
        return redirect()->to(base_url('admin/user-faculty'))
            ->with('success', 'เพิ่มบุคลากรในโครงสร้างองค์กร: ไปที่ “จัดการคณะผู้ใช้” แล้วเพิ่มผู้ใช้เข้า “คณะวิทยาศาสตร์และเทคโนโลยี” — ระบบจะสร้าง/เปิดใช้แถว personnel ให้อัตโนมัติเมื่อบันทึก');
    }

    /**
     * บันทึกรูปโปรไฟล์ลงตาราง user (profile_picture หรือ profile_image) เพื่อให้ข้อมูลเดียวกันทุกที่เมื่อ user อัปโหลดรูป
     */
    private function saveUserProfileImage(int $userUid, string $relativePath): void
    {
        $db = $this->userModel->db;
        $col = $db->fieldExists('profile_picture', 'user') ? 'profile_picture' : ($db->fieldExists('profile_image', 'user') ? 'profile_image' : null);
        if ($col === null) {
            return;
        }
        $this->userModel->update($userUid, [$col => $relativePath]);
    }

    /**
     * เดิมรับ POST สร้างบุคลากร — ปิดการใช้งาน (ใช้ admin/user-faculty แทน)
     */
    public function store()
    {
        return redirect()->to(base_url('admin/user-faculty'))
            ->with('error', 'การเพิ่มบุคลากรทำที่หน้า “จัดการคณะผู้ใช้” เท่านั้น — เลือกคณะ “คณะวิทยาศาสตร์และเทคโนโลยี” แล้วบันทึก');
    }

    /** อ่านรายการหลักสูตรที่ส่งมาจากฟอร์ม (program_assignments[]) - รองรับ is_primary */
    private function collectProgramAssignmentsFromPost(): array
    {
        $programIds = $this->request->getPost('program_assignments')['program_id'] ?? [];
        $roles = $this->request->getPost('program_assignments')['role_in_curriculum'] ?? [];
        $isPrimary = $this->request->getPost('program_assignments')['is_primary'] ?? [];
        $primaryProgramId = $this->request->getPost('primary_program_id');

        if (!is_array($programIds)) {
            return [];
        }
        $out = [];
        foreach ($programIds as $i => $pid) {
            $pid = (int) $pid;
            if ($pid <= 0) continue;

            // Determine if this is the primary program
            $isPrimaryFlag = false;
            if ($primaryProgramId !== null && (int) $primaryProgramId === $pid) {
                $isPrimaryFlag = true;
            } elseif (isset($isPrimary[$i]) && $isPrimary[$i]) {
                $isPrimaryFlag = true;
            }

            $out[] = [
                'program_id' => $pid,
                'role_in_curriculum' => isset($roles[$i]) && $roles[$i] !== '' ? $roles[$i] : null,
                'is_primary' => $isPrimaryFlag,
            ];
        }
        return $out;
    }

    /**
     * ถ้าตำแหน่งเป็น ประธานหลักสูตร ต้องมีอย่างน้อยหนึ่งหลักสูตร
     * คืนค่า error message หรือ null ถ้าผ่าน
     */
    private function validateProgramChairRequirement(?string $position, array $programRoles): ?string
    {
        if ($position === null || $position === '') {
            return null;
        }
        if (mb_strpos($position, 'ประธานหลักสูตร') === false) {
            return null;
        }
        if (empty($programRoles)) {
            return 'เมื่อตำแหน่งเป็น ประธานหลักสูตร กรุณาเพิ่มอย่างน้อยหนึ่งหลักสูตร';
        }
        return null;
    }

    /**
     * เมื่อตำแหน่งเป็น ประธานหลักสูตร กำหนด role_in_curriculum = ประธานหลักสูตร ให้หลักสูตรหลัก (ตัวแรก/primary)
     */
    private function applyChairRoleFromPosition(?string $position, array $programRoles): array
    {
        if ($position === null || $position === '' || mb_strpos($position, 'ประธานหลักสูตร') === false) {
            return $programRoles;
        }
        if (empty($programRoles)) {
            return $programRoles;
        }
        $primaryIdx = null;
        foreach ($programRoles as $i => $pr) {
            if (!empty($pr['is_primary'])) {
                $primaryIdx = $i;
                break;
            }
        }
        if ($primaryIdx === null) {
            $primaryIdx = 0;
        }
        $programRoles[$primaryIdx]['role_in_curriculum'] = 'ประธานหลักสูตร';
        return $programRoles;
    }

    /**
     * แก้ไขตำแหน่ง/ลำดับบุคลากรในโครงสร้าง
     */
    public function edit(int $id)
    {
        // ใช้ findWithUser เพื่อให้รูปปัจจุบัน (จาก user) แสดงในฟอร์ม
        $person = $this->personnelModel->findWithUser($id);
        if (!$person) {
            return redirect()->to(base_url('admin/organization'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $personnelPrograms = $this->personnelModel->db->tableExists('personnel_programs')
            ? $this->personnelProgramModel->getByPersonnelId((int) $id)
            : [];

        SeedEnsure::ensureComputerEngineering($this->personnelModel->db);

        $programs = $this->programModel->getWithDepartment();
        $organizationUnits = (new OrganizationUnitModel())->getOrdered();

        $orgRoles = [];
        if ($this->personnelOrgRoleModel->db->tableExists('personnel_org_roles')) {
            $orgRoles = $this->personnelOrgRoleModel->getByPersonnelId((int) $id);
        }

        $data = [
            'page_title' => 'แก้ไขตำแหน่งในโครงสร้างองค์กร',
            'person' => $person,
            'personnel_programs' => $personnelPrograms,
            'use_org_roles_ui' => $this->personnelOrgRoleModel->db->tableExists('personnel_org_roles'),
            'org_roles' => $orgRoles,
            'org_role_kind_options' => $this->orgRoleKindOptionsTh(),
            'position_options' => $this->getPositionOptions(),
            'academic_title_options' => CvProfile::academicTitleOptionsTh(),
            'academic_title_options_en' => CvProfile::academicTitleOptionsEn(),
            'programs' => $programs,
            'organization_units' => $organizationUnits,
        ];

        return view('admin/organization/edit', $data);
    }

    /**
     * บันทึกการแก้ไขตำแหน่ง/ลำดับ
     */
    public function update(int $id)
    {
        $person = $this->personnelModel->find($id);
        if (!$person) {
            return redirect()->to(base_url('admin/organization'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $userUidForValidation = (int) ($person['user_uid'] ?? 0);
        $linkedUser = $userUidForValidation > 0 ? $this->userModel->find($userUidForValidation) : null;
        $rules = [
            'name' => $linkedUser ? 'permit_empty|min_length[1]' : 'required|min_length[1]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $titleErr = $this->validatePostedAcademicTitles();
        if ($titleErr !== null) {
            return redirect()->back()->withInput()->with('errors', ['academic_title' => $titleErr]);
        }

        $name = $this->request->getPost('name');
        $nameEn = $this->request->getPost('name_en');
        $email = $this->request->getPost('email');
        if ($linkedUser && (trim($name ?? '') === '')) {
            $name = trim($this->userModel->getFullName($linkedUser));
        }
        if ($linkedUser && (trim($nameEn ?? '') === '')) {
            $nameEn = trim(($linkedUser['gf_name'] ?? '') . ' ' . ($linkedUser['gl_name'] ?? ''));
        }
        $positionEn = $this->request->getPost('position_en');
        $sortOrder    = (int) $this->request->getPost('sort_order');
        $programRoles = $this->collectProgramAssignmentsFromPost();

        $useOrgRoles = $this->personnelOrgRoleModel->db->tableExists('personnel_org_roles');
        $position    = null;
        $programId   = null;
        $positionDetailValue = $this->request->getPost('position_detail') ?: null;
        $organizationUnitId = null;
        $postedOrgUnitId    = $this->request->getPost('organization_unit_id');
        if ($postedOrgUnitId !== null && $postedOrgUnitId !== '' && (int) $postedOrgUnitId > 0) {
            $organizationUnitId = (int) $postedOrgUnitId;
        }

        if ($useOrgRoles) {
            $orgRows = $this->collectOrgRolesFromPost();
            foreach ($orgRows as &$row) {
                $rk = trim((string) ($row['role_kind'] ?? ''));
                if ($rk === '' || ! in_array($rk, PersonnelOrgRoleRules::roleKinds(), true)) {
                    $row['role_kind'] = PersonnelOrgRoleRules::inferRoleKind((string) $row['position_title']);
                }
            }
            unset($row);

            if ($orgRows === []) {
                return redirect()->back()->withInput()->with('errors', ['org_roles' => 'เพิ่มอย่างน้อยหนึ่งบทบาทในโครงสร้าง']);
            }
            $roleErr = PersonnelOrgRoleRules::validateRows($orgRows);
            if ($roleErr !== null) {
                return redirect()->back()->withInput()->with('errors', ['org_roles' => $roleErr]);
            }

            $mergedProgramRoles = PersonnelOrgRoleRules::buildProgramRolesFromOrgRoles($orgRows, 0);

            $this->personnelOrgRoleModel->replaceForPersonnel((int) $id, $orgRows);

            if ($this->personnelProgramModel->db->tableExists('personnel_programs')) {
                $this->personnelProgramModel->setProgramsForPersonnelWithPrimary((int) $id, $mergedProgramRoles);
            }

            $summary   = PersonnelOrgRoleRules::summarizeLegacyPersonnelColumns($orgRows);
            $position  = $summary['position'];
            $programId = $summary['program_id'];
            if (! empty($summary['position_detail'])) {
                $positionDetailValue = $summary['position_detail'];
            }
            if ($organizationUnitId === null && ! empty($summary['organization_unit_id'])) {
                $organizationUnitId = (int) $summary['organization_unit_id'];
            }
            $effPos = PersonnelOrgRoleRules::effectivePositionForTier($orgRows, $position ?? '');
            if ($organizationUnitId === null && $effPos !== '') {
                $isOfficeRole = (mb_strpos($effPos, 'หัวหน้าสำนักงาน') !== false || mb_strpos($effPos, 'เจ้าหน้าที่') !== false);
                if ($isOfficeRole) {
                    $orgUnitModel = new OrganizationUnitModel();
                    foreach ($orgUnitModel->getOrdered() as $u) {
                        if (($u['code'] ?? '') === 'office') {
                            $organizationUnitId = (int) ($u['id'] ?? 0);
                            break;
                        }
                    }
                }
            }
            if ($programId === null && $mergedProgramRoles !== []) {
                $primaryProgram = null;
                foreach ($mergedProgramRoles as $pr) {
                    if (! empty($pr['is_primary'])) {
                        $primaryProgram = $pr;
                        break;
                    }
                }
                if (! $primaryProgram) {
                    $primaryProgram = $mergedProgramRoles[0];
                }
                $ppid = (int) ($primaryProgram['program_id'] ?? 0);
                if ($ppid > 0) {
                    if ($organizationUnitId === null) {
                        $program = $this->programModel->find($ppid);
                        if ($program && ! empty($program['organization_unit_id'])) {
                            $organizationUnitId = (int) $program['organization_unit_id'];
                        }
                    }
                    $programId = $ppid;
                }
            }
        } else {
            $position = $this->request->getPost('position') ?: null;
            $posErr   = $this->validatePostedOrganizationPosition($position);
            if ($posErr !== null) {
                return redirect()->back()->withInput()->with('errors', ['position' => $posErr]);
            }
            $chairError = $this->validateProgramChairRequirement($position, $programRoles);
            if ($chairError !== null) {
                return redirect()->back()->withInput()->with('errors', ['program_chair' => $chairError]);
            }
            $programRoles = $this->applyChairRoleFromPosition($position, $programRoles);

            if (! empty($programRoles)) {
                $primaryProgram = null;
                foreach ($programRoles as $pr) {
                    if (! empty($pr['is_primary'])) {
                        $primaryProgram = $pr;
                        break;
                    }
                }
                if (! $primaryProgram) {
                    $primaryProgram = $programRoles[0];
                }

                $primaryProgramId = (int) ($primaryProgram['program_id'] ?? 0);
                if ($primaryProgramId > 0) {
                    if ($organizationUnitId === null) {
                        $program = $this->programModel->find($primaryProgramId);
                        if ($program && ! empty($program['organization_unit_id'])) {
                            $organizationUnitId = (int) $program['organization_unit_id'];
                        }
                    }
                    $programId = $primaryProgramId;
                }
            }
            if ($organizationUnitId === null && $position !== null) {
                $isOfficeRole = (mb_strpos($position, 'หัวหน้าสำนักงาน') !== false || mb_strpos($position, 'เจ้าหน้าที่') !== false);
                if ($isOfficeRole) {
                    $orgUnitModel = new OrganizationUnitModel();
                    foreach ($orgUnitModel->getOrdered() as $u) {
                        if (($u['code'] ?? '') === 'office') {
                            $organizationUnitId = (int) ($u['id'] ?? 0);
                            break;
                        }
                    }
                }
            }
        }

        $userUid = null;
        if ($email !== null && $email !== '' && $this->personnelModel->db->fieldExists('user_uid', 'personnel')) {
            $userRow = $this->userModel->where('email', $email)->first();
            $userUid = $userRow ? (int) ($userRow['uid'] ?? 0) : null;
        }

        $updateData = [
            'name' => $name === '' ? '' : $name,
            'name_en' => $nameEn === '' ? null : $nameEn,
            'email' => $email === '' ? null : $email,
            'position' => $position === '' ? null : $position,
            'position_en' => $positionEn === '' ? null : $positionEn,
            'sort_order' => $sortOrder,
        ];
        if ($this->personnelModel->db->fieldExists('organization_unit_id', 'personnel')) {
            $updateData['organization_unit_id'] = $organizationUnitId;
        }
        if ($this->personnelModel->db->fieldExists('position_detail', 'personnel')) {
            $updateData['position_detail'] = $positionDetailValue ?: null;
        }
        if ($this->personnelModel->db->fieldExists('academic_title', 'personnel')) {
            $updateData['academic_title'] = $this->request->getPost('academic_title') ?: null;
        }
        if ($this->personnelModel->db->fieldExists('academic_title_en', 'personnel')) {
            $updateData['academic_title_en'] = $this->request->getPost('academic_title_en') ?: null;
        }
        if ($this->personnelModel->db->fieldExists('program_id', 'personnel')) {
            $updateData['program_id'] = $programId;
        }
        if ($this->personnelModel->db->fieldExists('user_uid', 'personnel')) {
            $updateData['user_uid'] = $userUid;
        }

        // รูปโปรไฟล์เก็บในตาราง user เท่านั้น (ไม่ใช้ personnel.image)
        // CHANGE: Allow saving to personnel.image if user_uid is null
        $imagePath = StaffImageUpload::handleUpload($this->request->getFile('image'));
        if ($imagePath !== null) {
            if ($userUidForValidation > 0 && $linkedUser) {
                log_message('info', 'Updating profile image in USER table (uid=' . $userUidForValidation . '): ' . $imagePath);
                $oldImage = trim($linkedUser['profile_picture'] ?? $linkedUser['profile_image'] ?? '');
                StaffImageUpload::deleteStaffImageFile($oldImage);
                $this->saveUserProfileImage($userUidForValidation, $imagePath);
            } else {
                // Save to personnel table
                log_message('info', 'Updating profile image in PERSONNEL table (id=' . $id . '): ' . $imagePath);
                $oldImage = trim($person['image'] ?? '');
                StaffImageUpload::deleteStaffImageFile($oldImage);
                $updateData['image'] = $imagePath;
            }
        } else {
            log_message('info', 'No new image uploaded for personnel update (id=' . $id . ')');
        }

        if (!empty($updateData)) {
            try {
                $this->personnelModel->update($id, $updateData);
            } catch (DataException $e) {
                if (strpos($e->getMessage(), 'no data to update') === false && strpos($e->getMessage(), 'empty') === false) {
                    throw $e;
                }
            }
        }

        if (! $useOrgRoles && $this->personnelModel->db->tableExists('personnel_programs')) {
            $this->personnelProgramModel->setProgramsForPersonnelWithPrimary((int) $id, $programRoles);
        }
        $this->programModel->syncChairFromPersonnelPrograms();

        return redirect()->to(base_url('admin/organization'))->with('success', 'บันทึกการแก้ไขโครงสร้างองค์กรแล้ว');
    }

    /**
     * ลบบุคลากรออกจากระบบจริง (ลบแถวในตาราง personnel)
     * personnel_programs ลบตาม CASCADE, programs.chair_personnel_id ถูก SET NULL
     */
    public function delete(int $id)
    {
        $person = $this->personnelModel->find($id);
        if (!$person) {
            return redirect()->to(base_url('admin/organization'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $this->personnelModel->delete($id);

        return redirect()->to(base_url('admin/organization'))->with('success', 'ลบบุคลากรออกจากระบบแล้ว');
    }
}
