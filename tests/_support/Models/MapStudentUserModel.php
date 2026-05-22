<?php

namespace Tests\Support\Models;

use App\Models\StudentUserModel;

/**
 * In-memory stub for cert recipient resolver unit tests.
 */
final class MapStudentUserModel extends StudentUserModel
{
    /** @var list<array<string, mixed>> */
    public array $rows = [];

    private ?string $whereField = null;

    /** @var mixed */
    private $whereValue = null;

    public function find($id = null)
    {
        if ($id === null) {
            return parent::find($id);
        }
        foreach ($this->rows as $row) {
            if ((int) ($row['id'] ?? 0) === (int) $id) {
                return $row;
            }
        }

        return null;
    }

    public function where($key, $value = null, $escape = null)
    {
        $this->whereField = (string) $key;
        $this->whereValue = $value;

        return $this;
    }

    public function first()
    {
        if ($this->whereField === 'login_uid') {
            foreach ($this->rows as $row) {
                if (($row['login_uid'] ?? '') === $this->whereValue) {
                    return $row;
                }
            }
        }
        if ($this->whereField === 'email') {
            foreach ($this->rows as $row) {
                if (($row['email'] ?? '') === $this->whereValue) {
                    return $row;
                }
            }
        }

        return null;
    }

    public function findByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        foreach ($this->rows as $row) {
            if (strtolower(trim((string) ($row['email'] ?? ''))) === $email) {
                return $row;
            }
        }

        return null;
    }

    public function getFullName(array $row): string
    {
        return trim((string) ($row['tf_name'] ?? '') . ' ' . (string) ($row['tl_name'] ?? ''));
    }
}
