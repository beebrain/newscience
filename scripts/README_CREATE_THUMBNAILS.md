# สคริปต์สร้าง Thumbnail (create_thumbnails.php)

สคริปต์แยกจากโปรเจกต์ ไม่พึ่งพา CodeIgniter ใช้แค่ PHP + GD

## การทำงาน

- สร้าง thumbnail ใหม่โดย**ไม่คงรายละเอียดเต็ม** (ลดขนาดและคุณภาพ)
- ถ้ารูปที่ได้ใหญ่กว่า **1 MB** จะลดคุณภาพ/ขนาดจนเหลือ**น้อยกว่า 1 MB**
- รองรับ: JPG, JPEG, PNG, GIF, WEBP

## ความต้องการ

- PHP พร้อม extension **GD** (เปิดใน php.ini: `extension=gd`)

## วิธีรัน

จากโฟลเดอร์โปรเจกต์ (newScience):

```bash
php scripts/create_thumbnails.php
```

หรือระบุโฟลเดอร์ต้นทาง/ปลายทางและขนาดสูงสุด:

```bash
php scripts/create_thumbnails.php [โฟลเดอร์ต้นทาง] [โฟลเดอร์ thumbnail] [ความกว้างสูงสุด] [ความสูงสูงสุด] [ขนาดไฟล์สูงสุด(bytes)]
```

ค่าเริ่มต้น:

- ต้นทาง: `public/newsimages`
- ปลายทาง: `public/thumbnails`
- ขนาด thumbnail สูงสุด: 800x800 px
- ขนาดไฟล์สูงสุด: 1048576 (1 MB)

ตัวอย่าง:

```bash
# ใช้ค่าเริ่มต้น
php scripts/create_thumbnails.php

# ระบุโฟลเดอร์และขนาด thumbnail 800x800, ไม่เกิน 1 MB
php scripts/create_thumbnails.php C:/path/to/images C:/path/to/thumb 800 800 1048576
```

## โครงสร้างโฟลเดอร์

สคริปต์จะสร้างโฟลเดอร์ย่อยในโฟลเดอร์ปลายทางให้ตรงกับต้นทาง (รวม recursive)

## หมายเหตุ

- ไฟล์ต้นฉบับไม่ถูกแก้ไข
- ไฟล์ที่สร้างเป็น thumbnail ใหม่ในโฟลเดอร์ปลายทาง
