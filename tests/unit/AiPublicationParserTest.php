<?php

use App\Libraries\AiPublicationParser;
use App\Libraries\PublicationResearchFields;
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
        $this->assertSame([
            ['name' => 'A. One', 'email' => null, 'affiliation' => null, 'corresponding' => 0, 'order' => 1],
            ['name' => 'B. Two', 'email' => null, 'affiliation' => null, 'corresponding' => 0, 'order' => 2],
        ], $r['publication']['authors']);
        $this->assertNull($r['publication']['description']);
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
        $this->assertSame('ไทย หนึ่ง (English One)', $r['publication']['authors'][0]['name']);
        $this->assertNull($r['publication']['description']);
        $this->assertSame('10', $r['publication']['volume']);
        $this->assertSame('1-9', $r['publication']['pages']);
        $this->assertSame('คำ1, คำ2', $r['publication']['keywords']);
    }

    public function testDedupeContributorsByEmailAndName(): void
    {
        $rows = PublicationResearchFields::dedupeContributors([
            ['name' => 'พิศิษฐ นาคใจ', 'email' => 'pisit.nak@live.uru.ac.th', 'corresponding' => 0, 'order' => 1],
            ['name' => 'Pisit Nakjai', 'email' => 'pisit.nak@live.uru.ac.th', 'corresponding' => 0, 'order' => 2],
            ['name' => 'Tatpong Katanyukul', 'email' => null, 'corresponding' => 0, 'order' => 3],
            ['name' => 'Tatpong Katanyukul', 'email' => null, 'corresponding' => 0, 'order' => 4],
        ]);

        $this->assertCount(2, $rows);
        $this->assertSame('pisit.nak@live.uru.ac.th', $rows[0]['email']);
        $this->assertSame('Tatpong Katanyukul', $rows[1]['name']);
    }

    public function testNormalizeSkipsStringAuthorsWhenThEnPresent(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray([
            'title'      => 'Dup test',
            'authors_th' => ['สมชาย ใจดี'],
            'authors_en' => ['Somchai Jaidee'],
            'authors'    => ['สมชาย ใจดี', 'Somchai Jaidee'],
        ]);

        $this->assertTrue($r['success']);
        $this->assertCount(1, $r['publication']['authors']);
        $this->assertSame('สมชาย ใจดี (Somchai Jaidee)', $r['publication']['authors'][0]['name']);
    }

    public function testNormalizeStructuredAuthorsWithEmail(): void
    {
        $r = AiPublicationParser::normalizePublicationFromRrLikeArray([
            'title'   => 'Author Email Paper',
            'authors' => [[
                'name'        => 'Teacher One',
                'email'       => ' Teacher.One@Live.URU.ac.th ',
                'affiliation' => 'URU',
            ]],
            'abstract' => 'Abstract only',
        ]);

        $this->assertTrue($r['success']);
        $this->assertSame('teacher.one@live.uru.ac.th', $r['publication']['authors'][0]['email']);
        $this->assertSame('Abstract only', $r['publication']['abstract']);
        $this->assertNull($r['publication']['description']);
    }

    public function testNormalizeHeliyonShapePutsAbstractEnInAbstractField(): void
    {
        $sample = [
            'source'       => 'ai_extraction',
            'type'         => 'journal',
            'journalname'  => 'Heliyon',
            'title_en'     => 'Recognition awareness: adding awareness to pattern recognition using latent cognizance',
            'title_th'     => '',
            'authors_en'   => ['Tatpong Katanyukul', 'Pisit Nakjai'],
            'abstract_en'  => 'This study investigates an application of a new probabilistic interpretation of a softmax output to Open-Set Recognition (OSR).',
            'keywords_en'  => ['Artificial neural network', 'Open-set recognition'],
            'volume'       => '8',
            'issue'        => '4',
            'pages'        => 'e09240',
            'year_en'      => '2022',
            'year_th'      => 2565,
            'month_en'     => 'April',
            'doi'          => '10.1016/j.heliyon.2022.e09240',
            'url'          => 'https://doi.org/10.1016/j.heliyon.2022.e09240',
        ];

        $r = AiPublicationParser::normalizePublicationFromRrLikeArray($sample);
        $this->assertTrue($r['success']);
        $pub = $r['publication'];
        $this->assertStringContainsString('softmax', (string) $pub['abstract']);
        $this->assertStringNotContainsString('เล่ม (Volume)', (string) $pub['abstract']);
        $this->assertSame('8', $pub['volume']);
        $this->assertSame('e09240', $pub['pages']);
        $this->assertStringContainsString('Open-set recognition', (string) $pub['keywords']);
        $this->assertSame('Heliyon', $pub['organization']);
        $this->assertSame(4, $pub['month']);
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
