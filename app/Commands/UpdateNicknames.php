<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CLI Command: Update User Nicknames
 * 
 * Updates nickname field for users who don't have one
 * by using their Thai name (tf_name + tl_name)
 */
class UpdateNicknames extends BaseCommand
{
    protected $group = 'Users';
    protected $name = 'update:nicknames';
    protected $description = 'Update nickname field using Thai names for users without nickname';
    protected $usage = 'update:nicknames [--dry-run] [--force]';
    protected $arguments = [
        '--dry-run' => 'Show what would be updated without making changes',
        '--force' => 'Force update even if nickname exists (overwrite)',
    ];

    protected $userModel;
    protected $isDryRun = false;
    protected $isForce = false;

    public function run(array $params)
    {
        $this->userModel = new UserModel();

        // Parse options
        $this->isDryRun = CLI::getOption('dry-run') !== null;
        $this->isForce = CLI::getOption('force') !== null;

        CLI::write('=== Update User Nicknames ===', 'cyan');
        CLI::write('');

        if ($this->isDryRun) {
            CLI::write('🔍 DRY RUN MODE - No changes will be made', 'yellow');
        }

        if ($this->isForce) {
            CLI::write('⚠️  FORCE MODE - Will overwrite existing nicknames', 'yellow');
        }

        CLI::write('');

        // Get all users
        $users = $this->userModel->findAll();
        $totalUsers = count($users);

        CLI::write("Found {$totalUsers} users in database");
        CLI::write('');

        $updates = [];
        $skips = [];
        $errors = [];

        foreach ($users as $user) {
            $userId = $user['uid'];
            $currentNickname = $user['nickname'] ?? '';
            $thaiName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));

            // Skip if no Thai name
            if (empty($thaiName)) {
                $skips[] = [
                    'uid' => $userId,
                    'reason' => 'No Thai name (tf_name + tl_name)',
                    'login_uid' => $user['login_uid'] ?? 'N/A'
                ];
                continue;
            }

            // Skip if nickname exists (unless force mode)
            if (!empty($currentNickname) && !$this->isForce) {
                $skips[] = [
                    'uid' => $userId,
                    'reason' => 'Already has nickname: ' . $currentNickname,
                    'login_uid' => $user['login_uid'] ?? 'N/A'
                ];
                continue;
            }

            // Prepare update data
            $updateData = [
                'uid' => $userId,
                'login_uid' => $user['login_uid'] ?? 'N/A',
                'current_nickname' => $currentNickname,
                'new_nickname' => $thaiName,
                'thai_name' => $thaiName,
                'eng_name' => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')) ?: 'N/A'
            ];

            $updates[] = $updateData;

            // Perform update if not dry run
            if (!$this->isDryRun) {
                try {
                    $this->userModel->update($userId, ['nickname' => $thaiName]);
                } catch (\Exception $e) {
                    $errors[] = [
                        'uid' => $userId,
                        'error' => $e->getMessage(),
                        'login_uid' => $user['login_uid'] ?? 'N/A'
                    ];
                }
            }
        }

        // Summary
        CLI::write('=== SUMMARY ===', 'cyan');
        CLI::write('');

        if (!empty($updates)) {
            CLI::write("✓ Users to update: " . count($updates), 'green');
            CLI::write('');

            // Show first 10 updates
            $limit = min(10, count($updates));
            CLI::write('Sample updates (first ' . $limit . '):');
            for ($i = 0; $i < $limit; $i++) {
                $u = $updates[$i];
                CLI::write("  [{$u['uid']}] {$u['login_uid']}", 'white');
                CLI::write("    Thai Name: \"{$u['thai_name']}\"", 'white');
                CLI::write("    Eng Name: \"{$u['eng_name']}\"", 'white');
                if (!empty($u['current_nickname'])) {
                    CLI::write("    Current Nickname: \"{$u['current_nickname']}\" → \"{$u['new_nickname']}\"", 'yellow');
                } else {
                    CLI::write("    Nickname: (empty) → \"{$u['new_nickname']}\"", 'green');
                }
                CLI::write('');
            }

            if (count($updates) > 10) {
                CLI::write('... and ' . (count($updates) - 10) . ' more users', 'white');
            }
        }

        if (!empty($skips)) {
            CLI::write("⚠ Users skipped: " . count($skips), 'yellow');
            CLI::write('');

            // Show skip reasons
            $skipReasons = [];
            foreach ($skips as $skip) {
                $reason = $skip['reason'];
                if (!isset($skipReasons[$reason])) {
                    $skipReasons[$reason] = 0;
                }
                $skipReasons[$reason]++;
            }

            foreach ($skipReasons as $reason => $count) {
                CLI::write("  - {$reason}: {$count} users", 'white');
            }
        }

        if (!empty($errors)) {
            CLI::write("✗ Errors: " . count($errors), 'red');
            CLI::write('');
            foreach ($errors as $error) {
                CLI::write("  [{$error['uid']}] {$error['login_uid']}: {$error['error']}", 'red');
            }
        }

        CLI::write('');
        CLI::write('=== END ===', 'cyan');

        if ($this->isDryRun) {
            CLI::write('Dry run completed. Use --force to apply changes.', 'yellow');
        } else {
            CLI::write('Update completed.', 'green');
        }
    }
}
