# แผนการผูก Event กับ Outlook Calendar (อีเมล @live / @outlook.com)

## 1. สรุปความต้องการ

- นำ **Event จากระบบปฏิทิน (calendar_events)** ไปแสดง/ซิงค์กับ **Outlook Calendar** ของผู้ใช้
- รองรับอีเมล **@live** (รวม @outlook.com, @hotmail.com — บัญชี Microsoft ส่วนตัว)
- ผู้ใช้ที่ลงทะเบียนอีเมล @live สามารถเลือก "เชื่อมต่อ Outlook" แล้วให้ event ที่ตัวเองเป็น participant ไปโผล่ใน Outlook ได้

---

## 2. ตรวจสอบความพร้อมของระบบปัจจุบัน

### 2.1 โครงสร้างข้อมูลที่ใช้ได้แล้ว

| รายการ | สถานะ |
|--------|--------|
| **calendar_events** | มีฟิลด์ title, description, start/end, all_day, location, color, status |
| **calendar_participants** | ใช้ **user_email** — รองรับอีเมล @live ได้ทันที |
| **user.email** | เก็บอีเมลผู้ใช้ (รวม @live) — ใช้เป็นตัวระบุการเชื่อม Outlook ต่อคน |
| **CalendarApi** | มี feed และ CRUD (สร้าง/แก้/ลบ event) — จุดที่ต้องเพิ่มการ “ส่งไป Outlook” |

### 2.2 สิ่งที่ยังไม่มี

- การยืนยันตัวตนกับ **Microsoft (OAuth 2.0)** สำหรับบัญชี @live
- การเก็บ **token** (access + refresh) ของแต่ละ user เพื่อเรียก Microsoft Graph
- ฟีเจอร์ “ส่ง event ไป Outlook” หรือ “ซิงค์กับ Outlook” หลังสร้าง/แก้/ลบ event

---

## 3. ทางเลือกการทำงานกับ Outlook (@live)

### 3.1 Microsoft Graph API (แนะนำ)

- **API**: `POST https://graph.microsoft.com/v1.0/me/calendar/events` (สร้าง event ในปฏิทินหลักของ user)
- **การยืนยันตัวตน**: OAuth 2.0 แบบ Delegated (ผู้ใช้ล็อกอิน Microsoft เอง)
- **บัญชีที่รองรับ**: @live, @outlook.com, @hotmail.com และบัญชีองค์กร (ถ้าเปิดใช้)
- **สิทธิที่ต้องขอ**: `Calendars.ReadWrite` (หรือ `Calendars.ReadWrite.Shared` ถ้าต้องการแชร์)

เหตุผลที่เหมาะกับ @live:

- รองรับบัญชีส่วนตัว (Personal Microsoft Account) โดยตรง
- สร้าง/อัปเดต/ลบ event ได้ครบ
- เอกสารและ SDK ชัดเจน

### 3.2 ทางเลือกอื่น (ไม่แนะนำสำหรับการซิงค์อัตโนมัติ)

- **iCal / .ics export**: ให้ user ดาวน์โหลดไฟล์แล้วนำเข้า Outlook เอง — ไม่ได้ “ผูก” แบบ real-time
- **Outlook REST (ล้าสมัย)**: Microsoft แนะนำให้ใช้ Graph แทน

**สรุป**: ใช้ **Microsoft Graph API + OAuth 2.0** เป็นหลัก

---

## 4. ขั้นตอนการทำงาน (Flow) ที่แนะนำ

### 4.1 การเชื่อมต่อ Outlook (ครั้งแรก)

1. User เลือกเมนูเช่น **“เชื่อมต่อ Outlook”** หรือ **“Sync กับ Outlook”**
2. ระบบ redirect ไปหน้า login Microsoft (ด้วย Client ID ของแอปที่ลงทะเบียนใน Azure)
3. User ลงชื่อเข้าใช้ด้วยบัญชี @live (หรือ @outlook.com)
4. Microsoft redirect กลับมาที่ **Redirect URI** ของเรา พร้อม `code`
5. หลัง endpoint แลก `code` เป็น **access_token** และ **refresh_token** แล้ว:
   - เก็บ **refresh_token** กับ **user (uid)** (ในตารางใหม่หรือคอลัมน์เพิ่ม)
   - แสดงข้อความว่า “เชื่อมต่อ Outlook แล้ว”

### 4.2 เมื่อสร้าง / แก้ไข / ลบ Event

- **สร้าง Event (store)**  
  - หลัง insert ใน `calendar_events` และ `calendar_participants`  
  - สำหรับแต่ละ **participant** ที่มีอีเมล @live (หรือ @outlook.com, @hotmail.com) **และ** เคย “เชื่อม Outlook” แล้ว:
    - ใช้ refresh_token ของ user นั้นขอ access_token ใหม่ (ถ้าหมดอายุ)
    - เรียก Graph `POST /me/calendar/events` เพื่อสร้าง event ใน Outlook ของ user นั้น
    - (ถ้าต้องการ) เก็บ `outlook_event_id` ผูกกับ `event_id` + `user_email` เพื่อใช้ตอนอัปเดต/ลบ

- **แก้ไข Event (update)**  
  - ถ้ามี `outlook_event_id` เก็บไว้: เรียก Graph `PATCH /me/calendar/events/{outlook_event_id}`  
  - ถ้าไม่มี: อาจสร้าง event ใหม่ใน Outlook (แล้วเก็บ id ไว้)

- **ลบ Event (delete)**  
  - เรียก Graph `DELETE /me/calendar/events/{outlook_event_id}` สำหรับแต่ละ user ที่เคยส่งไป

### 4.3 การตรวจสอบอีเมล @live

- ตรวจว่า domain เป็น Microsoft:  
  `@live.com`, `@outlook.com`, `@outlook.co.th`, `@hotmail.com`, `@hotmail.co.th`, `@live.co.th` ฯลฯ  
- ถ้าเป็น domain เหล่านี้ และ user คนนั้นมี refresh_token เก็บไว้ จึงส่งไป Outlook

---

## 5. โครงสร้างที่ต้องเพิ่มในระบบ

### 5.1 Azure Portal (ไม่ใช่ใน code)

- สร้าง **App registration** (แอปเว็บ)
- ตั้งค่า **Redirect URI**: เช่น `https://your-domain.com/outlook/callback` หรือ `https://sci.uru.ac.th/outlook/callback`
- เปิด **“Accounts in any organizational directory and personal Microsoft accounts”** (รวม @live)
- ขอสิทธิ **Delegated**: `Calendars.ReadWrite`
- เก็บ **Application (client) ID** และ **Client secret** ไว้ใส่ใน config/env

### 5.2 Database

- **ตัวเลือก A – ตารางใหม่ (แนะนำ)**  
  สร้างตาราง `user_outlook_tokens`:

  - `id` (PK)
  - `user_id` (FK → user.uid)
  - `user_email` (ซ้ำกับ user สำหรับความชัดเจน)
  - `access_token` (TEXT, เก็บ encrypted ถ้าเป็นไปได้)
  - `refresh_token` (TEXT, เก็บ encrypted)
  - `expires_at` (DATETIME หรือ timestamp)
  - `created_at`, `updated_at`

- **ตัวเลือก B – คอลัมน์ใน user**  
  เพิ่ม `outlook_refresh_token`, `outlook_token_expires` ฯลฯ (ไม่แนะนำถ้า token ยาว/ต้องการ encrypt แยก)

- **สำหรับ mapping Event → Outlook**  
  สร้างตาราง `calendar_event_outlook_sync` (หรือชื่อใกล้เคียง):

  - `id` (PK)
  - `event_id` (FK → calendar_events.id)
  - `user_email` (ผู้ใช้ที่ส่งไป Outlook — ตรงกับ participant)
  - `outlook_event_id` (ID ที่ Graph ส่งกลับมา)
  - `created_at` (optional)

  ใช้สำหรับตอน update/delete จะได้รู้ว่าจะ PATCH/DELETE event ไหนใน Outlook

### 5.3 Config / Environment

- เก็บใน `.env` หรือ config (ห้าม commit secret จริง):
  - `outlook.client_id`
  - `outlook.client_secret`
  - `outlook.redirect_uri`
  - (ถ้ามี) `outlook.tenant` — สำหรับ personal account มักใช้ `common`

### 5.4 โค้ดที่ต้องเขียน (สรุป)

| ส่วน | รายการ |
|------|--------|
| **Config** | โหลด client_id, client_secret, redirect_uri (จาก env) |
| **OAuth** | หน้า “เชื่อม Outlook” → ลิงก์ไป Microsoft login |
| **Callback** | Route รับ `code` → แลก token → เก็บ refresh_token กับ user |
| **Library/Service** | ฟังก์ชันขอ access_token จาก refresh_token, เรียก Graph สร้าง/อัปเดต/ลบ event |
| **CalendarEventModel / Service** | หลัง insert/update/delete event → วน participant ที่เป็น @live และมี token → ส่งไป Outlook + เก็บ outlook_event_id |
| **CalendarApi** | เรียก service ข้างบนจาก `store()`, `update()`, `delete()` |

---

## 6. ลำดับการพัฒนาที่แนะนำ (Implementation Order)

1. **ลงทะเบียนแอปใน Azure** และตั้งค่า Redirect URI, สิทธิ Calendars.ReadWrite, รองรับ personal accounts
2. **เพิ่ม config + ตาราง DB**: `user_outlook_tokens`, `calendar_event_outlook_sync` และ migration
3. **ทำ OAuth flow**: หน้าเชื่อม Outlook + callback แลก code → เก็บ refresh_token
4. **สร้าง Library/Service สำหรับ Graph**:  
   - ขอ access token จาก refresh token  
   - สร้าง event (POST), อัปเดต (PATCH), ลบ (DELETE)
5. **แมปฟิลด์ Event ของเรา → Graph**:  
   - title → subject  
   - description → body.content  
   - start_datetime, end_datetime → start.dateTime, end.dateTime (+ timeZone)  
   - location → location.displayName  
   - participants → attendees (ถ้าต้องการ)
6. **ผูกกับ CalendarApi**: หลัง store/update/delete เรียก service ส่งไป Outlook เฉพาะ participant ที่อีเมล @live และมี token
7. **ทดสอบ**: ใช้บัญชี @live จริง เชื่อมต่อ แล้วสร้าง/แก้/ลบ event ในระบบ ตรวจว่าโผล่ใน Outlook

---

## 7. หมายเหตุด้านความปลอดภัย

- **Client secret** เก็บใน env/config เท่านั้น ไม่ใส่ใน frontend
- **refresh_token** เก็บใน DB ควรเข้ารหัส (encrypt at rest) และใช้ HTTPS เสมอ
- Redirect URI ต้องตรงกับที่ลงทะเบียนใน Azure ทุกตัวอักษร
- จำกัดสิทธิแอปเฉพาะ `Calendars.ReadWrite` ไม่ขอเกินจำเป็น

---

## 8. การตรวจสอบอีเมล @live (ตัวอย่าง)

```php
// ตัวอย่างฟังก์ชันตรวจว่าเป็น Microsoft personal account หรือไม่
private function isMicrosoftPersonalEmail(string $email): bool
{
    $domain = strtolower(substr($email, strrpos($email, '@') + 1));
    $microsoftDomains = [
        'live.com', 'live.co.th', 'outlook.com', 'outlook.co.th',
        'hotmail.com', 'hotmail.co.th', 'msn.com', 'passport.com'
    ];
    return in_array($domain, $microsoftDomains, true);
}
```

---

## 9. สรุป

- **Event ในระบบ** ใช้ `calendar_events` + `calendar_participants` (มี `user_email`) — **รองรับ @live อยู่แล้ว**
- **การ “ผูกกับ Outlook”** ต้องใช้ **Microsoft Graph API** และ **OAuth 2.0** สำหรับบัญชี @live
- ต้องเพิ่ม: **ลงทะเบียนแอป Azure**, **ตารางเก็บ token + mapping**, **OAuth flow**, **Service เรียก Graph**, และ **การเรียก service จาก CalendarApi** ตอนสร้าง/แก้/ลบ event
- หลังทำครบ ผู้ใช้ที่ใช้อีเมล @live และกด “เชื่อม Outlook” จะได้ event ที่ตัวเองเป็น participant ไปโผล่ใน Outlook Calendar อัตโนมัติ
