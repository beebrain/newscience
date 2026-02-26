<?php

namespace App\Controllers\Edoc;

class EdocUploadController extends EdocBaseController
{
    /**
     * Upload file to edoc_documents directory
     */
    public function uploadFileEdoc()
    {
        $validationRule = [
            'file' => [
                'label' => 'Document File',
                'rules' => [
                    'uploaded[file]',
                    'mime_in[file,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpg,image/jpeg,image/png]',
                    'max_size[file,102400]',
                ],
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => $this->validator->getError('file')
            ]);
        }

        $file = $this->request->getFile('file');
        $uploadPath = $this->getEdocDocumentPath();

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Get current date for the filename
        $datePrefix = date('Ymd_His_');

        // Get file extension
        $ext = $file->getExtension();

        // Create filename with date prefix and random string
        $newName = $datePrefix . bin2hex(random_bytes(8)) . '.' . $ext;

        if ($file->move($uploadPath, $newName)) {
            return $this->response->setJSON([
                'status' => 'success',
                'msg' => "File successfully uploaded : " . $file->getClientName(),
                'filename' => $newName
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'msg' => 'Error uploading file'
        ]);
    }
}
