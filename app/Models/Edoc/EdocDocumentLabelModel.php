<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdocDocumentLabelModel extends Model
{
    protected $table = 'edoc_document_labels';
    protected $primaryKey = null;
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'document_id',
        'user_email',
        'label_id',
        'created_at',
    ];

    public function applyLabel(int $documentId, string $userEmail, int $labelId): bool
    {
        $email = strtolower(trim($userEmail));
        if ($documentId <= 0 || $email === '' || $labelId <= 0) {
            return false;
        }

        $exists = $this->where('document_id', $documentId)
            ->where('user_email', $email)
            ->where('label_id', $labelId)
            ->countAllResults() > 0;
        if ($exists) {
            return true;
        }

        return $this->insert([
            'document_id' => $documentId,
            'user_email'  => $email,
            'label_id'    => $labelId,
            'created_at'  => date('Y-m-d H:i:s'),
        ]) !== false;
    }

    public function removeLabel(int $documentId, string $userEmail, int $labelId): bool
    {
        $email = strtolower(trim($userEmail));
        return $this->where('document_id', $documentId)
            ->where('user_email', $email)
            ->where('label_id', $labelId)
            ->delete() !== false;
    }

    public function getLabelsForDocument(int $documentId, string $userEmail): array
    {
        $email = strtolower(trim($userEmail));
        return $this->db->table($this->table . ' dl')
            ->select('l.id, l.name, l.color')
            ->join('edoc_user_labels l', 'l.id = dl.label_id', 'inner')
            ->where('dl.document_id', $documentId)
            ->where('dl.user_email', $email)
            ->orderBy('l.sort_order', 'ASC')
            ->orderBy('l.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getDocumentIdsByLabel(int $labelId, string $userEmail): array
    {
        $email = strtolower(trim($userEmail));
        $rows  = $this->select('document_id')
            ->where('label_id', $labelId)
            ->where('user_email', $email)
            ->findAll();
        return array_map('intval', array_column($rows, 'document_id'));
    }

    public function getLabelsForDocumentIds(array $documentIds, string $userEmail): array
    {
        $email = strtolower(trim($userEmail));
        $ids   = array_values(array_unique(array_map('intval', $documentIds)));
        if (empty($ids) || $email === '') {
            return [];
        }

        $rows = $this->db->table($this->table . ' dl')
            ->select('dl.document_id, l.id, l.name, l.color')
            ->join('edoc_user_labels l', 'l.id = dl.label_id', 'inner')
            ->where('dl.user_email', $email)
            ->whereIn('dl.document_id', $ids)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $did = (int) $r['document_id'];
            $map[$did][] = [
                'id'    => (int) $r['id'],
                'name'  => $r['name'],
                'color' => $r['color'],
            ];
        }
        return $map;
    }
}
