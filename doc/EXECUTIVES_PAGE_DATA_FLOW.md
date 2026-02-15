# การดึงข้อมูลหน้า โครงสร้างผู้บริหาร (/executives)

## Route

- **URL:** `/executives`
- **Controller:** `Pages::executives()`

## ขั้นตอนการดึงข้อมูล

### 1. ข้อมูลร่วม (Common)

- `getCommonData()` → settings, site_info, layout

### 2. บุคลากรหลัก (คณบดี, รองคณบดี, ผู้ช่วยคณบดี, หัวหน้าหน่วย)

- **แหล่งข้อมูล:** `PersonnelModel::getActiveWithDepartment()`
  - JOIN `user` (left) → ชื่อจาก `user.th_name`/`user.thai_name` + `user.thai_lastname`
  - JOIN `departments` (left)
  - WHERE `personnel.status` = 'active'
  - ORDER BY departments.sort_order, personnel.sort_order
- **เสริม:** `enrichPersonnelWithProgramRoles($personnel)` → เพิ่ม programs_list_tags จากตาราง personnel_programs
- **จัดกลุ่ม:** `groupPersonnelByPositionTier($personnel)` ตาม `personnel.position`:
  - **Tier 1:** คณบดี (position มี "คณบดี" ไม่มี "รอง"/"ผู้ช่วย")
  - **Tier 2:** รองคณบดี (มี "รอง" + "คณบดี")
  - **Tier 3:** ผู้ช่วยคณบดี (มี "ผู้ช่วย" + "คณบดี")
  - **Tier 4:** อื่นๆ (อาจารย์, ประธานหลักสูตร, ผู้อำนวยการ ฯลฯ)

### 3. หัวหน้าหน่วย / ผู้อำนวยการ (Tier 4 ย่อย)

- กรองจาก Tier 4 เฉพาะที่ position/position_detail/position_en มีคำว่า: หัวหน้าหน่วย, ผอ., ผู้อำนวยการ, director
- แสดงในส่วน "หัวหน้าหน่วย / ผู้อำนวยการสำนักงาน"

### 4. ประธานหลักสูตร

- **แหล่งข้อมูล:** `buildProgramChairItemsFromCoordinators()`
  - โหลดหลักสูตร: `ProgramModel::getWithDepartment()`
  - ระบุประธานจาก (ตามลำดับ):
    1. `programs.chair_personnel_id`
    2. `programs.coordinator_id` (ถ้ามีคอลัมน์)
    3. ตาราง `personnel_programs` ที่ `role_in_curriculum` มี "ประธาน"
  - **โหลด personnel ประธาน:** `PersonnelModel::getActiveByIdsWithUser($chairIds)`  
    → JOIN user เพื่อให้ชื่อเป็น thai_name + thai_lastname
  - ผลลัพธ์: รายการ `['program_name' => ..., 'person' => personnel row]`

## การแสดงชื่อ

- **คณบดี/รอง/ผู้ช่วย/หัวหน้าหน่วย:** ใช้ `$p['name']` จากผล query ที่ join user แล้ว (COALESCE จาก user.th_name/thai_name + thai_lastname)
- **ประธานหลักสูตร:** ใช้ `$person['name']` จาก `getActiveByIdsWithUser()` ซึ่ง join user แล้ว เช่นกัน

## ตารางที่เกี่ยวข้อง

| ตาราง              | ใช้สำหรับ                                                      |
| ------------------ | -------------------------------------------------------------- |
| personnel          | บุคลากร, position, department_id, user_uid, image              |
| user               | ชื่อ (th_name/thai_name + thai_lastname), email, profile_image |
| departments        | ชื่อแผนก                                                       |
| programs           | หลักสูตร, chair_personnel_id, coordinator_id                   |
| personnel_programs | บทบาทในหลักสูตร (ประธานหลักสูตร ฯลฯ)                           |
