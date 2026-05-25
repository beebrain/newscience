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
}
