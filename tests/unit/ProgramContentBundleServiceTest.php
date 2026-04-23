<?php

use App\Services\ProgramContentBundleService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for ProgramContentBundleService (v1.1 — 3 namespace)
 *
 * ครอบ:
 *   - parseBundleJsonString (new format + legacy {program, page})
 *   - validateBasicShape / validateNamespaceShape
 *   - basicToUpdateRow (programs table normalize)
 *   - pageBundleToUpdateRow (program_pages table normalize)
 *   - buildBundleFromDatabase / buildContentSliceFromPage / buildSettingsSliceFromPage
 *   - buildEmptyTemplateBundle / buildSectionPreviews
 *   - Staging file round-trip
 *
 * @internal
 */
final class ProgramContentBundleServiceTest extends CIUnitTestCase
{
    private ProgramContentBundleService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['overview_lists', 'career_cards', 'tuition_fees']);
        $this->svc = new ProgramContentBundleService();
    }

    protected function tearDown(): void
    {
        $dir = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'program_bundle_import';
        if (is_dir($dir)) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . 'p999*.json') ?: [] as $leftover) {
                @unlink($leftover);
            }
        }
        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // parseBundleJsonString — new format (3 namespace)
    // ------------------------------------------------------------------

    public function testParseNewFormatAcceptsMinimalBundle(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 42,
            'basic'          => ['name_th' => 'วิทย์ข้อมูล'],
            'content'        => ['philosophy' => 'ok'],
            'settings'       => ['is_published' => 1],
        ]);

        $r = $this->svc->parseBundleJsonString($json);

        $this->assertSame([], $r['errors']);
        $this->assertSame(42, $r['program_id']);
        $this->assertFalse($r['legacy']);
        $this->assertSame('วิทย์ข้อมูล', $r['basic']['name_th']);
        $this->assertSame('ok', $r['content']['philosophy']);
        $this->assertSame(1, $r['settings']['is_published']);
    }

    public function testParseNewFormatAcceptsMissingOptionalNamespaces(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 1,
            'content'        => ['philosophy' => 'x'],
        ]);

        $r = $this->svc->parseBundleJsonString($json);

        $this->assertSame([], $r['errors']);
        $this->assertSame([], $r['basic']);
        $this->assertSame(['philosophy' => 'x'], $r['content']);
        $this->assertSame([], $r['settings']);
    }

    public function testParseRejectsBundleWithNoNamespaceAndNoPage(): void
    {
        $json = json_encode(['schema_version' => 1, 'program_id' => 1]);
        $r    = $this->svc->parseBundleJsonString($json);

        $this->assertNotEmpty($r['errors']);
        $this->assertTrue((bool) array_filter($r['errors'], static fn ($e) => str_contains($e, 'basic')));
    }

    public function testParseRejectsOversizePayload(): void
    {
        $big = str_repeat('a', 2_300_000);
        $r   = $this->svc->parseBundleJsonString($big);

        $this->assertStringContainsString('ไฟล์ใหญ่เกิน', $r['errors'][0]);
    }

    public function testParseRejectsMalformedJson(): void
    {
        $r = $this->svc->parseBundleJsonString('{not json');
        $this->assertStringContainsString('รูปแบบ JSON', $r['errors'][0]);
    }

    public function testParseRejectsBadSchemaVersion(): void
    {
        $json = json_encode(['schema_version' => 99, 'program_id' => 1, 'content' => []]);
        $r    = $this->svc->parseBundleJsonString($json);
        $this->assertTrue((bool) array_filter($r['errors'], static fn ($e) => str_contains($e, 'schema_version')));
    }

    public function testParseRejectsMissingProgramId(): void
    {
        $json = json_encode(['schema_version' => 1, 'content' => []]);
        $r    = $this->svc->parseBundleJsonString($json);
        $this->assertTrue((bool) array_filter($r['errors'], static fn ($e) => str_contains($e, 'program_id')));
        $this->assertNull($r['program_id']);
    }

    public function testParseSurfacesNamespaceShapeErrors(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 1,
            'basic'          => ['unknown_field' => 1],
            'content'        => ['evil' => 'x'],
            'settings'       => ['theme_color' => 'not-hex'],
        ]);

        $r = $this->svc->parseBundleJsonString($json);

        $haystack = implode(' | ', $r['errors']);
        $this->assertStringContainsString('basic', $haystack);
        $this->assertStringContainsString('unknown_field', $haystack);
        $this->assertStringContainsString('content', $haystack);
        $this->assertStringContainsString('evil', $haystack);
        $this->assertStringContainsString('theme_color', $haystack);
    }

    // ------------------------------------------------------------------
    // parseBundleJsonString — legacy {program, page} backward compat
    // ------------------------------------------------------------------

    public function testParseConvertsLegacyPageFormat(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 7,
            'program'        => ['name_th' => 'legacy name', 'level' => 'bachelor'],
            'page'           => [
                'philosophy'  => 'p',
                'theme_color' => '#112233',
                'is_published' => 1,
            ],
        ]);

        $r = $this->svc->parseBundleJsonString($json);

        $this->assertSame([], $r['errors']);
        $this->assertTrue($r['legacy']);
        $this->assertSame('legacy name', $r['basic']['name_th']);
        $this->assertSame('bachelor', $r['basic']['level']);
        $this->assertSame('p', $r['content']['philosophy']);
        $this->assertSame('#112233', $r['settings']['theme_color']);
        $this->assertSame(1, $r['settings']['is_published']);
    }

    public function testParseLegacyIgnoresReadOnlyProgramKeys(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 7,
            'program'        => ['id' => 7, 'status' => 'active', 'name_th' => 'x'],
            'page'           => ['philosophy' => 'p'],
        ]);

        $r = $this->svc->parseBundleJsonString($json);
        $this->assertArrayNotHasKey('id', $r['basic']);
        $this->assertArrayNotHasKey('status', $r['basic']);
        $this->assertSame('x', $r['basic']['name_th']);
    }

    // ------------------------------------------------------------------
    // validateBasicShape
    // ------------------------------------------------------------------

    public function testValidateBasicShapeAcceptsValidFields(): void
    {
        $errs = $this->svc->validateBasicShape([
            'name_th'  => 'ชื่อ',
            'name_en'  => 'Name',
            'level'    => 'bachelor',
            'credits'  => 120,
            'duration' => 4,
            'website'  => 'https://example.com',
        ]);

        $this->assertSame([], $errs);
    }

    public function testValidateBasicShapeRejectsUnknownKey(): void
    {
        $errs = $this->svc->validateBasicShape(['name_th' => 'x', 'secret' => 1]);
        $this->assertTrue((bool) array_filter($errs, static fn ($e) => str_contains($e, 'secret')));
    }

    public function testValidateBasicShapeRejectsBadLevel(): void
    {
        $errs = $this->svc->validateBasicShape(['level' => 'wizard']);
        $this->assertTrue((bool) array_filter($errs, static fn ($e) => str_contains($e, 'level')));
    }

    public function testValidateBasicShapeRejectsWrongTypes(): void
    {
        $errs = $this->svc->validateBasicShape([
            'name_th' => ['not', 'string'],
            'credits' => 'one-twenty',
        ]);
        $this->assertGreaterThanOrEqual(2, count($errs));
    }

    // ------------------------------------------------------------------
    // basicToUpdateRow
    // ------------------------------------------------------------------

    public function testBasicToUpdateRowClampsLongStrings(): void
    {
        $r = $this->svc->basicToUpdateRow([
            'name_th'     => str_repeat('ก', 600),
            'description' => str_repeat('a', 6000),
        ]);

        $this->assertSame(500, mb_strlen($r['update']['name_th']));
        $this->assertSame(5000, mb_strlen($r['update']['description']));
    }

    public function testBasicToUpdateRowCoercesCredits(): void
    {
        $r = $this->svc->basicToUpdateRow(['credits' => '120', 'duration' => 4]);
        $this->assertSame(120, $r['update']['credits']);
        $this->assertSame(4, $r['update']['duration']);
    }

    public function testBasicToUpdateRowRejectsNegativeCredits(): void
    {
        $r = $this->svc->basicToUpdateRow(['credits' => -5]);
        $this->assertNotEmpty($r['errors']);
        $this->assertSame(0, $r['update']['credits']);
    }

    public function testBasicToUpdateRowRejectsInvalidLevel(): void
    {
        $r = $this->svc->basicToUpdateRow(['level' => 'postdoc']);
        $this->assertNotEmpty($r['errors']);
        $this->assertArrayNotHasKey('level', $r['update']);
    }

    public function testBasicToUpdateRowRejectsInvalidWebsite(): void
    {
        $r = $this->svc->basicToUpdateRow(['website' => 'javascript:alert(1)']);
        $this->assertNotEmpty($r['errors']);
        $this->assertArrayNotHasKey('website', $r['update']);
    }

    public function testBasicToUpdateRowAcceptsEmptyWebsite(): void
    {
        $r = $this->svc->basicToUpdateRow(['website' => '']);
        $this->assertSame([], $r['errors']);
        $this->assertSame('', $r['update']['website']);
    }

    public function testBasicToUpdateRowOnlyAffectsProvidedKeys(): void
    {
        $r = $this->svc->basicToUpdateRow(['name_th' => 'x']);
        $this->assertSame(['name_th' => 'x'], $r['update']);
    }

    // ------------------------------------------------------------------
    // pageBundleToUpdateRow — same as before, still works for content+settings merged
    // ------------------------------------------------------------------

    public function testPageBundleToUpdateRowAcceptsObjectivesArray(): void
    {
        $r = $this->svc->pageBundleToUpdateRow(['objectives' => ['ข้อ 1', 'ข้อ 2']]);

        $this->assertSame([], $r['errors']);
        $this->assertSame(['ข้อ 1', 'ข้อ 2'], json_decode($r['update']['objectives'], true));
    }

    public function testPageBundleToUpdateRowValidatesThemeColor(): void
    {
        $rOk  = $this->svc->pageBundleToUpdateRow(['theme_color' => '#abcdef']);
        $rBad = $this->svc->pageBundleToUpdateRow(['theme_color' => 'red']);

        $this->assertSame('#abcdef', $rOk['update']['theme_color']);
        $this->assertSame('#1e40af', $rBad['update']['theme_color']);
    }

    public function testPageBundleToUpdateRowNormalizesCareersJson(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'careers_json' => [
                ['title' => 'Dev', 'description' => 'd', 'icon' => 'code'],
                ['title' => 'Engr', 'description' => '', 'icon' => 'unknown'],
            ],
        ]);

        $dec = json_decode($r['update']['careers_json'], true);
        $this->assertSame('Dev', $dec[0]['title']);
        $this->assertSame('rocket', $dec[1]['icon']);
    }

    public function testPageBundleToUpdateRowCoercesIsPublished(): void
    {
        $this->assertSame(1, $this->svc->pageBundleToUpdateRow(['is_published' => true])['update']['is_published']);
        $this->assertSame(0, $this->svc->pageBundleToUpdateRow(['is_published' => 0])['update']['is_published']);
    }

    public function testPageBundleToUpdateRowIgnoresUnknownKeys(): void
    {
        $r = $this->svc->pageBundleToUpdateRow(['slug' => 's', 'evil' => 'x']);
        $this->assertArrayHasKey('slug', $r['update']);
        $this->assertArrayNotHasKey('evil', $r['update']);
    }

    // ------------------------------------------------------------------
    // buildBundleFromDatabase / slice builders
    // ------------------------------------------------------------------

    public function testBuildBundleFromDatabaseReturns3NamespaceStructure(): void
    {
        $b = $this->svc->buildBundleFromDatabase(7, null, null);

        $this->assertSame(1, $b['schema_version']);
        $this->assertSame(7, $b['program_id']);
        $this->assertIsString($b['exported_at']);
        $this->assertIsArray($b['basic']);
        $this->assertIsArray($b['content']);
        $this->assertIsArray($b['settings']);
        $this->assertArrayNotHasKey('page', $b, 'new structure must not emit legacy "page" key');
        $this->assertArrayNotHasKey('program', $b, 'new structure must not emit legacy "program" key');
    }

    public function testBuildBasicSliceFromProgramPopulatesAllKeys(): void
    {
        $basic = $this->svc->buildBasicSliceFromProgram([
            'name_th'  => 'ชื่อ',
            'name_en'  => 'Name',
            'level'    => 'bachelor',
            'credits'  => 120,
            'duration' => 4,
        ]);

        $this->assertSame('ชื่อ', $basic['name_th']);
        $this->assertSame(120, $basic['credits']);
        $this->assertSame(4, $basic['duration']);
        $this->assertSame('', $basic['degree_th'], 'missing key produces empty string');
    }

    public function testBuildContentAndSettingsSlicesDoNotOverlap(): void
    {
        $row = [
            'philosophy'   => 'p',
            'slug'         => 's',
            'theme_color'  => '#112233',
            'is_published' => 1,
        ];
        $content  = $this->svc->buildContentSliceFromPage($row);
        $settings = $this->svc->buildSettingsSliceFromPage($row);

        // ไม่ต้องมี key ซ้ำระหว่าง 2 slice
        $overlap = array_intersect_key($content, $settings);
        $this->assertSame([], $overlap, 'content + settings must not share any key');

        $this->assertArrayHasKey('philosophy', $content);
        $this->assertArrayNotHasKey('philosophy', $settings);
        $this->assertArrayHasKey('slug', $settings);
        $this->assertArrayNotHasKey('slug', $content);
        $this->assertArrayHasKey('theme_color', $settings);
        $this->assertArrayHasKey('is_published', $settings);
    }

    public function testBuildBundleRoundTripsThroughImport(): void
    {
        $programRow = [
            'name_th'  => 'วิทย์',
            'name_en'  => 'Science',
            'level'    => 'bachelor',
            'credits'  => 120,
            'duration' => 4,
        ];
        $pageRow = [
            'slug'         => 'bsc-cs',
            'philosophy'   => 'ปรัชญา',
            'objectives'   => json_encode(['เข้าใจ CS', 'เขียนโค้ด']),
            'theme_color'  => '#112233',
            'is_published' => 1,
        ];

        $bundle = $this->svc->buildBundleFromDatabase(11, $programRow, $pageRow);

        $basicBack    = $this->svc->basicToUpdateRow($bundle['basic']);
        $pageBack     = $this->svc->pageBundleToUpdateRow($bundle['content'] + $bundle['settings']);

        $this->assertSame([], $basicBack['errors']);
        $this->assertSame([], $pageBack['errors']);
        $this->assertSame('วิทย์', $basicBack['update']['name_th']);
        $this->assertSame(120, $basicBack['update']['credits']);
        $this->assertSame(['เข้าใจ CS', 'เขียนโค้ด'], json_decode($pageBack['update']['objectives'], true));
        $this->assertSame('#112233', $pageBack['update']['theme_color']);
        $this->assertSame(1, $pageBack['update']['is_published']);
    }

    public function testBuildEmptyTemplateBundleHas3Namespaces(): void
    {
        $t = $this->svc->buildEmptyTemplateBundle(5, null);

        $this->assertSame(1, $t['schema_version']);
        $this->assertSame(5, $t['program_id']);
        $this->assertNull($t['exported_at']);
        $this->assertArrayHasKey('basic', $t);
        $this->assertArrayHasKey('content', $t);
        $this->assertArrayHasKey('settings', $t);
        $this->assertArrayNotHasKey('page', $t);
        $this->assertIsArray($t['content']['learning_standards_json']);
        $this->assertSame('#1e40af', $t['settings']['theme_color']);
    }

    public function testDecodePageRowHandlesMissingOptionalKeys(): void
    {
        $decoded = $this->svc->decodePageRowForBundle([
            'objectives' => json_encode(['a', 'b']),
        ]);

        $this->assertSame(['a', 'b'], $decoded['objectives']);
        $this->assertSame('#1e40af', $decoded['theme_color']);
        $this->assertSame('', $decoded['text_color']);
    }

    // ------------------------------------------------------------------
    // buildSectionPreviews — UI preview should still work on flat page
    // ------------------------------------------------------------------

    public function testBuildSectionPreviewsReturnsSixSections(): void
    {
        $flat = $this->svc->buildContentSliceFromPage([
            'philosophy' => 'p',
            'objectives' => json_encode(['a']),
            'elos_json'  => json_encode([['code' => 'ELO1']]),
        ]) + $this->svc->buildSettingsSliceFromPage(['slug' => 's', 'is_published' => 1]);

        $sections = $this->svc->buildSectionPreviews($flat);
        $ids      = array_column($sections, 'id');
        $this->assertSame(['overview', 'quality', 'curriculum', 'pages', 'alumni', 'publish'], $ids);
    }

    // ------------------------------------------------------------------
    // Edge cases (C1–C7) — คุมกฎ non-duplication, partial update, clamping
    // ------------------------------------------------------------------

    /**
     * C1 — Legacy file ที่มี theme_color ใน page ต้องถูก map ไปที่ settings (ไม่ใช่ content)
     */
    public function testC1LegacyThemeColorMapsToSettings(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 3,
            'program'        => ['name_th' => 'legacy'],
            'page'           => [
                'philosophy'  => 'p',
                'theme_color' => '#abcdef',
                'hero_image'  => 'hero.jpg',
                'is_published' => 1,
            ],
        ]);
        $r = $this->svc->parseBundleJsonString($json);

        $this->assertSame([], $r['errors']);
        $this->assertTrue($r['legacy']);
        $this->assertArrayHasKey('theme_color', $r['settings']);
        $this->assertArrayHasKey('hero_image', $r['settings']);
        $this->assertArrayHasKey('is_published', $r['settings']);
        $this->assertArrayHasKey('philosophy', $r['content']);
        $this->assertArrayNotHasKey('theme_color', $r['content'], 'theme_color must NOT leak to content');
        $this->assertArrayNotHasKey('philosophy', $r['settings']);
    }

    /**
     * C2 — Roundtrip ทั้ง export→parse ไม่เกิด duplicate key ระหว่าง content กับ settings
     */
    public function testC2RoundtripPreservesNoDuplicatesAcrossNamespaces(): void
    {
        $pageRow = [
            'philosophy'   => 'p',
            'slug'         => 's',
            'theme_color'  => '#112233',
            'is_published' => 1,
            'objectives'   => json_encode(['a', 'b']),
        ];
        $bundle = $this->svc->buildBundleFromDatabase(5, ['name_th' => 'x'], $pageRow);

        $overlap = array_intersect_key($bundle['content'], $bundle['settings']);
        $this->assertSame([], $overlap, 'content/settings must remain disjoint after build');

        $json = json_encode($bundle);
        $r    = $this->svc->parseBundleJsonString($json);
        $overlapParsed = array_intersect_key($r['content'], $r['settings']);
        $this->assertSame([], $overlapParsed, 'content/settings must remain disjoint after parse');
    }

    /**
     * C3 — Bundle ที่มีแค่ basic (ไม่มี content/settings) → normalize basic เท่านั้น, pageConv ว่าง
     */
    public function testC3BasicOnlyBundleProducesBasicUpdateOnly(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 7,
            'basic'          => ['name_th' => 'เฉพาะ basic', 'credits' => 130],
        ]);
        $r = $this->svc->parseBundleJsonString($json);

        $this->assertSame([], $r['errors']);
        $this->assertSame([], $r['content']);
        $this->assertSame([], $r['settings']);

        $bc = $this->svc->basicToUpdateRow($r['basic']);
        $pc = $this->svc->pageBundleToUpdateRow($r['content'] + $r['settings']);
        $this->assertSame(['name_th' => 'เฉพาะ basic', 'credits' => 130], $bc['update']);
        $this->assertSame([], $pc['update']);
    }

    /**
     * C4 — credits ติดลบ / ตัวเลขล้น → reject (และ clamp เป็น 0)
     */
    public function testC4BasicCreditsRejectsNegative(): void
    {
        $r = $this->svc->basicToUpdateRow(['credits' => -1, 'duration' => -5]);
        $this->assertNotEmpty($r['errors']);
        $this->assertSame(0, $r['update']['credits']);
        $this->assertSame(0, $r['update']['duration']);
    }

    /**
     * C5 — website ที่ไม่ใช่ http(s) (เช่น javascript:) → reject + ไม่ขึ้นใน update
     */
    public function testC5BasicWebsiteRejectsUnsafeScheme(): void
    {
        $cases = ['javascript:alert(1)', 'data:text/html,x', 'ftp://x.y', 'not-a-url'];
        foreach ($cases as $u) {
            $r = $this->svc->basicToUpdateRow(['website' => $u]);
            $this->assertNotEmpty($r['errors'], 'should reject: ' . $u);
            $this->assertArrayNotHasKey('website', $r['update'], 'update ต้องไม่มี website ที่ไม่ผ่าน: ' . $u);
        }
    }

    /**
     * C6 — validate shape จับได้ทั้ง bad value ใน basic + บาง namespace → errors[] รวม
     */
    public function testC6MultiNamespaceErrorsAreAggregated(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 1,
            'basic'          => ['level' => 'not-a-level', 'credits' => 'abc'],
            'content'        => ['philosophy' => ['should-be-string']],
            'settings'       => ['theme_color' => 'red'],
        ]);
        $r = $this->svc->parseBundleJsonString($json);

        $haystack = implode(' | ', $r['errors']);
        $this->assertStringContainsString('basic.level', $haystack);
        $this->assertStringContainsString('content.philosophy', $haystack);
        $this->assertStringContainsString('settings.theme_color', $haystack);
    }

    /**
     * C7 — Staging payload รูปแบบเก่า (flat update — ก่อน refactor) ยังต้องอ่านได้ผ่าน fallback
     */
    public function testC7LegacyStagingPayloadRemainsReadable(): void
    {
        $pid = 999;
        $token = $this->svc->writeStagingFile($pid, [
            'philosophy'   => 'ok',
            'theme_color'  => '#112233',
            'is_published' => 1,
        ]);

        $r = $this->svc->readStagingFile($pid, $token);
        $this->assertIsArray($r);
        // payload flat (ไม่ใช่ {basic_update, page_update}) — controller fallback จะหยิบ 'update' ทั้งก้อนเป็น page_update
        $this->assertArrayHasKey('update', $r);
        $this->assertSame('ok', $r['update']['philosophy']);
        $this->assertArrayNotHasKey('basic_update', $r['update'] ?? []);

        $this->svc->deleteStagingFile($pid, $token);
    }

    /**
     * Additional: Unicode Thai roundtrip ไม่เสีย encoding
     */
    public function testThaiTextRoundtripPreservesEncoding(): void
    {
        $pageRow = ['philosophy' => 'ปรัชญาหลักสูตร — สัมมา🎓สัมโพธิ'];
        $b = $this->svc->buildBundleFromDatabase(1, ['name_th' => 'ชื่อ'], $pageRow);
        $json = json_encode($b, JSON_UNESCAPED_UNICODE);
        $back = $this->svc->parseBundleJsonString($json);

        $this->assertSame([], $back['errors']);
        $this->assertSame('ชื่อ', $back['basic']['name_th']);
        $this->assertSame('ปรัชญาหลักสูตร — สัมมา🎓สัมโพธิ', $back['content']['philosophy']);
    }

    // ------------------------------------------------------------------
    // Schema file
    // ------------------------------------------------------------------

    public function testSchemaFileHas3NamespaceStructure(): void
    {
        $path = ProgramContentBundleService::SCHEMA_PATH;
        $this->assertFileExists($path);
        $decoded = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('basic', $decoded['properties']);
        $this->assertArrayHasKey('content', $decoded['properties']);
        $this->assertArrayHasKey('settings', $decoded['properties']);
        $this->assertArrayNotHasKey('page', $decoded['properties']);
    }

    // ------------------------------------------------------------------
    // Staging file — unchanged behavior
    // ------------------------------------------------------------------

    public function testStagingFileRoundTrip(): void
    {
        $pid   = 999;
        $data  = ['philosophy' => 'ok'];
        $token = $this->svc->writeStagingFile($pid, $data);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $token);

        $read = $this->svc->readStagingFile($pid, $token);
        $this->assertSame($data, $read['update']);

        $this->svc->deleteStagingFile($pid, $token);
        $this->assertNull($this->svc->readStagingFile($pid, $token));
    }

    public function testReadStagingRejectsMalformedTokenOrMismatchedProgram(): void
    {
        $pid   = 999;
        $token = $this->svc->writeStagingFile($pid, ['x' => 1]);

        $this->assertNull($this->svc->readStagingFile(1, 'bad-token'));
        $this->assertNull($this->svc->readStagingFile(998, $token));

        $this->svc->deleteStagingFile($pid, $token);
    }

    public function testStagingFileExpiresCleanly(): void
    {
        $pid   = 999;
        $token = $this->svc->writeStagingFile($pid, ['x' => 1]);
        $path  = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'program_bundle_import' . DIRECTORY_SEPARATOR . 'p' . $pid . '_' . $token . '.json';
        file_put_contents($path, json_encode([
            'program_id' => $pid,
            'created'    => time() - 3600,
            'expires'    => time() - 60,
            'update'     => ['x' => 1],
        ]));

        $this->assertNull($this->svc->readStagingFile($pid, $token));
        $this->assertFileDoesNotExist($path);
    }
}
