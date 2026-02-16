# ผลการทดสอบระบบ Content Builder

**วันที่ทดสอบ:** 2026-02-16  
**เวอร์ชั่น:** commit cd4b50c

## สรุปผลการทดสอบ

### ✅ Test 1: super_admin เข้าใช้งานได้
- **URL:** `/program-admin`
- **ผลลัพธ์:** PASS
- **หมายเหตุ:** Filter อนุญาต super_admin เข้าได้ทุกหลักสูตร

### ✅ Test 2: admin เข้าใช้งานได้
- **URL:** `/program-admin`
- **ผลลัพธ์:** PASS
- **หมายเหตุ:** แก้ไข ProgramAdminFilter ให้อนุญาต admin (commit 5c9ed87)

### ⏳ Test 3: faculty (chair) - รอทดสอบ
- **เงื่อนไข:** ต้องมีข้อมูลใน `personnel_programs` กับ `role_in_curriculum = 'ประธานหลักสูตร'`
- **ผลลัพธ์:** รอทดสอบด้วยข้อมูลจริง

### ⏳ Test 4-7: รอทดสอบเพิ่มเติม

## โค้ดที่แก้ไขแล้ว

### 1. ProgramAdminFilter.php
```php
// บรรทัด 43-45: อนุญาต super_admin และ admin
if ($userRole === 'super_admin' || $userRole === 'admin') {
    $isProgramAdmin = true;
}

// บรรทัด 51: return null แทน return $request
return null;

// บรรทัด 57: เพิ่ม $arguments = null
public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
```

### 2. ContentBuilder.php
```php
// บรรทัด 330-332: อนุญาต super_admin และ admin
if ($userRole === 'super_admin' || $userRole === 'admin') {
    return true;
}
```

### 3. Routes.php
- เพิ่ม route `/program-site/(:num)` สำหรับเว็บไซต์สาธารณะ
- เพิ่ม routes สำหรับ Content Builder CRUD

### 4. admin_layout.php
- เพิ่มเมนู "Content Builder (แก้ไขเว็บหลักสูตร)" สำหรับ super_admin และ admin

## ขั้นตอนการทดสอบเพิ่มเติม

### ทดสอบด้วย Browser

1. **Login เป็น super_admin:**
   ```
   http://localhost/dev/login-as-admin
   ```

2. **เข้า Content Builder:**
   ```
   http://localhost/program-admin
   http://localhost/program-admin/content-builder/1
   ```

3. **สร้างบล็อกทดสอบ:**
   - คลิก "สร้างบล็อกใหม่"
   - ใส่ชื่อ: "ทดสอบ"
   - เลือกประเภท: HTML
   - บันทึก

4. **ดูตัวอย่าง:**
   ```
   http://localhost/program-admin/live-preview/1
   ```

5. **ดูเว็บไซต์สาธารณะ:**
   ```
   http://localhost/program-site/1
   ```

### ทดสอบสิทธิ์ faculty

ต้องเตรียมข้อมูลในฐานข้อมูล:

```sql
-- ตรวจสอบว่า user เป็น faculty หรือไม่
SELECT uid, role, email FROM user WHERE role = 'faculty';

-- ตรวจสอบ personnel_programs
SELECT pp.*, p.name_th 
FROM personnel_programs pp
JOIN programs p ON pp.program_id = p.id
WHERE role_in_curriculum LIKE '%ประธาน%';
```

## ปัญหาที่พบและแก้ไข

| ปัญหา | สาเหตุ | การแก้ไข | commit |
|-------|--------|----------|--------|
| admin เข้าไม่ได้ | Filter ตรวจสอบแค่ super_admin | เพิ่ม `\|\| $userRole === 'admin'` | 5c9ed87 |
| TypeError ที่ before() | return $request แทน return null | เปลี่ยนเป็น `return null;` | d006652 |
| TypeError ที่ after() | ขาดพารามิเตอร์ $arguments | เพิ่ม `, $arguments = null` | 8f7fcae |

## สถานะการ Deploy

- [x] โค้ด push ไป GitHub แล้ว (cd4b50c)
- [x] Database migration พร้อม
- [x] Filter แก้ไขแล้ว
- [ ] รอทดสอบบน server จริง
- [ ] รอตรวจสอบจากผู้ใช้

## คำแนะนำการใช้งาน

### สำหรับ super_admin / admin:
1. Login ผ่าน `/admin/login`
2. เมนูซ้ายจะมี "Content Builder (แก้ไขเว็บหลักสูตร)"
3. คลิกเลือกหลักสูตรที่ต้องการแก้ไข
4. สร้าง/แก้ไขบล็อกเนื้อหาได้เลย

### สำหรับประธานหลักสูตร:
1. Login ผ่าน `/admin/login`
2. เข้า `/program-admin`
3. จะเห็นเฉพาะหลักสูตรที่เป็นประธาน
4. คลิก "Content Builder" tab เพื่อจัดการเนื้อหา

## ติดต่อสอบถาม

หากพบปัญหาเพิ่มเติม ตรวจสอบ log ที่:
```
writable/logs/log-2026-02-16.log
```

ค้นหาข้อความ "ProgramAdminFilter" เพื่อดูค่า userRole ที่ระบบตรวจสอบ
