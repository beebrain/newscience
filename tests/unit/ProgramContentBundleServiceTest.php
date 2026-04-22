<?php

use App\Services\ProgramContentBundleService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for ProgramContentBundleService
 *
 * ครอบ parseBundleJsonString, pageBundleToUpdateRow, buildBundleFromDatabase,
 * buildEmptyTemplateBundle, decodePageRowForBundle, staging file round-trip
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
    // parseBundleJsonString
    // ------------------------------------------------------------------

    public function testParseBundleJsonStringAcceptsValidMinimalBundle(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 42,
            'page'           => ['philosophy' => 'ok'],
        ]);

        $r = $this->svc->parseBundleJsonString($json);

        $this->assertSame([], $r['errors']);
        $this->assertSame(42, $r['program_id']);
        $this->assertIsArray($r['page']);
        $this->assertSame('ok', $r['page']['philosophy']);
    }

    public function testParseBundleJsonStringRejectsOversizePayload(): void
    {
        $big = str_repeat('a', 2_300_000);
        $r   = $this->svc->parseBundleJsonString($big);

        $this->assertNotEmpty($r['errors']);
        $this->assertStringContainsString('ไฟล์ใหญ่เกิน', $r['errors'][0]);
        $this->assertNull($r['program_id']);
        $this->assertNull($r['page']);
    }

    public function testParseBundleJsonStringRejectsEmptyString(): void
    {
        $r = $this->svc->parseBundleJsonString('   ');

        $this->assertSame(['ไฟล์ว่าง'], $r['errors']);
    }

    public function testParseBundleJsonStringRejectsMalformedJson(): void
    {
        $r = $this->svc->parseBundleJsonString('{not json');

        $this->assertNotEmpty($r['errors']);
        $this->assertStringContainsString('รูปแบบ JSON ไม่ถูกต้อง', $r['errors'][0]);
    }

    public function testParseBundleJsonStringRejectsUnsupportedSchemaVersion(): void
    {
        $json = json_encode(['schema_version' => 99, 'program_id' => 1, 'page' => []]);
        $r    = $this->svc->parseBundleJsonString($json);

        $this->assertTrue(
            (bool) array_filter($r['errors'], static fn ($e) => str_contains($e, 'schema_version')),
            'expected schema_version error: ' . implode(' | ', $r['errors']),
        );
    }

    public function testParseBundleJsonStringRejectsMissingProgramId(): void
    {
        $json = json_encode(['schema_version' => 1, 'page' => []]);
        $r    = $this->svc->parseBundleJsonString($json);

        $this->assertTrue(
            (bool) array_filter($r['errors'], static fn ($e) => str_contains($e, 'program_id')),
        );
        $this->assertNull($r['program_id']);
    }

    public function testParseBundleJsonStringRejectsNonObjectPage(): void
    {
        $json = json_encode(['schema_version' => 1, 'program_id' => 1, 'page' => 'not an object']);
        $r    = $this->svc->parseBundleJsonString($json);

        $this->assertTrue(
            (bool) array_filter($r['errors'], static fn ($e) => str_contains($e, 'page')),
        );
        $this->assertNull($r['page']);
    }

    // ------------------------------------------------------------------
    // validatePageShape (inline JSON Schema v1 check)
    // ------------------------------------------------------------------

    public function testValidatePageShapeAcceptsWhitelistedKeys(): void
    {
        $errs = $this->svc->validatePageShape([
            'slug'         => 's',
            'philosophy'   => 'p',
            'objectives'   => ['a', 'b'],
            'is_published' => 1,
            'theme_color'  => '#1e40af',
            'text_color'   => null,
            'elos_json'    => [],
        ]);

        $this->assertSame([], $errs);
    }

    public function testValidatePageShapeRejectsUnknownKey(): void
    {
        $errs = $this->svc->validatePageShape(['slug' => 's', 'evil_field' => 1]);

        $this->assertNotEmpty($errs);
        $this->assertTrue((bool) array_filter($errs, static fn ($e) => str_contains($e, 'evil_field')));
    }

    public function testValidatePageShapeRejectsWrongTypes(): void
    {
        $errs = $this->svc->validatePageShape([
            'philosophy'   => ['not', 'string'],
            'is_published' => 'yes',
            'objectives'   => 123,
        ]);

        $this->assertCount(3, $errs);
    }

    public function testValidatePageShapeRejectsNonHexColor(): void
    {
        $errs = $this->svc->validatePageShape(['theme_color' => 'red']);

        $this->assertNotEmpty($errs);
        $this->assertTrue((bool) array_filter($errs, static fn ($e) => str_contains($e, 'theme_color')));
    }

    public function testParseBundleJsonStringSurfacesPageShapeErrors(): void
    {
        $json = json_encode([
            'schema_version' => 1,
            'program_id'     => 1,
            'page'           => ['bogus_key' => 'x', 'philosophy' => 42],
        ]);

        $r = $this->svc->parseBundleJsonString($json);

        $this->assertNotEmpty($r['errors']);
        $haystack = implode(' | ', $r['errors']);
        $this->assertStringContainsString('bogus_key', $haystack);
        $this->assertStringContainsString('philosophy', $haystack);
    }

    public function testSchemaFileExistsAndIsValidJson(): void
    {
        $path = ProgramContentBundleService::SCHEMA_PATH;
        $this->assertFileExists($path);
        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($decoded);
        $this->assertSame('Program Content Bundle v1', $decoded['title']);
        $this->assertArrayHasKey('page', $decoded['properties']);
    }

    // ------------------------------------------------------------------
    // pageBundleToUpdateRow
    // ------------------------------------------------------------------

    public function testPageBundleToUpdateRowAcceptsObjectivesArray(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'objectives' => ['ข้อ 1', 'ข้อ 2', 'ข้อ 3'],
        ]);

        $this->assertSame([], $r['errors']);
        $dec = json_decode($r['update']['objectives'], true);
        $this->assertSame(['ข้อ 1', 'ข้อ 2', 'ข้อ 3'], $dec);
    }

    public function testPageBundleToUpdateRowAcceptsObjectivesMultilineString(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'objectives' => "ข้อ 1\nข้อ 2\n\nข้อ 3",
        ]);

        $this->assertSame([], $r['errors']);
        $dec = json_decode($r['update']['objectives'], true);
        $this->assertSame(['ข้อ 1', 'ข้อ 2', 'ข้อ 3'], $dec);
    }

    public function testPageBundleToUpdateRowRejectsInvalidObjectivesShape(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'objectives' => 123.45,
        ]);

        $this->assertNotEmpty($r['errors']);
        $this->assertStringContainsString('objectives', $r['errors'][0]);
    }

    public function testPageBundleToUpdateRowTrimsObjectivesToFortyLines(): void
    {
        $long = array_map(static fn ($i) => "ข้อ {$i}", range(1, 50));
        $r    = $this->svc->pageBundleToUpdateRow(['objectives' => $long]);

        $dec = json_decode($r['update']['objectives'], true);
        $this->assertCount(40, $dec);
        $this->assertNotEmpty($r['errors']);
    }

    public function testPageBundleToUpdateRowValidatesThemeColor(): void
    {
        $rOk  = $this->svc->pageBundleToUpdateRow(['theme_color' => '#abcdef']);
        $rBad = $this->svc->pageBundleToUpdateRow(['theme_color' => 'red']);

        $this->assertSame('#abcdef', $rOk['update']['theme_color']);
        $this->assertSame('#1e40af', $rBad['update']['theme_color'], 'invalid theme_color falls back to default');
    }

    public function testPageBundleToUpdateRowValidatesTextColor(): void
    {
        $rEmpty = $this->svc->pageBundleToUpdateRow(['text_color' => '']);
        $rOk    = $this->svc->pageBundleToUpdateRow(['text_color' => '#112233']);
        $rBad   = $this->svc->pageBundleToUpdateRow(['text_color' => 'zzz']);

        $this->assertNull($rEmpty['update']['text_color']);
        $this->assertSame('#112233', $rOk['update']['text_color']);
        $this->assertNotEmpty($rBad['errors']);
    }

    public function testPageBundleToUpdateRowNormalizesCareersJsonFromArray(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'careers_json' => [
                ['title' => 'Data Scientist', 'description' => 'วิเคราะห์ข้อมูล', 'icon' => 'chart'],
                ['title' => '', 'description' => '', 'icon' => 'rocket'], // should be filtered
                ['title' => 'Engineer', 'description' => '', 'icon' => 'unknown-icon'], // icon fallback
            ],
        ]);

        $this->assertSame([], $r['errors']);
        $dec = json_decode($r['update']['careers_json'], true);
        $this->assertCount(2, $dec);
        $this->assertSame('Data Scientist', $dec[0]['title']);
        $this->assertSame('chart', $dec[0]['icon']);
        $this->assertSame('rocket', $dec[1]['icon'], 'unknown icon falls back to rocket');
    }

    public function testPageBundleToUpdateRowNormalizesTuitionFeesJsonFromArray(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'tuition_fees_json' => [
                ['label' => 'ค่าหน่วยกิต', 'amount' => '1,500', 'note' => 'ต่อหน่วย'],
                ['label' => '', 'amount' => '', 'note' => ''], // filtered
            ],
        ]);

        $this->assertSame([], $r['errors']);
        $dec = json_decode($r['update']['tuition_fees_json'], true);
        $this->assertCount(1, $dec);
        $this->assertSame('ค่าหน่วยกิต', $dec[0]['label']);
    }

    public function testPageBundleToUpdateRowEncodesStructuredJsonField(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'elos_json' => [
                ['code' => 'ELO1', 'desc' => 'เข้าใจพื้นฐาน'],
            ],
        ]);

        $this->assertSame([], $r['errors']);
        $dec = json_decode($r['update']['elos_json'], true);
        $this->assertSame('ELO1', $dec[0]['code']);
    }

    public function testPageBundleToUpdateRowClampsLongStringsToMaxLength(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'philosophy' => str_repeat('a', 6000),
        ]);

        $this->assertSame(5000, mb_strlen($r['update']['philosophy']));
    }

    public function testPageBundleToUpdateRowCoercesIsPublishedToInt(): void
    {
        $rTrue  = $this->svc->pageBundleToUpdateRow(['is_published' => true]);
        $rFalse = $this->svc->pageBundleToUpdateRow(['is_published' => 0]);
        $rStr   = $this->svc->pageBundleToUpdateRow(['is_published' => '1']);

        $this->assertSame(1, $rTrue['update']['is_published']);
        $this->assertSame(0, $rFalse['update']['is_published']);
        $this->assertSame(1, $rStr['update']['is_published']);
    }

    public function testPageBundleToUpdateRowOnlyAffectsProvidedKeys(): void
    {
        $r = $this->svc->pageBundleToUpdateRow(['slug' => 'my-slug']);

        $this->assertSame(['slug' => 'my-slug'], $r['update']);
    }

    public function testPageBundleToUpdateRowIgnoresUnknownKeys(): void
    {
        $r = $this->svc->pageBundleToUpdateRow([
            'slug'                   => 's',
            'not_a_real_field'       => 'x',
            '__proto__'              => 'x',
        ]);

        $this->assertArrayHasKey('slug', $r['update']);
        $this->assertArrayNotHasKey('not_a_real_field', $r['update']);
        $this->assertArrayNotHasKey('__proto__', $r['update']);
    }

    // ------------------------------------------------------------------
    // buildBundleFromDatabase / decodePageRowForBundle / template
    // ------------------------------------------------------------------

    public function testBuildBundleFromDatabaseWithEmptyRowsHasCorrectShape(): void
    {
        $b = $this->svc->buildBundleFromDatabase(7, null, null);

        $this->assertSame(1, $b['schema_version']);
        $this->assertSame(7, $b['program_id']);
        $this->assertIsString($b['exported_at']);
        $this->assertSame(['id' => 7], $b['program']);
        $this->assertIsArray($b['page']);
        $this->assertSame([], $b['page']['objectives']);
        $this->assertSame([], $b['page']['graduate_profile']);
        $this->assertSame('#1e40af', $b['page']['theme_color']);
        $this->assertSame(0, $b['page']['is_published']);
    }

    public function testBuildBundleFromDatabaseIncludesProgramSliceFields(): void
    {
        $program = [
            'id'      => 3,
            'name_th' => 'วิทยาศาสตร์',
            'name_en' => 'Science',
            'level'   => 'bachelor',
            'status'  => 'active',
        ];
        $b = $this->svc->buildBundleFromDatabase(3, $program, null);

        $this->assertSame('วิทยาศาสตร์', $b['program']['name_th']);
        $this->assertSame('Science', $b['program']['name_en']);
        $this->assertSame('bachelor', $b['program']['level']);
    }

    public function testDecodePageRowForBundleHandlesObjectivesAsJsonString(): void
    {
        $decoded = $this->svc->decodePageRowForBundle([
            'objectives'       => json_encode(['a', 'b']),
            'graduate_profile' => "line1\nline2",
            'elos_json'        => json_encode([['code' => 'ELO1']]),
            'gallery_images'   => null,
        ]);

        $this->assertSame(['a', 'b'], $decoded['objectives']);
        $this->assertSame(['line1', 'line2'], $decoded['graduate_profile']);
        $this->assertSame('ELO1', $decoded['elos_json'][0]['code']);
        $this->assertNull($decoded['gallery_images']);
    }

    public function testDecodePageRowForBundleFallsBackForNonJsonString(): void
    {
        $decoded = $this->svc->decodePageRowForBundle([
            'elos_json' => 'not-json-text',
        ]);

        $this->assertSame('not-json-text', $decoded['elos_json']);
    }

    public function testBuildBundleFromDatabaseRoundTripsMinimalPage(): void
    {
        // DB-shaped row (as stored): JSON columns are strings
        $row = [
            'slug'                    => 'bsc-cs',
            'philosophy'              => 'ปรัชญาหลักสูตร',
            'objectives'              => json_encode(['เข้าใจ CS', 'เขียนโค้ดได้']),
            'graduate_profile'        => json_encode(['รอบรู้', 'สื่อสาร']),
            'elos_json'               => json_encode([['code' => 'ELO1', 'desc' => 'd']]),
            'learning_standards_json' => json_encode(['intro' => 'i', 'standards' => [], 'mapping' => []]),
            'curriculum_json'         => json_encode([]),
            'alumni_messages_json'    => json_encode([]),
            'careers_json'            => json_encode([['title' => 'Dev', 'description' => 'code', 'icon' => 'code']]),
            'tuition_fees_json'       => json_encode([['label' => 'ค่าหน่วยกิต', 'amount' => '1500', 'note' => '']]),
            'theme_color'             => '#112233',
            'is_published'            => 1,
        ];

        $bundle = $this->svc->buildBundleFromDatabase(11, null, $row);

        // Re-import the page and compare key fields
        $back = $this->svc->pageBundleToUpdateRow($bundle['page']);

        $this->assertSame([], $back['errors']);
        $this->assertSame(['เข้าใจ CS', 'เขียนโค้ดได้'], json_decode($back['update']['objectives'], true));
        $this->assertSame('Dev', json_decode($back['update']['careers_json'], true)[0]['title']);
        $this->assertSame('#112233', $back['update']['theme_color']);
        $this->assertSame(1, $back['update']['is_published']);
    }

    public function testBuildEmptyTemplateBundleHasExpectedShape(): void
    {
        $t = $this->svc->buildEmptyTemplateBundle(5, null);

        $this->assertSame(1, $t['schema_version']);
        $this->assertSame(5, $t['program_id']);
        $this->assertNull($t['exported_at']);
        $this->assertIsString($t['template_note']);
        $this->assertIsArray($t['page']['learning_standards_json']);
        $this->assertArrayHasKey('intro', $t['page']['learning_standards_json']);
        $this->assertArrayHasKey('standards', $t['page']['learning_standards_json']);
        $this->assertArrayHasKey('mapping', $t['page']['learning_standards_json']);
    }

    // ------------------------------------------------------------------
    // buildSectionPreviews
    // ------------------------------------------------------------------

    public function testBuildSectionPreviewsReturnsSixSections(): void
    {
        $sections = $this->svc->buildSectionPreviews($this->svc->decodePageRowForBundle([
            'philosophy'  => 'p',
            'objectives'  => json_encode(['a']),
            'elos_json'   => json_encode([['code' => 'ELO1']]),
            'slug'        => 's',
            'is_published' => 1,
        ]));

        $ids = array_column($sections, 'id');
        $this->assertSame(['overview', 'quality', 'curriculum', 'pages', 'alumni', 'publish'], $ids);
        foreach ($sections as $sec) {
            $this->assertArrayHasKey('title', $sec);
            $this->assertArrayHasKey('items', $sec);
        }
    }

    // ------------------------------------------------------------------
    // Staging file (write / read / delete / token format / expiry)
    // ------------------------------------------------------------------

    public function testStagingFileRoundTripReadsBackSameData(): void
    {
        $programId = 999;
        $payload   = ['philosophy' => 'ok', 'is_published' => 1];

        $token = $this->svc->writeStagingFile($programId, $payload);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $token);

        $read = $this->svc->readStagingFile($programId, $token);
        $this->assertIsArray($read);
        $this->assertSame($programId, $read['program_id']);
        $this->assertSame($payload, $read['update']);

        $this->svc->deleteStagingFile($programId, $token);
        $this->assertNull($this->svc->readStagingFile($programId, $token));
    }

    public function testReadStagingFileRejectsMalformedToken(): void
    {
        $this->assertNull($this->svc->readStagingFile(1, 'bad-token'));
        $this->assertNull($this->svc->readStagingFile(1, ''));
        $this->assertNull($this->svc->readStagingFile(1, str_repeat('z', 40)));
    }

    public function testReadStagingFileRejectsMismatchedProgramId(): void
    {
        $programId = 999;
        $token     = $this->svc->writeStagingFile($programId, ['x' => 1]);

        $this->assertNull(
            $this->svc->readStagingFile(998, $token),
            'token for program 999 must not read under program 998',
        );

        $this->svc->deleteStagingFile($programId, $token);
    }

    public function testReadStagingFileRejectsExpiredPayload(): void
    {
        $programId = 999;
        $token     = $this->svc->writeStagingFile($programId, ['x' => 1]);

        // Rewrite file with expired timestamp
        $path = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'program_bundle_import' . DIRECTORY_SEPARATOR . 'p' . $programId . '_' . $token . '.json';
        $this->assertFileExists($path);
        file_put_contents($path, json_encode([
            'program_id' => $programId,
            'created'    => time() - 3600,
            'expires'    => time() - 60,
            'update'     => ['x' => 1],
        ]));

        $this->assertNull($this->svc->readStagingFile($programId, $token));
        $this->assertFileDoesNotExist($path, 'expired staging file must be deleted on read');
    }
}
