# โค้ดอ้างอิงสำหรับ Edoc (logout redirect กลับ newScience)

โฟลเดอร์นี้เก็บตัวอย่างโค้ดสำหรับฝั่ง **Edoc** (edoc.sci.uru.ac.th) เพื่อให้หลัง logout เด้งกลับมาหน้า login ของ newScience

## ไฟล์

- **AuthController_logout_example.php** — ตัวอย่าง method `logout()` ใน AuthController
  - ล้าง session ของ Edoc
  - รองรับ query `return_url` (ต้องผ่าน allowlist โดเมน newScience)
  - ไม่มีหรือไม่ผ่าน → redirect ไป URL คงที่ `https://sci.uru.ac.th/admin/edoc-logout-return`

## วิธีใช้

1. คัดลอก method `logout()` และ helper `getNewscienceLogoutReturnUrl()` ไปใส่ใน Controller ที่จัดการ logout ของ Edoc
2. ตั้งค่า config ใน Edoc (เช่น `NewscienceSso` หรือ .env):
   - `logoutReturnUrl` หรือ `newscience.logout_return_url` = `https://sci.uru.ac.th/admin/edoc-logout-return`
3. ตั้ง route ให้ชี้ไปที่ method นี้ (เช่น `GET /auth/logout` หรือ `/index.php/auth/logout`)
4. ปรับ `$allowedHosts` ใน `logout()` ให้ตรงกับโดเมนของ newScience ที่ใช้จริง

## ฝั่ง newScience

- Route `GET /admin/edoc-logout-return` รับ redirect จาก Edoc แล้วส่งต่อไป `admin/login?logout=1`
- ไม่ต้องแก้ไขอะไรเพิ่ม ถ้า Edoc redirect มาที่ URL ด้านบน
