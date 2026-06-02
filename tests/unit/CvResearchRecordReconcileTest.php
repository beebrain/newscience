<?php

use App\Libraries\CvResearchRecordReconcile;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CvResearchRecordReconcileTest extends CIUnitTestCase
{
    public function testTriggerUiConstantIsStable(): void
    {
        $this->assertSame('reconcile_all_ui', CvResearchRecordReconcile::TRIGGER_UI);
    }

    public function testRunFullDoesNotReferencePublicationSyncEngine(): void
    {
        $ref = new \ReflectionClass(CvResearchRecordReconcile::class);
        $src = (string) file_get_contents($ref->getFileName());
        $this->assertStringNotContainsString('PublicationSyncEngine', $src);
        $this->assertStringContainsString('pullPublicationsOnly', $src);
    }
}
