# แผนสรุป: Login จาก newScience เข้าได้ทั้ง Edoc และ Research Record

## วัตถุประสงค์

- **จุดเข้าเดียว**: หน้า Login ของ newScience เป็นจุดเริ่มต้น
- **สองทางเลือก OAuth**: ผู้ใช้เลือกได้ว่าจะล็อกอินผ่าน **Edoc** (edoc.sci.uru.ac.th) หรือ **Research Record** (research.academic.uru.ac.th) — ทั้งคู่ใช้ URU Portal OAuth
- **Secret เดียวกัน**: newScience กับ Edoc และ Research Record ใช้ **shared secret ชุดเดียวกัน** ได้
- หลังล็อกอินที่ Portal สำเร็จ → กลับมา newScience → สร้าง session + เก็บ token ใน newScience

---

## Flow โดยรวม

```
newScience (หน้า Login)
       │
       ├──► [Login ผ่าน URU Portal (Edoc)]     ──► edoc.sci.uru.ac.th/auth/login
       │         │                                    │
       │         │                                    ▼
       │         │                              URU Portal (OAuth)
       │         │                                    │
       │         │                                    ▼
       │         └──────────────────────────► newScience /admin/oauth-callback?code=xxx&provider=edoc
       │
       └──► [Login ผ่าน URU Portal (Research Record)] ──► research.academic.uru.ac.th/auth/login
                 │                                    │
                 │                                    ▼
                 │                              URU Portal (OAuth)
                 │                                    │
                 │                                    ▼
                 └──────────────────────────► newScience /admin/oauth-callback?code=xxx&provider=researchrecord
```

---

## ลำดับขั้น (Step by Step)

### 1. ผู้ใช้อยู่ที่ newScience หน้า Login

- URL: `.../admin/login`
- มีทางเข้า:
  - ฟอร์มล็อกอิน (อีเมล/รหัสผ่าน) — เข้า newScience โดยตรง
  - **Login ผ่าน URU Portal (Edoc)** — ไปล็อกอินที่ Edoc
  - **Login ผ่าน URU Portal (Research Record)** — ไปล็อกอินที่ Research Record

### 2. ถ้าเลือก Edoc

1. กด "Login ผ่าน URU Portal (Edoc)" → newScience redirect ไป  
   `edoc.sci.uru.ac.th/auth/login?from=newscience&return_url=.../admin/oauth-callback?provider=edoc`
2. Edoc แสดงหน้า login แล้วลิงก์ไป **URU Portal (OAuth)**
3. ผู้ใช้ล็อกอินที่ URU Portal
4. Portal redirect กลับ Edoc; Edoc ได้ user แล้วสร้าง **one-time code** แล้ว redirect ไป  
   `newScience .../admin/oauth-callback?provider=edoc&code=xxx`
5. newScience เรียก Edoc API แลก `code` + **shared secret** → ได้ user info (+ token)
6. newScience สร้าง/อัปเดต user, ตรวจ role admin → set session + เก็บ token → redirect ไป admin (เช่น admin/news)

### 3. ถ้าเลือก Research Record

1. กด "Login ผ่าน URU Portal (Research Record)" → newScience redirect ไป  
   `research.academic.uru.ac.th/auth/login?from=newscience&return_url=.../admin/oauth-callback?provider=researchrecord`
2. Research Record แสดงหน้า login แล้วลิงก์ไป **URU Portal (OAuth)**
3. ผู้ใช้ล็อกอินที่ URU Portal
4. Portal redirect กลับ Research Record; Research Record ได้ user แล้วสร้าง **one-time code** แล้ว redirect ไป  
   `newScience .../admin/oauth-callback?provider=researchrecord&code=xxx`
5. newScience เรียก Research Record API แลก `code` + **shared secret (ตัวเดียวกับ Edoc)** → ได้ user info (+ token)
6. newScience สร้าง/อัปเดต user, ตรวจ role admin → set session + เก็บ token → redirect ไป admin

### 4. ผลลัพธ์

- ผู้ใช้ล็อกอินเข้า **newScience Admin** ได้ (ไม่ว่าจะเลือกทาง Edoc หรือ Research Record)
- Session เก็บ `admin_login_via` = `edoc_sso` หรือ `researchrecord_sso`
- ใช้ **secret ชุดเดียวกัน** กับทั้ง Edoc และ Research Record ได้ (ตั้งใน newScience ฝั่ง EdocSso/ResearchRecordSso, ฝั่ง Edoc/Research Record ตั้งค่าเดียวกัน)

---

## สรุปความสัมพันธ์ระหว่างระบบ

| ระบบ                                              | บทบาท                                                                                                                                                           |
| ------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **newScience**                                    | จุดเข้า Login เดียว; แสดงปุ่มไป Edoc หรือ Research Record; รับ callback + แลก code เป็น user info; สร้าง session + เก็บ token                                   |
| **Edoc** (edoc.sci.uru.ac.th)                     | รับ from=newscience + return_url; แสดงหน้า login + URU OAuth; หลังล็อกอินสร้าง one-time code แล้ว redirect กลับ newScience; มี API แลก code (ใช้ shared secret) |
| **Research Record** (research.academic.uru.ac.th) | ทำเหมือน Edoc; ใช้ **shared secret ชุดเดียวกับ Edoc** ได้                                                                                                       |
| **URU Portal**                                    | OAuth provider ร่วม; แต่ละแอป (Edoc, Research Record) ลงทะเบียน client เอง; newScience ไม่ต้องมี client เอง                                                     |

---

## ไฟล์/Config ที่เกี่ยวข้อง

### newScience

- Config: `app/Config/EdocSso.php`, `app/Config/ResearchRecordSso.php`
  - ใช้ secret เดียวกัน: ตั้ง `edocsso.sharedSecret` แล้วให้ ResearchRecordSso ใช้ค่านี้ (หรือตั้ง `researchrecordsso.sharedSecret` ให้เท่ากัน)
- Routes: `admin/portal-login`, `admin/portal-login-research`, `admin/oauth-callback`
- Controller: `Admin\Auth::portalLogin`, `portalLoginResearch`, `oauthCallback` (รองรับ `provider=edoc|researchrecord`)
- View: หน้า login มีปุ่ม Edoc และปุ่ม Research Record

### Edoc

- Config: `app/Config/NewscienceSso.php` (sharedSecret, returnUrlAllowlist)
- AuthenController: login รับ from/return_url; callback สร้าง one-time code แล้ว redirect กลับ newScience
- API: `POST api/sso/exchange-code` แลก code เป็น user info (ใช้ shared secret)

### Research Record

- Config: `app/Config/NewscienceSso.php` (sharedSecret เหมือน Edoc, returnUrlAllowlist)
- AuthenController: login รับ from/return_url; callback สร้าง one-time code แล้ว redirect กลับ newScience
- API: `POST api/sso/exchange-code` แลก code เป็น user info (ใช้ shared secret ชุดเดียวกัน)

---

## การตั้งค่า Shared Secret (ใช้ชุดเดียว)

- **newScience**: ตั้งใน `.env` เช่น `edocsso.sharedSecret = "your_secret"`
  - ResearchRecordSso อ่านจาก `researchrecordsso.sharedSecret` หรือ fallback เป็น `edocsso.sharedSecret` จึงใช้ค่าเดียวกันได้
- **Edoc**: ตั้งใน config NewscienceSso ให้ `sharedSecret` = ค่าเดียวกัน
- **Research Record**: ตั้งใน config NewscienceSso ให้ `sharedSecret` = ค่าเดียวกันกับ Edoc/newScience

เมื่อตั้งครบแล้ว: **Login จาก newScience หน้าเดียว จะเข้าใช้งานได้ทั้งผ่าน Edoc (OAuth) และผ่าน Research Record (OAuth)** โดยใช้ secret ชุดเดียวกัน
