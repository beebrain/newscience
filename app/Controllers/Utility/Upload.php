<?php

namespace App\Controllers\Utility;

use App\Controllers\BaseController;
use App\Models\NewsImageModel;

class Upload extends BaseController
{
    protected $newsImageModel;

    public function __construct()
    {
        $this->newsImageModel = new NewsImageModel();
    }

    /**
     * Upload single image (AJAX)
     */
    public function uploadImage()
    {
        // Check if admin is logged in
        if (!session()->get('admin_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ])->setStatusCode(401);
        }

        $file = $this->request->getFile('image');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No valid file uploaded.'
            ]);
        }

        // Validate file type
        $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $validTypes)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'
            ]);
        }

        // Validate file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File too large. Maximum size: 5MB'
            ]);
        }

        try {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/news', $newName);

            return $this->response->setJSON([
                'success' => true,
                'filename' => $newName,
                'url' => base_url('uploads/news/' . $newName)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Upload multiple images (AJAX)
     */
    public function uploadMultiple()
    {
        // Check if admin is logged in
        if (!session()->get('admin_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ])->setStatusCode(401);
        }

        $files = $this->request->getFiles();
        $uploaded = [];
        $errors = [];

        if (!isset($files['images']) || empty($files['images'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No files uploaded.'
            ]);
        }

        $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($files['images'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                // Validate type
                if (!in_array($file->getMimeType(), $validTypes)) {
                    $errors[] = $file->getClientName() . ': Invalid file type';
                    continue;
                }

                // Validate size
                if ($file->getSize() > 5 * 1024 * 1024) {
                    $errors[] = $file->getClientName() . ': File too large';
                    continue;
                }

                try {
                    $newName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/news', $newName);
                    
                    $uploaded[] = [
                        'filename' => $newName,
                        'original' => $file->getClientName(),
                        'url' => base_url('uploads/news/' . $newName)
                    ];
                } catch (\Exception $e) {
                    $errors[] = $file->getClientName() . ': ' . $e->getMessage();
                }
            }
        }

        return $this->response->setJSON([
            'success' => count($uploaded) > 0,
            'uploaded' => $uploaded,
            'errors' => $errors
        ]);
    }

    /**
     * Delete image (AJAX)
     */
    public function deleteImage($id = null)
    {
        // Check if admin is logged in
        if (!session()->get('admin_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ])->setStatusCode(401);
        }

        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Image ID is required.'
            ]);
        }

        $result = $this->newsImageModel->deleteImage($id);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Image deleted successfully.' : 'Failed to delete image.'
        ]);
    }

    /**
     * Delete file by filename (AJAX)
     */
    public function deleteFile()
    {
        // Check if admin is logged in
        if (!session()->get('admin_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ])->setStatusCode(401);
        }

        $filename = $this->request->getPost('filename');
        
        if (!$filename) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Filename is required.'
            ]);
        }

        $filePath = FCPATH . 'uploads/news/' . basename($filename);
        
        if (file_exists($filePath)) {
            unlink($filePath);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'File deleted successfully.'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'File not found.'
        ]);
    }
}
