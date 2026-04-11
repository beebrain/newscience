<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Libraries\CvBundleCanonical;
use App\Libraries\CvProfile;
use App\Libraries\ResearchRecordCvSyncClient;
use App\Libraries\ResearchRecordCvSyncMerge;
use App\Models\CvSyncLogModel;
use App\Models\PersonnelModel;
use Config\ResearchApi;

class ResearchRecordSync extends BaseController
{
    private function sessionEmail(): string
    {
        return CvProfile::normalizeEmail((string) session()->get('admin_email'));
    }

    private function resolveOwnedPersonnel(): ?array
    {
        $email = $this->sessionEmail();
        if ($email === '') {
            return null;
        }
        $personnelModel = new PersonnelModel();
        $row             = $personnelModel->findByUserEmail($email);
        if ($row !== null) {
            return $row;
        }

        return $personnelModel->groupStart()
            ->where('user_email', null)
            ->orWhere('user_email', '')
            ->groupEnd()
            ->where('email', $email)
            ->first();
    }

    private function canonicalEmailForPerson(array $person): string
    {
        if (!empty($person['user_email'])) {
            return CvProfile::normalizeEmail((string) $person['user_email']);
        }
        if (!empty($person['email'])) {
            return CvProfile::normalizeEmail((string) $person['email']);
        }

        return $this->sessionEmail();
    }

    public function index()
    {
        if ($this->sessionEmail() === '') {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return redirect()->to(base_url('dashboard/profile'))->with('error', 'ไม่พบข้อมูลบุคลากรที่ผูกกับบัญชีของคุณ');
        }

        $researchApi = config(ResearchApi::class);
        $configured  = $researchApi->baseUrl !== '';

        return view('user/profile/research_record_sync', [
            'page_title'  => 'ซิงค์กับ Research Record',
            'person'      => $person,
            'sync_email'  => $this->canonicalEmailForPerson($person),
            'api_configured' => $configured,
        ]);
    }

    public function compare()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบบุคลากร']);
        }

        $personnelId = (int) ($person['id'] ?? 0);
        $email       = $this->canonicalEmailForPerson($person);

        $nsBundle = CvBundleCanonical::buildFromNewScience($personnelId, $email);

        $rr = ResearchRecordCvSyncClient::fetchCvBundle($email);
        if (!$rr['success']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $rr['message'] ?? 'ดึงข้อมูลจาก Research Record ไม่สำเร็จ',
                'error'   => $rr['error'] ?? null,
            ]);
        }
        $rrBundle = $rr['bundle'] ?? [];

        $mergeRows = ResearchRecordCvSyncMerge::buildMergeRows($nsBundle, $rrBundle);

        $pubRows = [];
        $pubs    = [];
        $pubRes  = ResearchRecordCvSyncClient::fetchPublicationsSyncBundle($email);
        if ($pubRes['success'] && !empty($pubRes['publications'])) {
            $pubs = $pubRes['publications'];
            foreach ($pubs as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $k = (string) ($p['external_key'] ?? '');
                if ($k === '') {
                    continue;
                }
                $pubRows[] = [
                    'id'          => 'pub|' . $k,
                    'kind'        => 'publication',
                    'title'       => (string) ($p['title'] ?? ''),
                    'summary_rr'  => trim(($p['publication_year'] ?? '') . ' ' . ($p['source'] ?? '') . ' ' . ($p['doi'] ?? '')),
                ];
            }
        }

        return $this->response->setJSON([
            'success'       => true,
            'ns_bundle'     => $nsBundle,
            'rr_bundle'     => $rrBundle,
            'merge_rows'    => $mergeRows,
            'publications'  => $pubs,
            'publication_rows' => $pubRows,
            'ns_hash'       => $nsBundle['content_hash'] ?? '',
            'rr_hash'       => $rrBundle['content_hash'] ?? '',
        ]);
    }

    public function apply()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบบุคลากร']);
        }

        $personnelId = (int) ($person['id'] ?? 0);
        $email       = $this->canonicalEmailForPerson($person);

        $input = $this->request->getJSON(true);
        if (!is_array($input)) {
            $input = [];
        }
        $decisions = $input['decisions'] ?? [];
        if (!is_array($decisions)) {
            $decisions = [];
        }
        $nsBundle = $input['ns_bundle'] ?? null;
        $rrBundle = $input['rr_bundle'] ?? null;
        if (!is_array($nsBundle) || !is_array($rrBundle)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ส่งข้อมูลเปรียบเทียบไม่ครบ กรุณาโหลดเปรียบเทียบใหม่']);
        }

        $choiceMap = [];
        foreach ($decisions as $d) {
            if (!is_array($d) || empty($d['id']) || empty($d['choice'])) {
                continue;
            }
            $choiceMap[(string) $d['id']] = (string) $d['choice'];
        }
        if (!empty($input['orcid_choice'])) {
            $choiceMap['orcid'] = (string) $input['orcid_choice'];
        }

        try {
            $merged = ResearchRecordCvSyncMerge::mergedCvBundle($choiceMap, $nsBundle, $rrBundle, $email);
            ResearchRecordCvSyncMerge::replaceNewScienceCvFromBundle($personnelId, $merged);

            $pubCount = 0;
            if (!empty($input['publications']) && is_array($input['publications'])) {
                $pubDecisions = [];
                foreach ($input['publication_choices'] ?? [] as $pc) {
                    if (is_array($pc) && !empty($pc['id']) && !empty($pc['choice'])) {
                        $pubDecisions[(string) $pc['id']] = (string) $pc['choice'];
                    }
                }
                $pubCount = ResearchRecordCvSyncMerge::applyPublicationsToCvEntries($personnelId, $input['publications'], $pubDecisions);
            }

            $log = new CvSyncLogModel();
            if ($log->db->tableExists('cv_sync_log')) {
                $log->insert([
                    'personnel_id'      => $personnelId,
                    'direction'         => 'apply_merged_to_ns',
                    'ns_content_hash'   => $nsBundle['content_hash'] ?? null,
                    'rr_content_hash'   => $rrBundle['content_hash'] ?? null,
                    'decisions_json'    => json_encode(['decisions' => $decisions, 'orcid' => $choiceMap['orcid'] ?? null], JSON_UNESCAPED_UNICODE),
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->response->setJSON([
                'success'            => true,
                'message'            => 'บันทึกการซิงค์ลง newScience เรียบร้อย',
                'merged_hash'        => $merged['content_hash'] ?? '',
                'publications_saved' => $pubCount,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ResearchRecordSync::apply ' . $e->getMessage());

            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function pullAll()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบบุคลากร']);
        }

        $personnelId = (int) ($person['id'] ?? 0);
        $email       = $this->canonicalEmailForPerson($person);

        $nsBundle = CvBundleCanonical::buildFromNewScience($personnelId, $email);
        $rr       = ResearchRecordCvSyncClient::fetchCvBundle($email);
        if (!$rr['success'] || empty($rr['bundle'])) {
            return $this->response->setJSON(['success' => false, 'message' => $rr['message'] ?? 'ดึงจาก RR ไม่สำเร็จ']);
        }

        try {
            ResearchRecordCvSyncMerge::replaceNewScienceCvFromBundle($personnelId, $rr['bundle']);

            $log = new CvSyncLogModel();
            if ($log->db->tableExists('cv_sync_log')) {
                $log->insert([
                    'personnel_id'    => $personnelId,
                    'direction'       => 'pull_all_rr_to_ns',
                    'ns_content_hash' => $nsBundle['content_hash'] ?? null,
                    'rr_content_hash' => $rr['bundle']['content_hash'] ?? null,
                    'decisions_json'  => null,
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'แทนที่ CV บน newScience ด้วยข้อมูลจาก Research Record แล้ว']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function pushAll()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $person = $this->resolveOwnedPersonnel();
        if ($person === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบบุคลากร']);
        }

        $personnelId = (int) ($person['id'] ?? 0);
        $email       = $this->canonicalEmailForPerson($person);

        $bundle = CvBundleCanonical::buildFromNewScience($personnelId, $email);
        $rr     = ResearchRecordCvSyncClient::pushCvBundle($email, $bundle);
        if (!$rr['success']) {
            return $this->response->setJSON(['success' => false, 'message' => $rr['message'] ?? 'ส่งไป RR ไม่สำเร็จ']);
        }

        $log = new CvSyncLogModel();
        if ($log->db->tableExists('cv_sync_log')) {
            $rrHash = is_array($rr['bundle'] ?? null) ? ($rr['bundle']['content_hash'] ?? null) : null;
            $log->insert([
                'personnel_id'    => $personnelId,
                'direction'       => 'push_all_ns_to_rr',
                'ns_content_hash' => $bundle['content_hash'] ?? null,
                'rr_content_hash' => $rrHash,
                'decisions_json'  => null,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'ส่ง CV จาก newScience ไปแทนที่ใน Research Record แล้ว',
        ]);
    }
}
