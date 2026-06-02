<?php

use App\Libraries\PublicationDisplay;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PublicationDisplayTest extends CIUnitTestCase
{
    public function testEnrichSectionsUsesMetadataWhenFromResearchRecord(): void
    {
        $sections = [
            [
                'type'    => 'research',
                'title'   => 'งานวิจัย',
                'entries' => [
                    [
                        'id'              => 1,
                        'title'           => 'ผลงานทดสอบ',
                        'metadata_array'  => [
                            'source'              => 'research_record',
                            'rr_publication_id'   => 42,
                            'sync_external_key'   => 'pub|test-key',
                            'publication_authors' => [
                                ['name' => 'ทดสอบ ผู้วิจัย', 'email' => 't@uru.ac.th', 'order' => 1],
                            ],
                            'abstract' => 'บทคัดย่อจาก bundle',
                        ],
                    ],
                ],
            ],
        ];

        $out = PublicationDisplay::enrichSections(1, $sections);
        $entry = $out[0]['entries'][0];

        $this->assertStringContainsString('ทดสอบ ผู้วิจัย', (string) ($entry['publication_contributors_display'] ?? ''));
        $this->assertSame('บทคัดย่อจาก bundle', $entry['publication_biblio']['abstract'] ?? null);
    }
}
