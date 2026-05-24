<?php

use App\Libraries\PublicationAuthorSearch;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PublicationAuthorSearchTest extends CIUnitTestCase
{
    public function testFormatResultNormalizesEmailAndUsesProgramAffiliation(): void
    {
        $row = [
            'personnel_id'    => 12,
            'name'            => 'อาจารย์ ทดสอบ',
            'email'           => ' Teacher.Test@Live.URU.ac.th ',
            'program_name_th' => 'สาขาวิทยาการคอมพิวเตอร์',
        ];

        $r = PublicationAuthorSearch::formatResult($row);

        $this->assertSame(12, $r['personnel_id']);
        $this->assertSame('อาจารย์ ทดสอบ', $r['name']);
        $this->assertSame('teacher.test@live.uru.ac.th', $r['email']);
        $this->assertSame('สาขาวิทยาการคอมพิวเตอร์', $r['affiliation']);
    }

    public function testFormatResultFallsBackToUserNameAndDefaultAffiliation(): void
    {
        $row = [
            'personnel_id' => 13,
            'name'         => '',
            'user_tf_name' => 'สมชาย',
            'user_tl_name' => 'ใจดี',
            'user_email'   => 'somchai@example.com',
        ];

        $r = PublicationAuthorSearch::formatResult($row);

        $this->assertSame('สมชาย ใจดี', $r['name']);
        $this->assertSame('somchai@example.com', $r['email']);
        $this->assertSame('มหาวิทยาลัยราชภัฏอุตรดิตถ์', $r['affiliation']);
    }

    public function testFormatResultRejectsMissingPersonnelId(): void
    {
        $this->assertNull(PublicationAuthorSearch::formatResult(['name' => 'No ID']));
    }
}
