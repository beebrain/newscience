<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CLI Command: Update Nicknames to First Name Only
 * 
 * Updates nickname field to use only first name (tf_name) instead of full name
 * This helps match exam data which typically contains only first names
 */
class UpdateNicknamesFirstName extends BaseCommand
{
    protected $group = 'Users';
    protected $name = 'update:nicknames-firstname';
    protected $description = 'Update nicknames to use only first name (tf_name) for better exam matching';
    protected $usage = 'update:nicknames-firstname [--dry-run] [--force]';
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
        
        CLI::write('=== Update Nicknames to First Name Only ===', 'cyan');
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
            $firstName = $user['tf_name'] ?? '';
            $lastName = $user['tl_name'] ?? '';
            $fullName = trim($firstName . ' ' . $lastName);
            
            // Skip if no first name
            if (empty($firstName)) {
                $skips[] = [
                    'uid' => $userId,
                    'reason' => 'No first name (tf_name)',
                    'login_uid' => $user['login_uid'] ?? 'N/A'
                ];
                continue;
            }
            
            // Skip if nickname exists and is already first name (unless force mode)
            if (!empty($currentNickname) && !$this->isForce) {
                // Check if current nickname is already just the first name
                if (trim($currentNickname) === trim($firstName)) {
                    $skips[] = [
                        'uid' => $userId,
                        'reason' => 'Already has first name as nickname: ' . $currentNickname,
                        'login_uid' => $user['login_uid'] ?? 'N/A'
                    ];
                } else {
                    $skips[] = [
                        'uid' => $userId,
                        'reason' => 'Already has nickname: ' . $currentNickname . ' (use --force to overwrite)',
                        'login_uid' => $user['login_uid'] ?? 'N/A'
                    ];
                }
                continue;
            }
            
            // Prepare update data
            $updateData = [
                'uid' => $userId,
                'login_uid' => $user['login_uid'] ?? 'N/A',
                'current_nickname' => $currentNickname,
                'new_nickname' => $firstName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $fullName,
                'eng_name' => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')) ?: 'N/A'
            ];
            
            $updates[] = $updateData;
            
            // Perform update if not dry run
            if (!$this->isDryRun) {
                try {
                    $this->userModel->update($userId, ['nickname' => $firstName]);
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
                CLI::write("    First Name: \"{$u['first_name']}\"", 'white');
                CLI::write("    Full Name: \"{$u['full_name']}\"", 'white');
                CLI::write("    Eng Name: \"{$u['eng_name']}\"", 'white');
                if (!empty($u['current_nickname'])) {
                    CLI::write("    Nickname: \"{$u['current_nickname']}\" → \"{$u['new_nickname']}\"", 'yellow');
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
