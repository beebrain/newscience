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
        $this->assertSame('https://doi.org/10.1000/test', $r['publication']['url']);
    }

    public function testNormalizeIncludesAuthorsAndLocation(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray([
            'title'    => 'Paper',
            'authors'  => ['A. One', 'B. Two'],
            'location' => 'Bangkok',
            'year'     => 2022,
        ]);
        $this->assertTrue($r['success']);
        $this->assertSame('Bangkok', $r['publication']['location']);
        $this->assertStringContainsString('ผู้แต่ง:', $r['publication']['description']);
    }

    public function testNormalizeAuthorsThEnAndBibliographicDetails(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray([
            'title_th'    => 'ทดสอบ',
            'journalname' => 'J. Test',
            'year_en'     => 2024,
            'month_en'    => '6',
            'authors_th'  => ['ไทย หนึ่ง'],
            'authors_en'  => ['English One'],
            'volume'      => '10',
            'issue'       => '2',
            'pages'       => '1-9',
            'keywords_th' => ['คำ1', 'คำ2'],
        ]);
        $this->assertTrue($r['success']);
        $this->assertSame('2024-06-01', $r['publication']['start_date']);
        $this->assertSame('2024-06-30', $r['publication']['end_date']);
        $desc = (string) $r['publication']['description'];
        $this->assertStringContainsString('ผู้แต่ง:', $desc);
        $this->assertStringContainsString('เล่ม (Volume): 10', $desc);
        $this->assertStringContainsString('คำสำคัญ:', $desc);
    }

    public function testParseN8nRootLevelArray(): void
    {
        $path = dirname(__DIR__) . '/fixtures/cv_ai_n8n_response_array_root.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($decoded);

        $r = AiPublicationParser::parseN8nResponse($decoded);
        $this->assertTrue($r['success']);
        $this->assertSame('บทความจาก array root', $r['publication']['title']);
        $this->assertSame('10.1000/array.root', $r['publication']['doi']);
    }

    public function testParseFromUrlRejectsLocalhost(): void
    {
        $r = AiPublicationParser::parseFromUrl('http://localhost/test.pdf');
        $this->assertFalse($r['success']);
        $this->assertSame('BAD_URL', $r['error']);
    }

    public function testIgnoresAiExtractionAsOrganizationSource(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray([
            'title_th' => 'ชื่อ',
            'source'   => 'ai_extraction',
            'type'     => 'journal',
        ]);
        $this->assertTrue($r['success']);
        $this->assertNull($r['publication']['organization']);
    }
}
