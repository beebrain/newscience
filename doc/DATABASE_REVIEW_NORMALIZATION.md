# รายงานตรวจสอบฐานข้อมูล (Database Review & Normalization)

ตรวจสอบ schema ตามไฟล์ `database/complete_schema.sql` เพื่อหาส่วนซ้ำซ้อน (redundancy) และโอกาสในการ normalize เพิ่มเติม

---

## 1. สรุปความสัมพันธ์หลัก

| ตาราง                  | บทบาท                                                        |
| ---------------------- | ------------------------------------------------------------ |
| **user**               | ผู้ใช้ระบบ (admin/editor/user) — clone จาก researchrecord    |
| **site_settings**      | Key-value การตั้งค่าเว็บ                                     |
| **departments**        | แผนก/สาขาวิชา                                                |
| **personnel**          | บุคลากร (อาจารย์, ผู้บริหาร)                                 |
| **programs**           | หลักสูตร                                                     |
| **personnel_programs** | Pivot: บุคลากร ↔ หลักสูตร (หลายต่อหลาย) + บทบาท + is_primary |
| **news**               | ข่าวสาร                                                      |
| **news_images**        | รูปประกอบข่าว                                                |
| **activities**         | กิจกรรม/อัลบั้ม                                              |
| **activity_images**    | รูปกิจกรรม                                                   |
| **links**              | ลิงก์ภายนอก/ภายใน                                            |

---

## 2. ส่วนที่ซ้ำซ้อน (Redundancy)

### 2.1 หลักสูตรหลักของบุคลากร (ซ้ำ 2 ที่)

| ที่เก็บ                           | คำอธิบาย                                                                  |
| --------------------------------- | ------------------------------------------------------------------------- |
| **personnel.program_id**          | หลักสูตรหลัก (deprecated ใน comment — แนะนำให้ใช้ personnel_programs แทน) |
| **personnel_programs.is_primary** | หลักสูตรหลัก = แถวที่ is_primary = 1 (Single Source of Truth ที่ใช้ในแอป) |

**สถานะ:** แอปยังเขียน/อ่าน `personnel.program_id` (Organization controller, PersonnelModel) เพื่อ backward compatibility และ query ง่าย  
**ข้อเสนอ:**

- **ระยะสั้น:** เก็บไว้ทั้งคู่ แต่ให้ `personnel_programs.is_primary` เป็นแหล่งความจริง เวลา save ให้ sync ไป `personnel.program_id` (ทำอยู่แล้ว)
- **ระยะยาว:** เมื่อไม่ต้องรองรับ legacy แล้ว สามารถ **ลบคอลัมน์ personnel.program_id** ได้ และ derive หลักสูตรหลักจาก personnel_programs เท่านั้น

---

### 2.2 ประธานหลักสูตรของหลักสูตร (ซ้ำ 2 ที่)

| ที่เก็บ                         | คำอธิบาย                                                                          |
| ------------------------------- | --------------------------------------------------------------------------------- |
| **programs.coordinator_id**     | ประธานหลักสูตร (deprecated ใน comment)                                            |
| **programs.chair_personnel_id** | ประธานหลักสูตร (ใช้เป็นหลัก)                                                      |
| **personnel_programs**          | บทบาท 'ประธานหลักสูตร' เป็นแหล่งความจริง แล้ว sync ไป programs.chair_personnel_id |

**สถานะ:** แอปอ่านทั้ง `chair_personnel_id` และ `coordinator_id` (fallback) ใน Pages และ ProgramModel  
**ข้อเสนอ:**

- **ระยะสั้น:** เก็บทั้งคู่เพื่อความปลอดภัย
- **ระยะยาว:** เมื่อยืนยันว่าไม่มีที่ไหนอ้างอิง coordinator_id แล้ว **ลบ programs.coordinator_id** ได้

---

### 2.3 สังกัดแผนกของบุคลากร (Denormalization โดยตั้งใจ)

| ที่เก็บ                     | คำอธิบาย                                                    |
| --------------------------- | ----------------------------------------------------------- |
| **personnel.department_id** | แผนกที่สังกัด (ได้จากหลักสูตรหลัก → programs.department_id) |

**สถานะ:** ค่า derive ได้จาก `personnel_programs` (is_primary=1) → `programs.department_id` แต่เก็บใน personnel เพื่อไม่ต้อง join ทุกครั้ง  
**ข้อเสนอ:** **เก็บไว้** — การ denormalize นี้ช่วย performance และการออกแบบถือว่ายอมรับได้ (อัปเดตตอน save อยู่แล้ว)

---

## 3. การตรวจสอบ Normalization (1NF, 2NF, 3NF)

### 3.1 1NF (First Normal Form)

- ค่าทุกคอลัมน์เป็น atomic ไม่มี repeating group
- **personnel.education**, **personnel.expertise** เก็บเป็น JSON (array) — ถ้าต้องการ 1NF เต็มที่อาจแยกเป็นตาราง `personnel_education`, `personnel_expertise` ได้ แต่การเก็บเป็น JSON ก็ใช้ได้และยืดหยุ่น  
  **สรุป:** ผ่าน 1NF ในระดับที่ใช้งานได้

### 3.2 2NF (Second Normal Form)

- ทุกตารางมี PK ชัดเจน
- ไม่มี partial dependency (คอลัมน์ที่ไม่ขึ้นกับ PK ทั้งก้อน)  
  **สรุป:** ผ่าน 2NF

### 3.3 3NF (Third Normal Form)

- **personnel.department_id:** ถือว่าเป็น transitive dependency (ได้จาก program → department) แต่เก็บไว้เพื่อความเร็ว และอัปเดตจากแอปอยู่แล้ว — **ยอมรับได้**
- **programs.chair_personnel_id:** ได้จาก personnel_programs แต่เก็บใน programs เพื่อ query ง่าย — **ยอมรับได้**  
  **สรุป:** ผ่าน 3NF โดยมี denormalization ที่มีเหตุผลและควบคุมได้

---

## 4. ความสัมพันธ์ที่บังคับด้วย FK (ดำเนินการแล้ว)

| คอลัมน์                             | อ้างอิง      | นโยบาย                                              | สถานะ                  |
| ----------------------------------- | ------------ | --------------------------------------------------- | ---------------------- |
| **personnel_programs.personnel_id** | personnel.id | ON DELETE CASCADE                                   | ✅ เพิ่มแล้ว           |
| **personnel_programs.program_id**   | programs.id  | ON DELETE CASCADE                                   | ✅ เพิ่มแล้ว           |
| **personnel.user_uid**              | user.uid     | ON DELETE RESTRICT (ไม่ลบ user มีเพียงถอดถอนสิทธิ์) | ✅ เพิ่มแล้ว           |
| **news.author_id**                  | user.uid     | ON DELETE SET NULL                                  | ✅ เพิ่มแล้ว           |
| **departments.head_personnel_id**   | personnel.id | —                                                   | ยังไม่เพิ่ม (optional) |

Migration: `php scripts/run_add_foreign_keys.php`

---

## 5. โครงสร้างที่คล้ายกัน (ไม่จำเป็นต้องรวม)

### 5.1 news vs activities

ทั้งคู่มีโครงสร้างคล้าย (title, slug, description, featured_image, status, ตารางรูปลูก) แต่เป็นคนละ domain (ข่าว vs กิจกรรม)  
**ข้อเสนอ:** **แยกตารางไว้** — รวมเป็นตาราง content เดียวจะซับซ้อนและไม่ตรงกับ domain

### 5.2 news_images vs activity_images

โครงสร้างเหมือนกัน (parent_id, image_path, caption, sort_order)  
**ข้อเสนอ:**

- **ตัวเลือก A (คงเดิม):** แยกตาราง — query ง่าย FK ชัด
- **ตัวเลือก B (normalize เพิ่ม):** รวมเป็นตาราง `media` หรือ `images` แบบ polymorphic (entity_type + entity_id) — ลดความซ้ำของ schema แต่ query และ FK ซับซ้อนขึ้น  
  **แนะนำ:** เก็บแบบแยกตารางไว้ก่อน

---

## 6. ตาราง user (clone จาก researchrecord)

- มีชื่อ 4 คอลัมน์: **gf_name, gl_name** (อังกฤษ), **tf_name, tl_name** (ไทย) — ถ้าอยากลดความซ้ำอาจรวมเป็น **name_th**, **name_en** ได้ แต่ตารางมาจากระบบอื่น การแก้ต้องระวังความเข้ากันได้  
  **ข้อเสนอ:** ไม่แนะนำแก้โครงสร้าง user ในโปรเจกต์นี้ เว้นแต่เป็นมาตรฐานของทั้ง researchrecord

---

## 7. สรุปข้อเสนอเชิงปฏิบัติ

| ลำดับ | รายการ                                                      | ความเร่งด่วน  | หมายเหตุ                                                        |
| ----- | ----------------------------------------------------------- | ------------- | --------------------------------------------------------------- |
| 1     | ~~เพิ่ม FK สำหรับ personnel_programs~~                      | —             | ✅ ทำแล้ว                                                       |
| 2     | ~~FK personnel.user_uid, news.author_id → user.uid~~        | —             | ✅ ทำแล้ว (RESTRICT / SET NULL)                                 |
| 3     | ลบ personnel.program_id                                     | ต่ำ (ระยะยาว) | หลัง refactor ให้ดึงหลักสูตรหลักจาก personnel_programs เท่านั้น |
| 4     | ลบ programs.coordinator_id                                  | ต่ำ (ระยะยาว) | หลังตัดการอ้างอิงในโค้ดทั้งหมด                                  |
| 5     | แยกตาราง personnel_education / personnel_expertise แทน JSON | เลือกได้      | ถ้าต้องการ query/รายงานตาม education หรือ expertise โดยตรง      |

---

## 8. สรุปภาพรวม

- **ส่วนซ้ำซ้อนหลัก:** หลักสูตรหลักและประธานหลักสูตรเก็บไว้ 2 ที่ (คอลัมน์ deprecated + แหล่งความจริง) เพื่อ backward compatibility — แนะนำให้ค่อยๆ เลิกใช้คอลัมน์ deprecated แล้วลบออกในระยะยาว
- **Normalization:** โครงสร้างผ่าน 1NF–3NF ในระดับที่ใช้งานได้ มีการ denormalize (เช่น personnel.department_id, programs.chair_personnel_id) ที่มีเหตุผลและควบคุมจากแอป
- **การปรับปรุงที่คุ้มค่าที่สุด:** เพิ่ม FK ให้ personnel_programs และ (ถ้าต้องการ) ให้ user_uid / author_id เพื่อความสมบูรณ์ของ schema
