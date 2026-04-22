# Handoff: Program Content JSON Bundle (นำเข้า/ส่งออกหลักสูตร)

เอกสารนี้สรุปสิ่งที่ทำแล้วและสิ่งที่ยังเปิดไว้ให้ agent/นักพัฒนาตัวถัดไป implement ต่อ **ไม่แก้ไฟล์แผนใน `.cursor/plans/`** — อ้างอิงแนวทางจากแผน “program JSON import” ใน repo

---

## สถานะโดยย่อ

| พื้นที่ | สถานะ |
|--------|--------|
| Service แปลง bundle, normalize, preview ต่อ section, staging token, snapshot ลง `writable/uploads/programs/{id}/data/` | ทำแล้ว (`ProgramContentBundleService`) |
| Routes + `Dashboard` (export, template, preview, import preview/commit) | ทำแล้ว |
| UI แท็บเนื้อหา `edit_content.php` (ดาวน์โหลด, แม่แบบ, ตรวจก่อนนำเข้า, ยืนยัน) | ทำแล้ว |
| JSON Schema แบบไฟล์ + validate เต็มรูปแบบ | **ยังเปิด** — ตอนนี้มีแต่ validation แบบ manual ใน `parseBundleJsonString` / `pageBundleToUpdateRow` |
| ทดสอบอัตโนมัติ (PHPUnit integration รอบ bundle) | **ยังเปิด** |
| เฟส 5: ลบตารางที่ไม่ใช้ (หลัง audit) | **ยังเปิด** — ต้อง audit บน DB จริง; ตัวอย่าง `program_content_blocks` ยังใช้ใน `ContentBuilder` + `ProgramWebsite` |
| เฟส 4: แหล่งความจริงเป็นไฟล์ + pointer ใน DB | **ไม่ทำ** (optional ตามแผน) — ปัจจุบันแหล่งจริงยังเป็น `program_pages` |

---

## ไฟล์หลักที่ต้องรู้

| บทบาท | Path |
|--------|------|
| Service | `app/Services/ProgramContentBundleService.php` |
| Controller | `app/Controllers/Admin/ProgramAdmin/Dashboard.php` — methods `exportContentBundle`, `exportContentBundleTemplate`, `currentBundlePreview`, `importContentBundlePreview`, `importContentBundleCommit` |
| Routes | `app/Config/Routes.php` — prefix `program-admin`: `bundle-export`, `bundle-template`, `bundle-preview`, `bundle-import-preview`, `bundle-import-commit` |
| UI | `app/Views/admin/programs/edit_content.php` — block `.program-bundle-panel` + script ท้ายไฟล์ |
| Upload helper | `app/Helpers/program_upload_helper.php` — ใช้ feature โฟลเดอร์ `data` ใต้ `programs/{id}/` |
| Staging import ชั่วคราว | `writable/temp/program_bundle_import/` (gitignore ภายใต้ `writable/temp/**`) |
| Snapshot ถาวร (สำรอง ไม่ใช่ source of truth) | `writable/uploads/programs/{programId}/data/content-bundle-latest.json`, `content-bundle-template.json` |

---

## พฤติกรรมที่ implement แล้ว (สำหรับ regression)

1. **Export** — GET ดาวน์โหลด JSON (`schema_version`, `program_id`, `program`, `page` โดย `page` decode `*_json` และ objectives/graduate เป็น `string[]`).
2. **Template** — GET แม่แบบว่าง (`buildEmptyTemplateBundle`) รวม `learning_standards_json` เป็น `{ intro, standards, mapping }`.
3. **Import** — POST ไฟล์ → validate → merge กับแถวปัจจุบัน → token → preview JSON (current vs import) → POST commit บันทึก `updateOrCreate`.
4. **Normalizers** — reuse `career_json_normalize`, `tuition_fees_json_normalize`, `overview_lines_*` ให้ path เดียวกับ `updatePage`.
5. **Snapshot** — หลัง export / template / import สำเร็จ เขียนไฟล์ snapshot ลง `uploads/.../data/` (ดู `SNAPSHOT_*` constants ใน service).

---

## รายละเอียดที่แนะนำให้ implement ต่อ

### 1) JSON Schema + validation ชั้นนอก (Prior: กลาง)

- เพิ่มไฟล์ schema มาตรฐาน เช่น `app/Config/program_content_bundle.v1.schema.json` (JSON Schema draft-07 หรือเทียบเท่า) อธิบาย root + `page` (properties อนุญาต, `additionalProperties` policy).
- ใน `ProgramContentBundleService::parseBundleJsonString` (หรือ method ใหม่ `validateBundleDocument`) เรียก validator:
  - ตัวเลือก: lib เช่น `justinrainbow/json-schema` ผ่าน Composer **หรือ** validator แบบเบา (recursive check ต่อ key ที่ `ProgramPageModel::$allowedFields` อนุญาตสำหรับ `page`).
- **Acceptance:** นำเข้าไฟล์ที่ field ผิด type / key ต้องห้าม ได้ error รายข้อก่อนเข้า preview; เอกสาร bundle ที่ export จากระบบผ่าน schema.

### 2) HTTP semantics และข้อความ error (Prior: ต่ำ)

- `currentBundlePreview` / import: แยก **404** เมื่อไม่มี `programs.id` กับ **403** เมื่อไม่มี `canManageProgram` (ตอนนี้รวมเป็น 403/ข้อความรวม).
- แสดง `errors[]` จาก backend บน UI เมื่อ validation ล้มเหลว (บาง error ยังรวมเป็นข้อความเดียว).

### 3) ทำความสะอาด staging เก่า (Prior: ต่ำ)

- ไฟล์ใน `writable/temp/program_bundle_import/` หมดอายุแล้วอาจค้าง — เพิ่ม cron/Task ลบ `*.json` ที่ `expires` < now หรือรัน `glob` + อายุไฟล์ > 24h.
- หรือ `spark` command `cleanup:program-bundle-staging`.

### 4) ทดสอบอัตโนมัติ (Prior: กลาง)

- `tests/unit/ProgramContentBundleServiceTest.php`:
  - `parseBundleJsonString` กับไฟล์ valid / invalid `schema_version` / ขนาดเกิน
  - `pageBundleToUpdateRow` กับ `objectives` เป็น `array` และ `string`
  - `buildBundleFromDatabase` round-trip: mock `page` row → decode → encode DB columns
- ถ้ามี test DB: feature test POST import (ต้อง auth program-admin — อาจ mock session).

### 5) DB cleanup (เฟส 5 ของแผน) (Prior: ต่ำ, ต้อง stakeholder)

- รัน audit: ดึง `SHOW TABLES` เทียบ `grep` ใน `app/`, `scripts/`, migrations.
- **อย่า** drop `program_content_blocks` จนกว่าจะ migrate ออกจาก `ContentBuilder` / `ProgramWebsite` (`app/Controllers/Admin/ProgramAdmin/ContentBuilder.php`, `ProgramWebsite.php`).
- ผลลัพธ์ที่ต้องการ: รายการตาราง candidate + **migration พร้อม `down()`** หรือ “ไม่ลบ” บันทึกเป็น `app/Database/README_SCHEMA_DECISIONS.txt` หรือ issue.

### 6) Optional เฟส 4: แหล่งจริงเป็นไฟล์ (Prior: ต่ำ, ยาก)

- คอลัมน์เดียว pointer เช่น `content_snapshot_path` บน `program_pages` หรืออ่านไฟล์ก่อน DB — ต้องออกแบบ migration + fallback อ่าน DB — **นอก scope งานปัจจุบัน** นอกว่าจะอนุมัติแยก

### 7) CLI batch import (แผนกล่าวถึง) (Prior: ต่ำ)

- `php spark program:import-bundle {programId} {path.json}` — reuse `pageBundleToUpdateRow` + merge logic เดียวกับ `importContentBundleCommit` โดยไม่ต้อง staging token (สำหรับ dev/staging อย่างเดียว + guard env).

---

## ข้อจำกัด/ความเสี่ยง

- **แหล่งความจริง** ยังเป็นตาราง `program_pages` — ไฟล์ snapshot เป็นแค่สำรอง.
- นำเข้า **ไม่** อัปเดตตาราง `programs` (ชื่อหลักสูตร ฯลฯ) ยกเว้นจะขยาย bundle รุ่นถัดไป.
- นำเข้า **ไม่** รวม `program_downloads`, `news` — ต้อง scope แยกถ้าต้องการ bundle เต็มเว็บหลักสูตร.

---

## คำสั่งตรวจเร็วหลังแก้โค้ด

```bash
php -l app/Services/ProgramContentBundleService.php
php -l app/Controllers/Admin/ProgramAdmin/Dashboard.php
```

(ในโปรเจกต์ Windows/PowerShell ใช้ `;` คั่นคำสั่ง)

---

## อ้างอิง identity / auth

- กลุ่ม `program-admin` ใช้ `ProgramAdminFilter` + ตรวจ `canManageProgram` ราย `program_id` ใน `Dashboard` — อย่าเปิด endpoint bundle โดยไม่ตรวจสอบนี้

---

*อัปเดตล่าสุด: สร้างเพื่อ handoff ไป agent ตัวถัดไป — แก้ไขไฟล์นี้ได้เมื่อ scope งานเปลี่ยน*
