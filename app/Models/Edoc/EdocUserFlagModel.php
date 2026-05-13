<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdocUserFlagModel extends Model
{
    protected $table = 'edoc_user_flags';
    protected $primaryKey = null;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'document_id',
        'user_email',
        'is_starred',
        'is_important',
        'is_archived',
        'read_at',
    ];

    public function getFlags(int $documentId, string $userEmail): ?array
    {
        $email = strtolower(trim($userEmail));
        return $this->where('document_id', $documentId)
            ->where('user_email', $email)
            ->first() ?: null;
    }

    public function getFlagsForDocumentIds(array $documentIds, string $userEmail): array
    {
        $email = strtolower(trim($userEmail));
        $ids   = array_values(array_unique(array_map('intval', $documentIds)));
        if (empty($ids) || $email === '') {
            return [];
        }

        $rows = $this->whereIn('document_id', $ids)
            ->where('user_email', $email)
            ->findAll();

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['document_id']] = $r;
        }
        return $map;
    }

    public function setFlag(int $documentId, string $userEmail, string $flag, $value): bool
    {
        $allowed = ['is_starred', 'is_important', 'is_archived', 'read_at'];
        if (!in_array($flag, $allowed, true)) {
            return false;
        }
        $email = strtolower(trim($userEmail));
        if ($documentId <= 0 || $email === '') {
            return false;
        }

        $existing = $this->getFlags($documentId, $email);
        if ($existing) {
            return $this->where('document_id', $documentId)
                ->where('user_email', $email)
                ->set([$flag => $value])
                ->update() !== false;
        }

        return $this->insert([
            'document_id' => $documentId,
            'user_email'  => $email,
            $flag         => $value,
        ]) !== false;
    }

    public function toggleStar(int $documentId, string $userEmail): bool
    {
        $existing = $this->getFlags($documentId, $userEmail);
        $next = $existing ? (int) !((int) ($existing['is_starred'] ?? 0)) : 1;
        return $this->setFlag($documentId, $userEmail, 'is_starred', $next);
    }

    public function markRead(int $documentId, string $userEmail): bool
    {
        return $this->setFlag($documentId, $userEmail, 'read_at', date('Y-m-d H:i:s'));
    }

    public function archive(int $documentId, string $userEmail, bool $archived = true): bool
    {
        return $this->setFlag($documentId, $userEmail, 'is_archived', $archived ? 1 : 0);
    }
}
