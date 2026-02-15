# Personnel-User Normalization

## Overview

ฐานข้อมูลได้ถูก normalize เพื่อรวมข้อมูลส่วนบุคคลไว้ในตาราง `user` เพียงที่เดียว โดยใช้ `email` เป็น key หลักในการเชื่อมโยง และ `user_uid` เป็น foreign key ในตาราง `personnel`

## Before Normalization

### ปัญหาเดิม
- ข้อมูลชื่อ (`name`, `name_en`) ซ้ำซ้อนใน `personnel` และ `user`
- ข้อมูล `email` ซ้ำซ้อนในทั้งสองตาราง
- ข้อมูล `image`/`profile_image` ซ้ำซ้อน
- เมื่อต้องการอัปเดตข้อมูลส่วนบุคคล ต้องอัปเดตหลายที่

## After Normalization

### โครงสร้างใหม่

```
┌─────────────────────────────────────────────────────────────────┐
│                         user (ข้อมูลส่วนบุคคล)                    │
├─────────────────────────────────────────────────────────────────┤
│ uid (PK)                                                         │
│ email (UNIQUE) ← key หลักสำหรับการเชื่อมโยง                       │
│ password                                                         │
│ title, tf_name, tl_name (ชื่อ-นามสกุล ภาษาไทย)                    │
│ gf_name, gl_name (ชื่อ-นามสกุล ภาษาอังกฤษ)                        │
│ profile_image (รูปโปรไฟล์)                                        │
│ role, status, ...                                                │
└─────────────────────────────────────────────────────────────────┘
                               ▲
                               │ user_uid (FK)
                               │
┌─────────────────────────────────────────────────────────────────┐
│                    personnel (ข้อมูลด้านงาน)                      │
├─────────────────────────────────────────────────────────────────┤
│ id (PK)                                                          │
│ user_uid (FK → user.uid) ← ลิงก์ไปยังข้อมูลส่วนบุคคล               │
│ position, position_en (ตำแหน่งบริหาร)                             │
│ department_id, program_id (หน่วยงาน/หลักสูตร)                     │
│ phone (เบอร์โทรที่ทำงาน)                                          │
│ bio, bio_en, education, expertise (ข้อมูลวิชาการ)                 │
│ sort_order, status, ...                                          │
│                                                                  │
│ [DEPRECATED - will be removed]                                   │
│ name, name_en, email, image                                      │
└─────────────────────────────────────────────────────────────────┘
```

## Migration Steps

### 1. Run Migration Script

```bash
cd c:\xampp\htdocs\newScience
php scripts\run_normalize_personnel_user.php
```

Script นี้จะ:
- เชื่อมโยง `personnel` กับ `user` ที่มี email ตรงกัน
- สร้าง `user` record ใหม่สำหรับ personnel ที่ยังไม่มี
- อัปเดต `profile_image` ใน `user` ถ้าว่างอยู่
- อัปเดตชื่อใน `user` ถ้าว่างอยู่

### 2. Verify Data (ก่อนลบคอลัมน์)

```sql
-- ตรวจสอบ personnel ที่มี user_uid linked
SELECT p.id, p.name, p.email, p.user_uid, u.email as user_email
FROM personnel p
LEFT JOIN user u ON p.user_uid = u.uid
ORDER BY p.id;

-- ตรวจสอบ personnel ที่ยังไม่ linked
SELECT id, name, email FROM personnel WHERE user_uid IS NULL;
```

### 3. Drop Deprecated Columns (หลังจาก verify เรียบร้อย)

```sql
-- เปิด migration_normalize_personnel_user.sql และ uncomment ส่วน Step 6-7
ALTER TABLE `personnel` DROP COLUMN `name`;
ALTER TABLE `personnel` DROP COLUMN `name_en`;
ALTER TABLE `personnel` DROP COLUMN `email`;
ALTER TABLE `personnel` DROP COLUMN `image`;

ALTER TABLE `personnel` 
    ADD CONSTRAINT `fk_personnel_user` 
    FOREIGN KEY (`user_uid`) REFERENCES `user`(`uid`) 
    ON DELETE SET NULL ON UPDATE CASCADE;
```

## Model Usage

### PersonnelModel

หลังจาก normalize, `PersonnelModel` จะ JOIN กับ `user` โดยอัตโนมัติ:

```php
$personnelModel = new PersonnelModel();

// ดึงบุคลากรพร้อมข้อมูลจาก user
$personnel = $personnelModel->getActive();

// ข้อมูลจะมีทั้ง personnel fields และ user fields
foreach ($personnel as $p) {
    echo $p['name'];         // ชื่อ (จาก user หรือ fallback)
    echo $p['name_en'];      // ชื่ออังกฤษ
    echo $p['email'];        // email
    echo $p['image'];        // รูป (จาก user.profile_image หรือ fallback)
    echo $p['position'];     // ตำแหน่งบริหาร (personnel)
}

// ใช้ helper methods
$fullName = $personnelModel->getFullName($person);
$fullNameEn = $personnelModel->getFullNameEn($person);
$email = $personnelModel->getEmail($person);
$image = $personnelModel->getImage($person);
```

### Finding Personnel

```php
// ค้นหาจาก personnel ID
$person = $personnelModel->findWithUser($personnelId);

// ค้นหาจาก user_uid
$person = $personnelModel->findByUserUid($userUid);

// ค้นหาจาก email (ทั้ง user.email และ personnel.email)
$person = $personnelModel->findByEmail('john@example.com');
```

### Linking Personnel to User

```php
// เชื่อมโยง personnel กับ user โดยใช้ email
$personnelModel->linkToUserByEmail($personnelId);

// ดึงข้อมูล user ของ personnel
$userData = $personnelModel->getUserData($personnelId);
```

## Backward Compatibility

Model รองรับทั้งโครงสร้างเก่าและใหม่:

1. **Fallback behavior**: ถ้า `user_uid` ไม่ถูก set หรือ user ไม่มีข้อมูล, จะ fallback ไปใช้ข้อมูลจาก `personnel` โดยตรง

2. **Views ไม่ต้องแก้ไข**: เนื่องจาก Model return ข้อมูลในรูปแบบเดิม (`$p['name']`, `$p['image']`, etc.)

3. **Helper methods**: ใช้ `getFullName()`, `getImage()` etc. เพื่อความถูกต้องในทุกกรณี

## Files Changed

- `database/migration_normalize_personnel_user.sql` - SQL migration script
- `scripts/run_normalize_personnel_user.php` - PHP script to run migration
- `app/Models/PersonnelModel.php` - Updated to JOIN with user table

## Rollback

ถ้าต้องการ rollback (ก่อนลบคอลัมน์):

```sql
-- ไม่ต้องทำอะไร, ข้อมูลเดิมยังอยู่ใน personnel table
-- แค่ไม่ run SQL ที่ DROP COLUMN
```

## Date

Migration created: 2026-02-02
