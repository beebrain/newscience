<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdocUserLabelModel extends Model
{
    protected $table = 'edoc_user_labels';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'user_email',
        'name',
        'color',
        'sort_order',
    ];

    protected $validationRules = [
        'user_email' => 'required|valid_email|max_length[255]',
        'name'       => 'required|min_length[1]|max_length[100]',
        'color'      => 'permit_empty|max_length[20]',
        'sort_order' => 'permit_empty|integer',
    ];

    public function listForUser(string $userEmail): array
    {
        $email = strtolower(trim($userEmail));
        if ($email === '') {
            return [];
        }

        return $this->where('user_email', $email)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function createForUser(string $userEmail, string $name, string $color = '#6b7280'): ?int
    {
        $email = strtolower(trim($userEmail));
        $name  = trim($name);
        if ($email === '' || $name === '') {
            return null;
        }

        $exists = $this->where('user_email', $email)
            ->where('name', $name)
            ->first();
        if ($exists) {
            return (int) $exists['id'];
        }

        $id = $this->insert([
            'user_email' => $email,
            'name'       => $name,
            'color'      => $color !== '' ? $color : '#6b7280',
        ], true);

        return $id ? (int) $id : null;
    }

    public function renameForUser(int $id, string $userEmail, string $name, ?string $color = null): bool
    {
        $email = strtolower(trim($userEmail));
        $row   = $this->find($id);
        if (!$row || strtolower(trim($row['user_email'])) !== $email) {
            return false;
        }

        $data = ['name' => trim($name)];
        if ($color !== null && $color !== '') {
            $data['color'] = $color;
        }

        return $this->update($id, $data) !== false;
    }

    public function deleteForUser(int $id, string $userEmail): bool
    {
        $email = strtolower(trim($userEmail));
        $row   = $this->find($id);
        if (!$row || strtolower(trim($row['user_email'])) !== $email) {
            return false;
        }
        return $this->delete($id) !== false;
    }
}
