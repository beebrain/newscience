# Workflow: การจัดการข้อมูลประธานหลักสูตร (Program Chair)

เอกสารนี้อธิบาย workflow การอัปเดตข้อมูลประธานหลักสูตรและกฎซิงค์ระหว่าง 3 จุดเก็บข้อมูล เพื่อลดข้อผิดพลาดและป้องกันข้อมูลซ้ำซ้อนเมื่อมีการแก้ไข

---

## 1. แหล่งข้อมูลที่เกี่ยวข้อง

| ที่เก็บ | ตาราง/ฟิลด์ | ความหมาย |
|--------|----------------|----------|
| **A** | `personnel.position` | ตำแหน่งหลักของบุคลากร (เช่น คณบดี, ประธานหลักสูตร, อาจารย์) |
| **B** | `personnel.position_detail` | รายละเอียดเพิ่ม (เช่น ชื่อหลักสูตรของประธานหลักสูตร) |
| **C** | `personnel_programs.role_in_curriculum` | บทบาทของบุคลากรในแต่ละหลักสูตร (ประธานหลักสูตร, อาจารย์ ฯลฯ) |
| **D** | `programs.coordinator_id` | บุคลากรที่ดำรงตำแหน่งประธานหลักสูตรของหลักสูตรนั้น (FK → personnel.id) |

- **A, B** = แสดงในหน้าบุคลากร/โครงสร้างองค์กร (ตำแหน่งคน)
- **C** = ความสัมพันธ์คน–หลักสูตร (คนหนึ่งอาจเป็นประธานหลายหลักสูตรได้)
- **D** = หลักสูตรชี้ไปที่ประธานหนึ่งคน (หนึ่งหลักสูตรมีประธานหนึ่งคน)

---

## 2. จุดที่แก้ไขข้อมูล (Admin)

### 2.1 หน้าโครงสร้างองค์กร (Admin → Organization)

- **สร้าง/แก้ไขบุคลากร**: กำหนด `position`, `position_detail` และรายการหลักสูตร + `role_in_curriculum` (program_assignments)
- **กฎตรวจสอบ**: ถ้า `position` = "ประธานหลักสูตร" ต้องมีอย่างน้อย 1 หลักสูตรที่ `role_in_curriculum` = "ประธานหลักสูตร"
- **หลังบันทึก**:
  1. บันทึก `personnel` และ `personnel_programs` ตามฟอร์ม
  2. **ซิงค์ D**: สำหรับทุกหลักสูตรที่บุคลากรนี้มีบทบาท "ประธานหลักสูตร" → ตั้ง `programs.coordinator_id = บุคลากรนี้`  
     สำหรับหลักสูตรที่เคยชี้มาที่บุคลากรนี้แต่ไม่มีบทบาทประธานในฟอร์มแล้ว → ตั้ง `programs.coordinator_id = null`

### 2.2 หน้าจัดการหลักสูตร (Admin → Programs)

- **แก้ไขหลักสูตร**: เลือกประธานหลักสูตรจาก dropdown (`coordinator_id`)
- **หลังบันทึก**:
  1. บันทึก `programs.coordinator_id`
  2. **ซิงค์ C**: ลบแถว `personnel_programs` ของ (ประธานเก่า, หลักสูตรนี้) ถ้าประธานเปลี่ยน  
     เพิ่ม/อัปเดตแถว `personnel_programs` สำหรับ (ประธานใหม่, หลักสูตรนี้) ด้วย `role_in_curriculum = 'ประธานหลักสูตร'`
  3. **ซิงค์ A**: ตั้ง `personnel.position = 'ประธานหลักสูตร'` สำหรับประธานใหม่ (ถ้ามี)
  4. **ซิงค์ A,B (ประธานเก่า)**: ถ้าประธานเก่าถูกถอดจากหลักสูตรนี้ และ**ไม่มีหลักสูตรอื่นที่เขายังเป็นประธาน** → ล้าง `personnel.position` และ `personnel.position_detail` เพื่อไม่ให้ตำแหน่งค้างเป็น "ประธานหลักสูตร"

---

## 3. กฎซิงค์ (สรุป)

- **แหล่งความจริงของ "ใครเป็นประธานหลักสูตรของหลักสูตร X"** = `programs.coordinator_id` และ `personnel_programs(personnel_id, program_id, role_in_curriculum='ประธานหลักสูตร')` ต้องสอดคล้องกัน
- **แก้จาก Organization**: ใช้ `personnel_programs` เป็นตัวตั้ง → อัปเดต `programs.coordinator_id` ให้ตรง
- **แก้จาก Programs**: ใช้ `programs.coordinator_id` เป็นตัวตั้ง → อัปเดต `personnel_programs` และ `personnel.position` / `position_detail` ให้ตรง
- **เมื่อถอดประธานออกจากหลักสูตร (จาก Programs)**: ถ้าบุคคลนั้นไม่มีบทบาทประธานในหลักสูตรอื่นแล้ว ต้องล้าง `position` และ `position_detail` ของบุคคลนั้น เพื่อป้องกันข้อมูลซ้ำซ้อน/ตำแหน่งค้าง

---

## 4. การอ่านข้อมูล (Frontend)

- **หน้าหลักสูตร (academics)**: แสดงรูป/ชื่อประธานจาก `programs.coordinator_id` (+ fallback จาก `personnel_programs` ที่ role มี "ประธาน")
- **หน้าบุคลากรแยกตามหลักสูตร (personnel)**: ประธานหลักสูตรของแต่ละหลักสูตร = จาก `personnel_programs.role_in_curriculum` หรือจาก `personnel.position`/`position_detail` ที่มี "ประธานหลักสูตร"
- **หน้าคณะผู้บริหาร (executives)**: กลุ่มประธานหลักสูตร = บุคลากรที่ `position` หรือ `position_detail` มี "ประธานหลักสูตร" หรือมีบทบาทใน `personnel_programs` เป็นประธานหลักสูตร

---

## 5. การ implement ที่เกี่ยวข้อง

- `App\Controllers\Admin\Organization.php`: create/update บุคลากร + ซิงค์ `programs.coordinator_id` ตาม `chairProgramIdsFromProgramRoles()`
- `App\Controllers\Admin\Programs.php`: update หลักสูตร + ซิงค์ `personnel_programs` และ `personnel.position`; เมื่อถอดประธานเก่า ให้ตรวจสอบว่าประธานเก่าไม่มีบทบาทประธานที่อื่น แล้วล้าง `position` / `position_detail`
- `App\Models\PersonnelProgramModel.php`: เมธอดช่วย เช่น `personnelHasChairRole(personnelId)` สำหรับใช้ตรวจก่อนล้าง position
