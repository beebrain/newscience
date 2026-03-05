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
     * Search for taggable users/students by email or name.
     * ยึด table user และชื่อใน user (tf_name, tl_name, gf_name, gl_name) เป็นหลัก แล้วจึง student_user
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

        $words = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
        $hasMultipleWords = count($words) >= 2;

        // Search in user table (อีเมล, ชื่อ-นามสกุลไทย/อังกฤษ, และชื่อเต็ม "ชื่อ นามสกุล")
        $userBuilder = $this->db->table('user')
            ->select('email, tf_name, tl_name, gf_name, gl_name')
            ->groupStart()
                ->like('email', $query)
                ->orLike('tf_name', $query)
                ->orLike('tl_name', $query)
                ->orLike('gf_name', $query)
                ->orLike('gl_name', $query);
        if ($hasMultipleWords) {
            $userBuilder->orGroupStart()
                ->like('tf_name', $words[0])
                ->like('tl_name', $words[1])
                ->groupEnd()
                ->orGroupStart()
                ->like('gf_name', $words[0])
                ->like('gl_name', $words[1])
                ->groupEnd();
            if (count($words) >= 2 && ($words[0] !== $words[1])) {
                $userBuilder->orGroupStart()
                    ->like('tf_name', $words[1])
                    ->like('tl_name', $words[0])
                    ->groupEnd()
                    ->orGroupStart()
                    ->like('gf_name', $words[1])
                    ->like('gl_name', $words[0])
                    ->groupEnd();
            }
        }
        $users = $userBuilder->groupEnd()
            ->where('email IS NOT NULL')
            ->where('email !=', '')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $seenEmails = [];
        foreach ($users as $u) {
            $email = trim($u['email'] ?? '');
            if ($email === '') {
                continue;
            }
            $seenEmails[mb_strtolower($email)] = true;
            // ชื่อใน user table เป็นหลัก: tf_name + tl_name (ไทย) ก่อน แล้ว fallback gf_name + gl_name
            $name = trim(($u['tf_name'] ?? '') . ' ' . ($u['tl_name'] ?? ''));
            if ($name === '') {
                $name = trim(($u['gf_name'] ?? '') . ' ' . ($u['gl_name'] ?? ''));
            }
            $results[] = [
                'email'  => $email,
                'name'   => $name,
                'source' => 'user',
            ];
        }

        // รองลงมา: student_user (ไม่ซ้ำ email ที่มีจาก user แล้ว)
        $studentBuilder = $this->db->table('student_user')
            ->select('email, tf_name, tl_name, gf_name, gl_name')
            ->groupStart()
                ->like('email', $query)
                ->orLike('tf_name', $query)
                ->orLike('tl_name', $query)
                ->orLike('gf_name', $query)
                ->orLike('gl_name', $query);
        if ($hasMultipleWords) {
            $studentBuilder->orGroupStart()
                ->like('tf_name', $words[0])
                ->like('tl_name', $words[1])
                ->groupEnd()
                ->orGroupStart()
                ->like('gf_name', $words[0])
                ->like('gl_name', $words[1])
                ->groupEnd();
            if ($words[0] !== $words[1]) {
                $studentBuilder->orGroupStart()
                    ->like('tf_name', $words[1])
                    ->like('tl_name', $words[0])
                    ->groupEnd()
                    ->orGroupStart()
                    ->like('gf_name', $words[1])
                    ->like('gl_name', $words[0])
                    ->groupEnd();
            }
        }
        $remaining = $limit - count($results);
        $students = $remaining > 0
            ? $studentBuilder->groupEnd()
                ->where('email IS NOT NULL')
                ->where('email !=', '')
                ->limit($remaining)
                ->get()
                ->getResultArray()
            : [];

        foreach ($students as $s) {
            $email = trim($s['email'] ?? '');
            if ($email === '' || isset($seenEmails[mb_strtolower($email)])) {
                continue;
            }
            $seenEmails[mb_strtolower($email)] = true;
            $name = trim(($s['tf_name'] ?? '') . ' ' . ($s['tl_name'] ?? ''));
            if ($name === '') {
                $name = trim(($s['gf_name'] ?? '') . ' ' . ($s['gl_name'] ?? ''));
            }
            $results[] = [
                'email'  => $email,
                'name'   => $name,
                'source' => 'student_user',
            ];
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * Resolve a single email to display name (Thai name preferred, else English, else email).
     *
     * @param string $email
     * @return string
     */
    public function getDisplayNameByEmail(string $email): string
    {
        $map = $this->getDisplayNamesByEmails([$email]);
        return $map[$email] ?? $email;
    }

    /**
     * Resolve multiple emails to display names in one go (user + student_user).
     * Returns array keyed by lowercase email => display name (Thai preferred).
     *
     * @param array $emails
     * @return array<string, string> email (lowercase) => display name
     */
    public function getDisplayNamesByEmails(array $emails): array
    {
        $result = [];
        $emails = array_unique(array_map(function ($e) {
            return strtolower(trim((string) $e));
        }, $emails));
        $emails = array_filter($emails);

        if (empty($emails)) {
            return $result;
        }

        $cols = ['email', 'tf_name', 'tl_name', 'gf_name', 'gl_name'];
        if ($this->db->fieldExists('thai_name', 'user') && $this->db->fieldExists('thai_lastname', 'user')) {
            $cols = array_merge($cols, ['thai_name', 'thai_lastname']);
        }

        $users = $this->db->table('user')
            ->select(implode(', ', array_unique($cols)))
            ->whereIn('email', $emails)
            ->get()
            ->getResultArray();

        foreach ($users as $u) {
            $email = strtolower(trim($u['email'] ?? ''));
            if ($email === '') {
                continue;
            }
            $name = trim(($u['tf_name'] ?? '') . ' ' . ($u['tl_name'] ?? ''));
            if ($name === '' && !empty($u['thai_name'] ?? $u['thai_lastname'] ?? null)) {
                $name = trim(($u['thai_name'] ?? '') . ' ' . ($u['thai_lastname'] ?? ''));
            }
            if ($name === '') {
                $name = trim(($u['gf_name'] ?? '') . ' ' . ($u['gl_name'] ?? ''));
            }
            $result[$email] = $name !== '' ? $name : $email;
        }

        $remaining = array_diff($emails, array_keys($result));
        if (!empty($remaining) && $this->db->tableExists('student_user')) {
            $sCols = ['email', 'tf_name', 'tl_name', 'gf_name', 'gl_name'];
            $students = $this->db->table('student_user')
                ->select(implode(', ', $sCols))
                ->whereIn('email', $remaining)
                ->get()
                ->getResultArray();

            foreach ($students as $s) {
                $email = strtolower(trim($s['email'] ?? ''));
                if ($email === '') {
                    continue;
                }
                $name = trim(($s['tf_name'] ?? '') . ' ' . ($s['tl_name'] ?? ''));
                if ($name === '') {
                    $name = trim(($s['gf_name'] ?? '') . ' ' . ($s['gl_name'] ?? ''));
                }
                $result[$email] = $name !== '' ? $name : $email;
            }
        }

        foreach ($emails as $e) {
            if (!isset($result[$e])) {
                $result[$e] = $e;
            }
        }

        return $result;
    }
}
