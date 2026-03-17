<?php

namespace App\Controllers\Evaluate;

use App\Controllers\BaseController;
use App\Models\Evaluate\TeachingEvaluationModel;
use App\Models\Evaluate\EvaluationScoreModel;
use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Public evaluation form (from email link). No auth required.
 */
class EvaluateController extends BaseController
{
    protected $teachingModel;
    protected $scoreModel;

    public function __construct()
    {
        $this->teachingModel = new TeachingEvaluationModel();
        $this->scoreModel    = new EvaluationScoreModel();
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

        $fileDoc = $this->request->getPost('file_doc');
        $file = $this->request->getFile('fileupload');
        if ($file instanceof UploadedFile && $file->isValid() && ! $file->hasMoved()) {
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            $extension = strtolower((string) $file->getExtension());
            if (! in_array($extension, $allowedExtensions, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'รองรับเฉพาะไฟล์ .pdf, .doc และ .docx',
                ])->setStatusCode(422);
            }

            $uploadPath = WRITEPATH . 'uploads/documents/';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fileDoc = $file->getRandomName();
            if (! $file->move($uploadPath, $fileDoc)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ไม่สามารถอัปโหลดไฟล์หลักฐานได้',
                ])->setStatusCode(500);
            }
        }

        $data = [
            'comment'      => $this->request->getPost('comment'),
            'file_doc'     => $fileDoc,
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
