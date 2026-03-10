<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdoctitleModel extends Model
{
    protected $table = 'edoctitle';
    protected $primaryKey = 'iddoc';
    protected $returnType = 'array';
    protected $allowedFields = [
        'volume_id',
        'doc_year',
        'officeiddoc',
        'title',
        'datedoc',
        'doctype',
        'owner',
        'participant',
        'fileaddress',
        'userid',
        'pages',
        'copynum',
        'order'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'regisdate';
    protected $updatedField = '';

    public function querySQL($sql)
    {
        return $this->db->query($sql);
    }

    public function getDocInfo($iddoc)
    {
        return $this->select([
            'iddoc',
            'volume_id',
            'doc_year',
            'officeiddoc',
            'datedoc',
            'title',
            'doctype',
            'owner',
            'participant',
            'fileaddress',
            'pages',
            'order'
        ])->find($iddoc);
    }

    public function insertdoc($data)
    {
        return $this->insert($data);
    }

    public function updatedoc($iddoc, $data)
    {
        return $this->update($iddoc, $data);
    }

    public function getsummaryPaper()
    {
        return $this->db->table($this->table)
            ->select('SUM(pages * copynum) as papers', false)
            ->get()
            ->getRow()
            ->papers ?? 0;
    }

    /**
     * Get document count by document type
     * 
     * @return array
     */
    public function getDocumentCountByType()
    {
        $builder = $this->db->table('edoctitle');
        $builder->select('doctype, COUNT(*) as count');
        $builder->groupBy('doctype');
        return $builder->get()->getResultArray();
    }

    /**
     * Get documents created per month for the current year
     * 
     * @return array
     */
    public function getDocumentsPerMonth()
    {
        $builder = $this->db->table('edoctitle');
        $builder->select("MONTH(regisdate) as month, COUNT(*) as count");
        $builder->where('YEAR(regisdate)', date('Y'));
        $builder->groupBy('MONTH(regisdate)');
        $builder->orderBy('MONTH(regisdate)', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Get top document owners by document count
     * 
     * @param int $limit Number of records to return
     * @return array
     */
    public function getTopDocumentOwners($limit = 5)
    {
        $builder = $this->db->table('edoctitle');
        $builder->select('owner, COUNT(*) as count');
        $builder->groupBy('owner');
        $builder->orderBy('count', 'DESC');
        $builder->limit($limit);
        return $builder->get()->getResultArray();
    }

    /**
     * Get document distribution by page count
     * 
     * @return array
     */
    public function getDocumentsByPageCount()
    {
        $builder = $this->db->table('edoctitle');
        $builder->select("
            CASE 
                WHEN pages <= 5 THEN '1-5 pages'
                WHEN pages > 5 AND pages <= 10 THEN '6-10 pages'
                WHEN pages > 10 AND pages <= 20 THEN '11-20 pages'
                WHEN pages > 20 AND pages <= 50 THEN '21-50 pages'
                ELSE 'More than 50 pages'
            END as page_range,
            COUNT(*) as count
        ");
        $builder->groupBy('page_range');
        $builder->orderBy('FIELD(page_range, "1-5 pages", "6-10 pages", "11-20 pages", "21-50 pages", "More than 50 pages")');
        return $builder->get()->getResultArray();
    }

    /**
     * คืนรายการ iddoc ที่ user (อีเมลหรือชื่อ) เข้าถึงได้จาก owner/participant โดยตรง
     * ไม่ใช้ edoc_document_tags — ใช้ชื่อและแมปจาก user เพื่อเช็ค
     *
     * @param string $email อีเมลผู้ใช้
     * @param string $name ชื่อ-นามสกุลจาก user (tf_name + tl_name หรือ gf_name + gl_name)
     * @return array<int> รายการ iddoc
     */
    public function getDocumentIdsAccessibleByUser(string $email, string $name): array
    {
        $email = trim($email);
        $name = trim($name);
        $builder = $this->builder();
        $builder->select('edoctitle.iddoc');
        $builder->groupStart();
        if ($email !== '') {
            $builder->orWhere('edoctitle.owner', $email);
            $builder->orLike('edoctitle.participant', $email, 'both');
        }
        if ($name !== '') {
            $builder->orWhere('edoctitle.owner', $name);
            $builder->orLike('edoctitle.participant', $name, 'both');
        }
        $builder->orLike('edoctitle.participant', 'ทุกคน', 'both');
        $builder->groupEnd();
        $rows = $builder->get()->getResultArray();
        return array_values(array_unique(array_column($rows, 'iddoc')));
    }

    /**
     * ตรวจว่า user (email หรือ name) มีสิทธิ์ดูเอกสารนี้หรือไม่ (จาก owner/participant เท่านั้น)
     */
    public function canUserAccessDocument(int $iddoc, string $email, string $name): bool
    {
        $doc = $this->find($iddoc);
        if (!$doc) {
            return false;
        }
        $owner = trim((string) ($doc['owner'] ?? ''));
        $participant = trim((string) ($doc['participant'] ?? ''));
        if ($email !== '' && (strtolower($owner) === strtolower($email) || stripos($participant, $email) !== false)) {
            return true;
        }
        if ($name !== '' && ($owner === $name || strpos($participant, $name) !== false)) {
            return true;
        }
        if (strpos($participant, 'ทุกคน') !== false || in_array('ทุกคน', array_map('trim', explode(',', $participant)), true)) {
            return true;
        }
        return false;
    }

    /**
     * Get average pages per document type
     *
     * @return array
     */
    public function getAveragePagesPerDocType()
    {
        $builder = $this->db->table('edoctitle');
        $builder->select('doctype, AVG(pages) as avg_pages');
        $builder->groupBy('doctype');
        return $builder->get()->getResultArray();
    }
}
