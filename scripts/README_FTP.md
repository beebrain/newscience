# เชื่อมต่อ Server 49 ผ่าน FTP

Server 49 = **49.231.30.18** (โฮสต์เดียวกับ SCP/MySQL ที่ใช้ในโปรเจกต์)

## 1. เปิดพอร์ต FTP บน Server

บนเครื่อง Server 49 ต้องเปิดบริการ FTP และพอร์ต 21:

- รันสคริปต์ `enable-external-services.ps1` (รันในฐานะ Administrator)  
  หรือ
- เปิด IIS Manager → FTP Site → Start  
  และปิดกฎ Firewall ที่บล็อกพอร์ต 21 ชั่วคราว

รายละเอียด: [README_SECURE_SERVICES.md](README_SECURE_SERVICES.md)

## 2. การตั้งค่า FTP (จากเครื่องคุณ)

### วิธีที่ 1: ใส่รหัสผ่านเมื่อรัน

ไม่ต้องสร้างไฟล์ config รันแล้วใส่รหัสผ่านเมื่อสคริปต์ถาม:

```powershell
cd C:\xampp\htdocs\newScience\scripts
.\connect_ftp_server_49.ps1
```

### วิธีที่ 2: ใช้ไฟล์ config (แนะนำถ้ารันบ่อย)

1. คัดลอกไฟล์ตัวอย่าง:
   ```powershell
   copy ftp_server_49.example.env ftp_server_49.env
   ```
2. แก้ไข `ftp_server_49.env` กรอก User และรหัสผ่าน (และ path ถ้าไม่ใช่ `/sci_root`):
   ```
   FTP_HOST=49.231.30.18
   FTP_PORT=21
   FTP_USER=Administrator
   FTP_PASS=รหัสผ่านจริง
   FTP_REMOTE_PATH=/sci_root
   ```
3. **อย่า commit ไฟล์ `ftp_server_49.env`** (เพิ่มใน `.gitignore` ถ้าต้องการ)

จากนั้นรัน:

```powershell
.\connect_ftp_server_49.ps1
```

จะเชื่อมต่อและแสดงรายการโฟลเดอร์ที่ `RemotePath`

### วิธีที่ 3: ใช้ตัวแปรสภาพแวดล้อม

```powershell
$env:FTP_PASS = "รหัสผ่าน"
.\connect_ftp_server_49.ps1
```

## 3. คำสั่งที่ใช้บ่อย

| วัตถุประสงค์                        | คำสั่ง                                                                                                           |
| ----------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| ทดสอบเชื่อมต่อ + แสดงรายการโฟลเดอร์ | `.\connect_ftp_server_49.ps1`                                                                                    |
| อัปโหลดไฟล์เดียว                    | `.\connect_ftp_server_49.ps1 -UploadFile ".\enable-external-services.ps1" -UploadRemotePath "/sci_root/scripts"` |

ถ้าไม่ส่ง `-UploadRemotePath` จะใช้ค่า `FTP_REMOTE_PATH` จาก config/env

## 4. ทางเลือกอื่น: ใช้โปรแกรม FTP

- **FileZilla**: ใส่ Host `49.231.30.18`, Port `21`, User/Password ตามที่ตั้งบน server
- **Windows Explorer**: ในแถบที่อยู่พิมพ์ `ftp://49.231.30.18` แล้วใส่ user/pass เมื่อถาม

## 5. หมายเหตุความปลอดภัย

- FTP ส่งรหัสผ่านแบบไม่เข้ารหัส ใช้ในเครือข่ายภายในหรือ VPN จะปลอดภัยกว่า
- บน server ควรปิด FTP จากภายนอกเมื่อไม่ใช้ (รัน `secure-external-services.ps1`)
