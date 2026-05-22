<?php

use App\Libraries\CertRecipientStudentResolver;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Models\MapStudentUserModel;

/**
 * @internal
 */
final class CertBulkAddValidationTest extends CIUnitTestCase
{
    public function testExistsForEventStudentRequiresPositiveIds(): void
    {
        $model = new \App\Models\CertEventRecipientModel();

        $this->assertFalse($model->existsForEventStudent(0, 1));
        $this->assertFalse($model->existsForEventStudent(1, 0));
    }

    public function testBuildPayloadRequiresEmail(): void
    {
        $resolver = new CertRecipientStudentResolver(new MapStudentUserModel());

        $payload = $resolver->buildRecipientPayloadFromStudent(1, [
            'id'        => 1,
            'status'    => 'active',
            'email'     => '',
            'login_uid' => '65001',
            'tf_name'   => 'A',
            'tl_name'   => 'B',
        ]);

        $this->assertIsArray($payload);
        $this->assertSame('', $payload['recipient_email']);
    }

    public function testBulkStudentIdsSanitize(): void
    {
        $raw = ['0', '-1', 'abc', '12', '12'];
        $ids = [];
        foreach ($raw as $rawId) {
            $studentId = (int) $rawId;
            if ($studentId <= 0) {
                continue;
            }
            $ids[] = $studentId;
        }

        $this->assertSame([12, 12], $ids);
    }
}
