<?php

use App\Libraries\ResearchRecordCvSyncMerge;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ResearchRecordCvSyncMergeTest extends CIUnitTestCase
{
    public function testProtectedDefaultCvSectionIncludesEducationAndResearch(): void
    {
        $this->assertTrue(ResearchRecordCvSyncMerge::isProtectedDefaultCvSection(['type' => 'education']));
        $this->assertTrue(ResearchRecordCvSyncMerge::isProtectedDefaultCvSection(['type' => 'research']));
        $this->assertFalse(ResearchRecordCvSyncMerge::isProtectedDefaultCvSection(['type' => 'work']));
    }

    public function testMergedBundleFillsBlankNewScienceTitlesFromResearchRecord(): void
    {
        $nsBundle = [
            'sections' => [[
                'external_key'       => 'section:education',
                'type'               => 'education',
                'title'              => '',
                'description'        => 'NS description',
                'visible_on_public'  => 1,
                'entries'            => [[
                    'external_key'       => 'entry:degree',
                    'title'              => '',
                    'organization'       => 'NS University',
                    'visible_on_public'  => 1,
                    'metadata'           => [],
                ]],
            ]],
        ];
        $rrBundle = [
            'sections' => [[
                'external_key'       => 'section:education',
                'type'               => 'education',
                'title'              => 'การศึกษา',
                'description'        => 'RR description',
                'visible_on_public'  => 1,
                'entries'            => [[
                    'external_key'       => 'entry:degree',
                    'title'              => 'ปริญญาเอก วิทยาศาสตร์',
                    'organization'       => 'RR University',
                    'visible_on_public'  => 1,
                    'metadata'           => [],
                ]],
            ]],
        ];

        $merged = ResearchRecordCvSyncMerge::mergedCvBundle([], $nsBundle, $rrBundle, 'teacher@example.com');

        $this->assertSame('การศึกษา', $merged['sections'][0]['title']);
        $this->assertSame('ปริญญาเอก วิทยาศาสตร์', $merged['sections'][0]['entries'][0]['title']);
        $this->assertSame('NS description', $merged['sections'][0]['description']);
        $this->assertSame('NS University', $merged['sections'][0]['entries'][0]['organization']);
    }

    public function testMergedBundleKeepsNonBlankNewScienceTitles(): void
    {
        $nsBundle = [
            'sections' => [[
                'external_key' => 'section:research',
                'type'         => 'research',
                'title'        => 'หัวข้อวิจัยในฐานข้อมูลคณะ',
                'entries'      => [[
                    'external_key' => 'entry:research',
                    'title'        => 'ชื่อผลงานในฐานข้อมูลคณะ',
                    'metadata'     => [],
                ]],
            ]],
        ];
        $rrBundle = [
            'sections' => [[
                'external_key' => 'section:research',
                'type'         => 'research',
                'title'        => 'หัวข้อวิจัยจาก กบศ',
                'entries'      => [[
                    'external_key' => 'entry:research',
                    'title'        => 'ชื่อผลงานจาก กบศ',
                    'metadata'     => [],
                ]],
            ]],
        ];

        $merged = ResearchRecordCvSyncMerge::mergedCvBundle([], $nsBundle, $rrBundle, 'teacher@example.com');

        $this->assertSame('หัวข้อวิจัยในฐานข้อมูลคณะ', $merged['sections'][0]['title']);
        $this->assertSame('ชื่อผลงานในฐานข้อมูลคณะ', $merged['sections'][0]['entries'][0]['title']);
    }

    public function testPublicationSectionGroupingPreservesMeaningfulHeadings(): void
    {
        $method = new ReflectionMethod(ResearchRecordCvSyncMerge::class, 'publicationSectionGroupKey');
        $method->setAccessible(true);

        $researchKey = $method->invoke(null, ['type' => 'research', 'title' => 'หัวข้อการวิจัย']);
        $articlesKey = $method->invoke(null, ['type' => 'articles', 'title' => 'หัวข้อการวิจัย']);
        $otherResearchKey = $method->invoke(null, ['type' => 'research', 'title' => 'ผลงานตีพิมพ์']);
        $duplicateResearchKey = $method->invoke(null, ['type' => 'research', 'title' => " หัวข้อการวิจัย\n"]);

        $this->assertNotSame($researchKey, $articlesKey);
        $this->assertNotSame($researchKey, $otherResearchKey);
        $this->assertSame($researchKey, $duplicateResearchKey);
    }

    public function testNormalizePublicationSectionsInBundlePromotesMisplacedTitleToEntry(): void
    {
        $longTitle = 'จุฬารัตน์ รวมจิต ศรัญยู เรือนจันทร์. (2564). ผลการใช้โปรแกรมการสอนทางกายภาพบำบัด';
        $bundle    = [
            'sections' => [[
                'external_key' => 's:pub1',
                'type'         => 'custom',
                'title'        => $longTitle,
                'entries'      => [],
            ]],
        ];

        $out = ResearchRecordCvSyncMerge::normalizePublicationSectionsInBundle($bundle);

        $this->assertCount(1, $out['sections']);
        $this->assertSame('research', $out['sections'][0]['type']);
        $this->assertSame(
            ResearchRecordCvSyncMerge::canonicalPublicationSectionTitle(),
            $out['sections'][0]['title']
        );
        $this->assertCount(1, $out['sections'][0]['entries']);
        $this->assertSame($longTitle, $out['sections'][0]['entries'][0]['title']);
    }

    public function testShouldPromoteSectionTitleToEntryForEmptyResearchSection(): void
    {
        $this->assertTrue(ResearchRecordCvSyncMerge::shouldPromoteSectionTitleToEntry([
            'type'    => 'research',
            'title'   => 'ชื่อผลงานวิจัยที่ยาวมาก',
            'entries' => [],
        ]));
        $this->assertFalse(ResearchRecordCvSyncMerge::shouldPromoteSectionTitleToEntry([
            'type'    => 'research',
            'title'   => ResearchRecordCvSyncMerge::canonicalPublicationSectionTitle(),
            'entries' => [],
        ]));
    }

    public function testNormalizeSectionsInBundleMergesDuplicateEducationSections(): void
    {
        $bundle = [
            'sections' => [
                [
                    'external_key' => 's:edu1',
                    'type'         => 'education',
                    'title'        => 'Education',
                    'sort_order'   => 1,
                    'entries'      => [['external_key' => 'e:1', 'title' => 'ปริญญาเอก', 'metadata' => []]],
                ],
                [
                    'external_key' => 's:edu2',
                    'type'         => 'education',
                    'title'        => 'การศึกษา',
                    'sort_order'   => 2,
                    'entries'      => [['external_key' => 'e:2', 'title' => 'ปริญญาโท', 'metadata' => []]],
                ],
            ],
        ];

        $out = ResearchRecordCvSyncMerge::normalizeSectionsInBundle($bundle);

        $this->assertCount(1, $out['sections']);
        $this->assertSame('education', $out['sections'][0]['type']);
        $this->assertSame('การศึกษา', $out['sections'][0]['title']);
        $this->assertCount(2, $out['sections'][0]['entries']);
    }

    public function testNormalizeSectionsInBundleAddsEducationSectionWhenMissing(): void
    {
        $bundle = [
            'sections' => [[
                'external_key' => 's:work',
                'type'         => 'work',
                'title'        => 'ประสบการณ์การทำงาน',
                'sort_order'   => 5,
                'entries'      => [],
            ]],
        ];

        $out = ResearchRecordCvSyncMerge::normalizeSectionsInBundle($bundle);

        $types = array_map(static fn ($s) => $s['type'] ?? '', $out['sections']);
        $this->assertContains('education', $types);
        $edu = null;
        foreach ($out['sections'] as $sec) {
            if (($sec['type'] ?? '') === 'education') {
                $edu = $sec;
            }
        }
        $this->assertNotNull($edu);
        $this->assertSame('การศึกษา', $edu['title']);
        $this->assertSame([], $edu['entries']);
    }

    public function testIsPublicationCvSectionRecognizesResearchAndPublicationHeadings(): void
    {
        $this->assertTrue(ResearchRecordCvSyncMerge::isPublicationCvSection(['type' => 'research', 'title' => 'งานวิจัยที่ตีพิมพ์']));
        $this->assertTrue(ResearchRecordCvSyncMerge::isPublicationCvSection(['type' => 'articles', 'title' => 'บทความ']));
        $this->assertTrue(ResearchRecordCvSyncMerge::isPublicationCvSection(['type' => 'custom', 'title' => 'ผลงานตีพิมพ์']));
        $this->assertFalse(ResearchRecordCvSyncMerge::isPublicationCvSection(['type' => 'custom', 'title' => 'รางวัล']));
        $this->assertFalse(ResearchRecordCvSyncMerge::isPublicationCvSection(['type' => 'work', 'title' => 'ประสบการณ์']));
    }
}
