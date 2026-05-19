<?php

use App\Libraries\AiPublicationParser;
use App\Libraries\CvAiFileStorage;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CvAiWorkflowTest extends CIUnitTestCase
{
    public function testStoredNameValidation(): void
    {
        $this->assertTrue(CvAiFileStorage::isValidStoredName('a1b2c3d4e5f6789012345678901234ab.pdf'));
        $this->assertFalse(CvAiFileStorage::isValidStoredName('../evil.pdf'));
        $this->assertFalse(CvAiFileStorage::isValidStoredName('short.pdf'));
    }

    public function testNormalizeN8nFixture(): void
    {
        $path = dirname(__DIR__) . '/fixtures/cv_ai_n8n_response_sample.json';
        $this->assertFileExists($path);
        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($decoded);

        $r = AiPublicationParser::normalizePublicationFromRrLikeArray($decoded['output']);
        $this->assertTrue($r['success']);
        $this->assertSame('ตัวอย่างบทความทดสอบ workflow', $r['publication']['title']);
        $this->assertSame('10.1000/workflow.test', $r['publication']['doi']);
        $this->assertSame('journal', $r['publication']['publication_type']);
    }
}
