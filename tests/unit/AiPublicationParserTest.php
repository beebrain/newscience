<?php

use App\Libraries\AiPublicationParser;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AiPublicationParserTest extends CIUnitTestCase
{
    public function testNormalizeFromResearchRecordLikeShape(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray([
            'title'              => 'Sample Article',
            'doi'                => 'https://doi.org/10.1000/xyz',
            'publication_type'   => 'journal',
            'publication_year'   => 2024,
            'source'             => 'Journal of Examples',
        ]);
        $this->assertTrue($r['success']);
        $this->assertSame('10.1000/xyz', $r['publication']['doi']);
        $this->assertSame(2024, $r['publication']['year']);
        $this->assertSame('Journal of Examples', $r['publication']['organization']);
        $this->assertSame('journal', $r['publication']['publication_type']);
    }

    public function testNormalizeRejectsEmptyTitle(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray(['title' => '  ']);
        $this->assertFalse($r['success']);
        $this->assertSame('NO_TITLE', $r['error']);
    }

    public function testNormalizeFromRrAiExtractionShape(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray([
            'source'       => 'ai_extraction',
            'type'         => 'journal',
            'title_th'     => 'ชื่อไทย',
            'title_en'     => 'English title',
            'journalname'  => 'J. Example',
            'year_en'      => 2023,
            'doi'          => '10.1000/test',
        ]);
        $this->assertTrue($r['success']);
        $this->assertSame('ชื่อไทย', $r['publication']['title']);
        $this->assertSame('journal', $r['publication']['publication_type']);
    }
}
