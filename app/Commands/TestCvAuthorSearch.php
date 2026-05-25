<?php

namespace App\Commands;

use App\Libraries\PublicationAuthorSearch;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * ทดสอบ autocomplete ผู้แต่ง (PublicationAuthorSearch + route)
 *
 * php spark cv:test-author-search [query]
 */
class TestCvAuthorSearch extends BaseCommand
{
    protected $group       = 'CV';
    protected $name        = 'cv:test-author-search';
    protected $description = 'ทดสอบค้นหาชื่อบุคลากรสำหรับ autocomplete ผู้แต่ง';
    protected $usage       = 'cv:test-author-search [query]';

    public function run(array $params): int
    {
        $q = trim((string) ($params[0] ?? 'พิ'));
        CLI::write('=== CV Author Search ===', 'cyan');
        CLI::write('Query: ' . $q, 'yellow');
        CLI::newLine();

        $fail = 0;
        $db   = \Config\Database::connect();

        if (! $db->tableExists('personnel')) {
            CLI::write('[FAIL] ไม่มีตาราง personnel', 'red');

            return 1;
        }

        $hasUser = $db->tableExists('user');
        $hasFac  = $hasUser && $db->fieldExists('faculty', 'user');
        CLI::write('[INFO] personnel=OK user=' . ($hasUser ? 'OK' : 'NO') . ' user.faculty=' . ($hasFac ? 'OK' : 'NO'), 'white');

        $results = PublicationAuthorSearch::searchByName($q, 10);
        $n       = count($results);
        if ($n > 0) {
            CLI::write('[PASS] searchByName → ' . $n . ' รายการ', 'green');
            foreach ($results as $row) {
                CLI::write('  - ' . ($row['name'] ?? '') . ' <' . ($row['email'] ?? '') . '>', 'white');
            }
        } else {
            CLI::write('[FAIL] searchByName → 0 รายการ', 'red');
            $fail++;

            $raw = $db->table('personnel')
                ->select('id, name, name_en, email, user_email, status')
                ->groupStart()
                ->like('name', $q)
                ->orLike('name_en', $q)
                ->groupEnd()
                ->limit(5)
                ->get()
                ->getResultArray();
            CLI::write('[INFO] personnel ตรงชื่อ (ไม่ผ่าน filter): ' . count($raw), 'yellow');
            foreach ($raw as $r) {
                CLI::write('  raw id=' . ($r['id'] ?? '') . ' name=' . ($r['name'] ?? '') . ' status=' . ($r['status'] ?? ''), 'white');
            }
        }

        $base = rtrim((string) config(\Config\App::class)->baseURL, '/');
        $url  = $base . '/dashboard/profile/cv/search-personnel-names?name=' . rawurlencode($q) . '&limit=10';
        CLI::newLine();
        CLI::write('HTTP (ต้องล็อกอิน): ' . $url, 'yellow');

        CLI::newLine();

        return $fail > 0 ? 1 : 0;
    }
}
