<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsTagModel extends Model
{
    protected $table = 'news_tags';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug', 'sort_order'];
    protected $useTimestamps = false;

    /**
     * ดึง tag ทั้งหมดเรียงตาม sort_order
     */
    public function getAllOrdered()
    {
        return $this->orderBy('sort_order', 'ASC')->findAll();
    }

    /**
     * หา tag จาก slug
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * ดึง tag ids ของข่าวหนึ่ง
     */
    public function getTagIdsByNewsId(int $newsId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('news_news_tags')
            ->where('news_id', $newsId)
            ->get()
            ->getResultArray();
        return array_column($rows, 'news_tag_id');
    }

    /**
     * ดึง tag objects ของข่าวหนึ่ง (ชื่อ, slug)
     */
    public function getTagsByNewsId(int $newsId): array
    {
        $db = \Config\Database::connect();
        return $db->table('news_news_tags')
            ->select('news_tags.id, news_tags.name, news_tags.slug')
            ->join('news_tags', 'news_tags.id = news_news_tags.news_tag_id')
            ->where('news_news_tags.news_id', $newsId)
            ->orderBy('news_tags.sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * หาหรือสร้าง tag สำหรับหลักสูตร (slug = program_{programId})
     * @return array|null tag row with id, name, slug
     */
    public function findOrCreateForProgram(int $programId, string $programName): ?array
    {
        $slug = 'program_' . $programId;
        $row = $this->findBySlug($slug);
        if ($row) {
            return $row;
        }
        $name = 'หลักสูตร ' . $programName;
        $maxOrder = $this->selectMax('sort_order')->first();
        $sortOrder = (int) ($maxOrder['sort_order'] ?? 0) + 1;
        $id = $this->insert([
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sortOrder,
        ]);
        if (!$id) {
            return null;
        }
        return $this->find($id);
    }

    /**
     * ตั้งค่า tags ของข่าว (ลบเดิมแล้วใส่ใหม่)
     */
    public function setTagsForNews(int $newsId, array $tagIds): void
    {
        $db = \Config\Database::connect();
        $db->table('news_news_tags')->where('news_id', $newsId)->delete();
        if (empty($tagIds)) {
            return;
        }
        $rows = [];
        foreach ($tagIds as $tid) {
            $tid = (int) $tid;
            if ($tid > 0) {
                $rows[] = ['news_id' => $newsId, 'news_tag_id' => $tid];
            }
        }
        if (!empty($rows)) {
            $db->table('news_news_tags')->insertBatch($rows);
        }
    }
}
