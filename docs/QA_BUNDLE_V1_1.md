# QA Test Plan — Program Content Bundle v1.1 (3-namespace)

> **ถึง AI agent ที่รับช่วง:** เอกสารนี้คือ test plan แบบ self-contained สำหรับทดสอบฟีเจอร์ import/export JSON ของหลักสูตรหลัง refactor ใหญ่ล่าสุด. ทำตาม section ตามลำดับ และรายงานผลในรูปแบบที่ระบุท้ายเอกสาร

---

## 0. Context — สิ่งที่เพิ่ง refactor

**Commits ที่เป็นเป้าตรวจ (ล่าสุด 3 ก้อน):**
```
676a69b ui(program-admin): ย้าย bundle panel ไปด้านล่างสุดแท็บ "การตั้งค่าเว็บไซต์"
6d52b59 feat(bundle): import commit ข้าม 2 table ใน transaction + legacy staging fallback
6e925ce refactor(bundle): โครงสร้าง 3 namespace (basic/content/settings) — single source per field
```

**การเปลี่ยนแปลงสำคัญ:**
1. Bundle JSON เปลี่ยนจาก `{program, page}` → `{basic, content, settings}` — field เดียวกันต้องมีใน namespace เดียวเท่านั้น (no duplication)
2. Import commit อัปเดต **2 table** (`programs` + `program_pages`) ใน DB transaction
3. UI panel นำเข้า/ส่งออก JSON ย้ายจากแท็บ "เนื้อหาหลักสูตร" → **ด้านล่างสุดของแท็บ "การตั้งค่าเว็บไซต์"**
4. Legacy compat — ไฟล์ export รูปแบบเก่า `{program, page}` ยังต้อง import ได้ (แปลงอัตโนมัติ)

**ไฟล์ที่แตะ:**
- `app/Services/ProgramContentBundleService.php` — service layer
- `app/Config/program_content_bundle.v1.schema.json` — JSON schema
- `app/Controllers/Admin/ProgramAdmin/Dashboard.php` — HTTP endpoints
- `app/Commands/ProgramImportBundle.php` — CLI command
- `app/Views/admin/programs/edit_content.php` — UI
- `tests/unit/ProgramContentBundleServiceTest.php` — 47 unit tests (ผ่านแล้ว)

---

## 1. Prerequisites

### 1.1 Environment
- **OS:** Windows 11 + XAMPP (Apache + MySQL)
- **PHP:** 8.1.x
- **Shell:** Bash (Git Bash บน Windows)
- **Working directory:** `c:\xampp\htdocs\newScience`
- **Base URL:** `http://localhost/newScience`
- **DB:** MySQL `newscience` (user `root`, ไม่มี password default)

### 1.2 เริ่มต้นก่อนทดสอบ
```bash
# 1. ยืนยัน working tree สะอาด + อยู่ที่ commit ล่าสุด
git status --short    # expect: empty
git log --oneline -3  # expect: 676a69b, 6d52b59, 6e925ce

# 2. Apache/MySQL ต้อง running
curl -sI http://localhost/newScience/ | head -1  # expect: HTTP/1.1 200 or 302

# 3. PHPUnit ต้องผ่านก่อนเริ่ม
./vendor/bin/phpunit --filter ProgramContentBundleServiceTest --no-coverage 2>&1 | tail -5
# expect: OK (45+ tests)
```

หากข้อใดไม่ผ่าน → **หยุด** และรายงานใน section 4 (Reporting)

### 1.3 Credentials (ขอจากผู้ใช้ถ้าจำเป็น)
- Admin login URL: `http://localhost/newScience/admin/login` (หรือ `/login`)
- ต้องมีบัญชี role `admin` หรือ `super_admin` ที่มีสิทธิ์หลักสูตร id=1
- **ถ้าไม่มี credentials** — ทำเฉพาะ test ที่ไม่ต้องล็อกอิน (B-CLI-01..B-CLI-05 ใน section 3)

---

## 2. Test IDs — mapping

| ID | ต้อง browser? | Priority |
|---|---|---|
| B1 | ✅ | High (panel placement) |
| B2 | ✅ (ดาวน์โหลดไฟล์) | High |
| B3 | ✅ | High |
| B4 | ✅ | Medium |
| B5 | ✅ | High (new-format import) |
| B6 | ✅ | **Critical** (legacy compat — ถ้าพังจะเสียไฟล์ผู้ใช้เก่า) |
| B7 | ✅ | High (error UI) |
| B8 | ✅ + DB | **Critical** (commit data integrity) |
| B9 | ✅ + DB | **Critical** (legacy commit) |
| B10 | ✅ | Medium (regression) |
| B11 | ✅ | Medium (SPA regression) |
| B12 | curl ก็ได้ | Medium |
| B-CLI-* | ❌ | เทียบเท่า B5/B6 — ถ้าไม่มี browser ใช้ชุดนี้ |

---

## 3. Test Cases

### 3.1 CLI-only tests (ไม่ต้อง browser — ทดสอบ logic ผ่าน spark command)

#### B-CLI-01 — Unit tests ยังผ่านครบ
```bash
./vendor/bin/phpunit --filter ProgramContentBundleServiceTest --no-coverage
```
**Pass criteria:** `OK (47 tests, 151 assertions)` (หรือมากกว่า)
**Fail:** รายงาน output เต็ม

---

#### B-CLI-02 — Spark commands ลงทะเบียน
```bash
php spark list 2>&1 | grep -iE "bundle|program:import"
```
**Pass criteria:** เห็นทั้ง 2 commands:
- `cleanup:program-bundle-staging`
- `program:import-bundle`

---

#### B-CLI-03 — Routes ลงทะเบียน
```bash
php spark routes 2>&1 | grep -iE "bundle"
```
**Pass criteria:** เห็น 5 routes:
- `GET program-admin/bundle-export/([0-9]+)`
- `GET program-admin/bundle-template/([0-9]+)`
- `GET program-admin/bundle-preview/([0-9]+)`
- `POST program-admin/bundle-import-preview/([0-9]+)`
- `POST program-admin/bundle-import-commit/([0-9]+)`

---

#### B-CLI-04 — Service roundtrip ผ่าน CLI (สำคัญ)

**Setup:** สร้าง spark command ชั่วคราวเพื่อ exercise service กับ DB จริง

สร้างไฟล์ `app/Commands/_QaSmokeRoundtrip.php`:
```php
<?php
namespace App\Commands;
use App\Models\ProgramModel;
use App\Models\ProgramPageModel;
use App\Services\ProgramContentBundleService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class _QaSmokeRoundtrip extends BaseCommand
{
    protected $group = 'QA';
    protected $name = 'qa:smoke-roundtrip';
    protected $description = '[QA] Export real program and verify 3-namespace structure';
    public function run(array $params)
    {
        helper(['career_cards', 'tuition_fees', 'overview_lists']);
        $id = (int) ($params[0] ?? 1);
        $pm = new ProgramModel(); $pp = new ProgramPageModel();
        $svc = new ProgramContentBundleService();
        $program = $pm->find($id); $page = $pp->findByProgramId($id);
        if (!$program) { CLI::error('no program id='.$id); return; }
        $b = $svc->buildBundleFromDatabase($id, $program, $page);
        CLI::write('top-level: '.implode(',', array_keys($b)));
        CLI::write('basic='.count($b['basic']).' content='.count($b['content']).' settings='.count($b['settings']));
        $overlap = array_intersect_key($b['content'], $b['settings']);
        CLI::write(count($overlap)===0 ? 'PASS: no overlap' : 'FAIL: overlap '.implode(',',array_keys($overlap)));
        $out = WRITEPATH.'temp/qa_new.json';
        file_put_contents($out, json_encode($b, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        $legacy = ['schema_version'=>1,'program_id'=>$id,'program'=>['name_th'=>$program['name_th']??'','level'=>$program['level']??''],'page'=>$svc->decodePageRowForBundle($page??[])];
        file_put_contents(WRITEPATH.'temp/qa_legacy.json', json_encode($legacy, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        $p = $svc->parseBundleJsonString(file_get_contents($out));
        CLI::write('parse new: errors='.count($p['errors']).' legacy='.($p['legacy']?'Y':'N'));
        $pl = $svc->parseBundleJsonString(file_get_contents(WRITEPATH.'temp/qa_legacy.json'));
        CLI::write('parse legacy: errors='.count($pl['errors']).' legacy='.($pl['legacy']?'Y':'N'));
    }
}
```

รัน:
```bash
php spark qa:smoke-roundtrip 1
```

**Pass criteria:** output ต้องมี
- `top-level: schema_version,program_id,exported_at,basic,content,settings`
- `basic=10 content=16 settings=9`
- `PASS: no overlap`
- `parse new: errors=0 legacy=N`
- `parse legacy: errors=0 legacy=Y`

**หลังผ่าน:** ลบไฟล์ชั่วคราว
```bash
rm -f app/Commands/_QaSmokeRoundtrip.php writable/temp/qa_new.json writable/temp/qa_legacy.json
```

---

#### B-CLI-05 — CLI import dry-run (new + legacy)

**Setup:** ทำ B-CLI-04 ก่อน (สร้างไฟล์ `qa_new.json` + `qa_legacy.json`)

```bash
# New format
php spark program:import-bundle 1 "$(pwd)/writable/temp/qa_new.json" --dry-run 2>&1 | tail -10

# Legacy format
php spark program:import-bundle 1 "$(pwd)/writable/temp/qa_legacy.json" --dry-run 2>&1 | tail -10
```

**Pass criteria:**
- ทั้ง 2 รัน ไม่มี error
- `[DRY-RUN] ไม่บันทึก` แสดงในผลลัพธ์
- Legacy run แสดง `[legacy format {program, page}] แปลงเป็น 3 namespace แล้ว`
- `basic fields:` + `page fields:` แสดงจำนวน field

---

### 3.2 Browser-based tests (ต้องล็อกอินแอดมิน)

**Base:** ล็อกอินเข้า admin → ไปหน้า `http://localhost/newScience/program-admin/edit/1`

#### B1 — Panel placement
**Steps:**
1. เปิด `http://localhost/newScience/program-admin/edit/1`
2. สลับไปแท็บ **"เนื้อหาหลักสูตร"**
3. Scroll + ค้นหา element ที่มีข้อความ "นำเข้า / ส่งออก JSON"

**Expected:** ไม่เจอ — ไม่มี bundle panel ในแท็บนี้

4. สลับไปแท็บ **"การตั้งค่าเว็บไซต์"**
5. Scroll ลงสุด

**Expected:** เห็น panel สีขาวมีหัวข้อ `"นำเข้า / ส่งออก JSON (ข้อมูลพื้นฐาน · เนื้อหาหลักสูตร · การตั้งค่า)"`

**Pass criteria:** ทั้ง 2 ข้อข้างบน

---

#### B2 — Export current bundle
**Steps:**
1. อยู่แท็บ "การตั้งค่าเว็บไซต์"
2. กดปุ่ม **"ดาวน์โหลด JSON ปัจจุบัน"**
3. เปิดไฟล์ที่ดาวน์โหลด (เช่น `program-1-content-bundle.json`)

**Expected JSON structure:**
```json
{
  "schema_version": 1,
  "program_id": 1,
  "exported_at": "2026-...",
  "basic":    { "name_th": "...", "name_en": "...", "level": "bachelor", "credits": <int>, ... },
  "content":  { "philosophy": "...", "objectives": [...], ... },
  "settings": { "theme_color": "#...", "is_published": <0|1>, ... }
}
```

**Pass criteria (verify ด้วย jq หรือ text inspection):**
- มี top-level keys: `schema_version, program_id, exported_at, basic, content, settings`
- **ไม่มี** key `program` หรือ `page` ที่ top-level
- `basic` มี 10 keys; `content` มี 16 keys; `settings` มี 9 keys
- ไม่มี key ซ้ำระหว่าง `content` กับ `settings`

```bash
# verify ด้วย jq
jq '.basic | keys | length, .content | keys | length, .settings | keys | length' downloaded.json
# expect: 10, 16, 9
jq '(.content | keys) as $c | (.settings | keys) as $s | $c - ($c - $s)' downloaded.json
# expect: []  (no overlap)
```

---

#### B3 — Export empty template
**Steps:**
1. กด **"ดาวน์โหลดแม่แบบว่าง"**
2. เปิดไฟล์

**Pass criteria:**
- `template_note` มีข้อความภาษาไทย
- `content.learning_standards_json` = `{intro:"", standards:[], mapping:[]}`
- `settings.theme_color` = `"#1e40af"` (default)
- `settings.is_published` = `0`

---

#### B4 — Preview current
**Steps:**
1. กด **"ดูสรุปฐานปัจจุบันต่อหัวข้อ"**

**Pass criteria:**
- Panel ด้านล่างขยาย แสดง grid 2 คอลัมน์
- ฝั่งซ้ายมี 6 sections titles (`1. ภาพรวม`, `2. มาตรฐาน & PLO`, `3. แผนการเรียน`, `4. อาชีพ · รับสมัคร · ติดต่อ`, `ศิษย์เก่า`, `5. เผยแพร่ & หน้าเว็บ`)
- ฝั่งขวาเป็นคำว่า "(ฝั่งขวา: นำเข้า — ยังไม่มี)"

---

#### B5 — Import preview (new format)
**Steps:**
1. Download ไฟล์จาก B2 (ถ้ายังไม่มี)
2. แก้ไฟล์ — เปลี่ยน `basic.name_th` เป็น `"QA TEST " + timestamp`
3. อัปโหลดไฟล์ที่ปุ่ม **"นำเข้าไฟล์ .json"**
4. กด **"ตรวจก่อนนำเข้า"**

**Pass criteria:**
- ข้อความแสดง `"ตรวจผ่าน — sha1: ..."` สีเขียว
- Panel compare ขยาย แสดง 2 คอลัมน์:
  - ซ้าย (ฐานปัจจุบัน) — ข้อมูลเดิม
  - ขวา (สิ่งที่นำเข้า) — ชื่อใหม่
- ปุ่ม **"ยืนยันบันทึกลงฐานข้อมูล"** แสดง

---

#### B6 — Import preview (legacy format) ⚠️ CRITICAL
**Setup:** สร้างไฟล์ `legacy_test.json`:
```json
{
  "schema_version": 1,
  "program_id": 1,
  "program": {"id": 1, "name_th": "QA LEGACY", "level": "bachelor", "status": "active"},
  "page": {
    "philosophy": "ทดสอบ legacy",
    "theme_color": "#abcdef",
    "hero_image": "",
    "is_published": 1,
    "slug": "legacy-test"
  }
}
```

**Steps:**
1. อัปโหลด `legacy_test.json` + กดตรวจ

**Pass criteria:**
- **ต้องผ่าน** — ไม่มี error
- DevTools Network tab → response ของ `bundle-import-preview` มี `"legacy": true`
- DevTools Network tab → response มี `"basic_keys": ["name_th", "level"]`
- Compare panel แสดงการเปลี่ยนแปลง

---

#### B7 — Import preview (bad file — error UI)
**Setup:** สร้าง `bad.json`:
```json
{
  "schema_version": 1,
  "program_id": 1,
  "basic": {"credits": -5, "level": "wizard", "website": "javascript:alert(1)"},
  "settings": {"theme_color": "red"}
}
```

**Steps:**
1. อัปโหลด + กดตรวจ

**Pass criteria:**
- ข้อความ `msg` เป็นสีแดง
- มี `<ul id="bundle-import-errors">` แสดง (ไม่ hidden)
- รายการ error อย่างน้อย 3 ข้อ ครอบคลุม:
  - `basic.credits` หรือคำว่า "ลบ"
  - `basic.level` หรือคำว่า "bachelor/master/doctorate"
  - `settings.theme_color` หรือคำว่า "#RRGGBB"
  - `basic.website` หรือคำว่า "http"
- ไม่มีปุ่ม "ยืนยันบันทึก" แสดง

---

#### B8 — Commit new-format import ⚠️ CRITICAL

**Steps:**
1. ทำ B5 ก่อน (มี staging token)
2. ก่อน commit: บันทึก `SELECT name_th FROM programs WHERE id=1` เดิม
3. กด **"ยืนยันบันทึกลงฐานข้อมูล"**
4. หน้า reload → ตรวจ `SELECT name_th FROM programs WHERE id=1` ใหม่

**Pass criteria:**
- ข้อความ `"นำเข้าและบันทึกเนื้อหาเรียบร้อยแล้ว"` สีเขียว
- DB: `programs.name_th` เปลี่ยนตามไฟล์ที่ import (`QA TEST ...`)
- File exists: `writable/uploads/programs/1/data/content-bundle-latest.json`
- Log file วันนี้ (`writable/logs/log-YYYY-MM-DD.log`) มี `program bundle import committed program_id=1`
- **ไม่มี** `CRITICAL` หรือ `ERROR` ใน log

**Rollback หลังทดสอบ:** UPDATE กลับเป็นค่าเดิม หรือใช้ DB snapshot

---

#### B9 — Commit legacy-format import ⚠️ CRITICAL

**Steps:**
1. ทำ B6 ก่อน + commit
2. ตรวจ DB:
   ```sql
   SELECT name_th, level FROM programs WHERE id=1;
   SELECT theme_color, is_published, slug FROM program_pages WHERE program_id=1;
   ```

**Pass criteria:**
- `programs.name_th` = `"QA LEGACY"`, `level` = `"bachelor"`
- `program_pages.theme_color` = `"#abcdef"`, `is_published` = `1`, `slug` = `"legacy-test"`
- **2 table update พร้อมกัน (transaction atomic)**

**Rollback:** restore จาก export B2 (ไฟล์ที่ดาวน์โหลดไว้ก่อนเริ่มทดสอบ)

---

#### B10 — Regression: basic/website form ยังบันทึกได้

**Steps:**
1. แท็บ **"ข้อมูลพื้นฐาน"** → แก้ `name_th` → Save
2. ตรวจหน้าโหลดซ้ำ + DB

**Pass criteria:** บันทึกสำเร็จ ไม่มี error

3. แท็บ **"การตั้งค่าเว็บไซต์"** → แก้ `theme_color` ใน `<input id="theme_color_hex">` → กด "บันทึกการตั้งค่าเว็บไซต์"
4. ตรวจ

**Pass criteria:** บันทึกสำเร็จ `program_pages.theme_color` เปลี่ยน

---

#### B11 — SPA regression

**Steps:**
1. เปิด `http://localhost/newScience/p/1/main` (incognito, ไม่ต้อง login)
2. เปิด DevTools Console

**Pass criteria:**
- หน้าโหลดสมบูรณ์ (hero + sections ครบ)
- ไม่มี `console.error` สีแดง
- Network tab: `GET /p/1/data` → 200, JSON body มี `"success": true`

---

#### B12 — 403/404 separation

**Steps (ใช้ curl กับ session cookie):**
```bash
# 404: program id ไม่มี
curl -sI -b cookies.txt http://localhost/newScience/program-admin/bundle-preview/999999

# 403: บัญชีไม่มีสิทธิ์ (ถ้ามี)
# ล็อกอินเป็นบัญชี role ต่ำ → curl -sI ...
```

**Pass criteria:**
- Request 1 → `HTTP/1.1 404`
- Request 2 → `HTTP/1.1 403` (ถ้า test case ใช้ได้)

---

## 4. Reporting Template

AI agent กรุณาส่งผลกลับในรูปแบบนี้ (เขียนเป็นไฟล์ `docs/QA_BUNDLE_V1_1_REPORT.md`):

```markdown
# QA Report — Bundle v1.1 (run YYYY-MM-DD HH:MM)

## Environment
- git HEAD: <commit hash>
- PHP: <version>
- Browser used: <Playwright Chromium / manual / N/A>
- Login account role: <admin/super_admin/N/A>

## Summary
- Total tests: <N>
- Passed: <N>
- Failed: <N>
- Skipped: <N> (reason)

## Detail

### B-CLI-01 — Unit tests
- Status: PASS / FAIL
- Evidence: `OK (47 tests, 151 assertions)` / paste full output

### B-CLI-02 — Spark commands
- Status: PASS / FAIL
- Evidence: `<command output>`

... (ทำซ้ำต่อ test ทุกข้อ) ...

## Critical findings
- <bullet list ของ CRITICAL failures + ข้อมูลให้ reproduce>

## Suggestions
- <optional — ถ้ามี improvement เสนอ>

## Artifacts
- `docs/QA_BUNDLE_V1_1_REPORT.md` — รายงานนี้
- `/tmp/qa_bundle_*.json` — ไฟล์ test ที่สร้าง (cleanup หลังรายงาน)
- `screenshots/*.png` — (ถ้า browser agent)
```

---

## 5. Stop conditions (หยุดทดสอบทันที)

AI agent **ต้องหยุด** และรายงาน ถ้าพบข้อใดข้อหนึ่ง:

1. **B-CLI-01 fail** — unit tests มี failure ใหม่ (service อาจเสียหลัก)
2. **B6 fail** — legacy compat พัง (จะกระทบไฟล์ export เก่าของผู้ใช้)
3. **B8 หรือ B9 fail ที่ระดับ DB** — transaction ไม่ atomic หรือข้อมูลหาย (critical data integrity)
4. **B11 fail** — SPA หน้าสาธารณะพัง (user-facing regression)
5. **มี CRITICAL/ERROR ใหม่ใน `writable/logs/log-YYYY-MM-DD.log`** ที่ timestamp ระหว่างทดสอบ

---

## 6. Cleanup หลังทดสอบ

```bash
# ลบไฟล์ชั่วคราวที่สร้างระหว่างทดสอบ
rm -f writable/temp/qa_*.json
rm -f app/Commands/_QaSmokeRoundtrip.php     # ถ้าสร้างไว้

# ตรวจ git status — ไม่ควรมีไฟล์ค้าง
git status --short

# ถ้า B8/B9 commit จริง → restore DB row กลับค่าเดิม
# (ใช้ไฟล์ export B2 + import อีกครั้ง)
```

---

## 7. ภาคผนวก — คำถามที่ถามผู้ใช้ได้

ถ้า AI agent ไม่มีข้อมูลต่อไปนี้ ให้ถามผู้ใช้:

1. **Admin credentials** สำหรับล็อกอิน (username + password)
2. **program id ที่ปลอดภัยสำหรับทดสอบ** (default ใช้ id=1 แต่ถ้ามี program id=999 สำหรับ test จะปลอดภัยกว่า)
3. **DB backup strategy** — ก่อน B8/B9 ควร snapshot หรือไม่ (ถ้า prod-like env)
4. **Timezone ของ log timestamps** — เทียบกับเวลาทดสอบ

---

**สถานะ document:** Created 2026-04-23 for post-refactor verification of Bundle v1.1 feature
**ผู้สร้าง:** Claude Opus 4.7 (previous session)
**ติดต่อเจ้าของ:** ดู `docs/HANDOFF_PROGRAM_CONTENT_BUNDLE.md`

---

# ภาคผนวก — ฟีเจอร์ "การรับสมัคร" (admission_details_json)

**เพิ่ม 2026-04-23** — tab ใหม่ + JSON column + SPA section

## New/changed files
- `app/Database/Migrations/2026-04-23-100000_add_admission_details_json_to_program_pages.php`
- `app/Helpers/admission_details_helper.php`
- `app/Models/ProgramPageModel.php` (+ `admission_details_json` in `allowedFields`)
- `app/Services/ProgramContentBundleService.php` (+ `admission_details_json` in `CONTENT_ALLOWED_KEYS`)
- `app/Config/program_content_bundle.v1.schema.json` (+ content.properties.admission_details_json)
- `app/Controllers/Admin/ProgramAdmin/Dashboard.php` (+ `updateAdmission()` method)
- `app/Controllers/ProgramSpaController.php` (+ `admission_details` in getData response)
- `app/Config/Routes.php` (+ `program-admin/update-admission/(:num)`)
- `app/Views/admin/programs/edit_content.php` (+ `#admission-tab` form)
- `app/Views/program_spa/main.php` (+ `<section id="admission">` + JS render)
- `tests/unit/ProgramContentBundleServiceTest.php` (+ 9 cases)

## Data shape
```json
{
  "plan_seats": "30 คน",
  "requirements": {
    "study_plan": "วิทย์-คณิต",
    "mor_kor_2_url": "https://shorturl.asia/gpIcn",
    "english_grade": "ไม่จำกัด",
    "selection_criteria": "สัมภาษณ์",
    "tuition_per_term": "10,400 บาท",
    "duration": "8 ภาคการศึกษา",
    "credits_note": "ไม่น้อยกว่า 120 หน่วยกิต",
    "program_type": "ภาคปกติ"
  },
  "supports": {
    "scholarship": true,
    "first_term_loan": true,
    "ksl_loan": true,
    "study_scholarship": true,
    "entrepreneur_fund": true,
    "dormitory": true
  }
}
```

**กฎสำคัญ:**
- `supports` **ไม่มีใน admin UI** — เก็บเป็น `true` default ทั้ง 6 (hardcoded ใน helper)
- `updateAdmission` preserve ค่า supports เดิมจาก DB เพื่อไม่ให้ถูกทับ
- `mor_kor_2_url` ต้องขึ้นต้นด้วย `http://` หรือ `https://` เท่านั้น (reject `javascript:`, `ftp:`, etc.)

## Test IDs เพิ่ม

### B-CLI-06 — Migration applied
```bash
php spark db:table program_pages 2>&1 | grep admission_details_json
```
**Pass:** เห็น `admission_details_json` column (type LONGTEXT)

### B-CLI-07 — Admission helper tests
รันอยู่ใน phpunit แล้ว:
```bash
./vendor/bin/phpunit --filter "testAdmission|testPageBundleToUpdateRowHandlesAdmission|testBuildContentSliceIncludesAdmission|testBuildEmptyTemplateIncludesAdmission" --no-coverage
```
**Pass:** 9/9 tests (admission_details_default_structure, decode, normalize, reject bad URL, clamp, roundtrip, etc.)

### B13 — Admission tab UI (browser)
1. เปิด `/program-admin/edit/1`
2. กดแท็บ **"การรับสมัคร"**
3. ต้องเห็น 2 sections: **จำนวนการรับนักศึกษา** (1 input) + **คุณสมบัติของผู้เข้าเรียน** (8 inputs ใน 2-column grid)
4. กรอกครบ (ใช้ data จาก `## Data shape` ด้านบน) → กด "บันทึกข้อมูลการรับสมัคร"
5. ขึ้น success, reload, ตรวจค่าคืน ✅

### B14 — Reject bad URL
1. กรอก `มคอ 2. URL` = `javascript:alert(1)`
2. Save → ต้องขึ้น error `admission_details.requirements.mor_kor_2_url: ต้องขึ้นต้นด้วย http:// หรือ https://`
3. DB: ค่าเดิมไม่ถูกทับ

### B15 — SPA render
1. เปิด `http://localhost/newScience/p/1/main`
2. Scroll หา section **"การรับสมัคร"**
3. ถ้ากรอกข้อมูลจาก B13 แล้ว:
   - หัวกล่อง "จำนวนรับตามแผน" + ตัวเลข "30 คน"
   - ตาราง 8 rows คุณสมบัติ (URL เป็น link)
   - Grid "สิ่งสนับสนุนการเรียน" 6 รายการพร้อม checkmark สีเขียว

### B16 — Bundle export includes admission_details
1. ทำ B2 (dowload export) → เปิดไฟล์ JSON
2. ยืนยัน `content.admission_details_json` มีโครงสร้าง `{plan_seats, requirements, supports}` ครบ

### B17 — Bundle import with admission
1. แก้ไฟล์จาก B16 — เปลี่ยน `content.admission_details_json.plan_seats`
2. Import → commit → ตรวจค่าใหม่ใน DB + หน้า SPA

## Reporting — append ใน QA_BUNDLE_V1_1_REPORT.md

เพิ่ม section "Admission feature" ใน report ที่ AI agent สร้าง พร้อม B13-B17 pass/fail
