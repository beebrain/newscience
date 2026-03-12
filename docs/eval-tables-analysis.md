# การวิเคราะห์ตารางประเมินผลการสอนใน sci-edoc (edoclocal)

สร้างจากคำสั่ง: `php spark edoc:analyze-eval edoclocal`

**สถานะ:** ณ เวลารันคำสั่ง ยังไม่สามารถเชื่อมต่อฐาน edoclocal (database ชื่อ `sci-edoc` บน localhost ไม่มี หรือยังไม่ได้โคลน/นำเข้า dump จาก sci-edoc). เมื่อมีฐาน sci-edoc ใน edoclocal แล้ว ให้รันคำสั่งอีกครั้งเพื่อดึงโครงสร้างจริงและตัวอย่างข้อมูล.

---

## แนะนำโครงสร้าง newScience (เมื่อยังไม่มีข้อมูลจาก sci-edoc)

จากแผน migration แนะนำให้มีตารางหลัก 3 ตาราง:

| ตาราง | วัตถุประสงค์ |
|-------|----------------|
| **evaluation_forms** | เก็บแบบฟอร์มประเมิน (ชื่อ, ปีการศึกษา, ภาคเรียน, สถานะ) |
| **evaluation_questions** | เก็บหัวข้อ/คำถามในแบบฟอร์ม (ลำดับ, ข้อความ, ประเภทคำตอบ) |
| **evaluation_responses** | เก็บผลประเมิน/คำตอบ (เชื่อมกับแบบฟอร์ม-คำถาม, ผู้ประเมิน, รายวิชา/ผู้สอน ถ้ามี) |

ความสัมพันธ์:

- `evaluation_questions.evaluation_form_id` → `evaluation_forms.id`
- `evaluation_responses.evaluation_question_id` → `evaluation_questions.id`
- `evaluation_responses.evaluation_form_id` → `evaluation_forms.id` (เพื่อความสะดวกในการ query)

เมื่อได้ผลจาก `edoc:analyze-eval` จริงแล้ว ให้เปรียบเทียบตารางใน sci-edoc กับโครงสร้างด้านบน แล้วปรับแมปในคำสั่ง `import:eval` ให้ตรงกับคอลัมน์จริง.
