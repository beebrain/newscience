<?php

use App\Libraries\ResearchRecordCvSyncMerge;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CvPublicationEntryPageTest extends CIUnitTestCase
{
    public function testPublicationEntryViewHasFormAndAiPanel(): void
    {
        $html = view('user/profile/cv_publication_entry', $this->viewData());

        $this->assertIsString($html);
        $this->assertStringContainsString('id="cv-pub-form"', $html);
        $this->assertStringContainsString('id="cv-pub-ai-panel"', $html);
        $this->assertStringContainsString('เพิ่มผลงานตีพิมพ์', $html);
        $this->assertStringContainsString('cv-publication-entry-page.js', $html);
        $this->assertStringContainsString('name="cv_publication_page"', $html);
        $this->assertStringContainsString('id="cv-pub-form-errors"', $html);
        $this->assertStringContainsString('novalidate', $html);
        $this->assertStringContainsString('validPubTypeCodes', $html);
        $this->assertStringContainsString('id="cv-p-authors-list"', $html);
        $this->assertStringContainsString('cv-author-search-dropdown', $html);
        $this->assertStringContainsString('cv-publication-author-search.js', $html);
        $this->assertStringNotContainsString('cv-pub-entry-modal', $html);
    }

    public function testCvManageUsesPublicationPageLinksNotPubModal(): void
    {
        $path = APPPATH . 'Views/user/profile/cv_manage.php';
        $this->assertFileExists($path);
        $src = (string) file_get_contents($path);

        $this->assertStringContainsString('dashboard/profile/cv/publication', $src);
        $this->assertStringContainsString('CV_PUB_PAGE_BASE', $src);
        $this->assertStringNotContainsString('cv-pub-entry-modal', $src);
        $this->assertStringNotContainsString('cv-ai-modal', $src);
    }

    public function testEntryModalsPartialHasNoPublicationModal(): void
    {
        $path = APPPATH . 'Views/user/profile/partials/cv_entry_modals.php';
        $src  = (string) file_get_contents($path);

        $this->assertStringContainsString('cv-entry-modal', $src);
        $this->assertStringContainsString('publication-page-v1', $src);
        $this->assertStringNotContainsString('cv-pub-entry-modal', $src);
    }

    public function testIsPublicationSectionIncludesResearchType(): void
    {
        $this->assertTrue(ResearchRecordCvSyncMerge::isPublicationCvSection([
            'type'  => 'research',
            'title' => 'งานวิจัยที่ตีพิมพ์',
        ]));
    }

    /**
     * @return array<string,mixed>
     */
    private function viewData(): array
    {
        return [
            'page_title'                => 'เพิ่มผลงานตีพิมพ์',
            'person'                    => ['id' => 1],
            'section'                   => ['id' => 660, 'title' => 'งานวิจัยที่ตีพิมพ์', 'type' => 'research'],
            'entry'                     => null,
            'section_id'                => 660,
            'entry_id'                  => 0,
            'is_edit'                   => false,
            'ai_cv_publication_enabled' => true,
            'open_ai_panel'             => true,
            'cv_owner_email'            => 'test@uru.ac.th',
            'cv_owner_name'             => 'ทดสอบ ผู้ใช้',
        ];
    }
}
