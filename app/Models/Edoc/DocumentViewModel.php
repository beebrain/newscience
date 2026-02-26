<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class DocumentViewModel extends Model
{
    protected $table = 'document_views';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'viewed_at';
    protected $updatedField = '';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['document_id', 'user_id', 'ip_address', 'viewed_at'];


    public function recordView($documentId, $userId = null)
    {
        $ipAddress = service('request')->getIPAddress();

        $data = [
            'document_id' => $documentId,
            'ip_address' => $ipAddress
        ];

        if ($userId) {
            $data['user_id'] = $userId;
        }

        return $this->insert($data);
    }

    public function getViewCount($documentId)
    {
        return $this->where('document_id', $documentId)->countAllResults();
    }

    public function getUniqueViewers($documentId)
    {
        $builder = $this->db->table($this->table);
        return $builder->select('COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id ELSE ip_address END) as count')
            ->where('document_id', $documentId)
            ->get()
            ->getRow()
            ->count;
    }

    public function getDocumentViewStats($documentId)
    {
        return [
            'total_views' => $this->getViewCount($documentId),
            'unique_viewers' => $this->getUniqueViewers($documentId),
            'recent_viewers' => $this->getRecentViewers($documentId)
        ];
    }


    public function getRecentViewers($documentId, $limit = 5)
    {
        return $this->select('document_views.*, user.thai_name, user.thai_lastname')
            ->join('user', 'user.uid = document_views.user_id', 'left')
            ->where('document_id', $documentId)
            ->orderBy('viewed_at', 'DESC')
            ->limit($limit)
            ->find();
    }
}
