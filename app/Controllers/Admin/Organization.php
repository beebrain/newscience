<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Models\DepartmentModel;
use App\Models\ProgramModel;

class Organization extends BaseController
{
    protected $personnelModel;
    protected $personnelProgramModel;
    protected $departmentModel;
    protected $programModel;

    public function __construct()
    {
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
        $this->departmentModel = new DepartmentModel();
        $this->programModel = new ProgramModel();
    }

    /** ตำแหน่งในโครงสร้าง: 1=คณบดี, 2=รองคณบดี, 3=ผู้ช่วยคณบดี, 4=ประธานหลักสูตร, 5=อื่นๆ */
    private static function getTier(string $position, string $positionEn = ''): int
    {
        $p = $position ?: '';
        $hasDean = mb_strpos($p, 'คณบดี') !== false;
        $hasVice = mb_strpos($p, 'รอง') !== false;
        $hasAssistant = mb_strpos($p, 'ผู้ช่วย') !== false;
        $hasProgramChair = mb_strpos($p, 'ประธานหลักสูตร') !== false;
        if ($hasDean && $hasVice && !$hasAssistant) return 2;
        if ($hasDean && $hasAssistant) return 3;
        if ($hasDean) return 1;
        if ($hasProgramChair) return 4;
        if ($positionEn !== '') {
            $pe = strtolower($positionEn);
            if (strpos($pe, 'associate dean') !== false || strpos($pe, 'vice dean') !== false) return 2;
            if (strpos($pe, 'assistant dean') !== false) return 3;
            if (strpos($pe, 'dean') !== false) return 1;
            if (strpos($pe, 'program chair') !== false || strpos($pe, 'chair') !== false) return 4;
        }
        return 5;
    }

    /** จัดกลุ่มบุคลากรตาม tier (คณบดี, รองคณบดี, ผู้ช่วยคณบดี, ประธานหลักสูตร, อื่นๆ) */
    private function groupByTier(array $personnel): array
    {
        $groups = [
            1 => ['label_th' => 'คณบดี', 'label_en' => 'Dean', 'personnel' => []],
            2 => ['label_th' => 'รองคณบดี', 'label_en' => 'Associate Dean', 'personnel' => []],
            3 => ['label_th' => 'ผู้ช่วยคณบดี', 'label_en' => 'Assistant Dean', 'personnel' => []],
            4 => ['label_th' => 'ประธานหลักสูตร', 'label_en' => 'Program Chair', 'personnel' => []],
            5 => ['label_th' => 'อาจารย์และบุคลากรในสังกัด', 'label_en' => 'Faculty & Staff', 'personnel' => []],
        ];
        foreach ($personnel as $p) {
            $tier = self::getTier($p['position'] ?? '', $p['position_en'] ?? '');
            $groups[$tier]['personnel'][] = $p;
        }
        foreach ($groups as $tier => &$g) {
            usort($g['personnel'], fn($a, $b) => ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0)));
        }
        return $groups;
    }

    /**
     * รายการโครงสร้างองค์กร (แบ่งตาม tier)
     */
    public function index()
    {
        $personnel = $this->personnelModel->getActiveWithDepartment();
        if ($this->personnelModel->db->tableExists('personnel_programs')) {
            foreach ($personnel as &$p) {
                $ppList = $this->personnelProgramModel->getByPersonnelId((int) ($p['id'] ?? 0));
                if (empty($ppList)) {
                    $name = $p['program_name_th'] ?? $p['program_name_en'] ?? '';
                    $p['programs_list'] = $name;
                    $p['programs_list_tags'] = $name ? [['name' => $name, 'role' => '']] : [];
                } else {
                    $tags = [];
                    foreach ($ppList as $pp) {
                        $pr = $this->programModel->find((int) ($pp['program_id'] ?? 0));
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
        $groups = $this->groupByTier($personnel);

        // ตัวกรองตำแหน่ง (ค้นหา)
        $positionFilterOptions = $this->getPositionFilterOptions();
        $filterTier = $this->request->getGet('position');
        if ($filterTier !== null && $filterTier !== '') {
            $filterTier = (int) $filterTier;
            if ($filterTier >= 1 && $filterTier <= 5) {
                $personnel = array_filter($personnel, function ($p) use ($filterTier) {
                    $tier = self::getTier($p['position'] ?? '', $p['position_en'] ?? '');
                    return $tier === $filterTier;
                });
                $groups = $this->groupByTier(array_values($personnel));
                $groups = [$filterTier => $groups[$filterTier]];
            }
        }

        $data = [
            'page_title' => 'โครงสร้างองค์กร',
            'groups' => $groups,
            'position_filter_options' => $positionFilterOptions,
            'filter_position' => $filterTier ?? '',
        ];

        return view('admin/organization/index', $data);
    }

    /** ตัวเลือกสำหรับตัวกรองตำแหน่ง (ค้นหา) */
    private function getPositionFilterOptions(): array
    {
        return [
            '' => 'ทั้งหมด',
            1 => 'คณบดี',
            2 => 'รองคณบดี',
            3 => 'ผู้ช่วยคณบดี',
            4 => 'ประธานหลักสูตร',
            5 => 'อาจารย์และบุคลากรในสังกัด',
        ];
    }

    /** ตำแหน่งบริหารและตำแหน่งอื่น (ใช้ใน create/edit) */
    private function getPositionOptions(): array
    {
        return [
            // ตำแหน่งบริหาร
            'คณบดี' => 'คณบดี',
            'รองคณบดี' => 'รองคณบดี',
            'ผู้ช่วยคณบดี' => 'ผู้ช่วยคณบดี',
            'ประธานหลักสูตร' => 'ประธานหลักสูตร',
            'หัวหน้าหน่วยจัดการงานวิจัย' => 'หัวหน้าหน่วยจัดการงานวิจัย',
            'ผู้อำนวยการ สำนักงานคณบดี' => 'ผู้อำนวยการ สำนักงานคณบดี',
            // รองคณบดี แยกฝ่าย
            'รองคณบดี ฝ่ายวิชาการ' => 'รองคณบดี ฝ่ายวิชาการ',
            'รองคณบดี ฝ่ายวิจัยและนวัตกรรม' => 'รองคณบดี ฝ่ายวิจัยและนวัตกรรม',
            'รองคณบดี ฝ่ายแผนและพัฒนาคุณภาพ' => 'รองคณบดี ฝ่ายแผนและพัฒนาคุณภาพ',
            'ผู้ช่วยคณบดี ฝ่ายวิชาการ' => 'ผู้ช่วยคณบดี ฝ่ายวิชาการ',
            // อื่นๆ
            'อาจารย์' => 'อาจารย์',
            '' => '(ไม่ระบุ)',
        ];
    }

    /**
     * ฟอร์มเพิ่มบุคลากร
     */
    public function create()
    {
        $data = [
            'page_title' => 'เพิ่มบุคลากร',
            'person' => [
                'title' => '', 'first_name' => '', 'last_name' => '',
                'first_name_en' => '', 'last_name_en' => '',
                'email' => '', 'position' => '', 'position_en' => '',
                'sort_order' => 0, 'phone' => '',
                'department_id' => null, 'program_id' => null,
            ],
            'personnel_programs' => [],
            'position_options' => $this->getPositionOptions(),
            'programs' => $this->programModel->getWithDepartment(),
            'role_in_curriculum_options' => $this->getRoleInCurriculumOptions(),
        ];

        return view('admin/organization/create', $data);
    }

    /** ตัวเลือกบทบาทในหลักสูตร (ใช้ในฟอร์มหลายหลักสูตร) */
    private function getRoleInCurriculumOptions(): array
    {
        return [
            '' => '— ไม่ระบุ —',
            'ประธานหลักสูตร' => 'ประธานหลักสูตร',
            'กรรมการหลักสูตร' => 'กรรมการหลักสูตร',
            'อาจารย์ประจำหลักสูตร' => 'อาจารย์ประจำหลักสูตร',
        ];
    }

    /** โฟลเดอร์อัปโหลดรูปบุคลากร (อยู่ใต้ public) */
    private static function staffUploadPath(): string
    {
        return FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'staff';
    }

    /** บันทึก path รูปที่อัปโหลด (เช่น staff/filename.jpg) หรือ null */
    private function handleStaffImageUpload(): ?string
    {
        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $validTypes)) {
            return null;
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            return null; // max 5MB
        }
        $dir = self::staffUploadPath();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = $file->getRandomName();
        $file->move($dir, $newName);
        return 'staff/' . $newName;
    }

    /**
     * บันทึกบุคลากรใหม่
     */
    public function store()
    {
        $rules = [
            'first_name' => 'required|min_length[1]',
            'last_name'  => 'required|min_length[1]',
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

        $departmentId = null;
        $programId = null;
        if (!empty($programRoles)) {
            $firstProgramId = (int) ($programRoles[0]['program_id'] ?? 0);
            if ($firstProgramId > 0) {
                $program = $this->programModel->find($firstProgramId);
                if ($program && !empty($program['department_id'])) {
                    $departmentId = (int) $program['department_id'];
                }
                $programId = $firstProgramId;
            }
        }

        $data = [
            'title' => $this->request->getPost('title') ?: null,
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'first_name_en' => $this->request->getPost('first_name_en') ?: null,
            'last_name_en' => $this->request->getPost('last_name_en') ?: null,
            'email' => $this->request->getPost('email') ?: null,
            'phone' => $this->request->getPost('phone') ?: null,
            'position' => $this->request->getPost('position') ?: null,
            'position_en' => $this->request->getPost('position_en') ?: null,
            'department_id' => $departmentId,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'status' => 'active',
        ];
        if ($this->personnelModel->db->fieldExists('program_id', 'personnel')) {
            $data['program_id'] = $programId;
        }

        $imagePath = $this->handleStaffImageUpload();
        if ($imagePath !== null) {
            $data['image'] = $imagePath;
        }

        $newId = $this->personnelModel->insert($data);
        if ($newId && $this->personnelModel->db->tableExists('personnel_programs') && !empty($programRoles)) {
            $this->personnelProgramModel->setProgramsForPersonnel((int) $newId, $programRoles);
        }

        return redirect()->to(base_url('admin/organization'))->with('success', 'เพิ่มบุคลากรแล้ว');
    }

    /** อ่านรายการหลักสูตรที่ส่งมาจากฟอร์ม (program_assignments[]) */
    private function collectProgramAssignmentsFromPost(): array
    {
        $programIds = $this->request->getPost('program_assignments')['program_id'] ?? [];
        $roles = $this->request->getPost('program_assignments')['role_in_curriculum'] ?? [];
        if (!is_array($programIds)) {
            return [];
        }
        $out = [];
        foreach ($programIds as $i => $pid) {
            $pid = (int) $pid;
            if ($pid <= 0) continue;
            $out[] = [
                'program_id' => $pid,
                'role_in_curriculum' => isset($roles[$i]) && $roles[$i] !== '' ? $roles[$i] : null,
            ];
        }
        return $out;
    }

    /**
     * ถ้าตำแหน่งเป็น ประธานหลักสูตร ต้องมีอย่างน้อย 1 หลักสูตรที่เลือกบทบาท ประธานหลักสูตร
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
        foreach ($programRoles as $pr) {
            if (isset($pr['role_in_curriculum']) && $pr['role_in_curriculum'] === 'ประธานหลักสูตร') {
                return null;
            }
        }
        return 'เมื่อตำแหน่งเป็น ประธานหลักสูตร ต้องระบุว่าประธานของหลักสูตรใด โดยเพิ่มหลักสูตรและเลือกบทบาท "ประธานหลักสูตร" ในหลักสูตรที่เลือก';
    }

    /**
     * แก้ไขตำแหน่ง/ลำดับบุคลากรในโครงสร้าง
     */
    public function edit(int $id)
    {
        $person = $this->personnelModel->find($id);
        if (!$person) {
            return redirect()->to(base_url('admin/organization'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $personnelPrograms = $this->personnelModel->db->tableExists('personnel_programs')
            ? $this->personnelProgramModel->getByPersonnelId((int) $id)
            : [];

        $data = [
            'page_title' => 'แก้ไขตำแหน่งในโครงสร้าง',
            'person' => $person,
            'personnel_programs' => $personnelPrograms,
            'position_options' => $this->getPositionOptions(),
            'programs' => $this->programModel->getWithDepartment(),
            'role_in_curriculum_options' => $this->getRoleInCurriculumOptions(),
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

        $rules = [
            'first_name' => 'required|min_length[1]',
            'last_name'  => 'required|min_length[1]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $firstName = $this->request->getPost('first_name');
        $lastName = $this->request->getPost('last_name');
        $firstNameEn = $this->request->getPost('first_name_en');
        $lastNameEn = $this->request->getPost('last_name_en');
        $email = $this->request->getPost('email');
        $position = $this->request->getPost('position') ?: null;
        $positionEn = $this->request->getPost('position_en');
        $sortOrder = (int) $this->request->getPost('sort_order');

        $programRoles = $this->collectProgramAssignmentsFromPost();
        $chairError = $this->validateProgramChairRequirement($position, $programRoles);
        if ($chairError !== null) {
            return redirect()->back()->withInput()->with('errors', ['program_chair' => $chairError]);
        }
        $departmentId = null;
        $programId = null;
        if (!empty($programRoles)) {
            $firstProgramId = (int) ($programRoles[0]['program_id'] ?? 0);
            if ($firstProgramId > 0) {
                $program = $this->programModel->find($firstProgramId);
                if ($program && !empty($program['department_id'])) {
                    $departmentId = (int) $program['department_id'];
                }
                $programId = $firstProgramId;
            }
        }

        $updateData = [
            'title' => $title === '' ? null : $title,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'first_name_en' => $firstNameEn === '' ? null : $firstNameEn,
            'last_name_en' => $lastNameEn === '' ? null : $lastNameEn,
            'email' => $email === '' ? null : $email,
            'position' => $position === '' ? null : $position,
            'position_en' => $positionEn === '' ? null : $positionEn,
            'department_id' => $departmentId,
            'sort_order' => $sortOrder,
        ];
        if ($this->personnelModel->db->fieldExists('program_id', 'personnel')) {
            $updateData['program_id'] = $programId;
        }

        $imagePath = $this->handleStaffImageUpload();
        if ($imagePath !== null) {
            $oldImage = $person['image'] ?? '';
            if ($oldImage !== '' && strpos($oldImage, 'staff/') === 0) {
                $oldPath = self::staffUploadPath() . DIRECTORY_SEPARATOR . basename($oldImage);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $updateData['image'] = $imagePath;
        }

        $this->personnelModel->update($id, $updateData);

        if ($this->personnelModel->db->tableExists('personnel_programs')) {
            $this->personnelProgramModel->setProgramsForPersonnel((int) $id, $programRoles);
        }

        return redirect()->to(base_url('admin/organization'))->with('success', 'บันทึกการแก้ไขโครงสร้างองค์กรแล้ว');
    }

    /**
     * ลบบุคลากร (ปิดการแสดงผล = status inactive)
     */
    public function delete(int $id)
    {
        $person = $this->personnelModel->find($id);
        if (!$person) {
            return redirect()->to(base_url('admin/organization'))->with('error', 'ไม่พบข้อมูลบุคลากร');
        }

        $this->personnelModel->update($id, ['status' => 'inactive']);

        return redirect()->to(base_url('admin/organization'))->with('success', 'ลบบุคลากรออกจากโครงสร้างแล้ว (ไม่แสดงบนหน้าสาธารณะ)');
    }
}
