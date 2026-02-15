# Clone ตาราง user จาก researchrecord

โคลนตาราง `user` จากฐานข้อมูล **researchrecord** มาใช้ในโปรเจกต์ (newscience) เพื่อใช้ **uid** อ้างอิงข้ามระบบ (เช่น news.author_id → user.uid)

## วิธีรัน

1. แก้ค่าเชื่อมต่อในสคริปต์ถ้าต้องการ (ต้นทาง/ปลายทาง):
   - `scripts/clone_user_table_from_researchrecord.php`  
     - `$source`: host, user, pass, **db = researchrecord**  
     - `$target`: host, user, pass, **db = newscience**

2. โคลนเฉพาะโครงสร้าง (ไม่คัดลอกข้อมูล):
   ```bash
   php scripts/clone_user_table_from_researchrecord.php
   ```

3. โคลนโครงสร้าง + คัดลอกข้อมูลจาก researchrecord.user:
   ```bash
   php scripts/clone_user_table_from_researchrecord.php --copy-data
   ```

4. ไม่สำรองตาราง user เดิม (ทับเลย):
   ```bash
   php scripts/clone_user_table_from_researchrecord.php --copy-data --no-backup
   ```

## พฤติกรรม

- **มีตาราง user ใน newscience อยู่แล้ว**: จะเปลี่ยนชื่อเป็น `user_backup_YYYYMMDD_HHMMSS` ก่อน (ยกเว้นใช้ `--no-backup`)
- **--copy-data**: ใช้ได้เมื่อต้นทางและปลายทางอยู่บน MySQL server เดียวกัน และ user เดียวกัน
- โครงสร้างตารางจะตรงกับ researchrecord.user (ดึงจาก `SHOW CREATE TABLE`)

### ทำไม FK ถึงชี้ไป user_backup_*

เมื่อรันโคลน**แบบมี backup** สคริปต์จะ `RENAME TABLE user TO user_backup_YYYYMMDD_HHMMSS`  
ใน MySQL การเปลี่ยนชื่อตารางจะทำให้ **foreign key ที่อ้างอิงตารางนั้นไปชี้ที่ชื่อใหม่** ดังนั้น constraint เช่น `personnel.fk_personnel_user` (REFERENCES user(uid)) จะกลายเป็น REFERENCES user_backup_... หลัง RENAME

สคริปต์โคลนรุ่นล่าสุดจะแก้ FK กลับให้ชี้ที่ตาราง `user` หลังสร้างตารางใหม่ (Step 3b)  
ถ้าเคยรันโคลนมาก่อนและยัง error ว่า FK อ้างอิง user_backup ให้รันแก้มือเดียว:

```bash
php scripts/run_fix_personnel_user_fk.php
```

หรือใช้ SQL ใน `database/fix_personnel_user_fk_references.sql`

## อ้างอิง uid ในโปรเจกต์

- ตาราง `news.author_id` อ้างอิงไปที่ `user.uid` (INT(3) UNSIGNED ZEROFILL)
- โมเดล `UserModel` ใช้ตาราง `user` และ primary key `uid`
