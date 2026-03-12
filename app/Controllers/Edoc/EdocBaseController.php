<?php

namespace App\Controllers\Edoc;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\AccessControl;

class EdocBaseController extends BaseController
{
    protected $edocUser;
    protected $isEdocAdmin = false;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $session = session();

        $this->edocUser = [
            'uid'   => $session->get('admin_id'),
            'email' => $session->get('admin_email'),
            'name'  => $session->get('admin_name'),
            'role'  => $session->get('admin_role'),
        ];

        if ($this->edocUser['uid']) {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($this->edocUser['uid']);
            if ($user) {
                $this->isEdocAdmin = AccessControl::hasAccess($this->edocUser['uid'], 'edoc_admin');
                // ทุกคนที่ล็อกอินเข้า E-Document (ดูเอกสาร) ได้; จัดการ (admin) เฉพาะ edoc_admin
                $this->edocUser['edoc']       = 1;
                $this->edocUser['admin_edoc'] = $this->isEdocAdmin ? 1 : 0;
                $this->edocUser['tf_name']   = $user['tf_name'] ?? '';
                $this->edocUser['tl_name']   = $user['tl_name'] ?? '';
            }
        }
    }

    protected function isLoggedIn(): bool
    {
        return !empty($this->edocUser['uid']);
    }

    protected function getEdocDocumentPath(): string
    {
        return WRITEPATH . 'edoc_documents' . DIRECTORY_SEPARATOR;
    }
}
