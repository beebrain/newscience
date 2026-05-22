<?php

use App\Libraries\CertRecipientStudentResolver;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Models\MapStudentUserModel;

/**
 * @internal
 */
final class CertRecipientStudentLinkTest extends CIUnitTestCase
{
    private function resolverWithRows(array $rows): CertRecipientStudentResolver
    {
        $model = new MapStudentUserModel();
        $model->rows = $rows;

        return new CertRecipientStudentResolver($model);
    }

    public function testResolveByExplicitStudentId(): void
    {
        $resolver = $this->resolverWithRows([
            ['id' => 7, 'email' => 'a@test.local', 'login_uid' => '65001', 'status' => 'active'],
        ]);

        $this->assertSame(7, $resolver->resolve(7, null, null));
    }

    public function testResolveByLoginUid(): void
    {
        $resolver = $this->resolverWithRows([
            ['id' => 3, 'email' => 'stu@test.local', 'login_uid' => '65002', 'status' => 'active'],
        ]);

        $this->assertSame(3, $resolver->resolve(null, '', '65002'));
    }

    public function testResolveByEmailNormalized(): void
    {
        $resolver = $this->resolverWithRows([
            ['id'  => 9, 'email' => 'student@test.local', 'login_uid' => '65003', 'status' => 'active'],
        ]);

        $this->assertSame(9, $resolver->resolve(null, '  Student@Test.Local ', null));
    }

    public function testResolveReturnsNullWhenNoMatch(): void
    {
        $resolver = $this->resolverWithRows([]);

        $this->assertNull($resolver->resolve(null, 'nobody@test.local', '99999'));
    }

    public function testBuildPayloadSkipsInactiveStudent(): void
    {
        $resolver = new CertRecipientStudentResolver(new MapStudentUserModel());

        $payload = $resolver->buildRecipientPayloadFromStudent(1, [
            'id'        => 5,
            'status'    => 'inactive',
            'email'     => 'x@test.local',
            'login_uid' => '65004',
            'tf_name'   => 'ทด',
            'tl_name'   => 'สอบ',
        ]);

        $this->assertNull($payload);
    }

    public function testBuildPayloadForActiveStudent(): void
    {
        $resolver = new CertRecipientStudentResolver(new MapStudentUserModel());

        $payload = $resolver->buildRecipientPayloadFromStudent(12, [
            'id'         => 5,
            'status'     => 'active',
            'email'      => 'Active@Test.Local',
            'login_uid'  => '65005',
            'tf_name'    => 'สมชาย',
            'tl_name'    => 'ใจดี',
            'program_id' => 2,
        ]);

        $this->assertIsArray($payload);
        $this->assertSame(12, $payload['event_id']);
        $this->assertSame(5, $payload['student_id']);
        $this->assertSame('สมชาย ใจดี', $payload['recipient_name']);
        $this->assertSame('active@test.local', $payload['recipient_email']);
        $this->assertSame('65005', $payload['recipient_id_no']);
        $this->assertSame('pending', $payload['status']);
    }
}
