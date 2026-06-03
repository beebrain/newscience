<?php

namespace App\Commands;

use App\Libraries\ResearchRecordSsoBridge;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\ResearchRecordSso;

/**
 * Smoke: routes + SSO bridge สำหรับผลงาน NS → RR
 *
 * php spark cv:smoke-ns-rr-publication
 */
class SmokeNsRrPublication extends BaseCommand
{
    protected $group       = 'CV';
    protected $name        = 'cv:smoke-ns-rr-publication';
    protected $description = 'Smoke test NS↔RR publication integration (routes, SSO URL)';
    protected $usage       = 'cv:smoke-ns-rr-publication';

    public function run(array $params): int
    {
        CLI::write('=== Smoke NS ↔ RR publications ===', 'cyan');
        $fail = 0;

        $routesFile = APPPATH . 'Config/Routes.php';
        $routesSrc  = (string) file_get_contents($routesFile);
        foreach ([
            'rr-publication/create',
            'rr-publication/edit',
            'rr-publication/manage',
            'go-rr-publication',
        ] as $needle) {
            if (str_contains($routesSrc, $needle)) {
                CLI::write('[PASS] route registered: ' . $needle, 'green');
            } else {
                CLI::write('[FAIL] route missing: ' . $needle, 'red');
                $fail++;
            }
        }

        $sso = config(ResearchRecordSso::class);
        if ($sso->enabled && $sso->baseUrl !== '' && $sso->sharedSecret !== '') {
            $return = rtrim((string) config(\Config\App::class)->baseURL, '/')
                . '/index.php/dashboard/profile/cv?tab=sections&rr_sync=1';
            $url    = ResearchRecordSsoBridge::signedEntryUrl(
                'smoke.test@uru.ac.th',
                'Smoke Test',
                ResearchRecordSsoBridge::rrEntryPath('publications/create'),
                $return
            );
            if ($url !== null && str_contains($url, 'token=') && str_contains($url, 'sso-entry')) {
                CLI::write('[PASS] SSO signed URL builds', 'green');
                CLI::write('       ' . mb_substr($url, 0, 120) . '…', 'white');
            } else {
                CLI::write('[FAIL] SSO signed URL invalid', 'red');
                $fail++;
            }
            $path = ResearchRecordSsoBridge::rrEntryPath('publications/edit/99');
            if (str_contains($path, 'publications/edit/99')) {
                CLI::write('[PASS] rrEntryPath edit: ' . $path, 'green');
            } else {
                CLI::write('[FAIL] rrEntryPath: ' . $path, 'red');
                $fail++;
            }
        } else {
            CLI::write('[WARN] Research Record SSO not configured — skip signed URL check', 'yellow');
        }

        $profileCv = (string) file_get_contents(APPPATH . 'Controllers/User/ProfileCv.php');
        if (str_contains($profileCv, 'pullPublicationsOnly') && ! str_contains($profileCv, 'PublicationSyncEngine::reconcileForPersonnel')) {
            CLI::write('[PASS] ProfileCv: pull-only (no reconcileForPersonnel in controller)', 'green');
        } else {
            CLI::write('[FAIL] ProfileCv still references PublicationSyncEngine reconcile', 'red');
            $fail++;
        }
        if (str_contains($profileCv, 'cv_publication_page') && str_contains($profileCv, 'fromCvPublicationPage')) {
            CLI::write('[PASS] saveCvEntry: AI page may save locally; other posts → RR', 'green');
        } else {
            CLI::write('[FAIL] saveCvEntry missing cv_publication_page exception', 'red');
            $fail++;
        }

        $mergeSrc = (string) file_get_contents(APPPATH . 'Libraries/ResearchRecordCvSyncMerge.php');
        if (str_contains($mergeSrc, 'encodeBibliographicMetadata($pub)')) {
            CLI::write('[PASS] pull merges RR bundle biblio into cv_entries metadata', 'green');
        } else {
            CLI::write('[FAIL] applyPublicationsToCvEntries missing encodeBibliographicMetadata', 'red');
            $fail++;
        }

        $displaySrc = (string) file_get_contents(APPPATH . 'Libraries/PublicationDisplay.php');
        if (str_contains($displaySrc, 'fromRrBundle')) {
            CLI::write('[PASS] PublicationDisplay prefers RR metadata', 'green');
        } else {
            CLI::write('[FAIL] PublicationDisplay missing fromRrBundle path', 'red');
            $fail++;
        }

        $cvManage = (string) file_get_contents(APPPATH . 'Views/user/profile/cv_manage.php');
        if (str_contains($cvManage, "qs.delete('rr_sync')")) {
            CLI::write('[PASS] cv_manage cleans rr_sync from URL after return', 'green');
        } else {
            CLI::write('[FAIL] cv_manage missing rr_sync URL cleanup', 'red');
            $fail++;
        }

        CLI::newLine();
        if ($fail > 0) {
            CLI::write('Summary: FAILED (' . $fail . ')', 'red');

            return 1;
        }
        CLI::write('Summary: OK — ทดสอบ HTTP ด้วยเบราว์เซอร์หลังล็อกอิน', 'green');

        return 0;
    }
}
