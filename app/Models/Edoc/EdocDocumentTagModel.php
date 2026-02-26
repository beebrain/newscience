<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdocDocumentTagModel extends Model
{
    protected $table = 'edoc_document_tags';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'document_id',
        'tag_email',
        'source_table',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Set tags for a document (replace all existing)
     *
     * @param int $documentId
     * @param array $emails Array of ['email' => '...', 'source' => 'user'|'student_user']
     * @return bool
     */
    public function setDocumentTags(int $documentId, array $emails): bool
    {
        // Delete existing tags for this document
        $this->where('document_id', $documentId)->delete();

        if (empty($emails)) {
            return true;
        }

        $batch = [];
        foreach ($emails as $item) {
            $email = is_array($item) ? ($item['email'] ?? $item) : $item;
            $source = is_array($item) ? ($item['source'] ?? 'user') : 'user';

            if (empty($email)) continue;

            $batch[] = [
                'document_id'  => $documentId,
                'tag_email'    => strtolower(trim($email)),
                'source_table' => $source,
            ];
        }

        if (empty($batch)) {
            return true;
        }

        return $this->insertBatch($batch) !== false;
    }

    /**
     * Add a single tag to a document
     *
     * @param int $documentId
     * @param string $email
     * @param string $source 'user' or 'student_user'
     * @return bool
     */
    public function addTag(int $documentId, string $email, string $source = 'user'): bool
    {
        $email = strtolower(trim($email));

        // Check if already exists
        $exists = $this->where('document_id', $documentId)
            ->where('tag_email', $email)
            ->first();

        if ($exists) {
            return true;
        }

        return $this->insert([
            'document_id'  => $documentId,
            'tag_email'    => $email,
            'source_table' => $source,
        ]) !== false;
    }

    /**
     * Remove a single tag from a document
     *
     * @param int $documentId
     * @param string $email
     * @return bool
     */
    public function removeTag(int $documentId, string $email): bool
    {
        return $this->where('document_id', $documentId)
            ->where('tag_email', strtolower(trim($email)))
            ->delete();
    }

    /**
     * Get all tags for a document
     *
     * @param int $documentId
     * @return array
     */
    public function getDocumentTags(int $documentId): array
    {
        return $this->where('document_id', $documentId)
            ->orderBy('tag_email', 'ASC')
            ->findAll();
    }

    /**
     * Get all document IDs tagged with a specific email
     *
     * @param string $email
     * @return array Array of document_id values
     */
    public function getDocumentIdsByEmail(string $email): array
    {
        $results = $this->select('document_id')
            ->where('tag_email', strtolower(trim($email)))
            ->findAll();

        return array_column($results, 'document_id');
    }

    /**
     * Check if a user (by email) is tagged on a document
     *
     * @param int $documentId
     * @param string $email
     * @return bool
     */
    public function isTagged(int $documentId, string $email): bool
    {
        return $this->where('document_id', $documentId)
            ->where('tag_email', strtolower(trim($email)))
            ->countAllResults() > 0;
    }

    /**
     * Get tag emails as comma-separated string (for backward compatibility)
     *
     * @param int $documentId
     * @return string
     */
    public function getTagEmailsString(int $documentId): string
    {
        $tags = $this->getDocumentTags($documentId);
        return implode(',', array_column($tags, 'tag_email'));
    }

    /**
     * Search for taggable users/students by email or name
     * Combines results from both `user` and `student_user` tables
     *
     * @param string $query Search term
     * @param int $limit
     * @return array
     */
    public function searchTaggableEmails(string $query, int $limit = 20): array
    {
        $results = [];
        $query = trim($query);
        if (empty($query)) {
            return $results;
        }

        // Search in user table
        $users = $this->db->table('user')
            ->select('email, thai_name, thai_lastname, gf_name, gl_name')
            ->groupStart()
                ->like('email', $query)
                ->orLike('thai_name', $query)
                ->orLike('thai_lastname', $query)
                ->orLike('gf_name', $query)
                ->orLike('gl_name', $query)
            ->groupEnd()
            ->where('email IS NOT NULL')
            ->where('email !=', '')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($users as $u) {
            $name = trim(($u['thai_name'] ?? '') . ' ' . ($u['thai_lastname'] ?? ''));
            if (empty($name)) {
                $name = trim(($u['gf_name'] ?? '') . ' ' . ($u['gl_name'] ?? ''));
            }
            $results[] = [
                'email'  => $u['email'],
                'name'   => $name,
                'source' => 'user',
            ];
        }

        // Search in student_user table
        $students = $this->db->table('student_user')
            ->select('email, th_name, thai_lastname, gf_name, gl_name')
            ->groupStart()
                ->like('email', $query)
                ->orLike('th_name', $query)
                ->orLike('thai_lastname', $query)
                ->orLike('gf_name', $query)
                ->orLike('gl_name', $query)
            ->groupEnd()
            ->where('email IS NOT NULL')
            ->where('email !=', '')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($students as $s) {
            $name = trim(($s['th_name'] ?? '') . ' ' . ($s['thai_lastname'] ?? ''));
            if (empty($name)) {
                $name = trim(($s['gf_name'] ?? '') . ' ' . ($s['gl_name'] ?? ''));
            }
            $results[] = [
                'email'  => $s['email'],
                'name'   => $name,
                'source' => 'student_user',
            ];
        }

        // Remove duplicates by email
        $unique = [];
        $seen = [];
        foreach ($results as $r) {
            $key = strtolower($r['email']);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $r;
            }
        }

        return array_slice($unique, 0, $limit);
    }
}
