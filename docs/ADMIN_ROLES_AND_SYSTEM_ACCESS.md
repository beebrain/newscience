# บทบาทผู้ดูแลระบบ (Admin / Faculty Admin) และสิทธิ์การเข้าถึงระบบ (System Access)

**หมายเหตุ:** ตาราง `user_system_access` ใช้ **user_email** เป็นตัวระบุผู้ใช้ ต้องรัน migration ก่อนใช้:
`php spark migrate` (ไฟล์ `AddUserEmailToUserSystemAccess`)

## สรุปความแตกต่าง

| หัวข้อ | Admin (บทบาท) | Faculty Admin (บทบาท) | สิทธิ์ตามระบบ (user_system_access) |
|--------|----------------|------------------------|-------------------------------------|
| **ความหมาย** | บุคลากรที่กำหนดเป็น admin/editor ในระบบ (ตาราง user.role หรือ admin=1) | บุคลากรที่ดูแลหลักสูตรระดับคณะ (ผูกกับ program_id) | สิทธิ์แบบละระบบ: ใครเข้าได้ระบบไหน ระดับ view/manage/admin |
| **ขอบเขต** | จัดการเนื้อหาได้ (ข่าว, หลักสูตร, สไลด์ ฯลฯ) ตามสิทธิ์ระบบ | จัดการได้เฉพาะในหลักสูตรของตัวเอง + จัดการผู้ใช้ในหลักสูตร | แต่ละระบบ (admin_core, edoc, ecert ฯลฯ) เปิดให้ตามที่กำหนดในตาราง |
| **การเข้าใช้งาน** | ต้องมี role เป็น admin / editor / super_admin / faculty_admin ถึงจะเข้าเมนู Admin ได้ แล้วแต่ละเมนูเช็คสิทธิ์ระบบอีกชั้น | เหมือน Admin แต่การจัดการผู้ใช้จำกัดเฉพาะคนใน program_id เดียวกัน | เก็บในตาราง `user_system_access` ใช้ **อีเมล (user_email)** เป็นตัวระบุผู้ใช้ (แทน user_uid) |

---

## 1. บทบาท (Role) ในตาราง `user`

- **super_admin**  
  - เข้าถึงทุกระบบโดยอัตโนมัติ ไม่ต้องมีแถวใน `user_system_access`  
  - จัดการผู้ใช้ได้ทุกคน กำหนดสิทธิ์ระบบให้ใครก็ได้  

- **faculty_admin**  
  - เข้า **admin_core** (ข่าว, องค์กร, หลักสูตร, สไลด์, กิจกรรม) ได้โดยอัตโนมัติ  
  - จัดการผู้ใช้ได้เฉพาะคนที่อยู่ใน **หลักสูตรเดียวกับตัวเอง** (program_id) และไม่ใช่ super_admin  
  - ระบบอื่น (E-Doc, E-Cert, จัดการผู้ใช้ ฯลฯ) ต้องมีสิทธิ์ใน `user_system_access` จึงจะเข้าได้  

- **admin** / **editor**  
  - เข้า **admin_core** ได้โดยอัตโนมัติ  
  - จัดการผู้ใช้ได้ (ยกเว้น super_admin, faculty_admin)  
  - ระบบอื่นต้องมีใน `user_system_access`  

- **user**  
  - ไม่ได้สิทธิ์ admin_core จาก role ต้องให้สิทธิ์ผ่าน **user_system_access** เท่านั้น ถึงจะเข้าแต่ละส่วนได้  

---

## 2. สิทธิ์การเข้าถึงระบบ (ตาราง `user_system_access`)

- ใช้ **อีเมล (user_email)** เป็นตัวระบุผู้ใช้ แทน user_id/uid  
- แต่ละแถว = หนึ่งคน (อีเมล) + หนึ่งระบบ (system_id) + ระดับสิทธิ์ (view / manage / admin)  
- ระดับสิทธิ์: **view** (ดู) < **manage** (จัดการ) < **admin** (ดูแลระดับสูงในระบบนั้น)

### รายการระบบ (systems) ที่ใช้ในระบบ

| slug | ชื่อ (ไทย) | ความหมาย |
|------|------------|----------|
| **admin_core** | ระบบจัดการหลัก | ข่าวสาร, โครงสร้างองค์กร, หลักสูตร, สไลด์, กิจกรรม (admin/news, organization, programs, hero-slides, events) |
| **user_management** | จัดการผู้ใช้ | หน้า admin/users จัดการผู้ใช้และนักศึกษา + กำหนดสิทธิ์ระบบ |
| **site_settings** | ตั้งค่าเว็บไซต์ | หน้า admin/settings |
| **program_admin** | จัดการเว็บหลักสูตร | Content Builder แก้ไขเว็บหลักสูตร (program-admin) |
| **ecert** | ระบบ E-Certificate | กิจกรรม/อบรม, เทมเพลตใบรับรอง, คำขอใบรับรอง (admin/cert-*) |
| **cert_approve** | อนุมัติใบรับรอง | ระบบอนุมัติใบรับรอง (Program Chair / Dean) |
| **student_admin** | จัดการบาร์โค้ด/กิจกรรม | จัดการบาร์โค้ดและกิจกรรมนักศึกษา (student-admin) |
| **edoc** | E-Document (ดูเอกสาร) | ดูเอกสารในระบบสารบรรณ |
| **edoc_admin** | E-Document (จัดการ) | จัดการเอกสารในระบบสารบรรณ |
| **research_record** | จัดการงานวิจัย | ลิงก์ไปยังระบบ Research Record |
| **utility** | เครื่องมือผู้ดูแล | Upload, Import, Categorize News ฯลฯ |

---

## 3. การตรวจสิทธิ์ตอนเข้าใช้งาน

- **Filter `adminauth`**  
  ตรวจว่าเข้าสู่ระบบแล้ว และมี role เป็น admin / editor / super_admin / faculty_admin  

- **Filter `adminsystemaccess`**  
  หลัง adminauth แล้ว ตรวจจาก URI ว่าเป็นระบบไหน (admin_core, user_management, site_settings, ecert ฯลฯ) แล้วเช็คจาก `AccessControl::hasAccess(admin_id, system_slug)`  
  - ถ้าไม่มีสิทธิ์ → redirect ไป dashboard พร้อมข้อความ "คุณไม่มีสิทธิ์เข้าใช้ส่วนนี้"  

- **เมนู sidebar (admin layout)**  
  แสดงเมนูตามสิทธิ์ระบบเท่านั้น (เช่น มีสิทธิ์ admin_core ถึงเห็นข่าว/องค์กร/หลักสูตร ฯลฯ มีสิทธิ์ ecert ถึงเห็น E-Certificate มีสิทธิ์ user_management ถึงเห็นจัดการผู้ใช้)  

- **Program Admin (program-admin)**  
  ต้องมีสิทธิ์ **program_admin** (จาก user_system_access) หรือเป็น super_admin/admin หรือเป็น personnel ที่มีบทบาท chair ในหลักสูตร  

- **E-Document (edoc / edoc/admin)**  
  ใช้ filter `edocauth` เช็คสิทธิ์ระบบ **edoc** / **edoc_admin** ผ่าน AccessControl  

---

## 4. สรุปการทำงานร่วมกัน

- **Admin / Faculty Admin** = บทบาทในตาราง user ใช้ตัดว่า “เข้าโซนแอดมินได้หรือไม่” และให้สิทธิ์พื้นฐาน (เช่น admin_core สำหรับ faculty_admin, admin, editor)  
- **System Access** = กำหนดแบบละระบบว่า “คนนี้เข้าได้ระบบไหน ระดับไหน” ใช้ **อีเมล** ในตาราง `user_system_access`  
- การเข้าแต่ละส่วน (ข่าว, ผู้ใช้, E-Cert, E-Doc ฯลฯ) ต้องผ่านทั้ง role และสิทธิ์ระบบที่ตรงกับเมนู/ฟีเจอร์นั้น จึงจะเข้าใช้งานได้
