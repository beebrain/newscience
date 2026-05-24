<?php

use App\Libraries\CvBundleCanonical;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CvBundleCanonicalTest extends CIUnitTestCase
{
    public function testIsEducationSectionType(): void
    {
        $this->assertTrue(CvBundleCanonical::isEducationSectionType('education'));
        $this->assertTrue(CvBundleCanonical::isEducationSectionType('education_structured'));
        $this->assertFalse(CvBundleCanonical::isEducationSectionType('research'));
    }

    public function testFilterEducationSections(): void
    {
        $sections = [
            ['type' => 'work', 'title' => 'Job'],
            ['type' => 'education', 'title' => 'Degree'],
            ['type' => 'research', 'title' => 'Papers'],
        ];

        $filtered = CvBundleCanonical::filterEducationSections($sections);

        $this->assertCount(1, $filtered);
        $this->assertSame('education', $filtered[0]['type']);
    }
}
