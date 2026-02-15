<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsImageModel extends Model
{
    protected $table = 'news_images';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'news_id',
        'image_path',
        'caption',
        'sort_order',
        'file_type'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Get images for a news article (ใช้ file_type ถ้ามีคอลัมน์)
     */
    public function getImagesByNewsId(int $newsId)
    {
        if ($this->db->fieldExists('file_type', $this->table)) {
            return $this->where('news_id', $newsId)
                        ->where('file_type', 'image')
                        ->orderBy('sort_order', 'ASC')
                        ->findAll();
        }
        $all = $this->getAllByNewsId($newsId);
        $split = self::splitAttachmentsIntoImagesAndDocuments($all);
        return $split['images'];
    }

    /**
     * Get documents for a news article (ใช้ file_type ถ้ามีคอลัมน์)
     */
    public function getDocumentsByNewsId(int $newsId)
    {
        if ($this->db->fieldExists('file_type', $this->table)) {
            return $this->where('news_id', $newsId)
                        ->where('file_type', 'document')
                        ->orderBy('sort_order', 'ASC')
                        ->findAll();
        }
        $all = $this->getAllByNewsId($newsId);
        $split = self::splitAttachmentsIntoImagesAndDocuments($all);
        return $split['documents'];
    }

    /**
     * Get all attachments (images + documents) for a news article
     */
    public function getAttachmentsByNewsId(int $newsId)
    {
        $db = $this->db;
        if ($db->fieldExists('file_type', $this->table)) {
            return $this->where('news_id', $newsId)
                        ->orderBy('file_type', 'DESC')
                        ->orderBy('sort_order', 'ASC')
                        ->findAll();
        }
        return $this->where('news_id', $newsId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Get all rows for a news article (no file_type filter). Use to split into images/documents in caller.
     */
    public function getAllByNewsId(int $newsId)
    {
        return $this->where('news_id', $newsId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /** นามสกุลที่ถือว่าเป็นเอกสาร (ไม่ใช่รูป) */
    private static function getDocumentExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    }

    /**
     * แยกรายการจาก news_images เป็นรูป vs เอกสาร ตาม file_type หรือตามนามสกุลไฟล์
     */
    public static function splitAttachmentsIntoImagesAndDocuments(array $rows): array
    {
        $docs = [];
        $imgs = [];
        $docExts = self::getDocumentExtensions();
        foreach ($rows as $row) {
            $type = $row['file_type'] ?? null;
            if ($type === 'document') {
                $docs[] = $row;
                continue;
            }
            if ($type === 'image') {
                $imgs[] = $row;
                continue;
            }
            $ext = strtolower(pathinfo($row['image_path'] ?? '', PATHINFO_EXTENSION));
            if (in_array($ext, $docExts, true)) {
                $docs[] = $row;
            } else {
                $imgs[] = $row;
            }
        }
        return ['images' => $imgs, 'documents' => $docs];
    }

    /**
     * Add attachment to news
     */
    public function addAttachment(int $newsId, string $filePath, string $type = 'image', ?string $caption = null, int $sortOrder = 0)
    {
        return $this->insert([
            'news_id' => $newsId,
            'image_path' => $filePath, // column name is still image_path
            'file_type' => $type,
            'caption' => $caption,
            'sort_order' => $sortOrder
        ]);
    }

    /**
     * Add image to news (Legacy wrapper)
     */
    public function addImage(int $newsId, string $imagePath, ?string $caption = null, int $sortOrder = 0)
    {
        return $this->addAttachment($newsId, $imagePath, 'image', $caption, $sortOrder);
    }

    /**
     * Delete all attachments (images + documents) for a news article
     */
    public function deleteByNewsId(int $newsId)
    {
        $attachments = $this->getAttachmentsByNewsId($newsId);
        $baseNews = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR;
        $publicNews = FCPATH . 'uploads/news/';
        foreach ($attachments as $att) {
            $fn = $att['image_path'];
            $path = is_file($baseNews . $fn) ? $baseNews . $fn : (is_file($publicNews . $fn) ? $publicNews . $fn : null);
            if ($path) {
                @unlink($path);
            }
        }
        return $this->where('news_id', $newsId)->delete();
    }

    /**
     * Delete single image
     */
    public function deleteImage(int $imageId)
    {
        $image = $this->find($imageId);
        
        if ($image) {
            $fn = $image['image_path'];
            $writablePath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR . $fn;
            $publicPath = FCPATH . 'uploads/news/' . $fn;
            $filePath = is_file($writablePath) ? $writablePath : (is_file($publicPath) ? $publicPath : null);
            if ($filePath) {
                @unlink($filePath);
            }
            return $this->delete($imageId);
        }
        
        return false;
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(array $imageIds)
    {
        foreach ($imageIds as $order => $id) {
            $this->update($id, ['sort_order' => $order]);
        }
    }
}
