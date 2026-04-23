<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ComplaintModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Complaints extends BaseController
{
    private ComplaintModel $complaintModel;

    public function __construct()
    {
        $this->complaintModel = new ComplaintModel();

        $adminId = (int) session()->get('admin_id');
        $role = (string) session()->get('admin_role');
        if ($adminId <= 0 || $role !== 'super_admin') {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function index(): string
    {
        $status = trim((string) $this->request->getGet('status'));
        $search = trim((string) $this->request->getGet('search'));
        $selectedId = (int) ($this->request->getGet('selected') ?? 0);
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 20;

        $listModel = new ComplaintModel();
        $countModel = new ComplaintModel();
        $builder = $listModel->orderBy('created_at', 'DESC');
        $countBuilder = $countModel;

        if (ComplaintModel::isValidStatus($status)) {
            $builder->where('status', $status);
            $countBuilder->where('status', $status);
        } else {
            $status = '';
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('complainant_name', $search)
                ->orLike('complainant_email', $search)
                ->orLike('subject', $search)
                ->groupEnd();

            $countBuilder->groupStart()
                ->like('complainant_name', $search)
                ->orLike('complainant_email', $search)
                ->orLike('subject', $search)
                ->groupEnd();
        }

        $total = $countBuilder->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $complaints = $builder->findAll($perPage, $offset);
        $selectedComplaint = null;

        if ($selectedId > 0) {
            $selectedComplaint = (new ComplaintModel())->find($selectedId);
        }

        if ($selectedComplaint === null && $complaints !== []) {
            $selectedComplaint = $complaints[0];
        }

        return view('admin/complaints/index', [
            'page_title' => 'รายการร้องเรียน',
            'complaints' => $complaints,
            'selectedComplaint' => $selectedComplaint,
            'statusOptions' => ComplaintModel::getStatusOptions(),
            'currentStatus' => $status,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasPreviousPage' => $page > 1,
            'hasNextPage' => $page < $totalPages,
        ]);
    }

    public function updateStatus(int $id)
    {
        $complaint = $this->complaintModel->find($id);
        if ($complaint === null) {
            return redirect()->to(base_url('admin/complaints'))
                ->with('error', 'ไม่พบรายการร้องเรียนที่ต้องการอัปเดต');
        }

        $status = trim((string) $this->request->getPost('status'));
        if (! ComplaintModel::isValidStatus($status)) {
            return redirect()->back()->with('error', 'สถานะที่เลือกไม่ถูกต้อง');
        }

        $this->complaintModel->update($id, ['status' => $status]);

        $query = http_build_query([
            'selected' => $id,
            'status' => (string) ($this->request->getGet('status') ?? ''),
            'search' => (string) ($this->request->getGet('search') ?? ''),
            'page' => (string) ($this->request->getGet('page') ?? ''),
        ]);

        return redirect()->to(base_url('admin/complaints' . ($query !== '' ? '?' . $query : '')))
            ->with('success', 'อัปเดตสถานะเรื่องร้องเรียนเรียบร้อยแล้ว');
    }
}
