# สิ่งที่ต้องทำเพิ่ม: Outlook Calendar Integration (ทีละขั้นตอน)

ใช้เป็น checklist ทำตามลำดับจากขั้นที่ 1 ไปเรื่อยๆ

---

## ขั้นที่ 1 — ลงทะเบียนแอปใน Microsoft Azure (ไม่เกี่ยวกับ code)

- [ ] 1.1 เข้า [Azure Portal](https://portal.azure.com) → **Azure Active Directory** (หรือ Microsoft Entra ID) → **App registrations** → **New registration**
- [ ] 1.2 ตั้งชื่อแอป (เช่น `Science Calendar Outlook Sync`)
- [ ] 1.3 ใน **Supported account types** เลือก **"Accounts in any organizational directory and personal Microsoft accounts"** (รวม @live, @outlook.com)
- [ ] 1.4 ใน **Redirect URI** เลือก **Web** แล้วใส่ URL callback จริง เช่น  
  `https://sci.uru.ac.th/outlook/callback` หรือ `https://your-domain.com/outlook/callback`
- [ ] 1.5 กด **Register**
- [ ] 1.6 ไป **Certificates & secrets** → **New client secret** → กำหนดอายุ → เก็บ **Value** (Client secret) ไว้ในที่ปลอดภัย (แสดงครั้งเดียว)
- [ ] 1.7 ไป **API permissions** → **Add a permission** → **Microsoft Graph** → **Delegated** → เลือก **Calendars.ReadWrite** → Add
- [ ] 1.8 คัดลอก **Application (client) ID** จากหน้า Overview เก็บไว้

**ผลลัพธ์:** ได้ `Client ID` และ `Client secret` พร้อม Redirect URI ที่ลงทะเบียนแล้ว

---

## ขั้นที่ 2 — เพิ่ม Config และตัวแปร Environment

- [ ] 2.1 สร้างไฟล์ config ใหม่ `app/Config/OutlookCalendar.php`  
  - กำหนด property: `clientId`, `clientSecret`, `redirectUri`, `tenant` (ใช้ `common` สำหรับ personal account)  
  - อ่านจาก `env('outlook.clientId')` ฯลฯ แบบเดียวกับ `UruPortalOAuth.php`
- [ ] 2.2 ใน `.env` (และ `.env.example`) เพิ่มบรรทัด:
  ```
  # OUTLOOK CALENDAR (Microsoft Graph)
  outlook.clientId       = YOUR_CLIENT_ID
  outlook.clientSecret   = YOUR_CLIENT_SECRET
  outlook.redirectUri    = https://your-domain.com/outlook/callback
  outlook.tenant         = common
  outlook.enabled        = true
  ```
- [ ] 2.3 ใส่ค่า Client ID / Client secret จริงใน `.env` (ห้าม commit ค่าจริงขึ้น git)

**ผลลัพธ์:** ระบบอ่านค่าสำหรับ OAuth และ Graph ได้จาก config

---

## ขั้นที่ 3 — สร้างตารางในฐานข้อมูล

- [ ] 3.1 สร้างไฟล์ migration SQL เช่น `database/add_outlook_calendar_tables.sql`
- [ ] 3.2 เพิ่มตาราง **user_outlook_tokens**  
  - คอลัมน์: `id`, `user_id` (FK → user.uid), `user_email`, `access_token` (TEXT), `refresh_token` (TEXT), `expires_at` (DATETIME), `created_at`, `updated_at`  
  - UNIQUE ที่ `user_id` (หนึ่ง user ต่อหนึ่งชุด token)
- [ ] 3.3 เพิ่มตาราง **calendar_event_outlook_sync**  
  - คอลัมน์: `id`, `event_id` (FK → calendar_events.id), `user_email` (VARCHAR), `outlook_event_id` (VARCHAR), `created_at`  
  - UNIQUE ที่ `(event_id, user_email)` เพื่อไม่สร้างซ้ำ
- [ ] 3.4 รัน migration บน DB (หรือ import SQL ผ่าน phpMyAdmin/CLI)

**ผลลัพธ์:** มีที่เก็บ token ต่อ user และ mapping event → Outlook event id

---

## ขั้นที่ 4 — สร้าง Model สำหรับ Token และ Sync

- [ ] 4.1 สร้าง `app/Models/UserOutlookTokenModel.php`  
  - ตาราง `user_outlook_tokens`  
  - เมธอดเช่น: `getByUserId($userId)`, `getByEmail($email)`, `upsert($userId, $email, $accessToken, $refreshToken, $expiresAt)`, `deleteByUserId($userId)`
- [ ] 4.2 สร้าง `app/Models/CalendarEventOutlookSyncModel.php`  
  - ตาราง `calendar_event_outlook_sync`  
  - เมธอดเช่น: `getOutlookEventId($eventId, $userEmail)`, `add($eventId, $userEmail, $outlookEventId)`, `deleteByEventId($eventId)` หรือ `deleteByEventAndEmail($eventId, $userEmail)`

**ผลลัพธ์:** โค้ดอื่นเรียกใช้ Model เพื่ออ่าน/เขียน token และ mapping ได้

---

## ขั้นที่ 5 — ทำ OAuth Flow (หน้าเชื่อมต่อ + Callback)

- [ ] 5.1 เพิ่ม route ใน `app/Config/Routes.php`  
  - GET `outlook/connect` → Controller สำหรับแสดงปุ่ม/ลิงก์ "เชื่อมต่อ Outlook" และ redirect ไป Microsoft login  
  - GET `outlook/callback` → Controller รับ `?code=...` แล้วแลก token และเก็บลง DB
- [ ] 5.2 สร้าง Controller เช่น `app/Controllers/User/OutlookCalendarController.php` (หรือ Admin ถ้าให้เฉพาะ admin)  
  - **connect():**
    - ตรวจว่า user ล็อกอินแล้ว (session)
    - สร้าง URL ไป `https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize` พร้อม query: `client_id`, `response_type=code`, `redirect_uri`, `scope=offline_access Calendars.ReadWrite`, `response_mode=query`
    - redirect ไป URL นั้น
  - **callback():**
    - รับ `code` จาก query (ถ้ามี `error` ให้แสดงข้อความแล้ว redirect กลับ)
    - POST ไป `https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token` เพื่อแลก code ได้ access_token, refresh_token, expires_in
    - ใช้ UserModel หา user จาก session
    - ใช้ UserOutlookTokenModel upsert token กับ user_id + user_email
    - redirect กลับหน้า calendar หรือ settings พร้อม flash message "เชื่อมต่อ Outlook แล้ว"
- [ ] 5.3 ใน view ปฏิทิน (หรือเมนูตั้งค่า) เพิ่มปุ่ม/ลิงก์ไป `outlook/connect` และแสดงสถานะ "เชื่อมต่อแล้ว" ถ้า user มี token ใน DB

**ผลลัพธ์:** User กด "เชื่อม Outlook" → ล็อกอิน Microsoft → กลับมาหน้าเราและเก็บ refresh_token ได้

---

## ขั้นที่ 6 — สร้าง Library/Service เรียก Microsoft Graph

- [ ] 6.1 สร้างคลาสเช่น `app/Libraries/OutlookCalendarService.php`  
  - รับ Config Outlook และใช้ HTTP client (หรือ CURL) เรียก Graph
- [ ] 6.2 เมธอด **refreshAccessToken($refreshToken):**  
  - POST ไป token endpoint กับ grant_type=refresh_token ได้ access_token ใหม่ + expires_in  
  - return access_token ใหม่ (และถ้าต้องการ เก็บกลับลง DB)
- [ ] 6.3 เมธอด **createEvent($accessToken, $eventData):**  
  - แปลง `title→subject`, `description→body.content`, `start_datetime`/`end_datetime` → `start.dateTime`/`end.dateTime` (รูปแบบ ISO + timeZone เช่น Asia/Bangkok), `location→location.displayName`  
  - POST `https://graph.microsoft.com/v1.0/me/calendar/events`  
  - return `id` ของ event ที่สร้างจาก response
- [ ] 6.4 เมธอด **updateEvent($accessToken, $outlookEventId, $eventData):**  
  - PATCH `https://graph.microsoft.com/v1.0/me/calendar/events/{id}` ด้วย payload เดียวกับ create (เฉพาะฟิลด์ที่แก้)
- [ ] 6.5 เมธอด **deleteEvent($accessToken, $outlookEventId):**  
  - DELETE `https://graph.microsoft.com/v1.0/me/calendar/events/{id}`

**ผลลัพธ์:** มี service ที่รับ token + ข้อมูล event แล้วสร้าง/อัปเดต/ลบใน Outlook ได้

---

## ขั้นที่ 7 — ตรวจสอบอีเมลว่าเป็น Microsoft (@live ฯลฯ)

- [ ] 7.1 ใน Service หรือ Helper สร้างฟังก์ชัน `isMicrosoftPersonalEmail($email)`  
  - ตรวจว่า domain อยู่ในรายการ: `live.com`, `live.co.th`, `outlook.com`, `outlook.co.th`, `hotmail.com`, `hotmail.co.th`, `msn.com`  
  - return true/false
- [ ] 7.2 ใช้ฟังก์ชันนี้ก่อนส่ง event ไป Outlook — ส่งเฉพาะ participant ที่เป็น Microsoft email และมี token เก็บไว้

**ผลลัพธ์:** ไม่เรียก Graph สำหรับอีเมลที่ไม่ใช่ Microsoft

---

## ขั้นที่ 8 — ผูกกับ CalendarApi (สร้าง/แก้/ลบ Event)

- [ ] 8.1 ใน `CalendarApi::store()` หลัง `$this->participantModel->syncParticipants(...)`  
  - วน participant_emails  
  - สำหรับแต่ละ email: ถ้า `isMicrosoftPersonalEmail(email)` และมี token ใน UserOutlookTokenModel  
    - ดึง/refresh access_token  
    - เรียก OutlookCalendarService::createEvent() ด้วยข้อมูล event ปัจจุบัน  
    - เก็บ outlook_event_id ลง CalendarEventOutlookSyncModel (event_id, user_email, outlook_event_id)
- [ ] 8.2 ใน `CalendarApi::update()` หลังอัปเดต event และ participants  
  - สำหรับแต่ละ participant ที่เป็น Microsoft และมี token:  
    - ถ้ามีแถวใน calendar_event_outlook_sync → เรียก updateEvent  
    - ถ้าไม่มี (participant ใหม่ที่เพิ่งเพิ่ม) → เรียก createEvent แล้วเก็บ outlook_event_id  
  - ลบแถว sync ของ participant ที่ถูกลบออกจากรายการ
- [ ] 8.3 ใน `CalendarApi::delete()` หลังลบ event  
  - ดึงรายการจาก calendar_event_outlook_sync ตาม event_id  
  - สำหรับแต่ละแถว: ดึง token ของ user_email, refresh ถ้าต้อง, เรียก deleteEvent(outlook_event_id)  
  - ลบแถวใน calendar_event_outlook_sync ของ event_id นี้

**ผลลัพธ์:** สร้าง/แก้/ลบ event ในระบบแล้วจะสะท้อนไป Outlook อัตโนมัติสำหรับผู้ใช้ @live ที่เชื่อมแล้ว

---

## ขั้นที่ 9 — หน้า UI (ปุ่มเชื่อมต่อ + สถานะ)

- [ ] 9.1 หน้า User Calendar (หรือ Admin Calendar ตามที่ออกแบบ)  
  - ถ้ายังไม่มี token: แสดงปุ่ม "เชื่อมต่อ Outlook" ลิงก์ไป `outlook/connect`  
  - ถ้ามี token: แสดงข้อความ "เชื่อม Outlook แล้ว" และปุ่ม "ยกเลิกการเชื่อม" (เรียก endpoint ลบ token ใน DB)
- [ ] 9.2 (ถ้ามีหน้า Settings/โปรไฟล์) เพิ่มส่วน "การเชื่อมต่อปฏิทิน" แสดงสถานะ Outlook และปุ่มเชื่อม/ยกเลิก

**ผลลัพธ์:** ผู้ใช้เห็นและควบคุมการเชื่อม Outlook ได้ชัดเจน

---

## ขั้นที่ 10 — ทดสอบและความปลอดภัย

- [ ] 10.1 ทดสอบด้วยบัญชี @live จริง: เชื่อม Outlook → สร้าง event ในระบบ → ตรวจว่า event โผล่ใน outlook.live.com  
- [ ] 10.2 ทดสอบแก้ไขและลบ event ในระบบ แล้วตรวจใน Outlook
- [ ] 10.3 ตรวจว่า Client secret ไม่อยู่ใน repo (ใช้ .env และ .gitignore)
- [ ] 10.4 (แนะนำ) เข้ารหัส refresh_token ก่อนเก็บใน DB ใช้ `Config\Encryption` หรือ encryption key ใน .env

**ผลลัพธ์:** ฟีเจอร์ใช้งานได้และไม่รั่วความลับ

---

## สรุปลำดับ (ย่อ)

| ลำดับ | สิ่งที่ทำ |
|-------|-----------|
| 1 | ลงทะเบียนแอป Azure + สิทธิ Calendars.ReadWrite + Redirect URI |
| 2 | Config + .env (clientId, clientSecret, redirectUri) |
| 3 | ตาราง user_outlook_tokens, calendar_event_outlook_sync |
| 4 | Model UserOutlookTokenModel, CalendarEventOutlookSyncModel |
| 5 | OAuth: route + Controller connect/callback เก็บ token |
| 6 | OutlookCalendarService (refresh token, create/update/delete event) |
| 7 | ฟังก์ชัน isMicrosoftPersonalEmail |
| 8 | ผูก CalendarApi store/update/delete กับ Service |
| 9 | UI: ปุ่มเชื่อม Outlook + แสดงสถานะ |
| 10 | ทดสอบ + ตรวจความปลอดภัย |

ทำครบทั้ง 10 ขั้น แล้ว Event ที่ participant ใช้ @live และเชื่อม Outlook จะไปโผล่ใน Outlook Calendar อัตโนมัติ
