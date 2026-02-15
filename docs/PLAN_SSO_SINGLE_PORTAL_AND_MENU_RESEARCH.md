# แผน: URU Portal เดียว + เมนูเข้า Research Record โดยไม่ต้อง Login ซ้ำ

## การระบุตัวตนร่วมกัน (ทั้ง 3 แอป)

**ทั้ง 3 Applications ใช้ Email Address ของผู้ใช้เท่านั้น ในการยืนยันว่าเป็นคนเดียวกัน**

- **newScience**, **Edoc**, **Research Record** ใช้ **Email Address** เป็นตัวระบุตัวตน (identity) ร่วมกัน
- เมื่อแลกข้อมูลผู้ใช้ (จาก OAuth, จาก token, จาก API) ให้ใช้ **email** เป็น key หลักในการหา/สร้าง user และยืนยันว่าเป็นคนเดียวกันในทั้ง 3 แอป
- ตาราง user ในแต่ละแอปควรมี email เป็น unique key (หรือใช้ email เป็นหลักในการ match ระหว่างแอป)

---

## วัตถุประสงค์

1. **URU Portal เดียว** — หน้า Login newScience มีทางเลือกล็อกอินผ่าน Portal **ทางเดียว** คือไปล็อกอินที่ **Edoc.sci.uru.ac.th** (ไม่แสดงปุ่ม Login ผ่าน Research Record)
2. **หลังล็อกอินที่ newScience แล้ว** — มี **เมนู** ใน newScience ที่พาไป **Research Record (research.academic.uru.ac.th)** และเข้าใช้งานได้ **โดยไม่ต้องล็อกอินซ้ำ** (ใช้ session/token ที่ได้จาก newScience)

---

## Flow โดยรวม

```
[หน้า Login newScience]
        │
        └──► Login ผ่าน URU Portal (ปุ่มเดียว) ──► edoc.sci.uru.ac.th ──► URU Portal (OAuth)
                                                      │
                                                      ▼
                                              กลับ newScience (session + token)
                                                      │
                                                      ▼
                                    [ใช้งาน newScience Admin ได้แล้ว]
                                                      │
                                    ┌─────────────────┴─────────────────┐
                                    │  เมนู "Research Record" / "ระบบวิจัย"  │
                                    │  (ลิงก์ไป research.academic.uru.ac.th   │
                                    │   พร้อม token จาก newScience)          │
                                    └─────────────────┬─────────────────┘
                                                      │
                                                      ▼
                                    research.academic.uru.ac.th/auth/sso-entry?token=xxx
                                                      │
                                    Research Record ตรวจ token → สร้าง session
                                                      │
                                                      ▼
                                    [เข้าใช้งาน Research Record ได้เลย ไม่ต้อง login ซ้ำ]
```

---

## สิ่งที่ต้องทำ

### 1. newScience — เหลือ URU Portal ทางเดียว (Edoc)

- **ลบ** ปุ่ม "Login ผ่าน URU Portal (Research Record)" และ route `admin/portal-login-research` (หรือซ่อนปุ่มไว้ ไม่ลบ route ก็ได้)
- หน้า Login แสดงเฉพาะ:
  - ฟอร์มล็อกอิน (อีเมล/รหัสผ่าน)
  - **ปุ่มเดียว**: "Login ผ่าน URU Portal" → ไปที่ `admin/portal-login` (ไป Edoc เท่านั้น)
- Callback หลังล็อกอินจาก Edoc ยังใช้ `admin/oauth-callback?provider=edoc` (หรือไม่ส่ง provider ก็ใช้ Edoc เป็นค่าเริ่มต้น)

### 2. newScience — เมนูไป Research Record (ไม่ต้อง login ซ้ำ)

- **สร้าง token สำหรับส่งไป Research Record**

  - เมื่อผู้ใช้ล็อกอิน newScience แล้ว เรามี session (admin_id, admin_email, admin_name, login_uid จาก user ฯลฯ)
  - เมื่อกดเมนู "Research Record" ให้สร้าง **token ชั่วคราว** (เช่น JWT หรือ HMAC-signed payload) ที่มีข้อมูล เช่น: **email** (ตัวระบุตัวตนหลัก), ชื่อ, อายุไม่เกิน 1–5 นาที
  - ลงนามด้วย **shared secret** (ตัวเดียวกับที่ใช้กับ Edoc/Research Record) เพื่อให้ Research Record ตรวจแล้วเชื่อได้

- **เพิ่มเมนูใน newScience**

  - ใน layout แอดมิน (หรือเมนูหลัก) เพิ่มรายการเมนู เช่น "Research Record" / "ระบบวิจัย"
  - ลิงก์ไม่ไปตรงที่ `https://research.academic.uru.ac.th` เปล่าๆ แต่ไปที่ **endpoint รับ SSO ของ Research Record** โดยส่ง token เป็น query string
  - ตัวอย่าง: `https://research.academic.uru.ac.th/auth/sso-entry?token=<signed_token>`
  - ทางเลือก: newScience มี route เช่น `admin/go-research-record` ที่สร้าง token แล้ว redirect ไปที่ URL ข้างบน (เพื่อไม่ให้ token โผล่ในเมนูถาวร)

- **การสร้าง token (ฝั่ง newScience)**
  - ใช้ shared secret (จาก EdocSso/ResearchRecordSso — ใช้ตัวเดียวกัน)
  - Payload อย่างน้อย: **email** (ตัวระบุตัวตนหลัก), ชื่อ, exp (หมดอายุ) — อาจมี login_uid เป็นข้อมูลเสริม
  - ลงนามด้วย HMAC-SHA256(secret, payload) หรือใช้ JWT (ลงนามด้วย secret)
  - Research Record จะรับ token แล้วตรวจ signature + expiry แล้วใช้ **email** หา/สร้าง user และสร้าง session

### 3. Research Record — รับ token จาก newScience แล้วให้เข้าใช้ได้เลย

- **เพิ่ม route** เช่น `GET /auth/sso-entry` (หรือ `/auth/sso-entry?token=xxx`)
- **Logic**

  1. รับ `token` จาก query string
  2. ตรวจ signature ด้วย shared secret (เดียวกันกับ newScience)
  3. ตรวจ expiry
  4. อ่าน **email** จาก payload (ใช้เป็นตัวระบุตัวตนหลัก — ตรงกับทั้ง 3 แอป)
  5. หา user ใน Research Record จาก **email**; ถ้าไม่มีก็สร้างจากข้อมูลใน token (ใช้ email เป็น key)
  6. สร้าง session ของ Research Record (set user_data, logged_in ฯลฯ) เหมือนเพิ่งล็อกอินผ่าน OAuth
  7. Redirect ไปหน้า dashboard ของ Research Record (เช่น `/dashboard`)

- **ความปลอดภัย**
  - Token ใช้ครั้งเดียว (one-time) หรืออายุสั้นมาก (1–2 นาที) เพื่อลดความเสี่ยง replay
  - ตรวจ signature ทุกครั้ง — ต้องใช้ shared secret เดียวกับ newScience
  - Allowlist: รับ token จาก newScience เท่านั้น (ตรวจจาก signature)

### 4. สรุปความสัมพันธ์

| ระบบ                | บทบาท                                                                                                                                                                                                      |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **newScience**      | มี Login ผ่าน URU Portal **ทางเดียว** (ไป Edoc); หลังล็อกอินมีเมนู "Research Record" ที่สร้าง signed token แล้ว redirect ไป Research Record `/auth/sso-entry?token=xxx`                                    |
| **Edoc**            | ใช้เฉพาะสำหรับ **การล็อกอิน** (จาก newScience → Edoc → URU Portal → กลับ newScience)                                                                                                                       |
| **Research Record** | ไม่ใช้สำหรับกดล็อกอินจากหน้า login newScience; ใช้เฉพาะ **ทางเมนู** หลังล็อกอิน newScience แล้ว — รับ token จาก newScience ที่ `/auth/sso-entry` แล้วสร้าง session ให้เข้าใช้งานได้เลยโดยไม่ต้อง login ซ้ำ |

---

## รายการงาน (สรุป)

1. **newScience**

   - ลบหรือซ่อนปุ่ม "Login ผ่าน Research Record"; เหลือเฉพาะ "Login ผ่าน URU Portal" (ไป Edoc)
   - เพิ่มเมนู "Research Record" (หรือ "ระบบวิจัย") ในแอดมิน/เมนูหลัก
   - สร้าง route เช่น `admin/go-research-record` ที่สร้าง signed token (HMAC หรือ JWT) จาก session ปัจจุบัน แล้ว redirect ไป `https://research.academic.uru.ac.th/auth/sso-entry?token=xxx`
   - ใช้ shared secret ชุดเดียวกับที่ใช้กับ Edoc/Research Record

2. **Research Record**

   - เพิ่ม route `GET /auth/sso-entry` (รับ query `token`)
   - ตรวจ signature + expiry ของ token ด้วย shared secret
   - อ่าน **email** จาก token (ตัวระบุตัวตนร่วมกับ newScience และ Edoc); หา/สร้าง user ใน Research Record จาก email; set session
   - Redirect ไป dashboard

3. **Shared secret**
   - ใช้ชุดเดียวทั้ง newScience, Edoc และ Research Record (สำหรับการแลก code กับ Edoc และสำหรับการ sign token ไป Research Record)

เมื่อทำครบแล้ว: **มีแค่ URU Portal เดียว (ผ่าน Edoc)** และ **หลังกลับมา newScience จะมีเมนูเข้า Research Record ได้เลยโดยไม่ต้อง login ซ้ำ**
