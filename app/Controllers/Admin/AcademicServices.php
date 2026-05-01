<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AcademicServiceAttachmentModel;
use App\Models\AcademicServiceModel;
use App\Models\AcademicServiceParticipantModel;
use App\Models\UserModel;

class AcademicServices extends BaseController
{
    /** @var string[] */
    private const ATTACHMENT_ALLOWED_EXT = ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'ppt', 'pptx', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'txt'];

    private const ATTACHMENT_MAX_BYTES = 10 * 1024 * 1024;

    protected AcademicServiceModel $serviceModel;
    protected AcademicServiceParticipantModel $participantModel;
    protected AcademicServiceAttachmentModel $attachmentModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->serviceModel     = model(AcademicServiceModel::class);
        $this->participantModel = model(AcademicServiceParticipantModel::class);
        $this->attachmentModel  = model(AcademicServiceAttachmentModel::class);
        $this->userModel        = model(UserModel::class);
    }

    /**
     * รายการบริการวิชาการ (กรองตามปี + ช่วงวันที่ + ค้นหา)
     */
    public function index()
    {
        $year      = $this->request->getGet('year');
        $keyword   = $this->request->getGet('keyword');
        $dateFrom  = $this->request->getGet('date_from');
        $dateTo    = $this->request->getGet('date_to');

        $list = $this->serviceModel->search($keyword, $year, $dateFrom ?: null, $dateTo ?: null);

        $participantCounts = [];
        foreach ($list as $row) {
            $participantCounts[$row['id']] = $this->participantModel->countByServiceId((int) $row['id']);
        }

        $serviceIds = array_map(static fn ($r) => (int) $r['id'], $list);
        $db           = \Config\Database::connect();
        $attachmentCounts = $db->tableExists('academic_service_attachments')
            ? $this->attachmentModel->countsByServiceIds($serviceIds)
            : [];

        $years = $this->serviceModel->getDistinctYears();
        $currentBuddhistYear = (int) date('Y') + 543;
        if (! in_array((string) $currentBuddhistYear, $years, true)) {
            array_unshift($years, (string) $currentBuddhistYear);
        }

        $data = [
            'page_title'          => 'ข้อมูลการบริการวิชาการ',
            'list'                => $list,
            'participant_counts'  => $participantCounts,
            'attachment_counts'   => $attachmentCounts,
            'years'               => $years,
            'selected_year'       => $year,
            'keyword'             => $keyword,
            'selected_date_from'  => $dateFrom,
            'selected_date_to'    => $dateTo,
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
            'academic_year'    => 'permit_empty|max_length[20]',
            'service_date'     => 'required|valid_date',
            'service_date_end' => 'permit_empty|valid_date',
            'title'            => 'required|max_length[500]',
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
        $rangeErr = $this->validateServiceDateRangeMessage();
        if ($rangeErr !== null) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'errors'  => ['service_date_end' => $rangeErr],
                ]);
            }

            return redirect()->back()->withInput()->with('errors', ['service_date_end' => $rangeErr]);
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
        $uploadWarn = $this->saveUploadedFiles((int) $id);

        if ($this->request->isAJAX()) {
            $payload = ['success' => true, 'id' => (int) $id];
            if ($uploadWarn !== null && $uploadWarn !== '') {
                $payload['warning'] = $uploadWarn;
            }

            return $this->response->setJSON($payload);
        }
        $flash = 'เพิ่มรายการสำเร็จ กรุณากรอกข้อมูลเพิ่มเติม (ถ้าต้องการ)';
        if ($uploadWarn !== null && $uploadWarn !== '') {
            $flash .= ' (หมายเหตุอัปโหลด: ' . $uploadWarn . ')';
        }

        return redirect()->to(base_url('admin/academic-services/edit/' . $id))
            ->with('success', $flash);
    }

    /**
     * ป้ายชื่อสำหรับแต่ละมิติ (ใช้ในกราฟและตาราง)
     */
    private function getDimensionLabels(): array
    {
        return [
            'service_type' => [
                'training_seminar' => 'อบรม/สัมมนา',
                'workshop'         => 'ฝึกปฏิบัติการ/Workshop',
                'consultant'       => 'ที่ปรึกษาทางวิชาการ',
                'lab_testing'      => 'วิเคราะห์ทดสอบ/ห้องปฏิบัติการ',
                'expert_evaluator' => 'ผู้ทรงคุณวุฒิประเมินผล',
                'lecturer'         => 'วิทยากร',
                'other'            => 'อื่นๆ',
            ],
            'responsible_type' => [
                'faculty' => 'ระดับคณะ',
                'program' => 'ระดับหลักสูตร',
                'person'  => 'ระดับบุคคล',
            ],
            'target_group_type' => [
                'internal' => 'ภายในมหาวิทยาลัย',
                'external' => 'ภายนอกมหาวิทยาลัย',
            ],
        ];
    }

    /**
     * ดึงข้อมูลสรุปตามมิติและปี (สำหรับกราฟ/ตาราง)
     * @return array{labels: string[], data: int[], rows: array}
     */
    private function getReportDataByDimension(string $dimension, ?string $year, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $db = \Config\Database::connect();
        $labelsMap = $this->getDimensionLabels();
        $labelsMap['year'] = [];

        $builder = $db->table('academic_services');
        if ($year !== null && $year !== '') {
            $builder->where('academic_year', $year);
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $builder->where(
                'COALESCE(service_date_end, service_date) >= ' . $db->escape($dateFrom),
                null,
                false
            );
        }
        if ($dateTo !== null && $dateTo !== '') {
            $builder->where(
                'service_date <= ' . $db->escape($dateTo),
                null,
                false
            );
        }

        $groupColumn = $dimension === 'year' ? 'academic_year' : $dimension;
        $builder->select($groupColumn . ', COUNT(*) as count')
            ->groupBy($groupColumn)
            ->orderBy('count', 'DESC');

        $rows = $builder->get()->getResultArray();
        $labels = [];
        $data = [];
        $labelMap = $labelsMap[$dimension] ?? [];

        foreach ($rows as $row) {
            $key = $row[$groupColumn] ?? '';
            if ($key === null || $key === '') {
                $key = '_empty';
            }
            $label = $dimension === 'year' ? (string) $key : ($labelMap[$key] ?? $key);
            if ($label === '_empty' || $label === '') {
                $label = '(ไม่ระบุ)';
            }
            $labels[] = $label;
            $data[] = (int) $row['count'];
        }

        $rowsForTable = [];
        foreach ($rows as $row) {
            $key = $row[$groupColumn] ?? '';
            if ($key === null || $key === '') {
                $key = '_empty';
            }
            $label = $dimension === 'year' ? (string) $key : ($labelMap[$key] ?? $key);
            if ($label === '_empty' || $label === '') {
                $label = '(ไม่ระบุ)';
            }
            $rowsForTable[] = ['label' => $label, 'count' => (int) $row['count']];
        }

        return ['labels' => $labels, 'data' => $data, 'rows' => $rowsForTable];
    }

    /**
     * หน้ารายงานแบบอินเทอร์แอคทีฟ — เลือกมิติแล้วแสดงกราฟ + ตาราง, ออกรายงาน Excel ได้
     */
    public function report()
    {
        $db = \Config\Database::connect();
        $total = 0;
        $distinctParticipants = 0;
        $years = [];

        if ($db->tableExists('academic_services')) {
            $total = (int) $this->serviceModel->countAllResults();
            $years = $this->serviceModel->getDistinctYears();
        }
        if ($db->tableExists('academic_service_participants')) {
            $row = $db->query('SELECT COUNT(DISTINCT user_uid) AS c FROM academic_service_participants WHERE user_uid IS NOT NULL')->getRow();
            $distinctParticipants = (int) ($row->c ?? 0);
        }

        $dimension = $this->request->getGet('dimension') ?: 'service_type';
        $year        = $this->request->getGet('year');
        $dateFrom    = $this->request->getGet('date_from');
        $dateTo      = $this->request->getGet('date_to');
        $allowedDimensions = ['service_type', 'responsible_type', 'target_group_type', 'year'];
        if (! in_array($dimension, $allowedDimensions, true)) {
            $dimension = 'service_type';
        }

        $reportData = $this->getReportDataByDimension($dimension, $year, $dateFrom ?: null, $dateTo ?: null);

        $dimensionLabels = [
            'service_type'      => 'ลักษณะการบริการวิชาการ',
            'responsible_type'  => 'ผู้รับผิดชอบ',
            'target_group_type' => 'บริการให้ใคร',
            'year'              => 'ปีการศึกษา',
        ];

        $data = [
            'page_title'       => 'แบบรายงานสรุป บริการวิชาการ',
            'total'            => $total,
            'distinct_participants' => $distinctParticipants,
            'years'            => $years,
            'dimension'        => $dimension,
            'dimension_label'  => $dimensionLabels[$dimension] ?? $dimension,
            'year_filter'      => $year,
            'date_from'        => $dateFrom,
            'date_to'          => $dateTo,
            'chart_labels'     => $reportData['labels'],
            'chart_data'       => $reportData['data'],
            'table_rows'       => $reportData['rows'],
            'dimension_options' => $dimensionLabels,
        ];

        return view('admin/academic_services/report', $data);
    }

    /**
     * API คืนข้อมูลรายงานเป็น JSON (สำหรับอัปเดตกราฟ/ตารางแบบ AJAX)
     */
    public function reportData()
    {
        $dimension = $this->request->getGet('dimension') ?: 'service_type';
        $year        = $this->request->getGet('year');
        $dateFrom    = $this->request->getGet('date_from');
        $dateTo      = $this->request->getGet('date_to');
        $allowed = ['service_type', 'responsible_type', 'target_group_type', 'year'];
        if (! in_array($dimension, $allowed, true)) {
            $dimension = 'service_type';
        }
        $result = $this->getReportDataByDimension($dimension, $year, $dateFrom ?: null, $dateTo ?: null);
        return $this->response->setJSON($result);
    }

    /**
     * ออกรายงานเป็นไฟล์ Excel (CSV) ตามมิติและปีที่เลือก
     */
    public function reportExport()
    {
        $dimension = $this->request->getGet('dimension') ?: 'service_type';
        $year        = $this->request->getGet('year');
        $dateFrom    = $this->request->getGet('date_from');
        $dateTo      = $this->request->getGet('date_to');
        $allowed = ['service_type', 'responsible_type', 'target_group_type', 'year'];
        if (! in_array($dimension, $allowed, true)) {
            $dimension = 'service_type';
        }

        $result = $this->getReportDataByDimension($dimension, $year, $dateFrom ?: null, $dateTo ?: null);
        $dimensionLabels = [
            'service_type'      => 'ลักษณะการบริการวิชาการ',
            'responsible_type'  => 'ผู้รับผิดชอบ',
            'target_group_type' => 'บริการให้ใคร',
            'year'              => 'ปีการศึกษา',
        ];
        $headerLabel = $dimensionLabels[$dimension] ?? $dimension;

        $suffix = ($year ? '-' . $year : '') . ($dateFrom ? '-from' . $dateFrom : '') . ($dateTo ? '-to' . $dateTo : '');
        $filename = 'academic-service-report-' . $dimension . $suffix . '.csv';
        $buf = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $out = fopen('php://temp', 'r+');
        fputcsv($out, [$headerLabel, 'จำนวนรายการ']);
        foreach ($result['rows'] as $row) {
            fputcsv($out, [$row['label'], $row['count']]);
        }
        rewind($out);
        $buf .= stream_get_contents($out);
        fclose($out);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($buf);
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
     * รายละเอียดแบบอ่านอย่างเดียว — โหลดใน iframe ภายใน modal หน้า index
     */
    public function detailView($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            return redirect()->to(base_url('admin/academic-services'))->with('error', 'ไม่พบข้อมูล');
        }
        $service = $this->serviceModel->getWithParticipants($id);
        if (! $service) {
            return redirect()->to(base_url('admin/academic-services'))->with('error', 'ไม่พบข้อมูล');
        }
        $service['target_group_users'] = $this->decodeUserTags($service['target_group_spec'] ?? '');
        $service['responsible_users']  = $this->decodeUserTags($service['responsible_person_text'] ?? '');

        return view('admin/academic_services/detail_embed', [
            'service' => $service,
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
            'academic_year'    => 'permit_empty|max_length[20]',
            'service_date'     => 'required|valid_date',
            'service_date_end' => 'permit_empty|valid_date',
            'title'            => 'required|max_length[500]',
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
        $rangeErr = $this->validateServiceDateRangeMessage();
        if ($rangeErr !== null) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'errors'  => ['service_date_end' => $rangeErr],
                ]);
            }

            return redirect()->back()->withInput()->with('errors', ['service_date_end' => $rangeErr]);
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
        $uploadWarn = $this->saveUploadedFiles((int) $id);

        if ($this->request->isAJAX()) {
            $ajaxPayload = ['success' => true];
            if ($uploadWarn !== null && $uploadWarn !== '') {
                $ajaxPayload['warning'] = $uploadWarn;
            }

            return $this->response->setJSON($ajaxPayload);
        }
        $flash = 'แก้ไขรายการบริการวิชาการสำเร็จ';
        if ($uploadWarn !== null && $uploadWarn !== '') {
            $flash .= ' (หมายเหตุอัปโหลด: ' . $uploadWarn . ')';
        }

        return redirect()->to(base_url('admin/academic-services'))
            ->with('success', $flash);
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
        $db = \Config\Database::connect();
        if ($db->tableExists('academic_service_attachments')) {
            helper('program_upload');
            foreach ($this->attachmentModel->getByServiceId((int) $id) as $att) {
                $full = upload_resolve_full_path($att['stored_path']);
                if (is_file($full)) {
                    @unlink($full);
                }
            }
        }
        $this->serviceModel->delete($id);
        return redirect()->to(base_url('admin/academic-services'))
            ->with('success', 'ลบรายการสำเร็จ');
    }

    /**
     * POST: ลบไฟล์แนบรายการเดียว (ใช้จากฟอร์มแก้ไข / iframe)
     */
    public function deleteAttachment($attachmentId)
    {
        $attachmentId = (int) $attachmentId;
        $row          = $this->attachmentModel->find($attachmentId);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'ไม่พบไฟล์']);
        }
        helper('program_upload');
        $full = upload_resolve_full_path($row['stored_path']);
        if (is_file($full)) {
            @unlink($full);
        }
        $this->attachmentModel->delete($attachmentId);

        return $this->response->setJSON(['success' => true]);
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

    /**
     * ตรวจว่าวันสิ้นสุดไม่ก่อนวันเริ่ม (เมื่อระบุทั้งคู่)
     */
    private function validateServiceDateRangeMessage(): ?string
    {
        $start = trim((string) $this->request->getPost('service_date'));
        $end   = trim((string) $this->request->getPost('service_date_end'));
        if ($end === '') {
            return null;
        }
        if ($start === '') {
            return null;
        }
        if (strtotime($end) < strtotime($start)) {
            return 'วันสิ้นสุดต้องไม่ก่อนวันเริ่มต้น';
        }

        return null;
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

        $endRaw   = $this->request->getPost('service_date_end');
        $startRaw = $this->request->getPost('service_date');
        if ($endRaw !== null && $endRaw !== '' && $startRaw !== null && $startRaw !== '' && $endRaw === $startRaw) {
            $endRaw = '';
        }

        return [
            'academic_year'           => $this->request->getPost('academic_year') ?: null,
            'service_date'            => $this->request->getPost('service_date'),
            'service_date_end'        => ($endRaw !== null && $endRaw !== '') ? $endRaw : null,
            'title'                   => $this->request->getPost('title'),
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

    /**
     * บันทึกไฟล์ที่อัปโหลดมากับคำขอ store/update
     *
     * @return string|null ข้อความเตือนหากมีไฟล์ถูกข้าม/ผิดพลาด
     */
    private function saveUploadedFiles(int $serviceId): ?string
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('academic_service_attachments')) {
            return null;
        }

        helper('program_upload');

        $list = [];
        if (method_exists($this->request, 'getFileMultiple')) {
            $list = $this->request->getFileMultiple('service_attachments');
        }
        if ($list === [] || $list === null) {
            $all = $this->request->getFiles();
            if (! empty($all['service_attachments'])) {
                $g = $all['service_attachments'];
                $list = is_array($g) ? $g : [$g];
            }
        }
        if ($list === [] || $list === null) {
            return null;
        }

        $dir = upload_path('academic-services');
        $warnings = [];
        $sortBase = (int) $db->table('academic_service_attachments')
            ->where('academic_service_id', $serviceId)
            ->countAllResults();

        foreach ($list as $file) {
            if ($file === null || ! $file->isValid() || $file->hasMoved()) {
                if ($file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                    $warnings[] = $file->getClientName() . ' (อัปโหลดไม่สำเร็จ)';
                }
                continue;
            }
            $ext = strtolower((string) ($file->getExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION)));
            if (! in_array($ext, self::ATTACHMENT_ALLOWED_EXT, true)) {
                $warnings[] = $file->getClientName() . ' (นามสกุลไม่รองรับ)';
                continue;
            }
            if ($file->getSize() > self::ATTACHMENT_MAX_BYTES) {
                $warnings[] = $file->getClientName() . ' (เกิน 10MB)';
                continue;
            }
            $stored = 'as_' . $serviceId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (! $file->move($dir, $stored)) {
                $warnings[] = $file->getClientName() . ' (บันทึกไม่สำเร็จ)';
                continue;
            }
            $sortBase++;
            $this->attachmentModel->insert([
                'academic_service_id' => $serviceId,
                'original_name'       => $file->getClientName(),
                'stored_path'         => 'academic-services/' . $stored,
                'file_size'           => $file->getSize(),
                'sort_order'          => $sortBase,
                'created_at'          => date('Y-m-d H:i:s'),
            ]);
        }

        return $warnings !== [] ? implode(' ', $warnings) : null;
    }
}
