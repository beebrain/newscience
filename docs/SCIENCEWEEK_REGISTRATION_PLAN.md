# แผนงาน: ระบบรับสมัครออนไลน์ งานสัปดาห์วิทยาศาสตร์ 2569

> เอกสารนี้เขียนเพื่อให้ **agent หรือผู้พัฒนาคนใด ๆ** เข้ามาทำงานต่อได้ทันที
> อ่านส่วน "บริบท + การตัดสินใจ" ให้จบก่อนเริ่มเขียนโค้ด

---

## 1. บริบทและการตัดสินใจที่ล็อกแล้ว

- **ฐานระบบ:** CodeIgniter 4 (PHP ^8.1, MySQLi) — เป็นโปรเจกต์ `newScience` เดิม
- **รูปแบบ:** เป็น **โมดูลแยกภายใน newScience** (ไม่ใช่ repo/แอปใหม่) ใช้ route prefix `/scienceweek`, ตาราง DB prefix `sw_`, namespace controller `App\Controllers\ScienceWeek`
- **Auth อาจารย์:** **reuse ระบบ login เดิมของ newScience** — ใช้ filter `adminauth` (ตรวจ `session('admin_logged_in')` + role) ที่มีอยู่แล้ว ([app/Filters/AdminAuthFilter.php](../app/Filters/AdminAuthFilter.php)). ไม่สร้าง login ใหม่
- **การตรวจสอบของผู้สมัคร:** **หน้า list สาธารณะอย่างเดียว** — ไม่มีรหัสอ้างอิง ไม่ส่งอีเมล ผู้สมัครเข้าหน้า public list กรองตามรายการ/ระดับ แล้วเห็นชื่อทีม/โรงเรียนของตน
- ที่มาข้อมูลทั้งหมด: ไฟล์ใน [Sci_week/](../Sci_week/) (วิเคราะห์แล้วในข้อ 3)

### หน้าที่ของระบบ (สรุปจากโจทย์)
1. ฟอร์มรับสมัครออนไลน์ ครอบคลุม **5 รายการแข่งขัน** และ **ทุกประเภท/ระดับ**
2. อาจารย์ login → ดูรายชื่อผู้สมัครแยกตามรายการ → ลบข้อมูลที่ไม่สมบูรณ์
3. หน้าสาธารณะให้ผู้สมัครตรวจสอบว่ามีชื่อในระบบแล้ว

---

## 2. สถาปัตยกรรมไฟล์ (สิ่งที่ต้องสร้าง)

```
app/
  Config/
    Routes.php                         (แก้: เพิ่ม group 'scienceweek')
    SciWeek.php                        (ใหม่: catalog 5 รายการแข่งขัน — single source of truth)
  Controllers/ScienceWeek/
    Register.php                       (ใหม่: public — แสดงฟอร์ม + บันทึก)
    Verify.php                         (ใหม่: public — หน้า list ตรวจสอบรายชื่อ)
    Manage.php                         (ใหม่: teacher-only — list/detail/delete, filter adminauth)
  Models/
    SwRegistrationModel.php            (ใหม่)
    SwParticipantModel.php             (ใหม่)
  Database/Migrations/
    <ts>_create_sw_registrations.php   (ใหม่)
    <ts>_create_sw_participants.php    (ใหม่)
  Views/scienceweek/
    layout.php                         (ใหม่: layout ของโมดูล)
    index.php                          (ใหม่: หน้าเลือกรายการแข่งขัน)
    form.php                           (ใหม่: ฟอร์มสมัคร — render ตาม catalog)
    success.php                        (ใหม่: หน้ายืนยันหลังบันทึก)
    verify.php                         (ใหม่: หน้า list สาธารณะ)
    manage/
      list.php                         (ใหม่: ตารางผู้สมัครฝั่งอาจารย์)
      detail.php                       (ใหม่: รายละเอียด + ปุ่มลบ)
tests/unit/
    SwRegistrationValidationTest.php   (ใหม่)
```

> **หลักการ:** competition แต่ละรายการต่างกันเรื่องจำนวนสมาชิก/ระดับ/ฟิลด์พิเศษ
> จึงเก็บกฎทั้งหมดใน `Config/SciWeek.php` (data-driven) แล้ว controller/view/validation อ่านจาก config ตัวเดียว
> ห้าม hard-code กฎของแต่ละรายการกระจายในหลายไฟล์

---

## 3. ข้อมูลการแข่งขัน 5 รายการ (วิเคราะห์จากเอกสารต้นฉบับ)

| key | ชื่อรายการ | โครงสร้างทีม | ระดับ (level) | จำกัด | ฟิลด์พิเศษ |
|---|---|---|---|---|---|
| `seed_art` | ศิลปะจากเมล็ดพันธุ์ (Seed Art) | ทีม 3 คน (คงที่) | `primary` ประถม, `lower_secondary` ม.ต้น | 15 ทีม/ระดับ | ชั้นเรียนต่อคน |
| `rov` | E-sport ROV | หลัก 5 คน + สำรอง 0–2 | `primary_lower` ประถม-ม.ต้น, `lower_higher` ม.ต้น-อุดมศึกษา | 16 ทีม (รวม), 1 ทีม/สถาบัน/ระดับ | game_id ต่อคน, ชื่อในเกมห้ามหยาบ |
| `python` | เขียนโปรแกรม Python | ทีม 2 คน | `secondary` มัธยม/เทียบเท่า, `higher` อุดมศึกษา/เทียบเท่า | ≤2 ทีม/สถาบัน | ชั้นเรียนต่อคน |
| `recycle` | ออกแบบชุดรีไซเคิล | ทีม 1–5 คน | `primary` ประถม, `secondary` มัธยม | — | ครูที่ปรึกษาได้ 2 คน |
| `sci_drawing` | วาดภาพจินตนาการวิทยาศาสตร์ | เดี่ยว (1 คน) | `primary_upper` ประถมปลาย, `lower_secondary` ม.ต้น | — | อายุ, อาชีพ, ID Line, ครูควบคุมได้ 2 คน |

**ฟิลด์ร่วมทุกรายการ (ระดับ registration):** โรงเรียน/สถานศึกษา, ที่อยู่, เบอร์โทรโรงเรียน, อีเมล, ชื่อทีม (ถ้ามี), ครู/อาจารย์ผู้ควบคุม (ชื่อ-ตำแหน่ง-โทร-อีเมล)
**ฟิลด์ร่วมต่อผู้เข้าแข่งขัน:** ชื่อ-สกุล, ชั้น/ระดับ + ฟิลด์พิเศษตามตาราง

> หมายเหตุ deadline ในเอกสารไม่ตรงกันแต่ละรายการ (31 ก.ค. / 7 ส.ค. / 8 ส.ค. / 17 ส.ค. 2569) — เก็บ `deadline` ราย competition ใน config. ถ้ายังไม่ยืนยัน ให้ตั้งเป็น `null` (เปิดรับตลอด) และใส่ TODO

---

## 4. โครงสร้างฐานข้อมูล

### `sw_registrations`
| คอลัมน์ | ชนิด | หมายเหตุ |
|---|---|---|
| id | INT PK AI | |
| competition_key | VARCHAR(40) | ต้องตรงกับ key ใน Config/SciWeek.php |
| level_key | VARCHAR(40) | |
| school_name | VARCHAR(255) | |
| school_address | TEXT NULL | |
| contact_phone | VARCHAR(40) | |
| contact_email | VARCHAR(190) NULL | |
| team_name | VARCHAR(190) NULL | |
| coach_name | VARCHAR(190) | ครูผู้ควบคุม |
| coach_position | VARCHAR(120) NULL | |
| coach_phone | VARCHAR(40) NULL | |
| coach_email | VARCHAR(190) NULL | |
| extra | JSON NULL | ครูที่ปรึกษาคนที่ 2, ID Line ฯลฯ |
| status | VARCHAR(20) DEFAULT 'pending' | pending / confirmed |
| ip_address | VARCHAR(45) NULL | กันสแปม |
| created_at, updated_at | DATETIME | |
| deleted_at | DATETIME NULL | **soft delete** (อาจารย์ลบ) |

ดัชนี: `(competition_key, level_key)`, `deleted_at`

### `sw_participants`
| คอลัมน์ | ชนิด | หมายเหตุ |
|---|---|---|
| id | INT PK AI | |
| registration_id | INT FK → sw_registrations.id | ON DELETE CASCADE |
| full_name | VARCHAR(190) | |
| level_class | VARCHAR(80) NULL | ชั้น/ระดับการศึกษา |
| role | VARCHAR(20) DEFAULT 'main' | main / reserve |
| game_id | VARCHAR(120) NULL | สำหรับ ROV |
| age | INT NULL | สำหรับวาดภาพ |
| sort_order | INT DEFAULT 0 | |

> ใช้ migration timestamp รูปแบบเดิมของโปรเจกต์: `YYYYMMDDHHMMSS_snake_name.php` (ดู [app/Database/Migrations/](../app/Database/Migrations/))

---

## 5. Routes ที่ต้องเพิ่ม (app/Config/Routes.php)

```php
$routes->group('scienceweek', static function ($routes) {
    // public
    $routes->get('/', 'ScienceWeek\Register::index');
    $routes->get('register/(:segment)', 'ScienceWeek\Register::form/$1');     // เลือก competition
    $routes->post('register/(:segment)', 'ScienceWeek\Register::save/$1', ['filter' => 'csrf']);
    $routes->get('success/(:num)', 'ScienceWeek\Register::success/$1');
    $routes->get('verify', 'ScienceWeek\Verify::index');                       // หน้า list สาธารณะ

    // teacher-only (reuse newScience admin auth)
    $routes->group('manage', ['filter' => 'adminauth'], static function ($routes) {
        $routes->get('/', 'ScienceWeek\Manage::index');
        $routes->get('(:num)', 'ScienceWeek\Manage::detail/$1');
        $routes->post('(:num)/delete', 'ScienceWeek\Manage::delete/$1', ['filter' => 'csrf']);
    });
});
```

---

## 6. กฎ Validation (อ่านจาก Config/SciWeek.php)

ต่อ competition ให้ validate:
- `level_key` อยู่ในรายการระดับที่ config กำหนด
- จำนวน participant อยู่ในช่วง `team_min..team_max`; ROV: หลัก = 5, สำรอง ≤ 2
- ทุก participant ต้องมี `full_name` ไม่ว่าง; ฟิลด์พิเศษ required ตาม config (เช่น ROV ต้องมี `game_id`)
- โรงเรียน + เบอร์ติดต่อ + ชื่อครูผู้ควบคุม required เสมอ
- เช็กเพดานจำนวนทีม: `seed_art` 15/ระดับ, `rov` 16 รวม + 1 ทีม/สถาบัน/ระดับ, `python` ≤2 ทีม/สถาบัน — นับจาก `sw_registrations` ที่ยังไม่ถูกลบ ถ้าเต็มให้ปิดรับและแสดงข้อความ
- ตรวจ deadline ถ้า config กำหนด

---

## 7. งานย่อย (task breakdown) + เกณฑ์ผ่าน

ทำตามลำดับ; แต่ละ task commit แยกได้

- [ ] **T1 – Config catalog** สร้าง `app/Config/SciWeek.php` บรรจุ 5 competition (key, ชื่อ TH, levels, team_min/max, reserve, ฟิลด์พิเศษ, เพดานทีม, deadline). ✅ผ่านเมื่อ: เรียก config แล้วได้ครบ 5 รายการพร้อม metadata
- [ ] **T2 – Migrations** สร้าง 2 ตารางตามข้อ 4 + รัน `php spark migrate` ผ่าน. ✅ตารางถูกสร้าง, soft delete + FK cascade ทำงาน
- [ ] **T3 – Models** `SwRegistrationModel` (useSoftDeletes, allowedFields, casts extra→json), `SwParticipantModel`. ✅insert/find ได้
- [ ] **T4 – Register controller + views** `index` (เลือกรายการ), `form/{key}` (render dynamic จาก config), `save` (validate ข้อ 6 + เพดานทีม + บันทึก registration+participants ใน transaction), `success`. ✅สมัครครบทั้ง 5 รายการได้, ฟอร์มไม่ผ่าน validation แสดง error ภาษาไทย
- [ ] **T5 – หน้า success** แสดงสรุปข้อมูลที่บันทึก + ลิงก์ไปหน้า verify. ✅เห็นข้อมูลถูกต้อง
- [ ] **T6 – Verify (public list)** กรองตาม competition + level, แสดงชื่อทีม/โรงเรียน + รายชื่อผู้เข้าแข่งขัน (read-only, ไม่โชว์เบอร์/อีเมล/ข้อมูลส่วนตัวเกินจำเป็น). ✅ผู้สมัครหาเจอชื่อตัวเอง
- [ ] **T7 – Manage (teacher)** หลัง `adminauth`: list กรองตามรายการ/ระดับ + ค้นหา, detail ดูครบทุกฟิลด์, delete = soft delete พร้อม confirm + CSRF. ✅อาจารย์ที่ login เท่านั้นเข้าได้; ลบแล้วหายจาก public list แต่ row ยังอยู่ (deleted_at)
- [ ] **T8 – Tests** unit test validation + เพดานทีม (ดูแพตเทิร์นเดิม [tests/unit/](../tests/unit/)). ✅`./vendor/bin/phpunit` ผ่าน
- [ ] **T9 – ตรวจจริงบนเบราว์เซอร์** สมัคร→success→verify→login อาจารย์→ลบ ครบ flow ทั้ง 5 รายการ

---

## 8. ข้อควรระวัง / หมายเหตุสำหรับผู้ทำงานต่อ

- **อย่าสร้าง login ใหม่** — ใช้ filter `adminauth` ที่มีอยู่ (alias ใน [app/Config/Filters.php](../app/Config/Filters.php) บรรทัด 37). อาจารย์ = admin user เดิม
- ใช้ **CSRF filter** กับทุก POST (มีในตัวอย่าง route แล้ว) — ตามแพตเทิร์น `complaints/submit`
- การลบใช้ **soft delete เท่านั้น** เผื่อกู้คืน — โจทย์ระบุ "ลบข้อมูลที่ไม่สมบูรณ์" ไม่ใช่ลบถาวร
- หน้า public verify **ห้ามเปิดเผยเบอร์โทร/อีเมล/บัตรประชาชน** — แสดงเฉพาะชื่อทีม/โรงเรียน/รายชื่อ
- เพดานจำนวนทีมต้องเช็กแบบ atomic ใน transaction เดียวกับการ insert กันการสมัครชนกัน
- competition `rov` & `python` มีกฎ "จำกัดทีม/สถาบัน" — เช็กจาก `school_name` (พิจารณา normalize ช่องว่าง/ตัวพิมพ์)
- deadline แต่ละรายการต่างกันและบางส่วนในเอกสารยังเป็นปี 2568 (คัดลอกเก่า) — **ยืนยันวันที่กับผู้จัดก่อน go-live**
- ไฟล์เอกสารต้นฉบับอยู่ใน [Sci_week/](../Sci_week/) ใช้ทวนรายละเอียดเกณฑ์/รางวัลเพิ่มเติม

---

## 9. ข้อมูลติดต่อผู้รับผิดชอบแต่ละรายการ (จากเอกสาร — เผื่อยืนยันรายละเอียด)
- Seed Art: ผศ.ดร.สุทธิดา 089-858-6805 / ผศ.ดร.วารุณี 088-252-1477 / รศ.ดร.สิริวดี 081-751-0399
- ROV: อ.อนุชา เรืองศิริวัฒนกุล anucha@uru.ac.th 089-707-2231
- Python: อ.พรเทพ จันทร์เพ็ง pornthep@uru.ac.th 089-957-0965
- Recycle: รศ.ดร.ศรัณยู 081-786-1566
- วาดภาพ: ผศ.ดร.สุภาพร พงศ์ธรพฤกษ์ ajann_envi@uru.ac.th 089-704-4407
