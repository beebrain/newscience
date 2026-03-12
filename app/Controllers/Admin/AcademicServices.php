<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AcademicServiceModel;
use App\Models\AcademicServiceParticipantModel;
use App\Models\UserModel;

class AcademicServices extends BaseController
{
    protected AcademicServiceModel $serviceModel;
    protected AcademicServiceParticipantModel $participantModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->serviceModel     = model(AcademicServiceModel::class);
        $this->participantModel = model(AcademicServiceParticipantModel::class);
        $this->userModel        = model(UserModel::class);
    }

    /**
     * รายการบริการวิชาการ (กรองตามปี + ค้นหา)
     */
    public function index()
    {
        $year    = $this->request->getGet('year');
        $keyword = $this->request->getGet('keyword');

        $list = $this->serviceModel->search($keyword, $year);

        $participantCounts = [];
        foreach ($list as $row) {
            $participantCounts[$row['id']] = $this->participantModel->countByServiceId((int) $row['id']);
        }

        $years = $this->serviceModel->getDistinctYears();
        $currentBuddhistYear = (int) date('Y') + 543;
        if (! in_array((string) $currentBuddhistYear, $years, true)) {
            array_unshift($years, (string) $currentBuddhistYear);
        }

        $data = [
            'page_title'         => 'ข้อมูลการบริการวิชาการ',
            'list'               => $list,
            'participant_counts'  => $participantCounts,
            'years'              => $years,
            'selected_year'      => $year,
            'keyword'            => $keyword,
        ];

        return view('admin/academic_services/index', $data);
    }

    /**
     * ฟอร์มเพิ่มรายการ — เปิดเป็น Modal ในหน้า index; URL นี้ redirect ไป index พร้อมเปิด modal
     */
    public function create()
    {
        return redirect()->to(base_url('admin/academic-services?openModal=create'));
    }

    /**
     * บันทึกรายการใหม่
     */
    public function store()
    {
        $rules = [
            'academic_year' => 'permit_empty|max_length[20]',
            'service_date'  => 'required|valid_date',
            'title'         => 'required|max_length[500]',
        ];
        if (! $this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'errors'  => $this->validator->getErrors(),
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($this->request->getPost('has_compensation') === 'yes') {
            $comp = $this->request->getPost('compensation_amount');
            if ($comp === null || $comp === '') {
                $errors = $this->validator->getErrors();
                $errors['compensation_amount'] = 'กรุณาระบุจำนวนเงินค่าตอบแทน';
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'errors' => $errors]);
                }
                return redirect()->back()->withInput()->with('errors', $errors);
            }
        }

        $payload = $this->getServiceDataFromRequest();
        $payload['created_by_uid'] = session()->get('admin_id') ? (int) session()->get('admin_id') : null;

        $id = $this->serviceModel->insert($payload);
        if (! $id) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'บันทึกไม่สำเร็จ']);
            }
            return redirect()->back()->withInput()->with('error', 'บันทึกไม่สำเร็จ');
        }

        $this->syncParticipantsFromRequest((int) $id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'id' => (int) $id]);
        }
        return redirect()->to(base_url('admin/academic-services/edit/' . $id))
            ->with('success', 'เพิ่มรายการสำเร็จ กรุณากรอกข้อมูลเพิ่มเติม (ถ้าต้องการ)');
    }

    /**
     * แบบรายงานสรุป (สรุปตามปี / ประเภท / จำนวนผู้ร่วมงาน)
     */
    public function report()
    {
        $db = \Config\Database::connect();
        $total = 0;
        $byYear = [];
        $byServiceType = [];
        $distinctParticipants = 0;

        if ($db->tableExists('academic_services')) {
            $total = (int) $this->serviceModel->countAllResults();

            $years = $this->serviceModel->getDistinctYears();
            foreach ($years as $y) {
                $count = $this->serviceModel->where('academic_year', $y)->countAllResults();
                $byYear[] = ['year' => $y, 'count' => $count];
            }

            $builder = $db->table('academic_services')
                ->select('service_type, COUNT(*) as count')
                ->groupBy('service_type')
                ->orderBy('count', 'DESC');
            $rows = $builder->get()->getResultArray();
            foreach ($rows as $row) {
                $byServiceType[] = [
                    'service_type' => $row['service_type'] ?? '',
                    'count'        => (int) $row['count'],
                ];
            }
        }

        if ($db->tableExists('academic_service_participants')) {
            $row = $db->query('SELECT COUNT(DISTINCT user_uid) AS c FROM academic_service_participants WHERE user_uid IS NOT NULL')->getRow();
            $distinctParticipants = (int) ($row->c ?? 0);
        }

        $serviceTypeLabels = [
            'training_seminar' => 'อบรม/สัมมนา',
            'workshop'         => 'ฝึกปฏิบัติการ/Workshop',
            'consultant'       => 'ที่ปรึกษาทางวิชาการ',
            'lab_testing'      => 'วิเคราะห์ทดสอบ/ห้องปฏิบัติการ',
            'expert_evaluator' => 'ผู้ทรงคุณวุฒิประเมินผล',
            'lecturer'         => 'วิทยากร',
            'other'            => 'อื่นๆ',
        ];

        $data = [
            'page_title'            => 'แบบรายงานสรุป บริการวิชาการ',
            'total'                 => $total,
            'by_year'               => $byYear,
            'by_service_type'       => $byServiceType,
            'service_type_labels'   => $serviceTypeLabels,
            'distinct_participants' => $distinctParticipants,
        ];

        return view('admin/academic_services/report', $data);
    }

    /**
     * ฟอร์มสำหรับ embed ใน modal (ไม่มีเมนูแอดมิน) — ใช้ทั้งเพิ่มและแก้ไข
     * GET academic-services/form-view       = ฟอร์มเพิ่ม (ว่าง)
     * GET academic-services/form-view/123   = ฟอร์มแก้ไข
     */
    public function formView($id = null)
    {
        $service     = null;
        $participants = [];

        if ($id !== null && (int) $id > 0) {
            $service = $this->serviceModel->getWithParticipants((int) $id);
            if (! $service) {
                return redirect()->to(base_url('admin/academic-services'))->with('error', 'ไม่พบข้อมูล');
            }
            $service['target_group_users'] = $this->decodeUserTags($service['target_group_spec'] ?? '');
            $service['responsible_users']  = $this->decodeUserTags($service['responsible_person_text'] ?? '');
            $participants = $service['participants'] ?? [];
        }

        return view('admin/academic_services/form_embed', [
            'service'      => $service,
            'participants' => $participants,
        ]);
    }

    /**
     * ฟอร์มแก้ไข (หน้าเต็ม มีเมนู)
     */
    public function edit($id)
    {
        $service = $this->serviceModel->getWithParticipants((int) $id);
        if (! $service) {
            return redirect()->to(base_url('admin/academic-services'))->with('error', 'ไม่พบข้อมูล');
        }
        $service['target_group_users'] = $this->decodeUserTags($service['target_group_spec'] ?? '');
        $service['responsible_users']  = $this->decodeUserTags($service['responsible_person_text'] ?? '');

        $data = [
            'page_title'   => 'แก้ไขรายการบริการวิชาการ',
            'service'      => $service,
            'participants' => $service['participants'] ?? [],
        ];
        return view('admin/academic_services/form', $data);
    }

    /**
     * อัปเดตรายการ
     */
    public function update($id)
    {
        $service = $this->serviceModel->find($id);
        if (! $service) {
            return redirect()->to(base_url('admin/academic-services'))->with('error', 'ไม่พบข้อมูล');
        }

        $rules = [
            'academic_year' => 'permit_empty|max_length[20]',
            'service_date'  => 'required|valid_date',
            'title'         => 'required|max_length[500]',
        ];
        if (! $this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'errors'  => $this->validator->getErrors(),
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($this->request->getPost('has_compensation') === 'yes') {
            $comp = $this->request->getPost('compensation_amount');
            if ($comp === null || $comp === '') {
                $errors = $this->validator->getErrors();
                $errors['compensation_amount'] = 'กรุณาระบุจำนวนเงินค่าตอบแทน';
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'errors' => $errors]);
                }
                return redirect()->back()->withInput()->with('errors', $errors);
            }
        }

        $payload = $this->getServiceDataFromRequest();
        $this->serviceModel->update($id, $payload);
        $this->syncParticipantsFromRequest((int) $id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->to(base_url('admin/academic-services'))
            ->with('success', 'แก้ไขรายการบริการวิชาการสำเร็จ');
    }

    /**
     * ลบรายการ
     */
    public function delete($id)
    {
        $service = $this->serviceModel->find($id);
        if (! $service) {
            return redirect()->to(base_url('admin/academic-services'))->with('error', 'ไม่พบข้อมูล');
        }
        $this->serviceModel->delete($id);
        return redirect()->to(base_url('admin/academic-services'))
            ->with('success', 'ลบรายการสำเร็จ');
    }

    /**
     * AJAX: ค้นหาผู้ใช้สำหรับแท็ก (ชื่อ/อีเมล)
     * GET exclude_uids: คั่นด้วย comma เพื่อไม่ให้แสดงในผลลัพธ์ (ใช้กรณีผู้ร่วมบริการไม่ซ้ำกับผู้รับผิดชอบ)
     */
    public function searchUsers()
    {
        $q = trim($this->request->getGet('q') ?? '');
        if (strlen($q) < 2) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        $excludeRaw = $this->request->getGet('exclude_uids');
        $excludeIds = [];
        if (is_string($excludeRaw) && $excludeRaw !== '') {
            foreach (explode(',', $excludeRaw) as $id) {
                $id = (int) trim($id);
                if ($id > 0) {
                    $excludeIds[] = $id;
                }
            }
        }

        $builder = $this->userModel
            ->groupStart()
            ->like('email', $q)
            ->orLike('tf_name', $q)
            ->orLike('tl_name', $q)
            ->orLike('gf_name', $q)
            ->orLike('gl_name', $q)
            ->groupEnd();
        if ($excludeIds !== []) {
            $builder->whereNotIn('uid', $excludeIds);
        }
        $users = $builder->limit(20)->findAll();

        $data = [];
        foreach ($users as $u) {
            $nameTh = trim(($u['tf_name'] ?? '') . ' ' . ($u['tl_name'] ?? ''));
            $nameEn = trim(($u['gf_name'] ?? '') . ' ' . ($u['gl_name'] ?? ''));
            $label  = $nameTh ?: $nameEn ?: $u['email'] ?? '';
            $data[] = [
                'uid'    => (int) $u['uid'],
                'email'  => $u['email'] ?? '',
                'label'  => $label,
                'tf_name' => $u['tf_name'] ?? '',
                'tl_name' => $u['tl_name'] ?? '',
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }

    private function getServiceDataFromRequest(): array
    {
        $revenueOption = $this->request->getPost('revenue_option');
        $revenueAmount = null;
        $revenueUnknown = 0;
        if ($revenueOption === 'amount') {
            $revenueAmount  = $this->request->getPost('revenue_amount') !== '' ? (float) $this->request->getPost('revenue_amount') : null;
            $revenueUnknown = 0;
        } elseif ($revenueOption === 'unknown') {
            $revenueUnknown = 1;
        }

        $hasComp = $this->request->getPost('has_compensation');
        $compensationAmount = null;
        if ($hasComp === 'yes') {
            $compensationAmount = $this->request->getPost('compensation_amount') !== '' ? (float) $this->request->getPost('compensation_amount') : null;
        }

        return [
            'academic_year'           => $this->request->getPost('academic_year') ?: null,
            'service_date'           => $this->request->getPost('service_date'),
            'title'                  => $this->request->getPost('title'),
            'project_owner_type'     => $this->request->getPost('project_owner_type') ?: null,
            'project_owner_spec'     => $this->request->getPost('project_owner_spec') ?: null,
            'venue_type'             => $this->request->getPost('venue_type') ?: null,
            'venue_spec'             => $this->request->getPost('venue_spec') ?: null,
            'target_group_type'      => $this->request->getPost('target_group_type') ?: null,
            'target_group_spec'      => $this->request->getPost('target_group_spec') ?: null,
            'responsible_type'       => $this->request->getPost('responsible_type') ?: null,
            'responsible_program'    => $this->request->getPost('responsible_program') ?: null,
            'responsible_person_text' => $this->request->getPost('responsible_person_text') ?: null,
            'service_type'           => $this->request->getPost('service_type') ?: null,
            'service_type_spec'      => $this->request->getPost('service_type_spec') ?: null,
            'budget_source'          => $this->request->getPost('budget_source') ?: null,
            'budget_source_spec'     => $this->request->getPost('budget_source_spec') ?: null,
            'has_compensation'       => $this->request->getPost('has_compensation') ?: null,
            'compensation_amount'    => $compensationAmount,
            'revenue_amount'         => $revenueAmount,
            'revenue_unknown'        => $revenueUnknown,
        ];
    }

    private function syncParticipantsFromRequest(int $serviceId): void
    {
        $raw = $this->request->getPost('participants');
        $list = [];
        if (is_array($raw)) {
            $list = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $list = $decoded;
            }
        }

        $normalized = [];
        foreach ($list as $p) {
            $userUid = isset($p['user_uid']) && $p['user_uid'] !== '' ? (int) $p['user_uid'] : null;
            $displayName = isset($p['display_name']) ? trim((string) $p['display_name']) : '';
            $programName = isset($p['program_name']) ? trim((string) $p['program_name']) : null;
            if ($userUid === 0) {
                $userUid = null;
            }
            if ($userUid === null && $displayName === '') {
                continue;
            }
            $normalized[] = [
                'user_uid'     => $userUid,
                'display_name' => $displayName ?: null,
                'program_name' => $programName ?: null,
                'role'         => $p['role'] ?? 'co_participant',
            ];
        }

        $this->participantModel->syncParticipants($serviceId, $normalized);
    }

    /**
     * Decode user-tags JSON from spec/person_text field (array of {uid, label})
     */
    private function decodeUserTags(string $json): array
    {
        if ($json === '' || $json === null) {
            return [];
        }
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $item) {
            $label = isset($item['label']) ? trim((string) $item['label']) : '';
            if ($label === '') {
                continue;
            }
            $uid = isset($item['uid']) ? (int) $item['uid'] : 0;
            $out[] = ['uid' => $uid, 'label' => $label];
        }
        return $out;
    }
}
