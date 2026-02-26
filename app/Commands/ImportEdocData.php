<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class ImportEdocData extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'import:edoc';
    protected $description = 'Import Edoc data from sci-edoc database';

    public function run(array $params)
    {
        CLI::write('Starting Edoc data import...', 'yellow');

        // Connect to source database (sci-edoc)
        $sourceConfig = new \Config\Database();
        $sourceConfig->default['database'] = 'sci-edoc';
        $sourceDB = \Config\Database::connect($sourceConfig->default);

        // Connect to target database (newScience)
        $targetDB = \Config\Database::connect();

        try {
            // Import edoctitle
            CLI::write('Importing edoctitle...', 'yellow');
            $edoctitles = $sourceDB->query('SELECT * FROM edoctitle')->getResultArray();
            $count = 0;
            foreach ($edoctitles as $doc) {
                $targetDB->query("INSERT INTO edoctitle 
                    (iddoc, officeiddoc, title, datedoc, doctype, owner, participant, fileaddress, userid, pages, copynum, `order`, regisdate) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    officeiddoc = VALUES(officeiddoc),
                    title = VALUES(title),
                    datedoc = VALUES(datedoc),
                    doctype = VALUES(doctype),
                    owner = VALUES(owner),
                    participant = VALUES(participant),
                    fileaddress = VALUES(fileaddress),
                    userid = VALUES(userid),
                    pages = VALUES(pages),
                    copynum = VALUES(copynum),
                    `order` = VALUES(`order`)", [
                    $doc['iddoc'],
                    $doc['officeiddoc'],
                    $doc['title'],
                    $doc['datedoc'],
                    $doc['doctype'],
                    $doc['owner'],
                    $doc['participant'],
                    $doc['fileaddress'],
                    $doc['userid'],
                    $doc['pages'],
                    $doc['copynum'],
                    $doc['order'] ?? 0,
                    $doc['regisdate'] ?? date('Y-m-d H:i:s')
                ]);
                $count++;
                if ($count % 100 == 0) {
                    CLI::write("  Imported {$count} documents...", 'green');
                }
            }
            CLI::write("Imported {$count} edoctitle records", 'green');

            // Import edoctag and link with users by email
            CLI::write('Importing edoctag and linking with users by email...', 'yellow');

            // Get all users for matching by name
            $users = $targetDB->query('SELECT uid, email, gf_name, gl_name FROM users')->getResultArray();
            $userMap = [];
            foreach ($users as $user) {
                $key = trim($user['gf_name']) . ' ' . trim($user['gl_name']);
                $userMap[$key] = $user['email'];
            }

            $edoctags = $sourceDB->query('SELECT * FROM edoctag')->getResultArray();
            $count = 0;
            $linkedCount = 0;
            foreach ($edoctags as $tag) {
                // Try to match with user by name
                $email = null;
                $tagKey = trim($tag['gf_name'] ?? '') . ' ' . trim($tag['gl_name'] ?? '');
                if (isset($userMap[$tagKey])) {
                    $email = $userMap[$tagKey];
                    $linkedCount++;
                }

                $targetDB->query("INSERT INTO edoctag 
                    (idtag, gf_name, gl_name, nickname, office_idtag, email, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    gf_name = VALUES(gf_name),
                    gl_name = VALUES(gl_name),
                    nickname = VALUES(nickname),
                    office_idtag = VALUES(office_idtag),
                    email = VALUES(email)", [
                    $tag['idtag'],
                    $tag['gf_name'],
                    $tag['gl_name'],
                    $tag['nickname'],
                    $tag['office_idtag'] ?? null,
                    $email,
                    $tag['created_at'] ?? date('Y-m-d H:i:s'),
                    $tag['updated_at'] ?? date('Y-m-d H:i:s')
                ]);
                $count++;
            }
            CLI::write("Imported {$count} edoctag records (linked {$linkedCount} with users by email)", 'green');

            // Import edoctaggroups
            CLI::write('Importing edoctaggroups...', 'yellow');
            $groups = $sourceDB->query('SELECT * FROM edoctaggroups')->getResultArray();
            $count = 0;
            foreach ($groups as $group) {
                $targetDB->query("INSERT INTO edoctaggroups 
                    (group_id, group_name, idtag, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    group_name = VALUES(group_name),
                    idtag = VALUES(idtag)", [
                    $group['group_id'],
                    $group['group_name'],
                    $group['idtag'],
                    $group['created_at'] ?? date('Y-m-d H:i:s'),
                    $group['updated_at'] ?? date('Y-m-d H:i:s')
                ]);
                $count++;
            }
            CLI::write("Imported {$count} edoctaggroups records", 'green');

            // Import documentviews
            CLI::write('Importing documentviews...', 'yellow');
            $views = $sourceDB->query('SELECT * FROM documentviews')->getResultArray();
            $count = 0;
            foreach ($views as $view) {
                $targetDB->query("INSERT INTO documentviews 
                    (view_id, iddoc, idtag, view_time, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    view_time = VALUES(view_time)", [
                    $view['view_id'],
                    $view['iddoc'],
                    $view['idtag'],
                    $view['view_time'],
                    $view['ip_address'] ?? null,
                    $view['user_agent'] ?? null,
                    $view['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $count++;
                if ($count % 500 == 0) {
                    CLI::write("  Imported {$count} views...", 'green');
                }
            }
            CLI::write("Imported {$count} documentviews records", 'green');

            CLI::write('Edoc data import completed successfully!', 'green');
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
