# Flow การทำงานของระบบ E-Document (Edocument)

ระบบ E-Document เป็นระบบสารบรรณอิเล็กทรอนิกส์ของคณะวิทยาศาสตร์และเทคโนโลยี ใช้สำหรับลงทะเบียน/จัดการเอกสาร และแจ้งเตือนผู้ที่เกี่ยวข้องผ่านอีเมล

---

## 1. โครงสร้างสิทธิ์และการเข้าใช้งาน

| สิทธิ์ระบบ (slug) | ชื่อ | การใช้งาน |
|-------------------|------|-----------|
| **edoc** | E-Document (ดูเอกสาร) | ดูเอกสารที่ตัวเองเป็นเจ้าของ หรือถูก tag ด้วยอีเมล/ชื่อ หรือ participant เป็น "ทุกคน" |
| **edoc_admin** | E-Document (จัดการ) | จัดการเอกสารทั้งหมด, สร้าง/แก้ไข/ลบ, จัดการ Tag Groups, จัดการ Volume (เล่มปี) |

- **Filter:** `EdocAuthFilter` (ชื่อ filter: `edocauth`)
  - ตรวจว่า `admin_logged_in` และมี `admin_id`
  - ถ้า path มี `edoc/admin` → ต้องมีสิทธิ์ `edoc_admin`
  - ถ้า path เป็น `edoc` (ไม่ใช่ admin) → ต้องมีสิทธิ์ `edoc`
  - ไม่มีสิทธิ์ → redirect ไป `dashboard` พร้อมข้อความ error

- **Base:** Controller ทั้งหมดสืบทอด `EdocBaseController` ซึ่งดึง `edocUser`, `isEdocAdmin` จาก session และ `AccessControl::hasAccess()`.

---

## 2. Flow การทำงานหลัก

### 2.1 ผู้ใช้ทั่วไป (มีสิทธิ์ edoc) — ดูเอกสาร

```
[Login Admin] → [Dashboard] → คลิกเมนู "E-Document (ดูเอกสาร)"
    → GET /edoc  (EdocController::index → showAllDoc)
    → ตรวจสอบชื่อ-นามสกุล (thai_name, thai_lastname) ถ้าไม่มี → redirect พร้อม error
    → โหลดรายการเอกสาร: edoctitleModel->getsummaryPaper(), volumeModel->getAvailableYears()
    → แสดง View: edoc/documents/showEdoc
```

**การดึงรายการเอกสาร (DataTable):**

```
POST /edoc/getdoc (EdocController::getDoc)
    → กรองเอกสารที่ user มีสิทธิ์ดู:
        - ถูก tag ใน edoc_document_tags ตาม email (docTagModel->getDocumentIdsByEmail)
        - หรือ owner = ชื่อ user
        - หรือ participant มีชื่อ user หรือ "ทุกคน"
    → กรองตาม volume_id / doc_year (ถ้ามี)
    → ค้นหา/เรียง/แบ่งหน้า ตาม request
    → ส่งกลับ JSON (draw, recordsTotal, recordsFiltered, records)
```

**เปิดดูรายละเอียดเอกสาร + บันทึกการดู:**

```
POST /edoc/getdocinfo (iddoc)
    → EdocController::getDocInfo
    → edoctitleModel->getDocInfo(iddoc)
    → parseFileAddressForRead(fileaddress) → fileaddress_first, fileaddress_list
    → documentViews->recordView(iddoc, userId)
    → getDocumentViewStats(iddoc)
    → return JSON (result + view_statistics)
```

**ดู PDF:**

```
GET /edoc/viewPDF/{id} [?subfile=ชื่อไฟล์] [?file=true]
    → EdocController::viewPDF
    → หาเอกสารจาก iddoc, parse fileaddress
    → ถ้า file=true → ส่ง binary ไฟล์ (Content-Disposition: inline)
    → ไม่ใช่ → แสดง view pdfviewer (iframe โหลด URL ที่มี file=true)
```

**ดูรายชื่อผู้ที่เคยเปิดเอกสาร:**

```
POST /edoc/getallviewers (iddoc)
    → DocumentViewModel join user → รายชื่อผู้ดู + viewed_at
```

---

### 2.2 ผู้ดูแล (edoc_admin) — จัดการเอกสาร

```
[Login Admin] → [Dashboard] → คลิกเมนู "E-Document (จัดการ)"
    → GET /edoc/admin  (AdminEdocController::index → showAllDoc)
    → ตรวจ isEdocAdmin; ไม่มีสิทธิ์ → redirect ไป /edoc
    → โหลด suggestname (EdoctagModel), availableYears, volumes
    → แสดง View: edoc/documents/admin_documents
```

**ดึงรายการเอกสาร (Admin):**

```
POST /edoc/admin/getdoc
    → ไม่กรองตาม user — แสดงทุกเอกสาร
    → มี column view_count (จาก document_views)
    → ปุ่ม: ดูรายละเอียด, แก้ไข, สถิติ
```

**ดู/แก้ไขรายละเอียดเอกสาร:**

```
POST /edoc/admin/getdocinfo (iddoc)  → คล้าย getdocinfo แต่ไม่กรองสิทธิ์ (เพราะเป็น admin)
POST /edoc/admin/savedoc
    → รับ form: iddoc, officeiddoc, title, datedoc, doctype, owner, participant, fileaddress, tag_emails, ...
    → normalizeFileAddress(fileaddress) → JSON array
    → ถ้าไม่มี iddoc → insertdoc; มี → updatedoc
    → ถ้ามี tag_emails → docTagModel->setDocumentTags(iddoc, emailData)
    → อัปเดต participant ด้วย (backward compatibility)
```

**จัดการ Tag Groups (กลุ่มอีเมลสำหรับ tag เอกสาร):**

```
GET  /edoc/admin/gettaggroups       → TagGroupModel->getAll()
POST /edoc/admin/savetaggroup       → name, tags, id → saveGroup
POST /edoc/admin/deletetaggroup     → id → deleteGroup
```

**จัดการ Volume (เล่มปี):**

```
GET  /edoc/admin/volumes?year=YYYY  → getVolumeDocCounts(year)
GET  /edoc/admin/volumes/years     → getAvailableYears()
POST /edoc/admin/volumes/create-year → year → createYearVolumes(year)
POST /edoc/admin/volumes/toggle    → id → toggleActive(id)
```

**แนะนำอีเมลสำหรับ tag:**

```
GET /edoc/admin/suggest-emails?q=คำค้น  → docTagModel->searchTaggableEmails(q, 20)
GET /edoc/admin/document-tags?iddoc=...  → docTagModel->getDocumentTags(iddoc)
```

---

### 2.3 อัปโหลดไฟล์เอกสาร

```
POST /edoc/upload/edoc (multipart: file)
    → EdocUploadController::uploadFileEdoc
    → validate: pdf, doc, docx, jpg, jpeg, png; max 102400 KB
    → บันทึกไปที่ getEdocDocumentPath() = WRITEPATH . 'edoc_documents/'
    → ชื่อไฟล์: Ymd_His_<random>.ext
    → return JSON: status, msg, filename
```

ไฟล์ที่อัปโหลดจะถูกอ้างอิงในฟิลด์ `fileaddress` ของเอกสาร (เก็บเป็น JSON array ของชื่อไฟล์ หรือ comma-separated ใน legacy).

---

### 2.4 การแจ้งเตือนเอกสาร (อีเมล)

**ดึงข้อมูลการแจ้งเตือน (สำหรับวันใดวันหนึ่ง):**

```
GET /edoc/notifications
GET /edoc/notifications/{date}
    → GeneralController::getDocumentNotificationsData
    → getDocumentsByDate(date) จาก edoctitle โดย DATE(regisdate) = date
    → สำหรับแต่ละ user (active): ตรวจว่าเอกสารไหนที่ user เกี่ยวข้อง
        - participant มี "ทุกคน" → เอกสารสำหรับทุกคน
        - หรือ edoc_document_tags มี email ของ user
        - หรือ participant มีชื่อ user
        - หรือ owner = ชื่อ user
    → สร้าง access token (generateDocumentAccessToken) ต่อ (user_id, iddoc)
    → return JSON: documents, recipients (รายการเอกสารแยกตาม email)
```

**ส่งอีเมลแจ้งเตือน:**

```
GET /edoc/send-notifications  (หรือส่งสำหรับวันนั้น)
    → GeneralController::sendDocumentNotifications(date)
    → logic เดียวกับ getDocumentNotificationsData เพื่อแบ่งเอกสารตาม user
    → สำหรับแต่ละ recipient: สร้าง HTML + text content, subject "แจ้งเตือนเอกสารใน Edocument ประจำวันที่ ..."
    → แต่ละเอกสารมี link: base_url("edoc/public/secure-access?token=" . token)
    → SendmailModel->sendMailHTML(...)
    → return JSON: status, total_documents, date, recipients[email] = { status, message, documents_count }
```

---

### 2.5 การเข้าดูเอกสารผ่านลิงก์ในอีเมล (ไม่ต้อง Login)

```
GET /edoc/public/secure-access?token=...
    → GeneralController::secureAccess  (route สาธารณะ ไม่ผ่าน filter edocauth)
    → decode token (base64 + AES decrypt ด้วย key "Sci_edoc")
    → ตรวจ user_id, doc_id, expires; ถ้าหมดอายุ → 403
    → โหลด document, user
    → ตรวจสิทธิ์: tag ด้วย email / participant "ทุกคน" / owner / participant มีชื่อ
    → ตั้ง session ชั่วคราว: temp_user_id, temp_access_token
    → parseFileAddressForView(document)
    → แสดง view: edoc/documents/document_view (พร้อมปุ่มดู PDF ผ่าน token/session)
```

การดู PDF หลังจากเข้าผ่าน secure-access จะต้องใช้ session ที่ตั้งไว้ หรือใช้ลิงก์ viewPDF ที่ต้อง login — ขึ้นกับ implementation ใน document_view (ถ้ามีการส่ง token ไปด้วยจะใช้ secure URL ได้).

---

### 2.6 Document Analysis (Dashboard สถิติ)

```
GET /edoc/analysis
    → DocumentAnalysisController::index
    → แสดง view: edoc/analysis/document_dashboard

API (ใช้ใน dashboard):
  GET /edoc/api/summary-metrics      → สรุปจำนวนเอกสาร, จำนวนหน้า, ฯลฯ (กรองตามสิทธิ์: admin เห็นทั้งหมด, user เห็นแค่ที่เกี่ยวข้อง)
  GET /edoc/api/doc-type-distribution
  GET /edoc/api/monthly-trend
  GET /edoc/api/top-owners
  GET /edoc/api/page-distribution
  GET /edoc/api/advanced-analytics
  GET /edoc/api/export-report
```

---

### 2.7 Diagnostic (พัฒนาระบบ)

```
GET /edoc/diagnostic/checkfile?file=...  หรือ  /edoc/diagnostic/checkfile/{filename}
GET /edoc/diagnostic/listfiles
    → DiagnosticController — ใช้ตรวจสอบ path ไฟล์และรายการไฟล์ในโฟลเดอร์เอกสาร
```

---

## 3. โครงสร้างตาราง/โมเดลที่เกี่ยวข้อง

| โมเดล | ตาราง | บทบาท |
|--------|--------|--------|
| EdoctitleModel | edoctitle | ข้อมูลเอกสาร (iddoc, officeiddoc, title, doctype, owner, participant, fileaddress, volume_id, doc_year, ...) |
| DocumentViewModel | document_views | บันทึกการเปิดดู (document_id, user_id, viewed_at) |
| EdocDocumentTagModel | edoc_document_tags | Tag เอกสารด้วยอีเมล (document_id, tag_email, source_table) |
| EdoctagModel | edoctag | ชื่อสำหรับ suggest (first_name, last_name) |
| TagGroupModel | tag_groups | กลุ่ม tag (ชื่อ + รายการ tags) |
| EdocVolumeModel | edoc_volumes | เล่มปี (ปี, เล่มที่ 1–5, active) |
| SendmailModel | - | ส่งอีเมล (sendMailHTML) |

---

## 4. สรุป Flow แบบย่อ

1. **เข้าใช้งาน:** Login → ตรวจสิทธิ์ edoc/edoc_admin ผ่าน EdocAuthFilter และ AccessControl.
2. **ผู้ใช้ดูเอกสาร:** เปิด /edoc → โหลดรายการที่ตัวเองมีสิทธิ์ (tag/owner/participant) → getdocinfo → ดู PDF ผ่าน viewPDF.
3. **Admin จัดการ:** เปิด /edoc/admin → getdoc ทั้งหมด → getdocinfo/savedoc, จัดการ tag, volume, suggest emails.
4. **อัปโหลดไฟล์:** POST /edoc/upload/edoc → เก็บใน edoc_documents → ใส่ชื่อไฟล์ใน field fileaddress ตอน save เอกสาร.
5. **แจ้งเตือน:** getDocumentNotificationsData / sendDocumentNotifications → แบ่งเอกสารตาม user → สร้าง token → ส่งอีเมลพร้อมลิงก์ secure-access.
6. **เปิดจากอีเมล:** คลิกลิงก์ secure-access?token=... → decode token → ตรวจสิทธิ์ → แสดง document_view (ไม่ต้อง login).

---

*เอกสารนี้สรุปจากโค้ดใน `app/Controllers/Edoc/`, `app/Models/Edoc/`, `app/Filters/EdocAuthFilter.php` และ `app/Config/Routes.php`*
