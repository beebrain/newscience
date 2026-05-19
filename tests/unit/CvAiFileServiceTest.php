<?php

use App\Libraries\CvAiFileStorage;
use App\Services\CvAiFileService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CvAiFileServiceTest extends CIUnitTestCase
{
    public function testResolveForDownloadFindsUploadedFile(): void
    {
        $name = bin2hex(random_bytes(16)) . '.pdf';
        $dir  = CvAiFileStorage::uploadDir();
        $path = $dir . $name;
        file_put_contents($path, "%PDF-1.4\n%%EOF\n");

        try {
            $svc    = new CvAiFileService();
            $result = $svc->resolveForDownload($name);
            $this->assertIsArray($result);
            $this->assertSame($name, $result['filename']);
            $this->assertFileExists($result['path']);
            $this->assertSame('application/pdf', $result['mime']);
        } finally {
            @unlink($path);
        }
    }

    public function testResolveForDownloadRejectsInvalidName(): void
    {
        $svc = new CvAiFileService();
        $this->assertNull($svc->resolveForDownload('../evil.pdf'));
    }
}
