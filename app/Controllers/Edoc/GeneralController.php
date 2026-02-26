<?php

namespace App\Controllers\Edoc;

use App\Models\Edoc\EdoctitleModel;
use App\Models\Edoc\SendmailModel;
use App\Models\Edoc\EdocDocumentTagModel;

class GeneralController extends EdocBaseController
{
    protected $edoctitleModel;
    protected $sendmailModel;
    protected $docTagModel;

    public function __construct()
    {
        $this->edoctitleModel = new EdoctitleModel();
        $this->sendmailModel = new SendmailModel();
        $this->docTagModel = new EdocDocumentTagModel();
    }

    public function getDocumentsByDate($date)
    {
        $results = $this->edoctitleModel->where('DATE(regisdate)', $date)->findAll();
        return $results;
    }

    public function sendTodayDocumentNotifications()
    {
        return $this->sendDocumentNotifications(date('Y-m-d'));
    }

    public function getDocumentNotificationsData($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
            $dateDisplay = date('d/m/Y');
        } else {
            $dateDisplay = date('d/m/Y', strtotime($date));
        }

        $documents = $this->getDocumentsByDate($date);

        if (empty($documents)) {
            $response = [
                'status' => 'info',
                'message' => 'No documents registered on ' . $dateDisplay,
                'date' => $date,
                'date_display' => $dateDisplay,
                'documents' => [],
                'recipients' => []
            ];
            return $this->response->setJSON($response);
        }

        $userModel = new \App\Models\UserModel();
        $users = $userModel->where('status', 'active')->findAll();

        $documentsByUser = [];

        $documentsForEveryone = [];
        foreach ($documents as $document) {
            if (
                !empty($document['participant']) &&
                (strpos($document['participant'], 'ทุกคน') !== false ||
                    in_array('ทุกคน', array_map('trim', explode(',', $document['participant']))))
            ) {
                $documentsForEveryone[] = $document;
            }
        }

        foreach ($users as $user) {
            $userEmail = strtolower(trim($user['email'] ?? ''));
            $user_id = $user['uid'];
            $tagname = trim(($user['thai_name'] ?? '') . ' ' . ($user['thai_lastname'] ?? ''));

            if (empty($userEmail)) {
                continue;
            }

            $userDocuments = $documentsForEveryone;

            foreach ($documents as $document) {
                if (in_array($document, $documentsForEveryone)) {
                    continue;
                }

                $isTagged = false;

                // New: check edoc_document_tags by email
                if (!empty($document['iddoc'])) {
                    $isTagged = $this->docTagModel->isTagged((int) $document['iddoc'], $userEmail);
                }

                // Legacy fallback: check participant string by name
                if (!$isTagged && !empty($document['participant']) && !empty($tagname)) {
                    $participants = array_map('trim', explode(',', $document['participant']));
                    if (in_array($tagname, $participants)) {
                        $isTagged = true;
                    }
                }

                if (!$isTagged && !empty($document['owner']) && !empty($tagname)) {
                    if (trim($document['owner']) === $tagname) {
                        $isTagged = true;
                    }
                }

                if ($isTagged) {
                    $userDocuments[] = $document;
                }
            }

            if (!empty($userDocuments)) {
                $documentsByUser[$userEmail] = [
                    'userName' => $tagname ?: $userEmail,
                    'user_id' => $user_id,
                    'documents' => array_map(function ($doc) use ($user_id) {
                        $accessToken = $this->generateDocumentAccessToken($user_id, $doc["iddoc"]);
                        $doc['access_token'] = $accessToken;
                        $doc['access_url'] = base_url("index.php/edoc/public/secure-access?token=" . urlencode($accessToken));
                        return $doc;
                    }, $userDocuments)
                ];
            }
        }

        $response = [
            'status' => 'success',
            'message' => 'Documents found for date: ' . $dateDisplay,
            'date' => $date,
            'date_display' => $dateDisplay,
            'total_documents' => count($documents),
            'documents' => $documents,
            'recipients' => $documentsByUser
        ];

        return $this->response->setJSON($response);
    }

    public function sendDocumentNotifications($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
            $dateDisplay = date('d/m/Y');
        } else {
            $dateDisplay = date('d/m/Y', strtotime($date));
        }

        $documents = $this->getDocumentsByDate($date);

        if (empty($documents)) {
            $response = ['status' => 'info', 'message' => 'No documents registered on ' . $dateDisplay];
            return $this->response->setJSON($response);
        }

        $userModel = new \App\Models\UserModel();
        $users = $userModel->where('status', 'active')->findAll();

        $documentsByUser = [];

        $documentsForEveryone = [];
        foreach ($documents as $document) {
            if (
                !empty($document['participant']) &&
                (strpos($document['participant'], 'ทุกคน') !== false ||
                    in_array('ทุกคน', array_map('trim', explode(',', $document['participant']))))
            ) {
                $documentsForEveryone[] = $document;
            }
        }

        foreach ($users as $user) {
            $userEmail = strtolower(trim($user['email'] ?? ''));
            $user_id = $user['uid'];
            $tagname = trim(($user['thai_name'] ?? '') . ' ' . ($user['thai_lastname'] ?? ''));

            if (empty($userEmail)) {
                continue;
            }

            $userDocuments = $documentsForEveryone;

            foreach ($documents as $document) {
                if (in_array($document, $documentsForEveryone)) {
                    continue;
                }

                $isTagged = false;

                // New: check edoc_document_tags by email
                if (!empty($document['iddoc'])) {
                    $isTagged = $this->docTagModel->isTagged((int) $document['iddoc'], $userEmail);
                }

                // Legacy fallback: check participant string by name
                if (!$isTagged && !empty($document['participant']) && !empty($tagname)) {
                    $participants = array_map('trim', explode(',', $document['participant']));
                    if (in_array($tagname, $participants)) {
                        $isTagged = true;
                    }
                }

                if (!$isTagged && !empty($document['owner']) && !empty($tagname)) {
                    if (trim($document['owner']) === $tagname) {
                        $isTagged = true;
                    }
                }

                if ($isTagged) {
                    $userDocuments[] = $document;
                }
            }

            if (!empty($userDocuments)) {
                $documentsByUser[$userEmail] = [
                    'userName' => $tagname ?: $userEmail,
                    'documents' => $userDocuments,
                    'user_id' => $user_id
                ];
            }
        }

        $results = [
            'status' => 'success',
            'total_documents' => count($documents),
            'date' => $dateDisplay,
            'recipients' => []
        ];

        foreach ($documentsByUser as $email => $userData) {
            $userName = $userData['userName'];
            $userDocuments = $userData['documents'];
            $user_id = $userData['user_id'];

            $htmlContent = '<div style="font-family: \'Sarabun\', sans-serif;">';
            $htmlContent .= '<p>เรียน คุณ' . $userName . '</p>';
            $htmlContent .= '<p>ท่านมีเอกสารในระบบ Edocument ของคณะวิทยาศาสตร์และเทคโนโลยี ประจำวันที่ ' . $dateDisplay . ' ดังนี้</p>';
            $htmlContent .= '<ol>';

            $textContent = "เรียน คุณ" . $userName . " \n\n";
            $textContent .= "ท่านมีเอกสารในระบบ Edocument ของคณะวิทยาศาสตร์และเทคโนโลยี ประจำวันที่ " . $dateDisplay . " ดังนี้ \n\n";

            foreach ($userDocuments as $index => $doc) {
                $accessToken = $this->generateDocumentAccessToken($user_id, $doc["iddoc"]);
                $documentUrl = base_url("edoc/public/secure-access?token=" . urlencode($accessToken));

                $htmlContent .= '<li style="margin-bottom: 15px;">';
                $htmlContent .= '<div><strong>เรื่อง:</strong> <a href="' . $documentUrl . '" style="color: #0056b3; text-decoration: none;">' . $doc["title"] . '</a></div>';

                if (!empty($doc["officeiddoc"])) {
                    $htmlContent .= '<div><strong>เลขที่:</strong> ' . $doc["officeiddoc"] . '</div>';
                }

                $htmlContent .= '<div><strong>วันที่ลงทะเบียน:</strong> ' . date('d/m/Y', strtotime($doc["regisdate"])) . '</div>';
                $htmlContent .= '<div><strong>ประเภทเอกสาร:</strong> ' . $doc["doctype"] . '</div>';
                $htmlContent .= '</li>';

                $textContent .= ($index + 1) . ". เรื่อง: " . $doc["title"] . "\n";

                if (!empty($doc["officeiddoc"])) {
                    $textContent .= "   เลขที่: " . $doc["officeiddoc"] . "\n";
                }
                $textContent .= "   คำสั่งการ : " . ($doc["order"] ?? '') . "\n";
                $textContent .= "   วันที่ลงทะเบียน: " . date('d/m/Y', strtotime($doc["regisdate"])) . "\n";
                $textContent .= "   ประเภทเอกสาร: " . $doc["doctype"] . "\n";
                $textContent .= "   ลิงค์เอกสาร: " . $documentUrl . "\n\n";
            }

            $htmlContent .= '</ol>';
            $htmlContent .= '<p>เพื่อตรวจสอบเอกสารทั้งหมด โปรด <a href="' . base_url('edoc') . '" style="color: #0056b3;">เข้าสู่ระบบ</a> Edocument</p>';
            $htmlContent .= '</div>';

            $textContent .= "เพื่อตรวจสอบเอกสารทั้งหมด โปรด Login เข้าระบบ Edocument ตาม Link ด้านล่าง \n";
            $textContent .= base_url('edoc');

            $subject = "แจ้งเตือนเอกสารใน Edocument ประจำวันที่ " . $dateDisplay;

            $result = $this->sendmailModel->sendMailHTML($email, $htmlContent, $textContent, $subject);

            $results['recipients'][$email] = [
                'status' => isset($result['message']) && $result['message'] == 'ส่งอีเมล์สำเร็จ!' ? 'success' : 'error',
                'message' => $result['message'],
                'documents_count' => count($userDocuments)
            ];

            log_message('debug', "Notification email to {$email} with " . count($userDocuments) . " documents for date {$dateDisplay}");
        }

        return $this->response->setJSON($results);
    }

    private function generateDocumentAccessToken($user_id, $doc_id)
    {
        $data = [
            'user_id' => $user_id,
            'doc_id' => $doc_id,
            'timestamp' => time(),
            'expires' => time() + (7 * 24 * 60 * 60)
        ];

        $json = json_encode($data);

        $secret_key = "Sci_edoc";
        $encrypted = $this->simpleEncrypt($json, $secret_key);

        return strtr(base64_encode($encrypted), '+/=', '-_,');
    }

    private function simpleEncrypt($data, $key)
    {
        $method = 'AES-256-CBC';
        $iv = substr(hash('sha256', $key), 0, 16);
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        return $encrypted;
    }

    private function simpleDecrypt($data, $key)
    {
        $method = 'AES-256-CBC';
        $iv = substr(hash('sha256', $key), 0, 16);
        $decrypted = openssl_decrypt($data, $method, $key, 0, $iv);
        return $decrypted;
    }

    public function secureAccess()
    {
        $token = $this->request->getGet('token');

        if (empty($token)) {
            return $this->response->setStatusCode(400)->setBody('No access token provided');
        }

        $encrypted = base64_decode(strtr($token, '-_,', '+/='));

        $secret_key = "Sci_edoc";
        $json = $this->simpleDecrypt($encrypted, $secret_key);

        if ($json === false) {
            return $this->response->setStatusCode(400)->setBody('Invalid access token');
        }

        $data = json_decode($json, true);

        if (
            !isset($data['user_id']) || !isset($data['doc_id']) ||
            !isset($data['timestamp']) || !isset($data['expires'])
        ) {
            return $this->response->setStatusCode(400)->setBody('Invalid token format');
        }

        if (time() > $data['expires']) {
            return $this->response->setStatusCode(403)->setBody('Access token has expired');
        }

        $document = $this->edoctitleModel->find($data['doc_id']);

        if (!$document) {
            return $this->response->setStatusCode(404)->setBody('Document not found');
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($data['user_id']);

        if (!$user || empty($user)) {
            return $this->response->setStatusCode(404)->setBody('User not found');
        }

        $tagname = trim(($user['thai_name'] ?? '') . ' ' . ($user['thai_lastname'] ?? ''));
        $userEmail = strtolower(trim($user['email'] ?? ''));
        $hasAccess = false;

        // New: check edoc_document_tags by email
        if (!empty($userEmail) && $this->docTagModel->isTagged((int) $document['iddoc'], $userEmail)) {
            $hasAccess = true;
        }

        // Legacy: check participant string
        if (!$hasAccess && !empty($document['participant'])) {
            if (
                strpos($document['participant'], 'ทุกคน') !== false ||
                in_array('ทุกคน', array_map('trim', explode(',', $document['participant'])))
            ) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess && !empty($document['owner']) && !empty($tagname) && trim($document['owner']) === $tagname) {
            $hasAccess = true;
        }

        if (!$hasAccess && !empty($document['participant']) && !empty($tagname)) {
            $participants = array_map('trim', explode(',', $document['participant']));
            if (in_array($tagname, $participants)) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return $this->response->setStatusCode(403)->setBody('Access denied');
        }

        $session = session();
        $session->set('temp_user_id', $data['user_id']);
        $session->set('temp_access_token', $token);

        log_message('info', 'Secure document access: User ID ' . $data['user_id'] . ' accessed document ID ' . $data['doc_id']);

        $document = $this->parseFileAddressForView($document);

        $view_data = [
            'document' => $document,
            'user' => $user,
            'is_temporary_access' => true
        ];

        if (empty($document['fileaddress']) && empty($document['fileaddress_first'])) {
            $view_data['error'] = 'เอกสารนี้ไม่มีไฟล์แนบ';
        }

        return view('edoc/documents/document_view', $view_data);
    }

    /**
     * Parse fileaddress (JSON array หรือ comma-separated) แล้วใส่ fileaddress_first และ fileaddress_list ใน document
     */
    private function parseFileAddressForView(array $document): array
    {
        $raw = $document['fileaddress'] ?? '';
        if ($raw === null || trim((string) $raw) === '') {
            $document['fileaddress_first'] = '';
            $document['fileaddress_list'] = [];
            return $document;
        }
        $raw = trim((string) $raw);
        $list = [];
        $decoded = @json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $list = $decoded;
        } else {
            $parts = array_map('trim', explode(',', $raw));
            foreach ($parts as $p) {
                $p = trim($p, " \"'[]");
                if ($p !== '') {
                    $list[] = $p;
                }
            }
            if (empty($list)) {
                $clean = trim($raw, " \"'[]");
                if ($clean !== '') {
                    $list = [$clean];
                }
            }
        }
        $list = array_map(function ($f) {
            return trim(preg_replace('/["\'\[\]\s]+$/', '', preg_replace('/^["\'\[\]\s]+/', '', (string) $f)));
        }, $list);
        $list = array_values(array_filter($list, function ($f) {
            return $f !== '';
        }));
        $document['fileaddress_list'] = $list;
        $document['fileaddress_first'] = $list[0] ?? '';
        return $document;
    }
}
