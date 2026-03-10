# เอกสารระบบ newScience — สำหรับ AI Agents

เอกสารนี้อธิบายโครงสร้างและพฤติกรรมของโปรเจกต์ **newScience** (เว็บคณะวิทยาศาสตร์และเทคโนโลยี) เพื่อให้ AI agent ตัวอื่นสามารถเข้าใจและแก้ไขโค้ดได้อย่างถูกต้อง

---

## 1. ภาพรวมโปรเจกต์

| รายการ | รายละเอียด |
|--------|-------------|
| **Framework** | CodeIgniter 4 (PHP 8.1+) |
| **จุดประสงค์** | เว็บไซต์คณะวิทยาศาสตร์และเทคโนโลยี: หน้าแรก, ข่าว, หลักสูตร, บริการด้านเอกสาร, ระบบประกาศ/ป๊อปอัป, ใบรับรอง, บาร์โค้ดนักศึกษา, E-Document (Edoc) |
| **ฐานข้อมูล** | MySQL (ใช้ Migrations ใน `app/Database/Migrations/`) |
| **Frontend** | PHP Views + CSS/JS ใน `public/assets/`; ธีมสีจาก `public/assets/css/theme.css` (`--primary`, `--color-primary` เป็นโทนทอง) |
| **Line endings** | ทั่วทั้ง repo ใช้ **LF** (กำหนดใน `.gitattributes`: `* text=auto eol=lf`) |

โฟลเดอร์หลักที่ส่งออกสู่เว็บ: **`public/`** (ต้องชี้ document root ของเว็บเซิร์ฟเวอร์มาที่โฟลเดอร์นี้)

---

## 2. โครงสร้างโฟลเดอร์ (สรุป)

```
newScience/
├── app/
│   ├── Config/          # Routes, Filters, App, Database, ฯลฯ
│   ├── Controllers/     # ควบคุม logic แยกตามกลุ่ม (Admin, Edoc, Student, Api, ...)
│   ├── Database/Migrations/
│   ├── Filters/         # adminauth, adminsystemaccess, studentauth, edocauth, ...
│   ├── Helpers/
│   ├── Libraries/       # AccessControl, OrganizationRoles, ...
│   ├── Models/
│   ├── Views/           # layout, pages, admin, edoc, student, ...
│   └── ThirdParty/      # PHPMailer ฯลฯ
├── public/              # document root ของเว็บ
│   ├── index.php
│   └── assets/          # css, js, images
├── writable/            # uploads, cache, logs, session
├── .env                 # ค่าปรับแต่ง (baseURL, database, API keys)
├── .gitattributes       # บังคับ LF
├── DOCUMENTATION.md     # ไฟล์นี้
├── doc_api.rd           # API ภายนอก (Research Record) ที่ newScience เรียกใช้
└── README.md            # คู่มือ CI4 ทั่วไป
```

---

## 3. การพิสูจน์ตัวตนและสิทธิ์ (Authentication & Authorization)

### 3.1 บทบาทผู้ใช้ (Roles)

- **super_admin** — เข้าถึงทุกระบบใน Admin โดยไม่ต้องมีรายการใน `user_system_access`
- **faculty_admin**, **admin**, **editor** — ได้สิทธิ์ **admin_core** โดยอัตโนมัติ (ข่าว, องค์กร, หลักสูตร, Hero, Events, ดาวน์โหลดคณะ, ป๊อปอัปประกาศด่วน)
- **ผู้ใช้ทั่วไป** — เข้าถึงตามที่กำหนดในตาราง `user_system_access` (ระบบย่อยเป็น slug ในตาราง `systems`)

### 3.2 Library: AccessControl

- **ไฟล์:** `app/Libraries/AccessControl.php`
- **ใช้สำหรับ:** ตรวจสอบและกำหนดสิทธิ์การเข้าถึงระบบย่อย (แทนการเช็ค role/edoc โดยตรง)
- **เมธอดสำคัญ:**
  - `AccessControl::hasAccess(int $uid, string $systemSlug, string $minLevel = 'view'): bool` — เช็คว่า user มีสิทธิ์ระบบนั้นหรือไม่
  - `AccessControl::getUserSystems(int $uid): array` — รายการระบบที่ user เข้าถึงได้
  - `AccessControl::isSuperAdmin(int $uid)`, `AccessControl::isFacultyAdmin(int $uid)`
- **ระบบพิเศษ:**
  - **admin_core** — faculty_admin / admin / editor เข้าได้โดยอัตโนมัติ; ใช้กับข่าว, องค์กร, หลักสูตร, Hero Slides, Events, **downloads**, **urgent-popups**

### 3.3 Filter: AdminSystemAccessFilter

- **ไฟล์:** `app/Filters/AdminSystemAccessFilter.php`
- **ใช้กับ:** route group `admin` (ร่วมกับ `adminauth`)
- **หน้าที่:** แมป URI segment หลัง `admin/` กับ system slug แล้วเรียก `AccessControl::hasAccess`
- **แมป URI → slug (สรุป):**
  - `news` → admin_news (หรือ admin_core)
  - `organization`, `programs`, `hero-slides`, `events` → admin_core
  - `urgent-popups` → admin_urgent_popup (หรือ admin_core)
  - `downloads` → admin_downloads (หรือ admin_core)
  - `users` → user_management
  - `settings` → site_settings
  - `cert-templates`, `cert-events`, `certificates` → ecert
- **utility/** — ใช้สิทธิ์ระบบ `utility`

การเพิ่มเมนูหรือ route ใน Admin ควรสอดคล้องกับ slug ใน `systems` และการแมปใน filter นี้

---

## 4. Filters ที่ใช้บ่อย

| Alias | Class | ใช้กับ |
|-------|--------|--------|
| adminauth | AdminAuthFilter | เข้า Admin ต้องล็อกอิน |
| adminsystemaccess | AdminSystemAccessFilter | ตรวจสิทธิ์ระบบย่อยใน Admin |
| studentauth | StudentAuthFilter | พอร์ทัลนักศึกษา |
| studentadmin | StudentAdminFilter | จัดการบาร์โค้ด (นักศึกษาสโมสร/แอดมิน) |
| programadmin | ProgramAdminFilter | จัดการหลักสูตร (Program Chairs) |
| certapprover | CertApproverFilter | อนุมัติใบรับรอง |
| edocauth | EdocAuthFilter | ระบบ E-Document (Edoc) |
| loggedin | LoggedInFilter | ต้องล็อกอิน (เช่น dashboard) |

กำหนดใน `app/Config/Filters.php`

---

## 5. เส้นทางหลัก (Routes) — สรุป

### 5.1 หน้าสาธารณะ (ไม่ต้องล็อกอิน)

- `/` — หน้าแรก (Home), มีป๊อปอัปประกาศด่วน (urgent popup) สูงสุด 3 รายการ, แสดงอัตโนมัติ 3 วินาที/ข่าว
- `/about`, `/academics`, `/research`, `/campus-life`, `/admission`, `/contact`
- `/news`, `/news/(:num)` — ข่าว
- `/events`, `/events/(:num)` — กิจกรรม
- `/documents` — บริการด้านเอกสาร (แบบฟอร์ม, คำสั่ง/ประกาศ, เกณฑ์ประเมิน, เอกสารภายใน) — แท็บสลับ section
- `/support-documents`, `/official-documents`, `/promotion-criteria`, `/internal-documents` — หน้าแยกตามหมวดเอกสาร
- `/personnel`, `/executives` — บุคลากร
- `/p/(:num)` — หน้า SPA หลักสูตร (program id)
- `/verify/(:segment)` — ตรวจสอบใบรับรอง (สาธารณะ)
- `/personnel-cv/(:segment)` — CV บุคลากร

### 5.2 Admin (`/admin` — ใช้ filter adminauth + adminsystemaccess)

- ล็อกอิน: `/admin/login`, `/admin/logout`, SSO: `/admin/portal-login`, `/admin/oauth-callback`
- **จัดการเนื้อหา:** news, organization, programs, hero-slides, events, **urgent-popups**, **downloads**
- **ผู้ใช้และระบบ:** users, system-access ต่อ user
- **ใบรับรอง:** cert-templates, cert-events, certificates
- **ตั้งค่า:** settings

Route ทั้งหมดอยู่ใน `app/Config/Routes.php` กลุ่ม `$routes->group('admin', ['filter' => ['adminauth', 'adminsystemaccess']], ...)`

### 5.3 พอร์ทัลนักศึกษา (`/student`)

- ล็อกอิน, dashboard, barcodes, events, certificates (ดู/ดาวน์โหลด)

### 5.4 Student Admin (`/student-admin`)

- จัดการบาร์โค้ดและกิจกรรมบาร์โค้ด (barcode-events)

### 5.5 Program Admin (`/program-admin`)

- แก้ไขเนื้อหาเว็บหลักสูตร, downloads หลักสูตร, ข่าวหลักสูตร, activities

### 5.6 Edoc (`/edoc`)

- E-Document: ดูเอกสาร, จัดการแท็ก, volume, การวิเคราะห์, แจ้งเตือน, อัปโหลดไฟล์ (ใช้ filter edocauth)

### 5.7 API ภายใน (`/api/*`)

- ไม่มี prefix `api/public` — สำหรับ AJAX/ internal
- ตัวอย่าง: `api/news`, `api/hero-slides`, `api/events/upcoming`, `api/programs`, `api/settings`, `api/stats`
- กลุ่ม `api/executive/*` ใช้ filter adminauth (Executive Dashboard)

### 5.8 บริการอื่นๆ

- **personnel-api:** `/personnel-api/faculty`, `/personnel-api/faculty/status` — ดึงข้อมูลบุคลากรจาก Research Record API (ดู `doc_api.rd` และ `app/Config/ResearchApi.php`)
- **serve:** `/serve/uploads/(.+)` — serve ไฟล์จาก writable/uploads

---

## 6. ฟีเจอร์หลักและไฟล์ที่เกี่ยวข้อง

| ฟีเจอร์ | Controller / Entry | View / Model (สรุป) |
|--------|---------------------|----------------------|
| หน้าแรก + ป๊อปอัปประกาศด่วน | Home::index | `Views/pages/home.php`, UrgentPopupModel |
| ประกาศด่วน (Admin) | Admin\UrgentPopups | Views/admin/urgent_popups/, UrgentPopupModel |
| บริการด้านเอกสาร (หน้าสาธารณะ) | Pages::documents | Views/pages/documents.php |
| ดาวน์โหลดคณะ (Admin) | Admin\Downloads | DownloadCategoryModel, DownloadDocumentModel; ข้อมูลแสดงใน documents ตาม section |
| ข่าว | Pages::news / Admin\News | NewsModel, NewsImageModel, NewsTagModel |
| หลักสูตร (Program) | ProgramSpaController, Admin\Programs, ProgramAdmin | ProgramModel, ProgramPageModel, ProgramDownloadModel, ProgramActivityModel |
| Hero Slides | Admin\HeroSlides | HeroSlideModel |
| กิจกรรม (Events) | Admin\Events | EventModel |
| ใบรับรอง | Admin\CertTemplates, CertEvents, Approve\Certificate, CertVerify | CertTemplateModel, CertEventModel, CertificateModel, CertApprovalModel |
| บาร์โค้ดนักศึกษา | Student\Dashboard, StudentAdmin\BarcodeEvents | BarcodeModel, BarcodeEventModel |
| E-Document (Edoc) | Edoc\EdocController, Edoc\AdminEdocController | Models/Edoc/* |
| ตั้งค่าไซต์ | Admin\Settings | SiteSettingModel |

---

## 7. ฐานข้อมูลและ Migrations

- Migrations อยู่ที่ `app/Database/Migrations/`
- รัน: `php spark migrate`
- ตารางสำคัญ (สรุป): users, user_system_access, systems, news, news_images, news_tags, hero_slides, **urgent_popups**, download_categories, download_documents, program_*, events, cert_*, page_views, และตารางใน namespace Edoc

การเพิ่มระบบสิทธิ์ใหม่: เพิ่มแถวใน `systems` (slug, name_th, name_en) และแมปใน `AdminSystemAccessFilter::URI_TO_SYSTEM` ถ้าเป็น URI ภายใต้ `admin/`

---

## 8. การตั้งค่า (.env)

- **baseURL** — URL หลักของเว็บ (ชี้ไปที่ public)
- **database** — MySQL (hostname, database, username, password, DBDriver). ใช้ `database.default.*` สำหรับ Local และ `database.server.*` สำหรับ Server (ใช้กับ edoc:upload-title, schema:compare)
- **เปรียบเทียบโครงสร้าง Local กับ Server:** รัน `php spark schema:compare` (ต้องตั้งค่า database.default และ database.server ใน .env) จะแสดงตาราง/คอลัมน์ที่ต่างกัน หรือใช้ `php scripts/export_db_schema.php` แล้วเปรียบเทียบไฟล์ `scripts/schema_local.txt` กับ `scripts/schema_server.txt`
- **API_KEY** — ใช้กับ Public API (ถ้ามี route แบบ api/public ที่ใช้ apikey filter)
- **Research Record API (สำหรับบุคลากร):** RESEARCH_API_BASE_URL, RESEARCH_API_KEY, RESEARCH_API_FACULTY_ID หรือ RESEARCH_API_FACULTY_CODE — ดูรายละเอียดใน `doc_api.rd`

---

## 9. เอกสารอ้างอิงอื่น

- **doc_api.rd** — อธิบาย Public API ของระบบ Research Record ที่ newScience เรียกผ่าน `FacultyPersonnelController` และการตั้งค่า .env ที่เกี่ยวข้อง
- **README.md** — การติดตั้งและความต้องการของ CodeIgniter 4

---

## 10. สรุปสำหรับ AI Agent

1. **แก้ไขสิทธิ์:** ดู `AccessControl` และ `AdminSystemAccessFilter`; ระบบใหม่เพิ่มใน `systems` และแมป URI ใน filter ถ้าเป็น admin.
2. **เพิ่มหน้า/ route ใหม่:** กำหนด route ใน `app/Config/Routes.php` และใช้ filter ให้ตรงกับกลุ่มผู้ใช้ (สาธารณะ / admin / student / edoc).
3. **แก้ไขฟีเจอร์เนื้อหา:** หา Controller และ View ที่แมปจาก Routes; ข้อมูลจาก Model ที่เชื่อมกับตารางที่เกี่ยวข้อง.
4. **Theme / สี:** ใช้ตัวแปร `--primary`, `--color-primary` ใน `public/assets/css/theme.css` เพื่อความสอดคล้องกับไซต์.
5. **Line endings:** โปรเจกต์ใช้ LF; ไม่ควรเปลี่ยนเป็น CRLF ใน repo.
