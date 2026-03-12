<?php

namespace App\Controllers\Evaluate;

use App\Controllers\BaseController;
use App\Models\Evaluate\TeachingEvaluationModel;
use App\Models\Evaluate\EvaluationScoreModel;
use App\Models\Evaluate\SelfEvaluationModel;

/**
 * Public evaluation form (from email link). No auth required.
 * + หน้าการ์ดแบบประเมินตนเอง (ทุกคนเข้าได้)
 */
class EvaluateController extends BaseController
{
    protected $teachingModel;
    protected $scoreModel;
    protected $selfModel;

    public function __construct()
    {
        $this->teachingModel = new TeachingEvaluationModel();
        $this->scoreModel    = new EvaluationScoreModel();
        $this->selfModel     = new SelfEvaluationModel();
    }

    /**
     * หน้าการ์ด — ทุกคนเข้าได้ ไม่ต้อง login แสดงการ์ดลิงก์ไปแบบประเมินของตนเอง
     */
    public function card()
    {
        return view('evaluate/card');
    }

    /**
     * ฟอร์มกรอกแบบประเมินของตนเอง (สาธารณะ)
     */
    public function selfForm()
    {
        $data['academic_year'] = date('Y') + 543;
        $data['semester']      = (int) date('n') <= 6 ? '1' : '2';
        if (session()->get('admin_logged_in') && session()->get('admin_id')) {
            $user = (new \App\Models\UserModel())->find(session()->get('admin_id'));
            $data['prefill_name']  = $user ? ($user['gf_name'] ?? $user['tf_name'] ?? '') . ' ' . ($user['gl_name'] ?? $user['tl_name'] ?? '') : '';
            $data['prefill_email'] = $user['email'] ?? '';
            $data['prefill_uid']   = session()->get('admin_id');
        } else {
            $data['prefill_name']  = '';
            $data['prefill_email'] = '';
            $data['prefill_uid']   = null;
        }
        return view('evaluate/self_form', $data);
    }

    /**
     * บันทึกแบบประเมินของตนเอง
     */
    public function saveSelf()
    {
        $uid = session()->get('admin_logged_in') ? (int) session()->get('admin_id') : null;
        $name  = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        if ($name === null || $name === '') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'กรุณากรอกชื่อ']);
            }
            return redirect()->back()->withInput()->with('error', 'กรุณากรอกชื่อ');
        }
        $data = [
            'uid'           => $uid,
            'name'          => $name,
            'email'         => $email,
            'academic_year' => $this->request->getPost('academic_year'),
            'semester'      => $this->request->getPost('semester'),
            'score_1'       => (int) $this->request->getPost('score_1'),
            'score_2'       => (int) $this->request->getPost('score_2'),
            'score_3'       => (int) $this->request->getPost('score_3'),
            'score_4'       => (int) $this->request->getPost('score_4'),
            'score_5'       => (int) $this->request->getPost('score_5'),
            'comment'       => $this->request->getPost('comment'),
        ];
        $this->selfModel->insert($data);
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'บันทึกแบบประเมินของท่านเรียบร้อยแล้ว']);
        }
        return redirect()->to(base_url('evaluate/card'))->with('success', 'บันทึกแบบประเมินของท่านเรียบร้อยแล้ว');
    }

    public function index($param = null)
    {
        return $this->evaluate($param);
    }

    public function saveEvaluate()
    {
        $teachingId = $this->request->getPost('teaching_id');
        $email      = $this->request->getPost('email');
        if (! $teachingId || ! $email) {
            return $this->response->setJSON(['success' => false])->setStatusCode(400);
        }

        $data = [
            'comment'      => $this->request->getPost('comment'),
            'file_doc'     => $this->request->getPost('file_doc'),
            'score'        => $this->request->getPost('score'),
            'comment_date' => date('Y-m-d'),
            'status'       => (int) $this->request->getPost('status'),
        ];

        $updated = $this->scoreModel->updateByTeachingAndEmail($teachingId, $email, $data);

        return $this->response->setJSON([
            'teaching_id' => $teachingId,
            'email'       => $email,
            'success'     => $updated,
        ]);
    }

    /**
     * Display evaluation form (from base64 link param).
     */
    public function evaluate($param = null)
    {
        if (empty($param)) {
            return view('errors/html/error_404');
        }

        $decoded = base64_decode($param, true);
        $result  = $decoded !== false ? json_decode($decoded, true) : null;

        if (empty($result) || empty($result['id']) || empty($result['email'])) {
            return view('errors/html/error_404');
        }

        $id = (int) $result['id'];
        $count = $this->teachingModel->countWhere("id = {$id}");
        if ($count !== 1) {
            $data['info'] = 'notfound';
            return view('evaluate/evaluate_form', $data ?? []);
        }

        $teaching = $this->teachingModel->getById($id);
        $data['info']      = 'found';
        $data['evaluate']  = [$teaching];
        $data['refparam']  = $result;
        $data['refparam']['encodeurl'] = $param;

        $scores = $this->scoreModel->getByCondition([
            'teaching_id' => $id,
            'email'       => $result['email'],
        ]);
        $data['param'] = array_filter($scores, static function ($r) {
            return (int) $r['status'] >= 0;
        });
        if (empty($data['param'])) {
            $data['param'] = [];
        } else {
            $data['param'] = array_values($data['param']);
        }

        return view('evaluate/evaluate_form', $data);
    }
}
