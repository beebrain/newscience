<?php

namespace App\Commands;

use App\Libraries\ResearchRecordCvPull;
use App\Libraries\ResearchRecordCvSyncClient;
use App\Models\PersonnelModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Push NS-only publication cv_entries (those without rr_publication_id) into
 * Research Record via the dedup-safe sync API, then write the returned
 * rr_publication_id back into each cv_entry's metadata.
 *
 * Dry-run by default; pass --apply to write to RR and NS.
 */
class MigrateCvEntriesToRr extends BaseCommand
{
    protected $group       = 'Research';
    protected $name        = 'publications:migrate-cventries-rr';
    protected $description  = 'Push NS-only publication cv_entries to Research Record and link back (dedup-safe).';
    protected $usage        = 'publications:migrate-cventries-rr [--apply] [--email=a@b.c] [--exclude=1,2,3]';
    protected $options      = [
        '--apply'   => 'Actually push to RR and write rr_publication_id back (default: dry-run).',
        '--email'   => 'Limit to one personnel canonical email.',
        '--exclude' => 'Comma-separated cv_entry ids to skip.',
    ];

    public function run(array $params)
    {
        $apply      = (bool) CLI::getOption('apply');
        $emailOpt   = strtolower(trim((string) (CLI::getOption('email') ?? '')));
        $excludeOpt = (string) (CLI::getOption('exclude') ?? '');
        $exclude    = array_values(array_filter(array_map('intval', explode(',', $excludeOpt))));

        $db = Database::connect();

        $builder = $db->table('cv_entries e')
            ->select('e.id, e.section_id, e.title, e.organization, e.start_date, e.description, e.metadata, s.personnel_id')
            ->join('cv_sections s', 's.id = e.section_id', 'inner')
            ->where('s.type', 'research')
            ->where("JSON_EXTRACT(e.metadata, '\$.rr_publication_id') IS NULL", null, false);
        if ($exclude !== []) {
            $builder->whereNotIn('e.id', $exclude);
        }
        $rows = $builder->orderBy('s.personnel_id', 'ASC')->orderBy('e.id', 'ASC')->get()->getResultArray();

        $byPerson = [];
        foreach ($rows as $r) {
            $byPerson[(int) $r['personnel_id']][] = $r;
        }

        $personnelModel = new PersonnelModel();
        $totals = ['persons' => 0, 'entries' => 0, 'sent' => 0, 'inserted' => 0, 'updated' => 0, 'linked' => 0, 'skipped_no_email' => 0, 'failed' => 0];

        CLI::write(($apply ? 'APPLY' : 'DRY-RUN') . ' — NS-only publication entries to migrate: ' . count($rows), $apply ? 'red' : 'yellow');

        foreach ($byPerson as $pid => $entries) {
            $person = $personnelModel->find($pid);
            if ($person === null) {
                CLI::error("personnel {$pid} not found — skip " . count($entries));
                continue;
            }
            $email = ResearchRecordCvPull::canonicalEmailForPerson($person);
            $name  = PersonnelModel::resolvePublicDisplayNameTh($person);
            if ($emailOpt !== '' && $email !== $emailOpt) {
                continue;
            }
            if ($email === '') {
                CLI::error("personnel {$pid} has no email — skip " . count($entries));
                $totals['skipped_no_email'] += count($entries);
                continue;
            }

            $payload    = [];
            $keyToEntry = [];
            foreach ($entries as $e) {
                $meta = json_decode((string) $e['metadata'], true) ?: [];
                $doi  = self::normDoi($meta['doi'] ?? '');
                $year = $e['start_date'] ? (int) substr((string) $e['start_date'], 0, 4) : (int) ($meta['publication_year'] ?? 0);
                $type = trim((string) ($meta['rr_publication_type'] ?? '')) ?: self::typeFromWork((string) ($meta['work_type'] ?? ''));
                $key  = trim((string) ($meta['sync_external_key'] ?? ''));
                if ($key === '') {
                    $key = $doi !== '' ? 'h:' . sha1(strtolower($doi)) : 'nsentry:' . (int) $e['id'];
                }
                $keyToEntry[$key] = $e;
                $payload[] = [
                    'external_key'      => $key,
                    'ns_publication_id' => null,
                    'rr_publication_id' => null,
                    'title'             => (string) $e['title'],
                    'publication_year'  => $year ?: null,
                    'publication_type'  => $type ?: 'journal',
                    'source'            => ($e['organization'] !== null && trim((string) $e['organization']) !== '') ? $e['organization'] : null,
                    'doi'               => $doi !== '' ? $doi : null,
                    'sync_origin'       => 'newscience',
                    'content_hash'      => null,
                    'ref_url'           => $meta['url'] ?? null,
                    'contributors'      => [[
                        'email'         => $email,
                        'name'          => $name !== '' ? $name : $email,
                        'order'         => 1,
                        'corresponding' => 1,
                        'affiliation'   => 'มหาวิทยาลัยราชภัฏอุตรดิตถ์',
                    ]],
                ];
            }

            $totals['persons']++;
            $totals['entries'] += count($entries);
            CLI::write(sprintf('person %d <%s> "%s" entries=%d', $pid, $email, $name, count($entries)), 'yellow');

            if (! $apply) {
                foreach ($payload as $p) {
                    CLI::write('  [dry] ' . ($p['doi'] ?: '(no-doi)') . ' | ' . mb_substr((string) $p['title'], 0, 60));
                }
                continue;
            }

            $res = ResearchRecordCvSyncClient::pushPublicationsSyncBundle($email, $payload);
            if (! ($res['success'] ?? false)) {
                CLI::error('  push failed: ' . ($res['message'] ?? $res['error'] ?? 'unknown'));
                $totals['failed'] += count($entries);
                continue;
            }
            $stats = $res['stats'] ?? [];
            $totals['sent']     += count($payload);
            $totals['inserted'] += (int) ($stats['inserted'] ?? 0);
            $totals['updated']  += (int) ($stats['updated'] ?? 0);

            foreach (($res['publications'] ?? []) as $item) {
                $rid = (int) ($item['rr_publication_id'] ?? 0);
                if ($rid <= 0) {
                    continue;
                }
                $entry = $keyToEntry[(string) ($item['external_key'] ?? '')] ?? null;
                if ($entry === null) {
                    $entry = self::matchByDoiOrTitle($keyToEntry, $item);
                }
                if ($entry === null) {
                    continue;
                }
                $em = json_decode((string) $entry['metadata'], true) ?: [];
                $em['rr_publication_id'] = $rid;
                if (! empty($item['external_key'])) {
                    $em['sync_external_key'] = (string) $item['external_key'];
                }
                $db->table('cv_entries')->where('id', (int) $entry['id'])->update([
                    'metadata'   => json_encode($em, JSON_UNESCAPED_UNICODE),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $totals['linked']++;
            }
        }

        CLI::write('DONE ' . json_encode($totals, JSON_UNESCAPED_UNICODE), 'green');
    }

    /** @param array<string,array<string,mixed>> $keyToEntry */
    private static function matchByDoiOrTitle(array $keyToEntry, array $item): ?array
    {
        $itemDoi   = self::normDoi($item['doi'] ?? '');
        $itemTitle = mb_strtolower(trim((string) ($item['title'] ?? '')));
        foreach ($keyToEntry as $entry) {
            $em = json_decode((string) $entry['metadata'], true) ?: [];
            if ($itemDoi !== '' && self::normDoi($em['doi'] ?? '') === $itemDoi) {
                return $entry;
            }
            if ($itemTitle !== '' && mb_strtolower(trim((string) $entry['title'])) === $itemTitle) {
                return $entry;
            }
        }

        return null;
    }

    private static function normDoi($doi): string
    {
        return strtolower(trim(str_replace(['\\', ' '], '', (string) $doi)));
    }

    private static function typeFromWork(string $work): string
    {
        $work = strtolower($work);
        if (str_contains($work, 'conference') || str_contains($work, 'proceeding')) {
            return 'conference';
        }
        if (str_contains($work, 'book') || str_contains($work, 'chapter')) {
            return 'book';
        }

        return 'journal';
    }
}
