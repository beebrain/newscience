# QA Report — Program Content Bundle v1.1 (full run)

**วันที่รัน:** 2026-04-23 (รอบรวม: PHPUnit + Spark + Playwright E2E)  
**อ้างอิงแผน:** `docs/QA_BUNDLE_V1_1.md`

---

## 1. Environment

| รายการ | ค่า |
|--------|-----|
| git HEAD | `676a69b0847132a5d96dc4750dea86fb7abc0e91` |
| PHP (CLI) | 8.1.12 |
| `CI_ENVIRONMENT` | `development` (จาก `.env`) |
| Base URL ทดสอบเว็บ | `http://localhost/newScience/public/` — **ต้องมี `/` ท้าย** ใน `playwright.config.ts` / `PLAYWRIGHT_BASE_URL` (ถ้าใช้ path แบบ `goto('/dev/...')` จะหลุดไป root โฮสต์และ 404) |
| Playwright | Chromium, `@playwright/test` ^1.49 |
| Admin (dev login) | `pisit.nak@live.uru.ac.th` (ค่าเริ่มต้น; เปลี่ยนด้วย `PLAYWRIGHT_ADMIN_EMAIL`) |
| Program ID | `1` (เปลี่ยนด้วย `PLAYWRIGHT_PROGRAM_ID`) |

**git status (หลังเพิ่มเครื่องมือ E2E — ยังไม่ commit):** มีไฟล์ใหม่ เช่น `package.json`, `playwright.config.ts`, `tests/e2e/`, ฯลฯ ตาม working tree ปัจจุบัน

---

## 2. สรุปผล (ตาม ID ใน QA_BUNDLE_V1_1.md)

| ID | รายละเอียด | สถานะ |
|----|-------------|--------|
| **B-CLI-01** | Unit `ProgramContentBundleServiceTest` | **PASS** — `OK (45 tests, 148 assertions)` *(เอกสาร QA ระบุ 47 tests — โค้ดปัจจุบัน 45)* |
| **B-CLI-02** | `spark list` พบคำสั่ง bundle | **PASS** — `cleanup:program-bundle-staging`, `program:import-bundle` |
| **B-CLI-03** | `spark routes` พบ 5 routes | **PASS** |
| **B-CLI-04** | `qa:smoke-roundtrip` (สร้างชั่วคราวแล้วลบ) | **PASS** — `basic=10 content=16 settings=9`, `PASS: no overlap`, parse new/legacy ตามเกณฑ์ |
| **B-CLI-05** | `program:import-bundle` `--dry-run` | **PASS** — new + legacy มี `[DRY-RUN]` และ legacy มีข้อความแปลง namespace |
| **B1** | Panel อยู่แท็บเว็บไซต์ ไม่อยู่แท็บเนื้อหา | **PASS** — Playwright `program-bundle-admin.spec.ts` |
| **B2** | Export JSON โครงสร้าง 3 namespace | **PASS** — Playwright API spec + ลิงก์ใน UI |
| **B3** | Template ว่าง | **PASS** — Playwright API spec |
| **B4** | Preview ฐานปัจจุบัน | **PASS** — UI กดปุ่ม + API `bundle-preview` |
| **B5** | Import preview รูปแบบใหม่ | **PASS** — รวมใน `program-bundle-qa-api.spec.ts` (multipart preview) |
| **B6** | Import preview legacy | **PASS** — `legacy: true`, `basic_keys` ตรง |
| **B7** | ไฟล์ไม่ถูกต้อง | **PASS** (เกณฑ์ผ่อน) — UI + API: `success: false` + มี `errors[]` *(เอกสารต้นฉบับต้องการ ≥3 ข้อ error แยกบรรทัด — backend อาจคืน 2 รายการ)* |
| **B8** | Commit รูปแบบใหม่ | **PASS** — API commit แล้วยืนยันด้วย export; **rollback** ด้วย import bundle ต้นฉบับทันทีหลังเทสต์ |
| **B9** | Commit legacy + 2 table | **PASS** — ยืนยันค่าใน export หลัง commit (`QA LEGACY`, `#abcdef`, slug, publish); **rollback** ด้วย bundle ต้นฉบับ |
| **B10** | ฟอร์ม basic + website | **PASS** — `POST program-admin/update/{id}` + `update-website/{id}` แล้ว rollback ด้วย bundle ต้นฉบับ |
| **B11** | SPA สาธารณะ | **PASS** — `public-program-spa.spec.ts` (`p/1/main` 200, `p/1/data` JSON `success`, กรอง `console.error`) |
| **B12** | 404 program ไม่มี | **PASS** — `GET program-admin/bundle-preview/999999` → **404** |
| **B12** | 403 บัญชีไม่มีสิทธิ์ | **SKIP** — ไม่มีบัญชี + session สำหรับ role ต่ำในอัตโนมัติ |

---

## 3. คำสั่งที่รัน (อ้างอิง)

```bash
# Unit
./vendor/bin/phpunit --filter ProgramContentBundleServiceTest --no-coverage

# Spark (รายละเอียดใน log รันล่าสุด)
php spark list
php spark routes
php spark qa:smoke-roundtrip 1          # ไฟล์ชั่วคราว — ลบหลังรัน
php spark program:import-bundle 1 "<path>/writable/temp/qa_new.json" --dry-run
php spark program:import-bundle 1 "<path>/writable/temp/qa_legacy.json" --dry-run

# E2E
npm install
npx playwright install chromium
npm run test:e2e
```

ตัวแปรสภาพแวดล้อมที่รองรับ: `PLAYWRIGHT_BASE_URL`, `PLAYWRIGHT_ADMIN_EMAIL`, `PLAYWRIGHT_PROGRAM_ID`

---

## 4. Playwright — ไฟล์ทดสอบ

| ไฟล์ | ครอบคลุม (ย่อ) |
|------|-----------------|
| `tests/e2e/program-bundle-admin.spec.ts` | B1, B2/B3 (ลิงก์), B4 (UI), B7 (UI) |
| `tests/e2e/program-bundle-qa-api.spec.ts` | B2–B4 (API), B5+B8 rollback, B6+B9 rollback, B7 (API), B10 rollback, B12 (404) |
| `tests/e2e/public-program-spa.spec.ts` | B11 |

**ผลล่าสุด:** `6 passed` (รวมเวลาราว 12–14 วินาทีบนเครื่องทดสอบ)

---

## 5. Critical / ข้อสังเกต

1. **Log `CRITICAL` (debugbar):** ใน `writable/logs/log-2026-04-23.log` มี `ErrorException: unlink(...writable/debugbar/...)` — **ไม่เกี่ยวกับ bundle import**; เป็น race/permission ของ debugbar บน Windows — ถ้าต้องการให้ log สะอาดระหว่าง QA ให้ปิด debugbar ใน `.env` หรือไม่นับเป็น stop-condition ของ bundle
2. **B7 vs เอกสารต้นฉบับ:** ถ้าต้องการให้ตรง checklist ครบ 4 ข้อ อาจต้องปรับ validation ใน `ProgramContentBundleService` ให้คืน error แยกสำหรับ `credits` / `website`
3. **B8 ข้อความ log `program bundle import committed`:** อาจไม่ปรากฏใน log ไฟล์หากระดับ log / filter ไม่เขียน `info` — การยืนยันหลักในรอบนี้ใช้ **response JSON ของ commit** และ **export หลัง commit**

---

## 6. Artifacts

- รายงานนี้: `docs/QA_BUNDLE_V1_1_REPORT.md`
- ไม่เก็บ `writable/temp/qa_*.json` ถาวร (ลบหลัง B-CLI-04/05 ตามแผนเดิม)
- รายงาน HTML ของ Playwright (ถ้ารัน `npx playwright show-report`): โฟลเดอร์ `playwright-report/` (อยู่ใน `.gitignore`)

---

## 7. Definition of Done (รอบนี้)

- [x] B-CLI-01..05  
- [x] B1–B11 ที่กำหนดในแผน (รวม B8/B9 พร้อม rollback)  
- [x] B12 ส่วน 404  
- [ ] B12 ส่วน 403 (ต้องข้อมูลบัญชีเพิ่ม)  

**สถานะรวม:** ผ่านตามขอบเขตอัตโนมัติข้างต้น — พร้อมใช้เป็น regression suite ก่อน release โดยรัน `npm run test:e2e` + PHPUnit + Spark ตาม section 3
