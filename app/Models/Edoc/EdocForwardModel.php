<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdocForwardModel extends Model
{
    protected $table = 'edoc_forwards';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'forwarded_at';
    protected $updatedField = '';
    protected $allowedFields = [
        'document_id',
        'from_email',
        'to_email',
        'note',
        'forwarded_at',
    ];

    /**
     * Log a forward action. Does NOT touch the document file or edoctitle row —
     * the actual access grant is handled by adding the recipient to edoc_document_tags.
     */
    public function logForward(int $documentId, string $fromEmail, string $toEmail, ?string $note = null): bool
    {
        $from = strtolower(trim($fromEmail));
        $to   = strtolower(trim($toEmail));
        if ($documentId <= 0 || $from === '' || $to === '' || $from === $to) {
            return false;
        }
        return $this->insert([
            'document_id' => $documentId,
            'from_email'  => $from,
            'to_email'    => $to,
            'note'        => $note,
        ]) !== false;
    }

    public function getForwardsTo(string $userEmail): array
    {
        $email = strtolower(trim($userEmail));
        return $this->where('to_email', $email)
            ->orderBy('forwarded_at', 'DESC')
            ->findAll();
    }

    public function getForwardForUser(int $documentId, string $userEmail): ?array
    {
        $email = strtolower(trim($userEmail));
        return $this->where('document_id', $documentId)
            ->where('to_email', $email)
            ->orderBy('forwarded_at', 'DESC')
            ->first() ?: null;
    }

    public function getForwardedDocumentIds(string $userEmail): array
    {
        $email = strtolower(trim($userEmail));
        $rows  = $this->select('document_id')
            ->where('to_email', $email)
            ->findAll();
        return array_values(array_unique(array_map('intval', array_column($rows, 'document_id'))));
    }
}
