<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CLI Command: Fix EExamp Nickname Mapping
 * 
 * Checks and fixes nickname mapping for exam schedule matching
 */
class FixExamNicknameMapping extends BaseCommand
{
    protected $group = 'Exam';
    protected $name = 'exam:fix-nickname';
    protected $description = 'Check and fix nickname mapping for EExamp users';
    protected $usage = 'exam:fix-nickname [--check] [--fix] [--name=NAME]';
    protected $arguments = [
        '--check' => 'Check current nickname status only',
        '--fix' => 'Apply fixes to database',
        '--name' => 'Filter by specific name (e.g., "กนกวรรณ")',
    ];

    protected $userModel;

    /**
     * Known nickname mappings from EExamp
     * Format: 'ชื่อเต็ม' => 'ชื่อย่อใน EExamp'
     */
    protected $nicknameMappings = [
        // กนกวรรณ กันยะมี -> กนกวรรณ ก
        ['tf_name' => 'กนกวรรณ', 'tl_name' => 'กันยะมี', 'nickname' => 'กนกวรรณ ก'],
        // กนกวรรณ มารักษ์ -> กนกวรรณ ม
        ['tf_name' => 'กนกวรรณ', 'tl_name' => 'มารักษ์', 'nickname' => 'กนกวรรณ ม'],
    ];

    public function run(array $params)
    {
        $this->userModel = new UserModel();

        $checkOnly = CLI::getOption('check') !== null;
        $applyFix = CLI::getOption('fix') !== null;
        $filterName = CLI::getOption('name');

        CLI::write('=== EExamp Nickname Mapping Fix ===', 'cyan');
        CLI::write('');
        CLI::write("DEBUG: filterName = " . var_export($filterName, true), 'yellow');
        CLI::write('');

        if ($filterName) {
            CLI::write("Filtering by name: {$filterName}", 'yellow');
            CLI::write('');
        }

        // Check current status
        $this->checkCurrentStatus($filterName);

        if ($checkOnly) {
            CLI::write('');
            CLI::write('Check complete. Use --fix to apply changes.', 'green');
            return;
        }

        // Apply fixes if requested
        if ($applyFix) {
            CLI::write('');
            $this->applyFixes($filterName);
        } else {
            CLI::write('');
            CLI::write('To apply fixes, run with --fix option', 'yellow');
        }

        CLI::write('');
        CLI::write('=== END ===', 'cyan');
    }

    /**
     * Check current nickname status
     */
    private function checkCurrentStatus(?string $filterName = null): void
    {
        CLI::write('--- Current Database Status ---', 'white');
        CLI::write('');

        $builder = $this->userModel->builder();

        if ($filterName) {
            CLI::write("Filter value: '{$filterName}'", 'cyan');
            $builder->like('tf_name', $filterName);
        }

        $users = $builder->get()->getResultArray();

        CLI::write("Found " . count($users) . " users", 'cyan');
        CLI::write('');

        if (empty($users)) {
            CLI::write('No users found.', 'red');
            return;
        }

        CLI::write(sprintf('%-5s %-20s %-30s %-20s', 'UID', 'Login', 'Name (tf_name tl_name)', 'Current Nickname'), 'white');
        CLI::write(str_repeat('-', 80), 'white');

        foreach ($users as $user) {
            $fullName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));
            $nickname = $user['nickname'] ?? '(empty)';

            // Check if this user needs updating
            $needsUpdate = false;
            $suggestedNickname = null;

            foreach ($this->nicknameMappings as $mapping) {
                if ($user['tf_name'] === $mapping['tf_name'] && $user['tl_name'] === $mapping['tl_name']) {
                    $suggestedNickname = $mapping['nickname'];
                    if ($nickname !== $mapping['nickname']) {
                        $needsUpdate = true;
                    }
                    break;
                }
            }

            $color = $needsUpdate ? 'red' : 'white';
            CLI::write(
                sprintf(
                    '%-5d %-20s %-30s %-20s',
                    $user['uid'],
                    substr($user['login_uid'] ?? 'N/A', 0, 20),
                    substr($fullName, 0, 30),
                    $nickname
                ),
                $color
            );

            if ($needsUpdate && $suggestedNickname) {
                CLI::write("      → Suggested: {$suggestedNickname}", 'yellow');
            }
        }
    }

    /**
     * Apply nickname fixes
     */
    private function applyFixes(?string $filterName = null): void
    {
        CLI::write('--- Applying Fixes ---', 'white');
        CLI::write('');

        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($this->nicknameMappings as $mapping) {
            // Skip if filter is set and doesn't match
            if ($filterName && $mapping['tf_name'] !== $filterName) {
                continue;
            }

            // Find user
            $user = $this->userModel->where('tf_name', $mapping['tf_name'])
                ->where('tl_name', $mapping['tl_name'])
                ->first();

            if (!$user) {
                CLI::write("❌ User not found: {$mapping['tf_name']} {$mapping['tl_name']}", 'red');
                $errors++;
                continue;
            }

            $currentNickname = $user['nickname'] ?? '';
            $newNickname = $mapping['nickname'];

            if ($currentNickname === $newNickname) {
                CLI::write("✓ Already correct: {$newNickname}", 'green');
                $skipped++;
                continue;
            }

            // Update nickname
            $result = $this->userModel->update($user['uid'], ['nickname' => $newNickname]);

            if ($result) {
                CLI::write("✓ Updated: {$mapping['tf_name']} {$mapping['tl_name']} → {$newNickname}", 'green');
                $fixed++;
            } else {
                CLI::write("❌ Failed to update: {$mapping['tf_name']} {$mapping['tl_name']}", 'red');
                $errors++;
            }
        }

        CLI::write('');
        CLI::write("Summary: {$fixed} fixed, {$skipped} skipped, {$errors} errors", 'cyan');
    }
}
