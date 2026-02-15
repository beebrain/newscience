<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramDownloadModel extends Model
{
    protected $table = 'program_downloads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'program_id', 'title', 'file_path', 'file_type', 'file_size', 'sort_order'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    
    /**
     * Get downloads for a program
     */
    public function getByProgramId(int $programId)
    {
        return $this->where('program_id', $programId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get download by ID
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }
    
    /**
     * Add new download
     */
    public function addDownload(int $programId, array $data): int
    {
        $data['program_id'] = $programId;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        return $this->insert($data);
    }
    
    /**
     * Update download
     */
    public function updateDownload(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }
    
    /**
     * Delete download
     */
    public function deleteDownload(int $id): bool
    {
        return $this->delete($id);
    }
    
    /**
     * Get file size in human readable format
     */
    public function getFormattedSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get file type icon class
     */
    public function getFileIcon(string $fileType): string
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf',
            'doc' => 'fas fa-file-word',
            'docx' => 'fas fa-file-word',
            'xls' => 'fas fa-file-excel',
            'xlsx' => 'fas fa-file-excel',
            'ppt' => 'fas fa-file-powerpoint',
            'pptx' => 'fas fa-file-powerpoint',
            'zip' => 'fas fa-file-archive',
            'rar' => 'fas fa-file-archive',
            'jpg' => 'fas fa-file-image',
            'jpeg' => 'fas fa-file-image',
            'png' => 'fas fa-file-image',
            'gif' => 'fas fa-file-image',
            'mp4' => 'fas fa-video',
            'mp3' => 'fas fa-music',
            'txt' => 'fas fa-file-alt',
            'default' => 'fas fa-file'
        ];
        
        return $icons[strtolower($fileType)] ?? $icons['default'];
    }
    
    /**
     * Get download URL
     */
    public function getDownloadUrl(int $id): string
    {
        $download = $this->find($id);
        if (!$download) {
            return '';
        }
        
        return base_url('serve/uploads/programs/' . basename($download['file_path']));
    }
}
