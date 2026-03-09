# การวิเคราะห์ edoctitle: ย้ายโครงสร้างจาก sci-edoc ไป newScience

## 1. โครงสร้างตาราง

### 1.1 sci-edoc (ต้นทาง)

ตาราง `edoctitle` ในฐาน sci-edoc โดยทั่วไปมีคอลัมน์ดังนี้ (จากคำสั่ง `import:edoc`):

| คอลัมน์      | ประเภท        | หมายเหตุ |
|-------------|---------------|----------|
| iddoc       | int unsigned  | PK, AUTO_INCREMENT |
| officeiddoc | varchar(255)  | เลขที่หนังสือ |
| title       | text          | ชื่อเรื่อง |
| datedoc     | varchar(100)  | วันที่ลงหนังสือ |
| doctype     | varchar(100)  | ชนิดเอกสาร |
| owner       | text          | เจ้าของ (ชื่อคน) |
| participant | text          | ผู้มีส่วนร่วม (ชื่อคน คั่นด้วย comma หรือ "ทุกคน") |
| fileaddress | text          |  path/JSON ของไฟล์แนบ |
| userid      | varchar(255)  | |
| pages       | int           | จำนวนหน้า |
| copynum     | int           | จำนวนชุด |
| order       | varchar(100) | ลำดับ/หมายเหตุ (เก็บเป็นข้อความ) |
| regisdate   | datetime      | วันที่ลงทะเบียน |

- sci-edoc **อาจไม่มี** `volume_id`, `doc_year` (ขึ้นกับเวอร์ชันต้นทาง)

### 1.2 newScience (ปลายทาง)

ตาราง `edoctitle` ใน newScience มีคอลัมน์เหมือน sci-edoc และเพิ่ม:

| คอลัมน์    | ประเภท       | หมายเหตุ |
|-----------|--------------|----------|
| volume_id | int unsigned | FK ไป edoc_volumes, สำหรับจัดเล่มตามปี |
| doc_year  | int unsigned | ปีของเอกสาร (พ.ศ. หรือ ค.ศ.) |

- Index: `idx_volume_id`, `idx_doc_year`
- โครงสร้างเต็ม:  
  `iddoc, volume_id, doc_year, officeiddoc, title, datedoc, doctype, owner, participant, fileaddress, userid, pages, copynum, order, regisdate`

### 1.3 ตารางที่เกี่ยวข้องใน newScience

- **edoc_volumes** – เล่มเอกสารแยกตามปี
- **edoc_document_tags** – ผู้มีส่วนร่วมแบบอิง email (document_id, tag_email)
- **edoctag** – ข้อมูล tag/ผู้ใช้ (มีคอลัมน์ email)
- **document_views** – สถิติการเปิดดู

---

## 2. การแมปข้อมูลและความต่างที่สำคัญ

| รายการ        | sci-edoc              | newScience |
|---------------|------------------------|------------|
| participant   | ข้อความชื่อคน (หรือ "ทุกคน") | ยังเก็บใน edoctitle.participant + สร้างแถวใน edoc_document_tags (tag_email) |
| volume_id     | ไม่มี                  | เติมจาก regisdate/datedoc ผ่านคำสั่ง migrate |
| doc_year      | ไม่มี                  | เติมจาก regisdate/datedoc ผ่านคำสั่ง migrate |

---

## 3. ขั้นตอนย้ายข้อมูล (แนะนำ)

1. **โคลน sci-edoc ลง local**
   ```bash
   php spark db:clone-to-local edocserver edoclocal
   ```
   - ใช้ค่าใน `.env`: `database.edocserver.*` → `database.edoclocal.*`

2. **นำเข้า edoctitle จาก sci-edoc เข้า newScience**
   - ใช้คำสั่งที่มีอยู่:
     ```bash
     php spark import:edoc
     ```
   - ปัจจุบัน `import:edoc` ต่อกับ DB ชื่อ `sci-edoc` (จาก config ที่ override ในคำสั่ง)  
     ต้องให้ชี้ไปที่ connection **edoclocal** (หรือ DB ชื่อ sci-edoc บน local)  
   - คอลัมน์ที่ insert: iddoc, officeiddoc, title, datedoc, doctype, owner, participant, fileaddress, userid, pages, copynum, order, regisdate  
   - **ไม่มีการใส่** volume_id, doc_year ตอน import (จะเติมในขั้นตอนที่ 3)

3. **เติม volume_id / doc_year และย้าย participant → edoc_document_tags**
   ```bash
   php spark edoc:migrate-to-new-structure
   ```
   - เติม `volume_id`, `doc_year` จาก regisdate/datedoc (และสร้าง edoc_volumes ถ้ายังไม่มี)
   - แปลง participant (ชื่อคน / "ทุกคน") เป็นแถวใน `edoc_document_tags` (tag_email) และอาจอัปเดต participant ใน edoctitle เป็นรายการ email

4. **(ถ้าต้องการ) วิเคราะห์โครงสร้างก่อน/หลัง**
   ```bash
   php spark edoc:analyze-title-migration edoclocal default
   ```
   - ดูคอลัมน์และจำนวนแถวของ edoctitle ในกลุ่มที่ระบุ (เช่น edoclocal = sci-edoc, default = newScience)

---

## 4. หมายเหตุสำหรับ Import

- คำสั่ง `import:edoc` ปัจจุบัน hardcode ใช้ `$sourceConfig->default['database'] = 'sci-edoc'`  
  ถ้า sci-edoc อยู่ที่ connection อื่น (เช่น edoclocal) ต้องแก้ให้ใช้ config ของกลุ่มนั้น (เช่น อ่านจาก `database.edoclocal.*` ใน .env) แล้ว connect ไปที่ DB นั้นก่อน query/insert
- หลัง import แล้วต้องรัน `edoc:migrate-to-new-structure` ทุกครั้งเพื่อให้ได้ volume_id, doc_year และ edoc_document_tags ที่ถูกต้อง
