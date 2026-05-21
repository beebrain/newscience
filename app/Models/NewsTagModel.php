<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsTagModel extends Model
{
    /** Tag หลักสูตร: program_{id} */
    public const PROGRAM_TAG_PREFIX = 'program_';

    /** Tag ข่าวประชาสัมพันธ์ / ข่าวทั่วไป (แสดงในหน้าแรกและ /news) */
    public const PUBLIC_RELATIONS_TAG_SLUG = 'general';

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

    public static function isProgramTagSlug(string $slug): bool
    {
        return str_starts_with($slug, self::PROGRAM_TAG_PREFIX);
    }

    /**
     * Tag ข่าวประชาสัมพันธ์ (general) — สร้างถ้ายังไม่มี
     */
    public function findOrCreateGeneralTag(): ?array
    {
        $row = $this->findBySlug(self::PUBLIC_RELATIONS_TAG_SLUG);
        if ($row) {
            return $row;
        }
        $maxOrder = $this->selectMax('sort_order')->first();
        $sortOrder = (int) ($maxOrder['sort_order'] ?? 0) + 1;
        $id = $this->insert([
            'name'       => 'ข่าวทั่วไป',
            'slug'       => self::PUBLIC_RELATIONS_TAG_SLUG,
            'sort_order' => $sortOrder,
        ]);

        return $id ? $this->find($id) : null;
    }

    /**
     * Tags บังคับเมื่อสร้างข่าวจากหลักสูตร: program_{id} + general (ข่าวประชาสัมพันธ์)
     *
     * @return list<int>
     */
    public function tagIdsForProgramNews(int $programId, string $programName): array
    {
        $ids = [];
        $programTag = $this->findOrCreateForProgram($programId, $programName);
        if ($programTag) {
            $ids[] = (int) $programTag['id'];
        }
        $generalTag = $this->findOrCreateGeneralTag();
        if ($generalTag) {
            $ids[] = (int) $generalTag['id'];
        }

        return array_values(array_unique($ids));
    }

    /**
     * ตั้งค่า tags ของข่าว (ลบเดิมแล้วใส่ใหม่)
     * ถ้ามี tag หลักสูตร (program_*) ต้องมี general ด้วย — ป้องกัน buff ให้เห็นแค่หลักสูตรเดียวบนหน้าประชาสัมพันธ์
     */
    public function setTagsForNews(int $newsId, array $tagIds): void
    {
        $tagIds = array_values(array_unique(array_filter(array_map('intval', $tagIds), static fn ($id) => $id > 0)));

        if ($tagIds !== []) {
            $rows = $this->whereIn('id', $tagIds)->findAll();
            $hasProgram = false;
            $hasGeneral = false;
            foreach ($rows as $row) {
                $slug = (string) ($row['slug'] ?? '');
                if (self::isProgramTagSlug($slug)) {
                    $hasProgram = true;
                }
                if ($slug === self::PUBLIC_RELATIONS_TAG_SLUG) {
                    $hasGeneral = true;
                }
            }
            if ($hasProgram && ! $hasGeneral) {
                $general = $this->findOrCreateGeneralTag();
                if ($general) {
                    $tagIds[] = (int) $general['id'];
                    $tagIds = array_values(array_unique($tagIds));
                }
            }
        }

        $db = \Config\Database::connect();
        $db->table('news_news_tags')->where('news_id', $newsId)->delete();
        if ($tagIds === []) {
            return;
        }
        $insertRows = [];
        foreach ($tagIds as $tid) {
            $insertRows[] = ['news_id' => $newsId, 'news_tag_id' => $tid];
        }
        $db->table('news_news_tags')->insertBatch($insertRows);
    }

    /**
     * เพิ่ม tag general ให้ข่าวที่มีแต่ program_* (ย้ายข้อมูลเก่าเข้าหน้าประชาสัมพันธ์)
     */
    public function backfillGeneralTagForProgramOnlyNews(): int
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('news_tags') || ! $db->tableExists('news_news_tags')) {
            return 0;
        }
        $general = $this->findOrCreateGeneralTag();
        if (! $general) {
            return 0;
        }
        $generalId = (int) $general['id'];
        $newsIds = $db->table('news')->select('id')->where('status', 'published')->get()->getResultArray();
        $added = 0;
        foreach ($newsIds as $row) {
            $newsId = (int) ($row['id'] ?? 0);
            if ($newsId <= 0) {
                continue;
            }
            $tags = $this->getTagsByNewsId($newsId);
            if ($tags === []) {
                continue;
            }
            $hasProgram = false;
            $hasGeneral = false;
            foreach ($tags as $t) {
                $slug = (string) ($t['slug'] ?? '');
                if (self::isProgramTagSlug($slug)) {
                    $hasProgram = true;
                }
                if ($slug === self::PUBLIC_RELATIONS_TAG_SLUG) {
                    $hasGeneral = true;
                }
            }
            if ($hasProgram && ! $hasGeneral) {
                $db->table('news_news_tags')->ignore(true)->insert([
                    'news_id'      => $newsId,
                    'news_tag_id'  => $generalId,
                ]);
                $added++;
            }
        }

        return $added;
    }
}
