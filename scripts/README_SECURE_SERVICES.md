# สคริปต์ปิด/เปิด FTP และ MySQL จากภายนอก

ใช้ PowerShell **รันในฐานะ Administrator** เท่านั้น

## 1. ปิดการเข้าถึงจากภายนอก (Lock) – ป้องกันการโจมตี

```powershell
.\secure-external-services.ps1
```

- หยุดบริการ **FTP** (IIS / FTPSVC)
- สร้างกฎ **Firewall** บล็อกพอร์ต **21** (FTP) และ **3306** (MySQL) จากภายนอก
- อนุญาต **localhost (127.0.0.1)** สำหรับพอร์ต 21 และ 3306 เพื่อให้ใช้ในเครื่องได้แม้ล็อกอยู่

## 2. เปิดการเข้าถึงจากภายนอก (Unlock) – สำหรับนักพัฒนา

```powershell
.\enable-external-services.ps1
```

- เริ่มบริการ **FTP**
- ปิดกฎ Firewall ที่บล็อกพอร์ต 21 และ 3306 (การเข้าถึงจากภายนอกทำงานได้อีกครั้ง)

## วิธีรัน (ต้อง Run as Administrator)

1. คลิกขวาที่ PowerShell → **Run as administrator**
2. ไปที่โฟลเดอร์สคริปต์:
   ```powershell
   cd C:\xampp\htdocs\newScience\scripts
   ```
3. รันสคริปต์ที่ต้องการ:
   ```powershell
   .\secure-external-services.ps1   # ปิดจากภายนอก
   .\enable-external-services.ps1  # เปิดสำหรับนักพัฒนา
   ```

## ชื่อบริการ FTP

สคริปต์ลองหาบริการ FTP ในชื่อ: `FTPSVC`, `FtpSvc`, `MicrosoftFtpSvc` (IIS FTP).  
ถ้าใช้ FTP server อื่น (เช่น FileZilla) ให้แก้ชื่อบริการในสคริปต์ให้ตรงกับเครื่องคุณ

## ชื่อกฎ Firewall

- บล็อกภายนอก: `Sci-Secure-Block-FTP-21`, `Sci-Secure-Block-MySQL-3306`
- อนุญาต localhost: `Sci-Secure-Allow-FTP-Localhost`, `Sci-Secure-Allow-MySQL-Localhost`

---

## อัปโหลด 2 ไฟล์เข้า Server

จากเครื่องคุณ (ที่มีไฟล์และมี OpenSSH client) รัน:

```powershell
cd C:\xampp\htdocs\newScience\scripts
.\upload-secure-scripts-to-server.ps1
```

จะใช้ค่าเริ่มต้น: Host `49.231.30.18`, User `Administrator`, Remote path `C:\inetpub\sci_root\scripts`  
เมื่อรันจะถามรหัสผ่าน SSH ของบัญชีบน server

ถ้า path บน server ไม่ตรง ให้ส่ง parameter:

```powershell
.\upload-secure-scripts-to-server.ps1 -ServerUser Administrator -RemotePath "C:\inetpub\sci_root\scripts"
# หรือ Linux: -RemotePath "/home/xxx/newscience/scripts"
```

ถ้า server ใช้ Git clone โปรเจกต์อยู่แล้ว สามารถบน server รัน `git pull` แทนการอัปโหลดด้วย SCP ก็ได้
