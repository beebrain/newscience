# สรุปขั้นตอนที่ต้องทำบนแต่ละ Server (SSO เชื่อม 3 แอป)

ทั้ง 3 แอปใช้ **Email** เป็นตัวระบุตัวตนร่วมกัน และใช้ **shared secret = pisit_secret** (หรือตั้งใน env ของแต่ละ server)

---

## Server 1: newScience (เว็บคณะวิทยาศาสตร์)

**โดเมนตัวอย่าง:** sci.uru.ac.th หรือ localhost/newScience

### 1. ไฟล์/โค้ดที่มีอยู่แล้ว

- `app/Config/EdocSso.php` — baseUrl, exchangeCodeUrl, sharedSecret (pisit_secret)
- `app/Config/ResearchRecordSso.php` — baseUrl, sharedSecret (pisit_secret)
- `app/Controllers/Admin/Auth.php` — portalLogin, oauthCallback, goResearchRecord
- `app/Views/admin/auth/login.php` — ปุ่ม "Login ผ่าน URU Portal" (ไป Edoc เท่านั้น)
- `app/Views/admin/layouts/admin_layout.php` — เมนู "Research Record"
- Routes: `admin/portal-login`, `admin/oauth-callback`, `admin/go-research-record` (ในกลุ่ม admin)

### 2. ขั้นตอนบน Server newScience

1. **อัปโหลดโค้ด** — ให้ครบโฟลเดอร์ app (Config, Controllers, Views) และ routes ตามข้างบน
2. **ตั้งค่า base URL** — ใน `app/Config/App.php` (หรือ .env) ตั้ง `baseURL` ให้เป็น URL จริงของ newScience (เช่น `https://sci.uru.ac.th/`)
3. **ตั้งค่า Edoc SSO (ถ้าต้องการ override)**
   - ใน `.env`: `edocsso.baseUrl`, `edocsso.exchangeCodeUrl`, `edocsso.sharedSecret` (ถ้าไม่ตั้ง จะใช้ค่าใน config รวมถึง pisit_secret)
   - ให้ `edocsso.exchangeCodeUrl` ชี้ไปที่ Edoc จริง เช่น `https://edoc.sci.uru.ac.th/api/sso/exchange-code` (หรือมี index.php ตามที่ Edoc ใช้)
4. **ตั้งค่า Research Record SSO (ถ้าต้องการ override)**
   - ใน `.env`: `researchrecordsso.baseUrl` = `https://research.academic.uru.ac.th` (หรือ URL จริงของ Research Record)
   - `researchrecordsso.sharedSecret` ถ้าไม่ตั้ง จะใช้ edocsso.sharedSecret หรือ pisit_secret
5. **ตรวจสอบ**
   - เปิดหน้า `/admin/login` ต้องเห็นปุ่ม "Login ผ่าน URU Portal"
   - หลังล็อกอินแอดมิน ต้องเห็นเมนู "Research Record" ในแถบซ้าย

---

## Server 2: Edoc (edoc.sci.uru.ac.th)

**โดเมน:** edoc.sci.uru.ac.th

### 1. ไฟล์/โค้ดที่มีอยู่แล้ว

- `app/Config/NewscienceSso.php` — enabled, sharedSecret (pisit_secret), returnUrlAllowlist, codeTtl
- `app/Controllers/AuthenController.php` — login รับ from/return_url; callback สร้าง one-time code แล้ว redirect กลับ newScience
- `app/Controllers/SsoApiController.php` — POST api/sso/exchange-code แลก code เป็น user info
- `app/Views/auth/URULogin.php` — แสดงข้อความ "จาก newScience" เมื่อ from=newscience
- Routes: `auth/login`, `auth/callback`, `POST api/sso/exchange-code`

### 2. ขั้นตอนบน Server Edoc

1. **อัปโหลดโค้ด** — ให้ครบ Config (NewscienceSso), AuthenController, SsoApiController, View auth/URULogin, Routes
2. **ตั้งค่า NewscienceSso**
   - ใน config หรือ `.env`: `sharedSecret` = **pisit_secret** (ให้ตรงกับ newScience)
   - ตรวจ `returnUrlAllowlist` ว่ามี base URL ของ newScience เช่น `https://sci.uru.ac.th/`, `http://localhost/` ตามที่ใช้จริง
3. **ตรวจสอบว่า URU Portal OAuth ทำงาน** — client_id, client_secret, redirect_uri ของ Edoc ต้องลงทะเบียนกับ URU Portal แล้ว
4. **ตรวจสอบ**
   - เปิด `https://edoc.sci.uru.ac.th/auth/login?from=newscience&return_url=https://sci.uru.ac.th/admin/oauth-callback` (แทนที่ด้วย URL จริงของ newScience) ต้องเห็นหน้า login และข้อความจาก newScience
   - หลังล็อกอินที่ URU Portal ต้อง redirect กลับไปที่ return_url พร้อม `?code=xxx`

### 3. Logout ของ Edoc — redirect กลับ newScience

เมื่อผู้ใช้ล็อกอินผ่าน Edoc (จาก newScience) แล้วไปกด **ออกจากระบบ** บน Edoc ต้องให้เด้งกลับมาหน้า login ของ newScience ไม่ค้างอยู่ที่ Edoc

**สิ่งที่ Edoc ต้องทำ:**

1. **ตั้งค่า URL ปลายทางหลัง logout (คงที่)**  
   หลังผู้ใช้กดออกจากระบบ ให้ redirect ไปที่ URL ของ newScience เสมอ เช่น  
   `https://sci.uru.ac.th/admin/edoc-logout-return`  
   (แทน `sci.uru.ac.th` ด้วยโดเมนจริงของ newScience ถ้าไม่ใช่)

2. **ลำดับการทำงานในหน้า/Controller Logout ของ Edoc**

   - ล้าง session ของ Edoc (ออกจากระบบฝั่ง Edoc)
   - Redirect ไปที่ **URL คงที่** ข้างบน (ไม่ต้องรับ `return_url` จาก query ก็ได้ ถ้าต้องการให้ออกจากระบบแล้วกลับ newScience เสมอ)

   **ตัวอย่าง (Pseudocode):**

   ```php
   // ใน AuthController หรือที่จัดการ logout
   public function logout() {
       // 1. ล้าง session ของ Edoc
       session()->destroy(); // หรือเทียบเท่า

       // 2. Redirect กลับ newScience — ใช้ URL คงที่จาก config
       $newscienceLogoutReturn = 'https://sci.uru.ac.th/admin/edoc-logout-return';
       return redirect()->to($newscienceLogoutReturn);
   }
   ```

3. **Config แนะนำใน Edoc**  
   เก็บ URL ปลายทางใน config หรือ `.env` เพื่อเปลี่ยนโดเมนได้ง่าย เช่น  
   `newscience.logout_return_url = https://sci.uru.ac.th/admin/edoc-logout-return`

**ฝั่ง newScience (มีอยู่แล้ว):**

- Route: `GET /admin/edoc-logout-return` → `Admin\Auth::edocLogoutReturn`
- Method นี้จะ redirect ไป `admin/login?logout=1` และแสดงข้อความ "ออกจากระบบแล้ว"

ดังนั้น Edoc แค่ redirect ไปที่ `https://sci.uru.ac.th/admin/edoc-logout-return` หลัง logout ก็เพียงพอ

**ทางเลือก (ถ้า Edoc อยากรับ return_url จาก query):**

- ถ้า Edoc รับ query เช่น `?return_url=...` ตอนเข้า logout (เช่น จาก newScience ส่งมา) ให้ตรวจ **allowlist** ว่า `return_url` เป็นโดเมน newScience เท่านั้น แล้ว redirect ไปที่นั้นหลังล้าง session
- newScience ส่ง return_url ได้ในรูปแบบ:  
  `https://edoc.sci.uru.ac.th/index.php/auth/logout?return_url=https%3A%2F%2Fsci.uru.ac.th%2Fadmin%2Fedoc-logout-return`

---

## Server 3: Research Record (research.academic.uru.ac.th)

**โดเมน:** research.academic.uru.ac.th

### 1. ไฟล์/โค้ดที่มีอยู่แล้ว

- `app/Config/NewscienceSso.php` — enabled, sharedSecret (pisit_secret)
- `app/Controllers/AuthenController.php` — ssoEntry() รับ token จาก newScience, ตรวจ signature, หา/สร้าง user จาก email, set session, redirect /dashboard
- Routes: `auth/sso-entry` → AuthenController::ssoEntry

### 2. ขั้นตอนบน Server Research Record

1. **อัปโหลดโค้ด** — ให้ครบ Config (NewscienceSso), AuthenController (มี ssoEntry และ base64UrlDecode), Routes
2. **ตั้งค่า NewscienceSso**
   - ใน config หรือ `.env`: `newscience_sso.sharedSecret` = **pisit_secret** (ให้ตรงกับ newScience และ Edoc)
   - `newscience_sso.enabled` = true
3. **ตรวจสอบ**
   - จาก newScience (หลังล็อกอินแอดมิน) กดเมนู "Research Record" จะ redirect ไป `https://research.academic.uru.ac.th/auth/sso-entry?token=xxx`
   - Research Record ต้องรับ token, ตรวจ signature ด้วย pisit_secret, หา/สร้าง user จาก email แล้วสร้าง session แล้ว redirect ไป /dashboard โดยไม่ต้องล็อกอินซ้ำ

---

## สรุปค่า Shared Secret (ทั้ง 3 Server)

| Server          | Config / ตัวแปร                                      | ค่าที่ใช้        |
| --------------- | ---------------------------------------------------- | ---------------- |
| newScience      | EdocSso.sharedSecret, ResearchRecordSso.sharedSecret | **pisit_secret** |
| Edoc            | NewscienceSso.sharedSecret                           | **pisit_secret** |
| Research Record | NewscienceSso.sharedSecret                           | **pisit_secret** |

ถ้าใช้ .env ให้ตั้งให้ตรงกันทั้ง 3 ที่ (เช่น edocsso.sharedSecret, newscience_sso.sharedSecret).

---

## ลำดับการ deploy แนะนำ

1. **Server 2 (Edoc)** — ให้ login จาก newScience ไป Edoc แล้ว redirect กลับ newScience ได้
2. **Server 1 (newScience)** — ให้ล็อกอินผ่าน URU Portal (Edoc) และมีเมนู Research Record
3. **Server 3 (Research Record)** — ให้รับ token จาก newScience ที่ /auth/sso-entry แล้วเข้าใช้งานได้โดยไม่ต้อง login ซ้ำ
