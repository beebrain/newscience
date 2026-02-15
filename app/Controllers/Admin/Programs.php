<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProgramModel;
use App\Models\OrganizationUnitModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Libraries\SeedEnsure;
use CodeIgniter\Database\Exceptions\DataException;

class Programs extends BaseController
{
    protected $programModel;
    protected $organizationUnitModel;
    protected $personnelModel;
    protected $personnelProgramModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->organizationUnitModel = new OrganizationUnitModel();
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
    }

    /**
     * รายการหลักสูตรทั้งหมด (สำหรับแอดมิน)
     * Uses getWithCoordinator() which gets coordinator from personnel_programs (SSOT)
     */
    public function index()
    {
        SeedEnsure::ensureComputerEngineering($this->programModel->db);

        // Use new method that gets coordinator from personnel_programs
        $programs = $this->programModel->getWithCoordinator();

        // Build coordinator_names map for backward compatibility with view
        $coordinatorNames = [];
        foreach ($programs as $p) {
            $coordId = $p['coordinator_id_from_pp'] ?? null;
            if ($coordId && !empty($p['coordinator_name'])) {
                $coordinatorNames[$coordId] = $p['coordinator_name'];
            }
        }

        $data = [
            'page_title' => 'จัดการหลักสูตร',
            'programs' => $programs,
            'coordinator_names' => $coordinatorNames,
        ];
        return view('admin/programs/index', $data);
    }

    /**
     * แสดงฟอร์มเพิ่มหลักสูตร
     */
    public function create()
    {
        SeedEnsure::ensureComputerEngineering($this->programModel->db);

        $data = [
            'page_title' => 'เพิ่มหลักสูตร',
            'departments' => $this->getCurriculumOrganizationUnits(),
        ];
        return view('admin/programs/create', $data);
    }

    /**
     * บันทึกหลักสูตรใหม่
     */
    public function store()
    {
        $rules = [
            'name_th' => 'required|min_length[1]|max_length[255]',
            'level' => 'required|in_list[bachelor,master,doctorate]',
            'status' => 'required|in_list[active,inactive]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $sortOrder = (int) $this->request->getPost('sort_order');
        if ($sortOrder <= 0) {
            $max = $this->programModel->selectMax('sort_order')->first();
            $sortOrder = ((int) ($max['sort_order'] ?? 0)) + 1;
        }

        $orgUnitId = $this->request->getPost('organization_unit_id') ?: $this->defaultOrgUnitIdForLevel($this->request->getPost('level'));
        $data = [
            'name_th' => $this->request->getPost('name_th'),
            'name_en' => $this->request->getPost('name_en'),
            'degree_th' => $this->request->getPost('degree_th'),
            'degree_en' => $this->request->getPost('degree_en'),
            'level' => $this->request->getPost('level'),
            'description' => $this->request->getPost('description'),
            'description_en' => $this->request->getPost('description_en'),
            'credits' => $this->request->getPost('credits') ? (int) $this->request->getPost('credits') : null,
            'duration' => $this->request->getPost('duration'),
            'website' => $this->request->getPost('website'),
            'sort_order' => $sortOrder,
            'status' => $this->request->getPost('status'),
        ];
        if ($this->programModel->db->fieldExists('organization_unit_id', 'programs')) {
            $data['organization_unit_id'] = $orgUnitId;
        }

        $id = $this->programModel->insert($data);
        if (!$id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'บันทึกหลักสูตรไม่สำเร็จ');
        }
        return redirect()->to(base_url('admin/programs'))->with('success', 'เพิ่มหลักสูตรแล้ว');
    }

    /**
     * แสดงฟอร์มแก้ไขหลักสูตร
     * Coordinator is retrieved from personnel_programs (SSOT)
     */
    public function edit($id)
    {
        SeedEnsure::ensureComputerEngineering($this->programModel->db);

        $program = $this->programModel->find($id);
        if (!$program) {
            return redirect()->to(base_url('admin/programs'))->with('error', 'ไม่พบหลักสูตร');
        }

        // ประธานหลักสูตร: ใช้ programs.chair_personnel_id ก่อน แล้ว fallback จาก personnel_programs
        $currentCoordinatorId = (int) ($program['chair_personnel_id'] ?? 0) ?: null;

        $ppRows = $this->personnelProgramModel->getByProgramId((int) $id);
        $personnelIds = array_map(fn($r) => (int) $r['personnel_id'], $ppRows);

        $programPersonnel = [];
        if (!empty($personnelIds)) {
            $personnelList = $this->personnelModel->whereIn('id', $personnelIds)->findAll();
            $personnelMap = [];
            foreach ($personnelList as $p) {
                $personnelMap[(int) $p['id']] = $p;
            }

            foreach ($ppRows as $pp) {
                $pId = (int) $pp['personnel_id'];
                $p = $personnelMap[$pId] ?? null;
                if ($p) {
                    $programPersonnel[] = [
                        'id' => $pId,
                        'name' => trim($p['name'] ?? ''),
                        'role' => $pp['role_in_curriculum'] ?? '',
                    ];
                    if ($currentCoordinatorId === null && mb_strpos($pp['role_in_curriculum'] ?? '', 'ประธาน') !== false) {
                        $currentCoordinatorId = $pId;
                    }
                }
            }
        }

        $data = [
            'page_title' => 'แก้ไขหลักสูตร',
            'program' => $program,
            'departments' => $this->getCurriculumOrganizationUnits(),
            'program_personnel' => $programPersonnel,
            'current_coordinator_id' => $currentCoordinatorId,
        ];
        return view('admin/programs/edit', $data);
    }

    /**
     * อัปเดตหลักสูตร
     */
    public function update($id)
    {
        $program = $this->programModel->find($id);
        if (!$program) {
            return redirect()->to(base_url('admin/programs'))->with('error', 'ไม่พบหลักสูตร');
        }

        $rules = [
            'name_th' => 'required|min_length[1]|max_length[255]',
            'level' => 'required|in_list[bachelor,master,doctorate]',
            'status' => 'required|in_list[active,inactive]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Note: coordinator_id is no longer used (deprecated)
        // Coordinator is determined from personnel_programs.role_in_curriculum = 'ประธานหลักสูตร'
        $orgUnitId = $this->request->getPost('organization_unit_id') ?: $this->defaultOrgUnitIdForLevel($this->request->getPost('level'));
        $data = [
            'name_th' => $this->request->getPost('name_th'),
            'name_en' => $this->request->getPost('name_en'),
            'degree_th' => $this->request->getPost('degree_th'),
            'degree_en' => $this->request->getPost('degree_en'),
            'level' => $this->request->getPost('level'),
            'description' => $this->request->getPost('description'),
            'description_en' => $this->request->getPost('description_en'),
            'credits' => $this->request->getPost('credits') ? (int) $this->request->getPost('credits') : null,
            'duration' => $this->request->getPost('duration'),
            'website' => $this->request->getPost('website'),
            'sort_order' => (int) $this->request->getPost('sort_order') ?: $program['sort_order'],
            'status' => $this->request->getPost('status'),
        ];
        if ($this->programModel->db->fieldExists('organization_unit_id', 'programs')) {
            $data['organization_unit_id'] = $orgUnitId;
        }

        try {
            $this->programModel->update($id, $data);
        } catch (DataException $e) {
            if (strpos($e->getMessage(), 'no data to update') === false && strpos($e->getMessage(), 'empty') === false) {
                throw $e;
            }
        }

        // Note: No longer syncing coordinator here
        // Coordinator is managed through Organization controller via personnel_programs
        // personnel_programs.role_in_curriculum = 'ประธานหลักสูตร' is the Single Source of Truth

        return redirect()->to(base_url('admin/programs'))->with('success', 'บันทึกการแก้ไขหลักสูตรแล้ว');
    }

    /**
     * ลบหลักสูตร (soft: ตั้งเป็น inactive หรือลบจริงตามนโยบาย)
     */
    public function delete($id)
    {
        $program = $this->programModel->find($id);
        if (!$program) {
            return redirect()->to(base_url('admin/programs'))->with('error', 'ไม่พบหลักสูตร');
        }
        // ตั้งเป็น inactive แทนการลบ เพื่อไม่ให้กระทบ personnel_programs
        $this->programModel->update($id, ['status' => 'inactive']);
        return redirect()->to(base_url('admin/programs'))->with('success', 'ปิดการแสดงหลักสูตรแล้ว (ตั้งเป็นไม่ใช้งาน)');
    }

    /**
     * หน่วยงานที่เป็นหลักสูตรเท่านั้น (ป.ตรี id=4, บัณฑิต id=5) สำหรับ dropdown ในฟอร์มหลักสูตร
     */
    protected function getCurriculumOrganizationUnits(): array
    {
        $all = $this->organizationUnitModel->getOrdered();
        return array_values(array_filter($all, function ($row) {
            $id = (int) ($row['id'] ?? 0);
            return $id === 4 || $id === 5; // หลักสูตรป.ตรี, หลักสูตรบัณฑิต
        }));
    }

    /**
     * คืน organization_unit_id ตามระดับหลักสูตร: bachelor=4, master/doctorate=5
     */
    protected function defaultOrgUnitIdForLevel(?string $level): ?int
    {
        if ($level === 'bachelor') {
            return 4;
        }
        if ($level === 'master' || $level === 'doctorate') {
            return 5;
        }
        return null;
    }
}
