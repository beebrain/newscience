<?php

namespace App\Commands;

use App\Libraries\CvProfile;
use App\Libraries\PublicationSyncEngine;
use App\Libraries\ResearchRecordCvPull;
use App\Models\PersonnelModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\ResearchApi;

class SyncPublicationsWithRr extends BaseCommand
{
    protected $group       = 'Research';
    protected $name        = 'publications:sync-rr';
    protected $description = 'Reconcile publication catalog between newScience and Research Record.';
    protected $usage       = 'publications:sync-rr [--email=a@b.c] [--limit=50]';
    protected $options     = [
        '--email' => 'Sync one normalized personnel email.',
        '--limit' => 'Maximum personnel rows to scan when --email is not provided.',
    ];

    public function run(array $params)
    {
        $researchApi = config(ResearchApi::class);
        if (! $researchApi->syncConfigured()) {
            CLI::error('RESEARCH_API_BASE_URL and RESEARCH_API_KEY are required.');

            return;
        }

        $limit = max(1, (int) (CLI::getOption('limit') ?? 50));
        $emailOpt = CvProfile::normalizeEmail((string) (CLI::getOption('email') ?? ''));

        $model = new PersonnelModel();
        $db = $model->db;
        $builder = $model->builder()
            ->select('*')
            ->where('status', 'active')
            ->groupStart()
            ->where('TRIM(user_email) !=', '')
            ->orWhere('TRIM(email) !=', '')
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->limit($limit);
        if ($emailOpt !== '') {
            $builder->groupStart()
                ->where('LOWER(TRIM(user_email)) = ' . $db->escape($emailOpt), null, false)
                ->orWhere('LOWER(TRIM(email)) = ' . $db->escape($emailOpt), null, false)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();
        if ($rows === []) {
            CLI::write('No personnel found.');

            return;
        }

        foreach ($rows as $person) {
            $personnelId = (int) ($person['id'] ?? 0);
            $email = ResearchRecordCvPull::canonicalEmailForPerson($person);
            if ($personnelId <= 0 || $email === '') {
                continue;
            }

            CLI::write('Sync ' . $email . ' ... ', 'yellow');
            $res = PublicationSyncEngine::reconcileForPersonnel($personnelId, $email, 'command');
            if ($res['success']) {
                CLI::write('OK ' . json_encode(['rr_to_ns' => $res['rr_to_ns'] ?? [], 'ns_to_rr' => $res['ns_to_rr'] ?? []], JSON_UNESCAPED_UNICODE), 'green');
            } else {
                CLI::error(($res['error'] ?? 'ERROR') . ': ' . ($res['message'] ?? 'failed'));
            }
        }
    }
}
