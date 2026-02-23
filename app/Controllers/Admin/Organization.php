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
use CodeIgniter\Database\Exceptions\DataException;

class Organization extends BaseController
{
    protected $personnelModel;
    protected $personnelProgramModel;
    protected $programModel;
    protected $userModel;

    public function __construct()
    {
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
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
            $tier = self::getTier($p['position'] ?? '', $p['position_en'] ?? '');
            if (!isset($groups[$tier])) {
                $groups[$tier] = ['label_th' => 'อื่นๆ', 'label_en' => 'Other', 'personnel' => []];
            }
            $groups[$tier]['personnel'][] = $p;
        }
        foreach ($groups as $tier => &$g) {
            usort($g['personnel'], fn($a, $b) => ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0)));
        }
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
                $tier = self::getTier($p['position'] ?? '', $p['position_en'] ?? '');
                if ($tier === 6) {
                    $officePersonnel[] = $p;
                }
            }
        }
        $staffPosition = array_values(array_filter($personnel, function ($p) {
            return mb_strpos($p['position'] ?? '', 'เจ้าหน้าที่') !== false;
        }));
        $officeIds = array_fill_keys(array_map(fn($p) => (int) ($p['id'] ?? 0), $officePersonnel), true);
        foreach ($staffPosition as $p) {
            $id = (int) ($p['id'] ?? 0);
            if ($id > 0 && empty($officeIds[$id])) {
                $tier = self::getTier($p['position'] ?? '', $p['position_en'] ?? '');
                if ($tier === 6) {
                    $officePersonnel[] = $p;
                    $officeIds[$id] = true;
                }
            }
        }
        usort($officePersonnel, fn($a, $b) => ((int) ($a['sort_order'] ?? 0)) - ((int) ($b['sort_order'] ?? 0)));

        // หัวหน้าหน่วยวิจัย: เฉพาะ tier 6 ที่สังกัดหน่วย research (หัวหน้าหน่วยวิจัยไปอยู่ผู้บริหารแล้ว)
        $headResearch = [];
        if ($researchUnitId > 0 && $this->personnelModel->db->fieldExists('organization_unit_id', 'personnel')) {
            $headResearch = array_values(array_filter($personnel, function ($p) use ($researchUnitId) {
                if ((int) ($p['organization_unit_id'] ?? 0) !== $researchUnitId) {
                    return false;
                }
                $tier = self::getTier($p['position'] ?? '', $p['position_en'] ?? '');
                return $tier === 6;
            }));
            usort($headResearch, fn($a, $b) => ((int) ($a['sort_order'] ?? 0)) - ((int) ($b['sort_order'] ?? 0)));
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
                    if ($person && mb_strpos($person['position'] ?? '', 'ประธานหลักสูตร') !== false) {
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
        return $sections;
    }

    /**
     * ตำแหน่งในโครงสร้าง แบ่งตามฝ่ายงาน (สำหรับ optgroup ใน dropdown)
     * คืนค่า [ 'ชื่อฝ่าย' => [ 'value' => 'label', ... ], ... ]
     */
    private function getPositionOptions(): array
    {
        return [
            'บริหาร' => [
                'คณบดี' => 'คณบดี',
                'รองคณบดี' => 'รองคณบดี',
                'ผู้ช่วยคณบดี' => 'ผู้ช่วยคณบดี',
            ],
            'หน่วยงานวิจัย' => [
                'หัวหน้าหน่วยการจัดการงานวิจัย' => 'หัวหน้าหน่วยการจัดการงานวิจัย',
                'กรรมการหน่วยจัดการงานวิจัย' => 'กรรมการหน่วยจัดการงานวิจัย',
            ],
            'สำนักงานคณบดี' => [
                'หัวหน้าสำนักงานคณบดี' => 'หัวหน้าสำนักงานคณบดี',
                'เจ้าหน้าที่' => 'เจ้าหน้าที่',
            ],
            'หลักสูตร' => [
                'ประธานหลักสูตร' => 'ประธานหลักสูตร',
                'อาจารย์ประจำหลักสูตร' => 'อาจารย์ประจำหลักสูตร',
            ],
        ];
    }

    /** ตัวเลือกคำนำหน้าชื่อ ภาษาไทย */
    private function getAcademicTitleOptions(): array
    {
        return [
            '' => '— ไม่ระบุ —',
            'นาย' => 'นาย',
            'นาง' => 'นาง',
            'นางสาว' => 'นางสาว',
            'ดร.' => 'ดร.',
            'อาจารย์' => 'อาจารย์',
            'ผู้ช่วยศาสตราจารย์' => 'ผู้ช่วยศาสตราจารย์',
            'ผู้ช่วยศาสตราจารย์ ดร.' => 'ผู้ช่วยศาสตราจารย์ ดร.',
            'รองศาสตราจารย์' => 'รองศาสตราจารย์',
            'รองศาสตราจารย์ ดร.' => 'รองศาสตราจารย์ ดร.',
            'ศาสตราจารย์' => 'ศาสตราจารย์',
            'ศาสตราจารย์ ดร.' => 'ศาสตราจารย์ ดร.',
        ];
    }

    /** ตัวเลือกคำนำหน้าชื่อ ภาษาอังกฤษ */
    private function getAcademicTitleOptionsEn(): array
    {
        return [
            '' => '— Not specified —',
            'Mr.' => 'Mr.',
            'Mrs.' => 'Mrs.',
            'Miss' => 'Miss',
            'Dr.' => 'Dr.',
            'Lecturer' => 'Lecturer',
            'Asst. Prof.' => 'Asst. Prof.',
            'Asst. Prof. Dr.' => 'Asst. Prof. Dr.',
            'Assoc. Prof.' => 'Assoc. Prof.',
            'Assoc. Prof. Dr.' => 'Assoc. Prof. Dr.',
            'Prof.' => 'Prof.',
            'Prof. Dr.' => 'Prof. Dr.',
        ];
    }

    /**
     * ฟอร์มเพิ่มบุคลากร — เลือกจากตาราง user ได้
     */
    public function create()
    {
        // แสดงทุก user ใน autocomplete พร้อมสถานะ is_linked เพื่อให้รู้ว่าผู้ใดถูกผูกกับบุคลากรแล้ว
        $usersForPersonnel = $this->userModel->getListForPersonnel(false);

        $data = [
            'page_title' => 'เพิ่มบุคลากร',
            'person' => [
                'name' => '',
                'name_en' => '',
                'email' => '',
                'position' => 'อาจารย์',
                'position_en' => '',
                'academic_title' => '',
                'academic_title_en' => '',
                'sort_order' => 0,
                'phone' => '',
                'organization_unit_id' => null,
                'program_id' => null,
            ],
            'personnel_programs' => [],
            'position_options' => $this->getPositionOptions(),
            'academic_title_options' => $this->getAcademicTitleOptions(),
            'academic_title_options_en' => $this->getAcademicTitleOptionsEn(),
            'programs' => $this->programModel->getWithDepartment(),
            'users_for_personnel' => $usersForPersonnel,
        ];

        SeedEnsure::ensureComputerEngineering($this->personnelModel->db);

        $orgUnitModel = new OrganizationUnitModel();
        $data['organization_units'] = $orgUnitModel->getOrdered();

        return view('admin/organization/create', $data);
    }

    /** ตัวเลือกบทบาทในหลักสูตร (ใช้ในฟอร์มหลายหลักสูตร) - Uses OrganizationRoles constants */
    private function getRoleInCurriculumOptions(): array
    {
        $options = ['' => '— ไม่ระบุ —'];
        foreach (OrganizationRoles::CURRICULUM_ROLES as $key => $data) {
            $options[$data['th']] = $data['th'];
        }
        return $options;
    }

    /** โฟลเดอร์อัปโหลดรูปบุคลากร (อยู่ใต้ writable) */
    private static function staffUploadPath(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'staff';
    }

    /** บันทึก path รูปที่อัปโหลด (เช่น staff/filename.jpg) หรือ null */
    private function handleStaffImageUpload(): ?string
    {
        // LOG: Start upload process
        log_message('info', 'Organization::handleStaffImageUpload called');

        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            log_message('info', 'No valid file uploaded or upload error: ' . ($file ? $file->getError() : 'No file object'));
            return null;
        }
        $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $validTypes)) {
            log_message('error', 'Invalid mime type: ' . $file->getMimeType());
            return null;
        }
        if ($file->getSize() > 20 * 1024 * 1024) {
            log_message('error', 'File too large: ' . $file->getSize());
            return null; // max 20MB
        }
        $dir = self::staffUploadPath();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = $file->getRandomName();
        $file->move($dir, $newName);

        $fullPath = $dir . DIRECTORY_SEPARATOR . $newName;
        $maxBytes = 1 * 1024 * 1024; // 1 MB
        if (is_file($fullPath) && filesize($fullPath) > $maxBytes) {
            helper('image');
            if (resize_image_to_max_bytes($fullPath, $maxBytes)) {
                log_message('info', 'Profile image resized to under 1 MB: ' . $newName);
            }
        }

        // สร้าง thumbnail สำหรับแสดงในรายการ/ตาราง (โหลดเร็ว)
        helper('image');
        if (create_staff_thumbnail($fullPath)) {
            log_message('info', 'Staff thumbnail created: ' . $newName);
        }

        $relativePath = 'staff/' . $newName;
        log_message('info', 'File uploaded successfully to: ' . $relativePath . ' (Saved in: ' . $dir . ')');
        return $relativePath;
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

    /** ลบไฟล์รูปจาก path แบบ relative (staff/filename) ถ้ามี รวม thumbnail ด้วย */
    private static function deleteStaffImageFile(string $relativePath): void
    {
        if ($relativePath === '' || strpos($relativePath, 'staff/') !== 0) {
            return;
        }
        $fn = basename($relativePath);
        $dir = self::staffUploadPath();
        $path = $dir . DIRECTORY_SEPARATOR . $fn;
        $thumbPath = $dir . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $fn;
        if (is_file($path)) {
            @unlink($path);
        }
        if (is_file($thumbPath)) {
            @unlink($thumbPath);
        }
        $publicDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'staff';
        $publicPath = $publicDir . DIRECTORY_SEPARATOR . $fn;
        if (is_file($publicPath)) {
            @unlink($publicPath);
        }
        $publicThumb = $publicDir . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $fn;
        if (is_file($publicThumb)) {
            @unlink($publicThumb);
        }
    }

    /**
     * บันทึกบุคลากรใหม่ — ไม่บังคับกรอกชื่อเมื่อเลือก user (ใช้ชื่อจากตาราง user)
     */
    public function store()
    {
        $postedUserUid = $this->request->getPost('user_uid');
        $selectedUser = null;
        if ($postedUserUid !== null && $postedUserUid !== '' && (int) $postedUserUid > 0) {
            $selectedUser = $this->userModel->find((int) $postedUserUid);
            // ถ้า user นี้มีใน personnel แล้ว ห้ามเพิ่มซ้ำ
            if ($selectedUser && $this->personnelModel->db->fieldExists('user_uid', 'personnel')) {
                $existing = $this->personnelModel->where('user_uid', (int) $postedUserUid)->first();
                if ($existing) {
                    return redirect()->back()
                        ->withInput()
                        ->with('errors', ['user_uid' => 'ผู้ใช้นี้มีในโครงสร้างองค์กรแล้ว กรุณาเลือกผู้ใช้อื่นหรือเลือก "กรอกเอง"']);
                }
            }
        }
        $rules = [
            'name' => $selectedUser ? 'permit_empty|min_length[1]' : 'required|min_length[1]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $programRoles = $this->collectProgramAssignmentsFromPost();
        $position = $this->request->getPost('position') ?: null;
        $chairError = $this->validateProgramChairRequirement($position, $programRoles);
        if ($chairError !== null) {
            return redirect()->back()->withInput()->with('errors', ['program_chair' => $chairError]);
        }
        $programRoles = $this->applyChairRoleFromPosition($position, $programRoles);

        // organization_unit_id: จากฟอร์ม > จากสาขาหลัก > fallback ตำแหน่งสำนักงาน
        $organizationUnitId = null;
        $postedOrgUnitId = $this->request->getPost('organization_unit_id');
        if ($postedOrgUnitId !== null && $postedOrgUnitId !== '' && (int) $postedOrgUnitId > 0) {
            $organizationUnitId = (int) $postedOrgUnitId;
        }
        $programId = null;
        if (!empty($programRoles)) {
            $primaryProgram = null;
            foreach ($programRoles as $pr) {
                if (!empty($pr['is_primary'])) {
                    $primaryProgram = $pr;
                    break;
                }
            }
            if (!$primaryProgram) {
                $primaryProgram = $programRoles[0];
            }

            $primaryProgramId = (int) ($primaryProgram['program_id'] ?? 0);
            if ($primaryProgramId > 0) {
                if ($organizationUnitId === null) {
                    $program = $this->programModel->find($primaryProgramId);
                    if ($program && !empty($program['organization_unit_id'])) {
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

        $email = $this->request->getPost('email') ?: null;
        $userUid = null;
        $selectedUser = null;
        $postedUserUid = $this->request->getPost('user_uid');
        if ($postedUserUid !== null && $postedUserUid !== '') {
            $postedUserUid = (int) $postedUserUid;
            if ($postedUserUid > 0) {
                $selectedUser = $this->userModel->find($postedUserUid);
                if ($selectedUser) {
                    $userUid = $postedUserUid;
                    $email = trim($selectedUser['email'] ?? '') ?: $email;
                }
            }
        }
        if ($userUid === null && $email !== null && $email !== '' && $this->personnelModel->db->fieldExists('user_uid', 'personnel')) {
            $userRow = $this->userModel->where('email', $email)->first();
            $userUid = $userRow ? (int) ($userRow['uid'] ?? 0) : null;
        }

        $data = [
            'name' => $selectedUser ? (trim($this->userModel->getFullName($selectedUser)) ?: $this->request->getPost('name')) : $this->request->getPost('name'),
            'name_en' => $selectedUser ? (trim(($selectedUser['gf_name'] ?? '') . ' ' . ($selectedUser['gl_name'] ?? '')) ?: $this->request->getPost('name_en')) : ($this->request->getPost('name_en') ?: null),
            'email' => $email,
            'phone' => $this->request->getPost('phone') ?: null,
            'position' => $this->request->getPost('position') ?: null,
            'position_en' => $this->request->getPost('position_en') ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'status' => 'active',
        ];
        if ($this->personnelModel->db->fieldExists('organization_unit_id', 'personnel')) {
            $data['organization_unit_id'] = $organizationUnitId;
        }
        if ($this->personnelModel->db->fieldExists('position_detail', 'personnel')) {
            $data['position_detail'] = $this->request->getPost('position_detail') ?: null;
        }
        if ($this->personnelModel->db->fieldExists('academic_title', 'personnel')) {
            $data['academic_title'] = $this->request->getPost('academic_title') ?: null;
        }
        if ($this->personnelModel->db->fieldExists('academic_title_en', 'personnel')) {
            $data['academic_title_en'] = $this->request->getPost('academic_title_en') ?: null;
        }
        if ($this->personnelModel->db->fieldExists('program_id', 'personnel')) {
            $data['program_id'] = $programId;
        }
        if ($this->personnelModel->db->fieldExists('user_uid', 'personnel')) {
            $data['user_uid'] = $userUid;
        }

        // รูปโปรไฟล์เก็บในตาราง user เท่านั้น (ไม่ใช้ personnel.image)
        // CHANGE: Allow saving to personnel.image if user_uid is null
        $imagePath = $this->handleStaffImageUpload();
        if ($imagePath !== null) {
            if ($userUid !== null && (int) $userUid > 0) {
                log_message('info', 'Saving profile image to USER table (uid=' . $userUid . '): ' . $imagePath);
                $this->saveUserProfileImage((int) $userUid, $imagePath);
            } else {
                log_message('info', 'Saving profile image to PERSONNEL table (new record): ' . $imagePath);
                $data['image'] = $imagePath;
            }
        } else {
            log_message('info', 'No image uploaded for new personnel');
        }

        $newId = $this->personnelModel->insert($data);
        if ($newId && $this->personnelModel->db->tableExists('personnel_programs') && !empty($programRoles)) {
            $this->personnelProgramModel->setProgramsForPersonnelWithPrimary((int) $newId, $programRoles);
        }
        $this->programModel->syncChairFromPersonnelPrograms();

        return redirect()->to(base_url('admin/organization'))->with('success', 'เพิ่มบุคลากรแล้ว');
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

        $data = [
            'page_title' => 'แก้ไขตำแหน่งในโครงสร้างองค์กร',
            'person' => $person,
            'personnel_programs' => $personnelPrograms,
            'position_options' => $this->getPositionOptions(),
            'academic_title_options' => $this->getAcademicTitleOptions(),
            'academic_title_options_en' => $this->getAcademicTitleOptionsEn(),
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

        $name = $this->request->getPost('name');
        $nameEn = $this->request->getPost('name_en');
        $email = $this->request->getPost('email');
        if ($linkedUser && (trim($name ?? '') === '')) {
            $name = trim($this->userModel->getFullName($linkedUser));
        }
        if ($linkedUser && (trim($nameEn ?? '') === '')) {
            $nameEn = trim(($linkedUser['gf_name'] ?? '') . ' ' . ($linkedUser['gl_name'] ?? ''));
        }
        $position = $this->request->getPost('position') ?: null;
        $positionEn = $this->request->getPost('position_en');
        $sortOrder = (int) $this->request->getPost('sort_order');

        $programRoles = $this->collectProgramAssignmentsFromPost();
        $chairError = $this->validateProgramChairRequirement($position, $programRoles);
        if ($chairError !== null) {
            return redirect()->back()->withInput()->with('errors', ['program_chair' => $chairError]);
        }
        $programRoles = $this->applyChairRoleFromPosition($position, $programRoles);

        // organization_unit_id: จากฟอร์ม > จากสาขาหลัก > fallback ตำแหน่งสำนักงาน
        $organizationUnitId = null;
        $postedOrgUnitId = $this->request->getPost('organization_unit_id');
        if ($postedOrgUnitId !== null && $postedOrgUnitId !== '' && (int) $postedOrgUnitId > 0) {
            $organizationUnitId = (int) $postedOrgUnitId;
        }
        $programId = null;
        if (!empty($programRoles)) {
            $primaryProgram = null;
            foreach ($programRoles as $pr) {
                if (!empty($pr['is_primary'])) {
                    $primaryProgram = $pr;
                    break;
                }
            }
            if (!$primaryProgram) {
                $primaryProgram = $programRoles[0];
            }

            $primaryProgramId = (int) ($primaryProgram['program_id'] ?? 0);
            if ($primaryProgramId > 0) {
                if ($organizationUnitId === null) {
                    $program = $this->programModel->find($primaryProgramId);
                    if ($program && !empty($program['organization_unit_id'])) {
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
            $updateData['position_detail'] = $this->request->getPost('position_detail') ?: null;
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
        $imagePath = $this->handleStaffImageUpload();
        if ($imagePath !== null) {
            if ($userUidForValidation > 0 && $linkedUser) {
                log_message('info', 'Updating profile image in USER table (uid=' . $userUidForValidation . '): ' . $imagePath);
                $oldImage = trim($linkedUser['profile_picture'] ?? $linkedUser['profile_image'] ?? '');
                self::deleteStaffImageFile($oldImage);
                $this->saveUserProfileImage($userUidForValidation, $imagePath);
            } else {
                // Save to personnel table
                log_message('info', 'Updating profile image in PERSONNEL table (id=' . $id . '): ' . $imagePath);
                $oldImage = trim($person['image'] ?? '');
                self::deleteStaffImageFile($oldImage);
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

        if ($this->personnelModel->db->tableExists('personnel_programs')) {
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
