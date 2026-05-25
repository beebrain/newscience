# การทดสอบ CV Publication ด้วย Playwright

ออกแบบ E2E สำหรับ flow **เพิ่มผลงานตีพิมพ์** ที่ย้ายจาก modal ไปหน้าเต็ม  
`GET /dashboard/profile/cv/publication?section_id={id}`

## ชั้นทดสอบ

| ชั้น | เครื่องมือ | ไฟล์ / คำสั่ง |
|------|-----------|----------------|
| Unit | PHPUnit | `CvPublicationEntryPageTest`, `ResearchRecordCvSyncMergeTest`, `AiPublicationParserTest` |
| Integration CLI | Spark | `php spark cv:test-publication-page`, `php spark cv:test-ai-workflow` |
| **E2E UI** | **Playwright** | `tests/e2e/cv-publication-page.spec.ts` |
| Production smoke | Playwright | `tests/e2e/production-publication-smoke.spec.ts` |

## สเปก Playwright (local)

| ID | สถานะ | รายละเอียด |
|----|--------|------------|
| L0 | auth | ไม่ล็อกอิน → redirect ออกจาก `#cv-pub-form` |
| L1 | manage | หัวข้อ `data-show-pub="1"` มีลิงก์ **+ เพิ่มผลงาน** / **✦ AI** ไป `/cv/publication` ไม่มี `cv-pub-entry-modal` |
| L2 | page | โหลดหน้า publication — `#cv-pub-form`, `#cv-pub-ai-panel`, หัวข้อ h1 |
| L3 | AI UI | `?ai=1` → `data-open-ai="1"`; ปุ่ม `#cv-pub-ai-run` ตาม `CV_PUB_PAGE.aiReady` |
| L4 | save | กรอกขั้นต่ำ → บันทึก → redirect `?tab=sections` + flash สำเร็จ |
| L5 | regression | หัวข้อทั่วไปยังใช้ปุ่ม **เพิ่มรายการ** + `#cv-entry-modal` |
| L6 | optional | `PLAYWRIGHT_LIVE_N8N=1` — วิเคราะห์ URL จริง (ช้า, ต้อง n8n) |

Production (ต้อง `PLAYWRIGHT_STORAGE_STATE`):

| ID | รายละเอียด |
|----|------------|
| P5–P6 | จัดการ CV / แท็บหัวข้อ (เดิม) |
| P7 | หน้า publication โหลดได้หลังล็อกอิน |
| P8 | ไม่มี modal publication เก่าใน DOM |

## ตั้งค่า

```bash
npm ci
npx playwright install chromium
```

### ใช้ development mode ที่เครื่อง local (จำเป็น)

สเปก `cv-publication-page.spec.ts` **ออกแบบให้รันบนเครื่อง dev เท่านั้น** — ไม่ใช้ OAuth จริง แต่ใช้:

`GET /dev/login-as-admin?email=...` → ตั้ง `admin_logged_in` (เหมือน filter `loggedin`)

เงื่อนไข:

| รายการ | ค่าที่ต้องการ |
|--------|----------------|
| `.env` | `CI_ENVIRONMENT = development` |
| Docker/nginx | แอปตอบที่ `PLAYWRIGHT_BASE_URL` (เช่น `http://localhost/newscience/public/`) |
| DB local | user ใน `PLAYWRIGHT_CV_EMAIL` มี `personnel` + หัวข้อ `data-show-pub="1"` |

ถ้าไม่ใช่ development (เช่น production) route `/dev/login-as-admin` จะ **404** — เทสต์ L1–L5 จะ **skip** พร้อมข้อความอธิบาย

Production/staging ใช้ `production-publication-smoke.spec.ts` + `PLAYWRIGHT_STORAGE_STATE` แทน (login ด้วย codegen ครั้งเดียว)

`.env` แอป: `CI_ENVIRONMENT=development` (เพื่อ `/dev/login-as-admin`)

```bash
# baseURL ต้องลงท้าย /
export PLAYWRIGHT_BASE_URL=http://localhost/newscience/public/
export PLAYWRIGHT_CV_EMAIL=pisit.nak@live.uru.ac.th
# ถ้ารู้ section_id จาก spark cv:test-publication-page
export PLAYWRIGHT_CV_SECTION_ID=874
```

**หมายเหตุ path:** `playwright.config.ts` default เป็น `http://localhost/newScience/public` — ถ้า nginx ใช้ `newscience` (ตัวเล็ก) ให้ตั้ง `PLAYWRIGHT_BASE_URL` ให้ตรง Docker/nginx จริง

## รัน

```bash
npm run test:e2e:cv
npm run test:e2e:cv:headed
npm run test:e2e:cv:debug

# รายงาน HTML
npx playwright show-report
```

Production / staging:

```bash
npx playwright codegen "$PLAYWRIGHT_BASE_URL" --save-storage=playwright/.auth/user.json
PLAYWRIGHT_STORAGE_STATE=playwright/.auth/user.json \
  PLAYWRIGHT_BASE_URL=https://sci.uru.ac.th/ \
  npm run test:e2e:production
```

AI จริง (local มักล้มเพราะ n8n เข้า localhost ไม่ได้):

```bash
PLAYWRIGHT_LIVE_N8N=1 npm run test:e2e:cv
```

## Helpers

- `tests/e2e/helpers/cv-auth.ts` — `devLoginForCv`, `gotoCvSectionsTab`
- `tests/e2e/helpers/cv-publication.ts` — หา section, เปิดหน้า, กรอกฟอร์มขั้นต่ำ

## ข้อมูลขั้นต่ำสำหรับบันทึก (L4)

ตรง `PublicationResearchFields::validateResearchSave`:

- `entry_title`
- `publication_type` = `journal`
- `organization`
- `publication_year_be` = `2567`
- ผู้แต่งเริ่มต้นจาก JS (`cv-publication-entry-page.js`)

L4 สร้างรายการชื่อ `E2E Playwright {timestamp}` — ลบด้วยมือหรือ SQL ถ้าไม่ต้องการค้างใน DB

## เมื่อล้มเหลว

| อาการ | แนวทาง |
|--------|--------|
| 404 ทุก URL | ตรวจ `PLAYWRIGHT_BASE_URL` และ nginx ชี้ `public/` |
| dev login 404 | ไม่ใช่ `development` หรือรันบน production |
| ไม่มี `data-show-pub="1"` | เปลี่ยน `PLAYWRIGHT_CV_EMAIL` หรือรัน migrate `EnsurePublicationCvSections…` |
| L4 validation error | ตรวจฟิลด์บังคับใน helpers |
| L6 timeout | ตั้ง `app.baseURL` ที่ n8n เข้าถึงได้ หรือข้าม L6 |
