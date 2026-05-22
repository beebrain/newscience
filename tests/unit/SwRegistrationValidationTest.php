<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\SciWeek;

/**
 * ทดสอบ catalog config และ logic การ validate/capacity ของระบบรับสมัครวิทยาศาสตร์
 */
class SwRegistrationValidationTest extends CIUnitTestCase
{
    private SciWeek $cfg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cfg = config('SciWeek');
    }

    // ── catalog ──────────────────────────────────────────────────────────────

    public function testCatalogHasFiveCompetitions(): void
    {
        $this->assertCount(5, $this->cfg->competitions);
    }

    public function testAllCompetitionKeysExist(): void
    {
        $expected = ['seed_art', 'rov', 'python', 'recycle', 'sci_drawing'];
        $actual   = array_keys($this->cfg->competitions);
        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual);
    }

    public function testAllCompetitionsHaveTwoLevels(): void
    {
        foreach ($this->cfg->competitions as $key => $comp) {
            $this->assertCount(2, $comp['levels'], "Competition '{$key}' must have exactly 2 levels");
        }
    }

    public function testRequiredMetadataFields(): void
    {
        $required = ['name_th', 'name_en', 'levels', 'team_min', 'team_max',
                     'has_reserve', 'reserve_max', 'per_person',
                     'cap_per_level', 'cap_total', 'cap_per_school',
                     'deadline', 'extra_coaches', 'contact', 'notes'];

        foreach ($this->cfg->competitions as $key => $comp) {
            foreach ($required as $field) {
                $this->assertArrayHasKey($field, $comp, "Competition '{$key}' missing '{$field}'");
            }
        }
    }

    // ── ข้อมูลเฉพาะ per competition ─────────────────────────────────────────

    public function testSeedArtIsTeamOfThree(): void
    {
        $comp = $this->cfg->competitions['seed_art'];
        $this->assertSame(3, $comp['team_min']);
        $this->assertSame(3, $comp['team_max']);
        $this->assertFalse($comp['has_reserve']);
        $this->assertSame(15, $comp['cap_per_level']);
    }

    public function testRovHasFiveMainAndTwoReserve(): void
    {
        $comp = $this->cfg->competitions['rov'];
        $this->assertSame(5, $comp['team_min']);
        $this->assertSame(5, $comp['team_max']);
        $this->assertTrue($comp['has_reserve']);
        $this->assertSame(2, $comp['reserve_max']);
        $this->assertSame(16, $comp['cap_total']);
        $this->assertSame(1, $comp['cap_per_school']);
    }

    public function testRovHasGameIdPerPerson(): void
    {
        $comp = $this->cfg->competitions['rov'];
        $this->assertArrayHasKey('game_id', $comp['per_person']);
        $this->assertTrue($comp['per_person']['game_id']['required']);
    }

    public function testPythonTeamOfTwo(): void
    {
        $comp = $this->cfg->competitions['python'];
        $this->assertSame(2, $comp['team_min']);
        $this->assertSame(2, $comp['team_max']);
        $this->assertSame(2, $comp['cap_per_school']);
    }

    public function testRecycleFlexibleTeamSize(): void
    {
        $comp = $this->cfg->competitions['recycle'];
        $this->assertSame(1, $comp['team_min']);
        $this->assertSame(5, $comp['team_max']);
        $this->assertSame(2, $comp['extra_coaches']);
    }

    public function testSciDrawingIsSolo(): void
    {
        $comp = $this->cfg->competitions['sci_drawing'];
        $this->assertSame(1, $comp['team_min']);
        $this->assertSame(1, $comp['team_max']);
        $this->assertFalse($comp['has_reserve']);
        $this->assertArrayHasKey('age', $comp['per_person']);
        $this->assertSame(2, $comp['extra_coaches']);
    }

    // ── ตรวจ level keys ──────────────────────────────────────────────────────

    public function testSeedArtLevelKeys(): void
    {
        $levels = array_keys($this->cfg->competitions['seed_art']['levels']);
        $this->assertContains('primary', $levels);
        $this->assertContains('lower_secondary', $levels);
    }

    public function testRovLevelKeys(): void
    {
        $levels = array_keys($this->cfg->competitions['rov']['levels']);
        $this->assertContains('primary_lower', $levels);
        $this->assertContains('lower_higher', $levels);
    }

    // ── deadline logic ───────────────────────────────────────────────────────

    public function testDeadlinesAreNullOrValidDate(): void
    {
        foreach ($this->cfg->competitions as $key => $comp) {
            if ($comp['deadline'] === null) {
                continue;
            }
            $d = \DateTime::createFromFormat('Y-m-d', $comp['deadline']);
            $this->assertNotFalse($d, "Competition '{$key}' deadline is not a valid Y-m-d date");
        }
    }

    public function testRovDeadlineIsAugust2026(): void
    {
        $this->assertSame('2026-08-17', $this->cfg->competitions['rov']['deadline']);
    }

    // ── capacity helper logic (unit, no DB) ──────────────────────────────────

    /**
     * จำลอง checkCapacity logic: ถ้า count >= cap ต้องปฏิเสธ
     */
    public function testCapPerLevelRejectsWhenFull(): void
    {
        $comp  = $this->cfg->competitions['seed_art'];
        $cap   = $comp['cap_per_level'];
        $count = 15; // เต็มแล้ว
        $this->assertTrue($count >= $cap, 'Should reject when count >= cap_per_level');
    }

    public function testCapPerLevelAllowsWhenNotFull(): void
    {
        $comp  = $this->cfg->competitions['seed_art'];
        $cap   = $comp['cap_per_level'];
        $count = 14;
        $this->assertFalse($count >= $cap, 'Should allow when count < cap_per_level');
    }

    public function testCapTotalRovRejectsAt16(): void
    {
        $comp  = $this->cfg->competitions['rov'];
        $cap   = $comp['cap_total'];
        $this->assertTrue(16 >= $cap);
        $this->assertFalse(15 >= $cap);
    }

    public function testCapPerSchoolPythonRejectsAt2(): void
    {
        $comp = $this->cfg->competitions['python'];
        $cap  = $comp['cap_per_school'];
        $this->assertTrue(2 >= $cap);
        $this->assertFalse(1 >= $cap);
    }
}
