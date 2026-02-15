# กระบวนการ Login Admin — ตรวจสอบอย่างละเอียด

## 1. Routes (app/Config/Routes.php)

| Method | URI             | Controller                 | Filter                  |
| ------ | --------------- | -------------------------- | ----------------------- |
| GET    | `/admin/login`  | `Admin\Auth::login`        | ไม่มี (ไม่มี adminauth) |
| POST   | `/admin/login`  | `Admin\Auth::attemptLogin` | ไม่มี                   |
| GET    | `/admin/logout` | `Admin\Auth::logout`       | ไม่มี                   |

- หน้า login **ไม่ผ่าน** filter `adminauth` จึงเข้าได้โดยไม่ต้อง login
- กลุ่ม `admin/*` (news, organization, programs, hero-slides) ใช้ filter `adminauth` — ถ้ายังไม่ login จะ redirect ไป `/admin/login`

---

## 2. แสดงฟอร์ม Login — Auth::login()

1. ตรวจ `session()->get('admin_logged_in')` ถ้าเป็น true → redirect ไป `base_url('admin/news')`
2. ส่ง view `admin/auth/login` พร้อม `page_title`

---

## 3. ฟอร์ม Login (app/Views/admin/auth/login.php)

- **action:** `base_url('admin/login')` → POST ไปที่ `/admin/login`
- **method:** post
- **ฟิลด์:**
  - `login` — Username หรือ Email (ตรงกับ `user.email` หรือ `user.login_uid`)
  - `password` — รหัสผ่าน
- **CSRF:** มี `<?= csrf_field() ?>` (ถ้าเปิด CSRF filter ที่ POST จะตรวจ token)
- **Flash messages:** แสดง `error`, `success`, `errors` จาก session flashdata

**หมายเหตุ:** ถ้าใช้ XAMPP ที่ `http://localhost/newScience/public/` ต้องตั้ง `app.baseURL` ใน env หรือ App.php ให้ตรง (เช่น `http://localhost/newScience/public/`) เพื่อให้ `base_url()` ถูกต้อง

---

## 4. ประมวลผล Login — Auth::attemptLogin()

### 4.1 Validation

- `login`: required, string (รับได้ทั้ง **อีเมล** และ **login_uid** เช่น admin)
- `password`: required, min_length[6]
- ไม่ผ่าน → redirect back + withInput + errors

### 4.2 หา User — UserModel::findByIdentifier($login)

1. ใช้ query เดียว: `WHERE email = $login OR login_uid = $login` (builder ใหม่ทุกครั้ง ไม่ leak state)
2. ไม่พบ → redirect back + error "Invalid email or password."

### 4.3 ตรวจสถานะและบทบาท

- **status:** ต้องเป็น `'active'` (ถ้าคอลัมน์ไม่มีหรือเป็น null ถือว่าผ่าน)
- **role:** ต้องเป็น `'admin'`, `'editor'` หรือ `'super_admin'`
- ไม่ผ่าน → redirect back + error ตามข้อความที่กำหนด

### 4.4 ตรวจรหัสผ่าน — UserModel::verifyPassword($password, $user['password'])

- ใช้ `password_verify($password, $hash)`
- ถ้า hash ว่าง/null → return false
- ไม่ผ่าน → redirect back + error "Invalid email or password."

### 4.5 ตั้ง Session และ Redirect

- ตั้งค่า session: `admin_logged_in`, `admin_id` (uid), `admin_email`, `admin_name`, `admin_role`
- ชื่อจาก `UserModel::getFullName($user)` — ใช้ th_name/thai_name + thai_lastname ก่อน แล้ว fallback เป็น tf_name/tl_name หรือ gf_name/gl_name
- redirect ไป `session('redirect_url')` หรือ `base_url('admin/news')`
- ลบ `redirect_url` ออกจาก session

---

## 5. โครงสร้างตาราง user ที่ใช้กับ Login

คอลัมน์ที่ใช้ในกระบวนการ login:

| คอลัมน์   | การใช้                                                 |
| --------- | ------------------------------------------------------ |
| uid       | primary key, เก็บใน session เป็น admin_id              |
| email     | ใช้ค้น user (และแสดงใน session)                        |
| login_uid | ใช้ค้น user (username)                                 |
| password  | ต้องเป็น hash จาก password_hash(..., PASSWORD_DEFAULT) |
| role      | ต้องเป็น 'admin', 'editor' หรือ 'super_admin'          |
| status    | ต้องเป็น 'active' (ถ้ามีคอลัมน์)                       |

**หลัง clone จาก researchrecord:** ต้องมี ENUM ของ `role` รวมค่า `'admin'`, `'editor'`, `'super_admin'` (ถ้าเดิมไม่มีให้รัน `scripts/run_fix_user_role_enum.php`)

---

## 6. Filter adminauth (app/Filters/AdminAuthFilter.php)

- ใช้กับกลุ่ม route `admin/*` และ `utility/*`
- **before:** ตรวจ `session('admin_logged_in')` ถ้าไม่ใช่ true → เก็บ `redirect_url` แล้ว redirect ไป `base_url('admin/login')`
- ตรวจ `admin_role` ต้องเป็น 'admin', 'editor' หรือ 'super*admin' ถ้าไม่ใช่ → **ล้าง session ของ admin** (remove admin*\* และ redirect_url) แล้ว redirect ไป **admin/login** (ไม่ส่งไปหน้าแรก) พร้อมข้อความ "Session หมดอายุหรือไม่ถูกต้อง กรุณาเข้าสู่ระบบใหม่"

---

## 6.1 Debug Log (Admin Login)

ใช้ `log_message('debug', ...)` ใน Auth และ AdminAuthFilter:

| เหตุการณ์ | ข้อความ log (ระดับ debug) |
| ----------|----------------------------|
| เปิดหน้า login ขณะที่ login อยู่แล้ว | `Admin Auth: already logged in, redirect to admin/news. admin_id=...` |
| เริ่ม attempt login | `Admin Auth: login attempt, identifier=...` |
| Validation ไม่ผ่าน | `Admin Auth: validation failed. errors=...` |
| ไม่พบ user | `Admin Auth: user not found, identifier=...` |
| พบ user | `Admin Auth: user found uid=... role=... status=...` |
| User inactive | `Admin Auth: user inactive, uid=...` |
| Role ไม่ได้รับอนุญาต | `Admin Auth: role not allowed, uid=... role=...` |
| รหัสผ่านผิด | `Admin Auth: password verify failed, uid=...` |
| Login สำเร็จ | `Admin Auth: login success uid=... role=... redirect=...` |
| Logout / Clear session | `Admin Auth: logout, admin_id=...` หรือ `Admin Auth: clearSession, admin_id=...` |
| Filter: ยังไม่ login | `AdminAuthFilter: not logged in, redirect to login. intended_url=...` |
| Filter: role ไม่ถูกต้อง | `AdminAuthFilter: invalid role, clear session. admin_id=... role=...` |

ดู log ได้ที่ `writable/logs/log-YYYY-MM-DD.log` (ในโหมด development ระดับ debug ถูกเขียน ถ้า `Config\Logger::$threshold` เป็น 9)

---

## 7. Session (app/Config/Session.php)

- driver: FileHandler
- savePath: WRITEPATH . 'session' (writable/session)
- expiration: 7200 วินาที
- cookieName: ci_session

ตรวจว่าโฟลเดอร์ `writable/session` มีสิทธิ์เขียน

---

## 8. สคริปต์ที่เกี่ยวข้อง

| สคริปต์                              | การใช้                                                                           |
| ------------------------------------ | -------------------------------------------------------------------------------- |
| `scripts/set_admin_credentials.php`  | ตั้ง login_uid, password, role=admin ให้ user (หรือ user คนแรกถ้ายังไม่มี admin) |
| `scripts/run_fix_user_role_enum.php` | เพิ่ม 'admin','editor' ใน ENUM role และตั้ง role=admin ให้ login_uid=admin       |
| `scripts/check_admin_login.php`      | ตรวจโครงสร้าง user และ user ที่ login_uid=admin (รวม password_verify)            |

---

## 9. ล้าง Session (เมื่อเด้งไปหน้าแรกโดยไม่คาดคิด)

- **Logout:** `GET /admin/logout` — ล้าง session ของ admin แล้ว redirect ไป admin/login
- **Clear session:** `GET /admin/clear-session` — ล้าง session ทั้งหมด (ใช้เมื่อ session ค้างหรือ state ผิดปกติ) แล้ว redirect ไป admin/login

ใน Filter เมื่อ role ไม่ถูกต้อง จะ **ล้าง session ก่อน** redirect ไป admin/login (ไม่ส่งไปหน้าแรกอีก)

## 10. จุดที่อาจทำให้ Login ไม่ได้

1. **baseURL ไม่ตรงกับ URL จริง** → form action หรือ redirect ผิด
2. **ตาราง user ไม่มี role 'admin'/'editor'/'super_admin'** → แก้ ENUM และตั้ง role (รัน run_fix_user_role_enum.php)
3. **user.status ไม่ใช่ 'active'** → อัปเดตเป็น active
4. **password ใน DB ไม่ใช่ bcrypt** → รัน set_admin_credentials.php เพื่อตั้งรหัส admin123
5. **ไม่มี user ที่ email หรือ login_uid ตรงกับที่ใส่** → ตรวจข้อมูลใน DB หรือตั้ง login_uid=admin ด้วย set_admin_credentials.php
6. **Session ไม่บันทึก** → ตรวจ writable/session และ cookie
7. **เด้งไปหน้าแรกโดยไม่คาดคิด** → มักเกิดจาก admin_role ใน session เป็น null/ไม่ตรง — เปิด `/admin/clear-session` เพื่อล้าง session แล้ว login ใหม่
