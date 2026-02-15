# การใช้ Email เป็น Unique Key สำหรับเชื่อมกับ App ภายนอก

ฐานข้อมูลออกแบบให้ใช้ **อีเมล (email)** เป็นตัวระบุตัวบุคคลที่ใช้ร่วมกันได้ระหว่างระบบ (cross-app linking)

---

## 1. ตารางที่ใช้ Email เป็น Unique Key

| ตาราง         | คอลัมน์ | ความหมาย                                                         |
| ------------- | ------- | ---------------------------------------------------------------- |
| **user**      | `email` | UNIQUE, NOT NULL — หนึ่งอีเมลต่อหนึ่งบัญชีผู้ใช้                 |
| **personnel** | `email` | UNIQUE, NULL ได้ — หนึ่งอีเมลต่อหนึ่งบุคลากร (NULL = ยังไม่ระบุ) |

- **user.email** ใช้สำหรับล็อกอินและบัญชีระบบ
- **personnel.email** ใช้สำหรับระบุตัวบุคลากร และเชื่อมกับ `user` ผ่าน `personnel.user_uid` เมื่ออีเมลตรงกัน

---

## 2. การเชื่อมระหว่างตารางด้วย Email

```
personnel.email  ←→  user.email
       │                    │
       └── personnel.user_uid ──→ user.uid
```

- แอปจะ match อีเมลระหว่าง personnel กับ user แล้วเซ็ต `personnel.user_uid = user.uid`
- App ภายนอกสามารถใช้ **email** เป็น key ดึงหรืออัปเดตบุคลากร/ผู้ใช้ โดยไม่ต้องรู้ `id` หรือ `uid` ภายใน

---

## 3. การใช้กับ App ภายนอก

- **ระบุตัวบุคคล:** ส่ง `email` เป็นตัวระบุ (แทน id ภายใน)
- **API / Integration:** รับ-ส่ง email เป็น primary key ระหว่างระบบ (เช่น researchrecord, ระบบอื่น)
- **Lookup:** ค้นหา personnel ด้วย `WHERE email = ?` หรือ user ด้วย `WHERE email = ?` ได้โดยไม่ซ้ำ

---

## 4. Migration

- เพิ่ม UNIQUE บน `personnel.email`: รัน  
  `php scripts/run_add_personnel_email_unique.php`
- สคริปต์จะตรวจสอบอีเมลซ้ำก่อน; ถ้ามีซ้ำต้องแก้ข้อมูลก่อนรัน

---

## 5. ข้อควรระวัง

- **personnel.email** เป็น NULL ได้ (บุคลากรที่ยังไม่มีอีเมล); MySQL/MariaDB อนุญาตหลายแถวที่ email เป็น NULL ภายใต้ UNIQUE
- ต้องไม่ให้มีสองแถวที่อีเมลไม่ว่างและเหมือนกัน — ถ้ามีต้องแก้ก่อนเพิ่ม UNIQUE
