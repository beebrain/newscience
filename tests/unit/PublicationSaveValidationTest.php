<?php

use App\Libraries\PublicationResearchFields;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PublicationSaveValidationTest extends CIUnitTestCase
{
    public function testValidateResearchSaveRequiresPublicationTypeAndSource(): void
    {
        $err = PublicationResearchFields::validateResearchSave([
            'publication_type'     => '',
            'organization'         => '',
            'publication_year_be'  => '',
            'start_date'           => '',
        ]);
        $this->assertSame('กรุณาเลือกประเภทผลงานเผยแพร่', $err);

        $ok = PublicationResearchFields::validateResearchSave([
            'publication_type'    => 'journal',
            'organization'        => 'วารสารทดสอบ',
            'publication_year_be' => '2567',
        ]);
        $this->assertNull($ok);
    }

    public function testPublicationPageFieldErrorsMatchSaveRequirements(): void
    {
        $errors = PublicationResearchFields::publicationPageFieldErrors([
            'entry_title'         => '',
            'organization'        => '',
            'publication_type'    => '',
            'publication_year_be' => '',
        ]);
        $fields = array_column($errors, 'field');
        $this->assertContains('entry_title', $fields);
        $this->assertContains('organization', $fields);
        $this->assertContains('publication_type', $fields);
        $this->assertContains('publication_year_be', $fields);

        $ok = PublicationResearchFields::publicationPageFieldErrors([
            'entry_title'         => 'ชื่อทดสอบ',
            'organization'        => 'วารสาร',
            'publication_type'    => 'journal',
            'publication_year_be' => '2567',
        ]);
        $this->assertSame([], $ok);
    }

    public function testPublicationPageRejectsInvalidYearBe(): void
    {
        $errors = PublicationResearchFields::publicationPageFieldErrors([
            'entry_title'         => 'ชื่อ',
            'organization'        => 'แหล่ง',
            'publication_type'    => 'journal',
            'publication_year_be' => '999',
        ]);
        $yearErr = array_values(array_filter($errors, static fn (array $e): bool => $e['field'] === 'publication_year_be'));
        $this->assertNotEmpty($yearErr);
        $this->assertStringContainsString('2400', $yearErr[0]['message']);
    }

    public function testPublicationPageFieldElementIds(): void
    {
        $ids = PublicationResearchFields::publicationPageFieldElementIds();
        $this->assertSame('cv-p-title', $ids['entry_title']);
        $this->assertSame('cv-p-pubtype', $ids['publication_type']);
    }
}
