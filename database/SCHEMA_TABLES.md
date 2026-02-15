# โครงสร้างตาราง (Schema) — หลัง Migration organization_units

เอกสารนี้สรุปโครงสร้างตารางที่ใช้ในระบบ **หลังรัน migration organization_units** (มีตาราง `organization_units` ไม่มีตาราง `departments`)

---

## 1. user

บัญชีผู้ใช้ (ลิงก์กับ personnel ผ่าน email / user_uid)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| uid | INT(3) UNSIGNED ZEROFILL, PK, AUTO_INCREMENT | |
| login_uid | VARCHAR(255) | |
| email | VARCHAR(255), UNIQUE | ใช้เชื่อมกับ personnel และ App ภายนอก |
| password | VARCHAR(255) | |
| title | VARCHAR(255) | |
| gf_name, gl_name | VARCHAR(255) | ชื่อ-นามสกุล อังกฤษ |
| tf_name, tl_name | VARCHAR(255) | |
| th_name | VARCHAR(255) | ชื่อ (ไทย) |
| thai_lastname | VARCHAR(255) | นามสกุล (ไทย) |
| role | ENUM('admin','editor','user') | |
| profile_image | VARCHAR(255) | |
| status | ENUM('active','inactive') | |
| created_at, updated_at | DATETIME | |

---

## 2. site_settings

ตั้งค่าเว็บ (ชื่อคณะ, ที่อยู่, hero ฯลฯ)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| setting_key | VARCHAR(100), UNIQUE | |
| setting_value | TEXT | |
| setting_type | ENUM('text','textarea','image','json') | |
| category | VARCHAR(100) | เช่น general, contact, hero |
| created_at, updated_at | DATETIME | |

---

## 3. organization_units

หน่วยงาน 5 กลุ่มคงที่ (แทนตาราง departments)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | TINYINT UNSIGNED, PK | 1=ผู้บริหาร, 2=สำนักงานคณบดี, 3=หน่วยวิจัย, 4=หลักสูตรป.ตรี, 5=หลักสูตรบัณฑิต |
| name_th | VARCHAR(255) | ชื่อหน่วยงาน (ไทย) |
| name_en | VARCHAR(255) | |
| code | VARCHAR(32), UNIQUE | executives, office, research, bachelor, graduate |
| sort_order | TINYINT UNSIGNED | |
| created_at, updated_at | DATETIME | |

**ข้อมูลเริ่มต้น (5 แถว):**
- 1 ผู้บริหาร (executives)
- 2 สำนักงานคณบดี (office)
- 3 หัวหน้าหน่วยการจัดการงานวิจัย (research)
- 4 หลักสูตรระดับปริญญาตรี (bachelor)
- 5 หลักสูตรระดับบัณฑิตศึกษา (graduate)

---

## 4. personnel

บุคลากรคณะ (อาจารย์, เจ้าหน้าที่, ผู้บริหาร)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| name | VARCHAR(255) | ชื่อ-นามสกุล (ไทย) |
| name_en | VARCHAR(255) | |
| position | VARCHAR(255) | ตำแหน่งในโครงสร้าง (คณบดี, ประธานหลักสูตร ฯลฯ) |
| position_en | VARCHAR(255) | |
| position_detail | VARCHAR(255) | รายละเอียดตำแหน่ง เช่น ฝ่ายกิจกรรมนักศึกษา |
| academic_title | VARCHAR(255) | คำนำหน้าชื่อไทย (ดร., ผู้ช่วยศาสตราจารย์ ฯลฯ) |
| academic_title_en | VARCHAR(255) | คำนำหน้าชื่ออังกฤษ |
| organization_unit_id | TINYINT UNSIGNED, FK → organization_units.id | หน่วยงานสังกัด (กำหนดจากสาขาหลักหรือตั้งตรง) |
| program_id | INT UNSIGNED, FK → programs.id | Deprecated: ใช้ personnel_programs.is_primary แทน |
| email | VARCHAR(255), UNIQUE | เชื่อมกับ user |
| user_uid | INT UNSIGNED, FK → user.uid | ลิงก์บัญชี user |
| phone | VARCHAR(50) | |
| image | VARCHAR(255) | รูปโปรไฟล์ (fallback ถ้า user ไม่มี) |
| bio, bio_en | TEXT | |
| education, expertise | TEXT | |
| sort_order | INT | |
| status | ENUM('active','inactive') | |
| created_at, updated_at | DATETIME | |

**หมายเหตุ:** ไม่มี `department_id` (ยกเลิกแล้ว ใช้ `organization_unit_id` เท่านั้น)

---

## 5. programs

หลักสูตร (สาขา) — สังกัดหน่วยงานหลักสูตรป.ตรี (4) หรือบัณฑิต (5)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| name_th | VARCHAR(255) | ชื่อหลักสูตร (ไทย) |
| name_en | VARCHAR(255) | |
| degree_th | VARCHAR(100) | เช่น วิทยาศาสตรบัณฑิต |
| degree_en | VARCHAR(100) | |
| level | ENUM('bachelor','master','doctorate') | |
| organization_unit_id | TINYINT UNSIGNED, FK → organization_units.id | 4=ป.ตรี, 5=บัณฑิต |
| description | TEXT | |
| description_en | TEXT | |
| credits | INT | |
| duration | VARCHAR(50) | เช่น 4 ปี |
| website | VARCHAR(500) | |
| curriculum_file | VARCHAR(255) | |
| image | VARCHAR(255) | |
| coordinator_id | INT UNSIGNED | Deprecated |
| chair_personnel_id | INT UNSIGNED, FK → personnel.id | ประธานหลักสูตร |
| sort_order | INT | |
| status | ENUM('active','inactive') | |
| created_at, updated_at | DATETIME | |

**หมายเหตุ:** ไม่มี `department_id` (ยกเลิกแล้ว ใช้ `organization_unit_id` เท่านั้น)

---

## 6. personnel_programs

บุคลากร ↔ หลักสูตร (หลายหลักสูตรต่อคน + บทบาทในหลักสูตร)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| personnel_id | INT UNSIGNED, FK → personnel.id | |
| program_id | INT UNSIGNED, FK → programs.id | |
| role_in_curriculum | VARCHAR(100) | ประธานหลักสูตร, กรรมการหลักสูตร, อาจารย์ประจำหลักสูตร |
| is_primary | TINYINT(1) | 1 = หลักสูตรหลัก (ใช้กำหนด organization_unit_id ของ personnel) |
| sort_order | INT | |
| created_at | DATETIME | |
| updated_at | DATETIME | |

UNIQUE(personnel_id, program_id)

---

## 7. news

ข่าวประชาสัมพันธ์

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| title | VARCHAR(500) | |
| slug | VARCHAR(500), UNIQUE | |
| content | TEXT | |
| excerpt | VARCHAR(1000) | |
| status | ENUM('draft','published') | |
| featured_image | VARCHAR(255) | |
| author_id | INT(3) UNSIGNED ZEROFILL, FK → user.uid | |
| view_count | INT | |
| published_at | DATETIME | |
| display_as_event | TINYINT(1) | แสดงในหน้าประกาศกิจกรรม (ถ้ามีคอลัมน์) |
| created_at, updated_at | DATETIME | |

---

## 8. news_images

รูปประกอบข่าว (หลายรูปต่อข่าว)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| news_id | INT UNSIGNED, FK → news.id | |
| image_path | VARCHAR(255) | |
| caption | VARCHAR(500) | |
| sort_order | INT | |
| created_at | DATETIME | |

---

## 9. news_tags

ชนิดข่าว (tag) เช่น ข่าวทั่วไป, กิจกรรมนักศึกษา

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| name | VARCHAR(100) | ชื่อแสดง |
| slug | VARCHAR(100), UNIQUE | ใช้ใน URL/API |
| sort_order | INT | |
| created_at | DATETIME | |

---

## 10. news_news_tags

ข่าว ↔ tag (many-to-many)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| news_id | INT UNSIGNED, FK → news.id | PK |
| news_tag_id | INT UNSIGNED, FK → news_tags.id | PK |
| created_at | DATETIME | |

---

## 11. events

กิจกรรม/กำหนดการ (ถ้ารัน add_events_table แล้ว)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| title | VARCHAR(500) | |
| slug | VARCHAR(500), UNIQUE | |
| excerpt | TEXT | |
| content | TEXT | |
| event_date | DATE | |
| event_time | TIME | |
| event_end_date | DATE | |
| event_end_time | TIME | |
| location | VARCHAR(255) | |
| featured_image | VARCHAR(255) | |
| status | ENUM('draft','published') | |
| sort_order | INT | |
| author_id | INT(3) UNSIGNED ZEROFILL, FK → user.uid | |
| created_at, updated_at | DATETIME | |

---

## 12. hero_slides

สไลด์หน้าแรก (ถ้ารัน hero_slides.sql แล้ว)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| title | VARCHAR(255) | |
| subtitle | VARCHAR(255) | |
| description | TEXT | |
| image | VARCHAR(500) | |
| link | VARCHAR(500) | |
| link_text | VARCHAR(100) | |
| show_buttons | TINYINT(1) | |
| sort_order | INT | |
| is_active | TINYINT(1) | |
| start_date, end_date | DATETIME | |
| created_at, updated_at | DATETIME | |

---

## 13. activities / activity_images

กิจกรรมและรูปกิจกรรม (จาก complete_schema)

| ตาราง | หมายเหตุ |
|-------|----------|
| activities | title, slug, description, activity_date, location, featured_image, status |
| activity_images | activity_id, image_path, caption, sort_order |

---

## 14. links

ลิงก์ด่วน (เมนู/ฟุตเตอร์)

| คอลัมน์ | ประเภท | หมายเหตุ |
|--------|--------|----------|
| id | INT UNSIGNED, PK | |
| title | VARCHAR(255) | |
| url | VARCHAR(500) | |
| category | VARCHAR(100) | |
| icon | VARCHAR(100) | |
| target | ENUM('_self','_blank') | |
| sort_order | INT | |
| status | ENUM('active','inactive') | |
| created_at, updated_at | DATETIME | |

---

## ความสัมพันธ์หลัก (หลัง migration)

```
organization_units (1–5)
    ↑
    ├── personnel.organization_unit_id  (หน่วยงานสังกัด)
    └── programs.organization_unit_id   (หลักสูตรป.ตรี=4, บัณฑิต=5)

personnel ←→ programs  ผ่าน personnel_programs (หลายหลักสูตร + บทบาท + is_primary)
personnel.user_uid → user.uid
programs.chair_personnel_id → personnel.id
```

**ตารางที่ยกเลิกแล้ว:** `departments` (ใช้ `organization_units` แทน)

**Migration ที่ใช้:** `database/migration_organization_units_full.sql` (สร้าง organization_units, เพิ่ม organization_unit_id ใน programs/personnel, ลบ department_id และตาราง departments)
