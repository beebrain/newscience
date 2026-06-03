<?php

namespace App\Commands;

use App\Libraries\OrcidCvImport;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * One-off harness to exercise ORCID works import for a personnel id.
 * Temporary — remove after verification.
 */
class TestOrcidImport extends BaseCommand
{
    protected $group       = 'Research';
    protected $name        = 'test:orcid-import';
    protected $description  = 'One-off: run ORCID works import for a personnel id.';
    protected $usage        = 'test:orcid-import <personnelId> <orcidId>';

    public function run(array $params)
    {
        $pid   = (int) ($params[0] ?? 0);
        $orcid = (string) ($params[1] ?? '');
        if ($pid <= 0 || $orcid === '') {
            CLI::error('usage: test:orcid-import <personnelId> <orcidId>');

            return;
        }

        $res = OrcidCvImport::import($pid, $orcid, ['works']);
        CLI::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
