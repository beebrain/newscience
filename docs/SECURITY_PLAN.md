# แผนความปลอดภัย (Security Plan) — newScience

> เอกสารนี้สรุปผลตรวจสอบความปลอดภัยของโค้ด และเป็น **แผนสำหรับพิจารณาแก้ไขในอนาคต**
> ไม่จำเป็นต้องแก้ทั้งหมดทันที — ให้ใช้ตารางลำดับความสำคัญด้านล่างเลือกทำเป็นรอบ ๆ

- **วันที่ตรวจ:** 2026-06-17
- **ขอบเขต:** โค้ด `app/` ทั้งหมด + การตั้งค่า production (win-kc / `sci.uru.ac.th`, IIS + PHP 8.3, CodeIgniter 4.6)
- **ผลโดยรวม:** ✅ ไม่พบช่องโหว่วิกฤตที่นำไปสู่การยึดระบบได้ทันที (RCE / auth bypass) — โครงสร้างพื้นฐานทำมาดี เหลือเป็นงาน hardening ป้องกันเชิงลึก

---

## 0. สิ่งที่ทำมาดีแล้ว (ไม่ต้องแก้ — ไว้กันลืมว่าตรวจแล้ว)

| หัวข้อ | สถานะ |
|---|---|
| RCE sinks (`eval`/`system`/`shell_exec`/`unserialize`) | ไม่มีในเส้นทางที่เข้าถึงผ่านเว็บ (`proc_open` อยู่ใน CLI command, `popen` อยู่ใน PHPMailer) |
| Dev routes (`/dev/login-as-admin` ฯลฯ) | ปิดบน production จริง — `Dev` โยน 404, `DevTools` `exit()` ที่ constructor; `CI_ENVIRONMENT=production` |
| SQL Injection | ใช้ Query Builder / parameterized binding ทั่วทั้งระบบ ไม่พบการต่อ string ดิบจาก input |
| File upload สาธารณะ (complaints, evaluate) | whitelist นามสกุล/MIME + เก็บใน `writable/uploads/` (นอก document root) |
| Path traversal (`serve/uploads/...`) | กรอง `..` + `basename()` ก่อนเข้าถึงไฟล์ |
| Session cookie | `httponly=true`, `samesite=Lax` |
| Output | escape ผ่าน `esc()` ของ CI4 |
| Anti-bot ฟอร์มสาธารณะ | ✅ ติดตั้งแล้ว (honeypot + timing + token blocklist + reCAPTCHA v3) — ดู [SECURITY_PLAN ภาคผนวก A](#ภาคผนวก-a--เกราะกันบอตที่ติดตั้งแล้ว) |

---

## 1. ตารางสรุปจุดเสี่ยง (เรียงตามความสำคัญ)

| # | ระดับ | หัวข้อ | แรงงานโดยประมาณ | กระทบผู้ใช้? |
|---|---|---|---|---|
| F-1 | 🟠 Medium | CSRF ไม่ได้เปิดแบบ global (6/146 POST routes) | กลาง (ต้องทดสอบ regression) | อาจมี — ต้องทดสอบทุกฟอร์ม/AJAX |
| F-2 | 🟠 Medium | `public/uploads/` ไม่บล็อกการรัน script | ต่ำ | ไม่กระทบ |
| F-3 | 🟠 Medium | `cookie.secure = false` บน production HTTPS | ต่ำมาก | ไม่กระทบ |
| F-4 | 🟡 Medium | `orderBy($metric)` รับ input ตรง (edoc analytics) | ต่ำ | ไม่กระทบ |
| F-5 | 🟢 Low | `Serve::fileByPath` กรอง `..` แบบ string แทน `realpath()` | ต่ำ | ไม่กระทบ |
| F-6 | 🟢 Low | IIS `httpErrors errorMode="Detailed"` รั่ว stack trace | ต่ำมาก | ไม่กระทบ |
| F-7 | 🟢 Low | Impersonation ("Login As") — ควร verify role ซ้ำใน controller | ต่ำ | ไม่กระทบ |
| F-8 | 🟢 Low | CSRF `tokenRandomize=false`, `Session matchIP=false` | ต่ำมาก | ไม่กระทบ |
| F-9 | 🟢 Info | `DevTools` คืน HTTP 200 แทน 404 (info leak เล็กน้อย) | ต่ำมาก | ไม่กระทบ |

---

## 2. รายละเอียดแต่ละจุด + วิธีแก้

### F-1 🟠 CSRF ไม่ได้เปิดแบบ global
- **ตำแหน่ง:** `app/Config/Filters.php` (`$globals['before']` — บรรทัด `// 'csrf'` ถูกคอมเมนต์), `app/Config/Routes.php`
- **อาการ:** มีเพียง 6 จาก 146 POST routes ที่ใส่ `['filter' => 'csrf']` (complaints, scienceweek, impersonation/start, complaints/update-status) — ที่เหลือ เช่น `admin/news/store`, `admin/settings/store`, `admin/users/system-access/(:num)` (เปลี่ยนสิทธิ์ผู้ใช้), `admin/programs/store` ฯลฯ **ไม่มี CSRF token**
- **ความเสี่ยง:** หลอกผู้ดูแลที่ login อยู่ให้เปิดหน้าเว็บที่ฝัง form/JS ร้าย → ยิงคำสั่งแทนผู้ดูแล (สร้าง/ลบเนื้อหา, เปลี่ยน role/สิทธิ์) = privilege escalation
- **ตัวบรรเทาที่มีอยู่:** `samesite=Lax` บล็อก cookie บน cross-site POST ส่วนใหญ่ — แต่ไม่ครอบคลุม GET ที่เปลี่ยนสถานะ และเบราว์เซอร์เก่า
- **วิธีแก้:**
  ```php
  // app/Config/Filters.php
  public array $globals = [
      'before' => [
          'csrf' => ['except' => [
              // ใส่ route ที่เป็น API/webhook ภายนอกที่ไม่มี token เช่น
              'api/*', 'cv-ai/*', 'personnel-api/*',
          ]],
      ],
      ...
  ];
  ```
  - จากนั้นตรวจให้ทุก `<form>` admin มี `<?= csrf_field() ?>` และ AJAX ส่ง header `X-CSRF-TOKEN`
- **ทดสอบ:** หลังเปิด ต้องคลิกทดสอบทุกฟอร์ม/ปุ่ม AJAX ในแผง admin ว่ายังบันทึกได้ (ระวัง regression — นี่คือเหตุที่ effort = กลาง)

### F-2 🟠 `public/uploads/` ไม่บล็อกการรัน script
- **ตำแหน่ง:** `public/web.config` (rewrite เสิร์ฟไฟล์จริงโดยตรง), ไม่มี `web.config` ใน `public/uploads/`
- **ความเสี่ยง:** ถ้ามีช่องอัปโหลด `.php` หลุดลง `public/uploads/` (วันนี้ยังไม่มี เพราะทุก uploader whitelist นามสกุล) → IIS รัน PHP = **RCE**
- **วิธีแก้:** สร้าง `public/uploads/web.config`
  ```xml
  <?xml version="1.0" encoding="UTF-8"?>
  <configuration>
    <system.webServer>
      <handlers>
        <clear />   <!-- ลบ handler ทั้งหมด: ไฟล์ในนี้เป็น static เท่านั้น ไม่รันโค้ด -->
      </handlers>
      <security>
        <requestFiltering>
          <fileExtensions allowUnlisted="true">
            <add fileExtension=".php" allowed="false" />
            <add fileExtension=".phtml" allowed="false" />
            <add fileExtension=".asp" allowed="false" />
            <add fileExtension=".aspx" allowed="false" />
          </fileExtensions>
        </requestFiltering>
      </security>
    </system.webServer>
  </configuration>
  ```
- **เสริม:** เพิ่ม `<hiddenSegments>` หรือ requestFiltering ปฏิเสธ `.env` ที่ web.config ราก (กันกรณี misconfig)

### F-3 🟠 `cookie.secure = false` บน production (HTTPS)
- **ตำแหน่ง:** `.env` บน win-kc (ไม่ได้ตั้ง `cookie.secure` → default `false` จาก `app/Config/Cookie.php:57`)
- **ความเสี่ยง:** session/CSRF cookie อาจถูกส่งผ่าน HTTP → ถูกดักจับ (MITM)
- **วิธีแก้:** เพิ่มใน `.env` ของ **production** เท่านั้น (local ปล่อย false เพราะ http)
  ```ini
  cookie.secure = true
  ```
  แล้ว `php spark cache:clear` (ไม่ต้อง deploy ใหม่)

### F-4 🟡 `orderBy($metric)` รับ input ตรง
- **ตำแหน่ง:** [app/Controllers/Edoc/DocumentAnalysisController.php:320,331](../app/Controllers/Edoc/DocumentAnalysisController.php) (`getAdvancedAnalytics`)
- **อาการ:** `$metric = getGet('metric')`, `$dimension = getGet('dimension')` ส่งเข้า `orderBy()` / เลือกคอลัมน์ — CI4 escape identifier ให้บางส่วน แต่ไม่ควรพึ่ง (ต้อง login edoc)
- **วิธีแก้:** whitelist ก่อนใช้
  ```php
  $allowedMetrics = ['doc_count','avg_pages','total_paper','max_pages','min_pages'];
  if (!in_array($metric, $allowedMetrics, true)) { $metric = 'doc_count'; }
  $allowedDimensions = ['doctype','owner','time','participant'];
  if (!in_array($dimension, $allowedDimensions, true)) { $dimension = 'doctype'; }
  ```

### F-5 🟢 `Serve::fileByPath` กรอง `..` แบบ string
- **ตำแหน่ง:** [app/Controllers/Serve.php](../app/Controllers/Serve.php) (`extractUploadsRelativePath`)
- **อาการ:** ลบ `..` ด้วย `str_replace` + เช็ค `strpos('..')===false` — ปัจจุบันปลอดภัย แต่เปราะ
- **วิธีแก้ (hardening):** หลังรวม path ให้ใช้ `realpath()` แล้วยืนยันว่าอยู่ใต้ฐานที่อนุญาต
  ```php
  $real = realpath($fullPath);
  $baseReal = realpath($writableBase);
  if ($real === false || !str_starts_with($real, $baseReal)) {
      return $this->response->setStatusCode(404);
  }
  ```

### F-6 🟢 IIS แสดง error แบบ Detailed
- **ตำแหน่ง:** `public/web.config` → `<httpErrors errorMode="Detailed" />`
- **วิธีแก้:** บน production ตั้งเป็น `Custom` (CI4 ปิด error ของ PHP แล้วเพราะ `CI_ENVIRONMENT=production` แต่ error ระดับ IIS ยัง detailed)

### F-7 🟢 Impersonation role check
- **ตำแหน่ง:** `app/Controllers/Admin/Impersonation.php`, `app/Controllers/Admin/UserManagement.php` (`impersonateStudent`)
- **แนะนำ:** ยืนยันใน controller ว่าเฉพาะ `super_admin` เรียก `start()` ได้ (มี `csrf`+`adminauth`+`adminsystemaccess` แล้ว แต่ควรเช็ค role ในเมธอดซ้ำเพื่อกัน faculty_admin ยกระดับเป็น super_admin)

### F-8 🟢 Hardening เสริม
- `app/Config/Security.php` → `$tokenRandomize = true` (กัน BREACH)
- `app/Config/Session.php` → พิจารณา `$matchIP = true` (ผูก session กับ IP — ระวังผู้ใช้ที่ IP เปลี่ยนบ่อย)

### F-9 🟢 DevTools คืน 200
- **ตำแหน่ง:** `app/Controllers/DevTools.php:18` → `exit('...')` คืน HTTP 200
- **วิธีแก้:** เปลี่ยนเป็น `throw PageNotFoundException::forPageNotFound();` เหมือน `Dev` controller

---

## 3. ลำดับการแก้ที่แนะนำ (Roadmap)

**รอบที่ 1 — ทำได้เลย ผลสูง ความเสี่ยงต่ำ (≈ ครึ่งวัน)**
- [ ] F-3 `cookie.secure = true` บน prod .env
- [ ] F-2 วาง `public/uploads/web.config`
- [ ] F-4 whitelist `$metric`/`$dimension`
- [ ] F-6 IIS error → Custom

**รอบที่ 2 — ต้องทดสอบ regression (≈ 1 วัน)**
- [ ] F-1 เปิด CSRF global + ตรวจทุกฟอร์ม/AJAX admin
- [ ] F-7 ใส่ role check ใน impersonation

**รอบที่ 3 — hardening (เมื่อมีเวลา)**
- [ ] F-5 `realpath()` containment ใน Serve
- [ ] F-8 tokenRandomize / matchIP
- [ ] F-9 DevTools → 404

---

## 4. การยืนยันหลังแก้ (Verification)
- เปิด `https://sci.uru.ac.th/dev/login-as-admin` → ต้อง 404 (ยังคงเดิม)
- ทดสอบส่งฟอร์ม complaints + scienceweek ผ่าน browser จริง → ยังบันทึกได้
- ลองอัปโหลดไฟล์ `.txt` เปลี่ยนชื่อเป็น `.php` ผ่านช่องอัปโหลด admin → ต้องถูกปฏิเสธ และเข้าถึง `public/uploads/<ชื่อ>.php` ตรง ๆ ต้องไม่รัน
- ตรวจ response header ว่ามี `Set-Cookie: ...; Secure; HttpOnly; SameSite=Lax`

---

## ภาคผนวก A — เกราะกันบอตที่ติดตั้งแล้ว
ฟอร์มสาธารณะ `complaints/submit` และ `scienceweek/register/{key}` มีการป้องกัน 5 ชั้น (โค้ดรวมใน `App\Controllers\Concerns\AntiBot` + partial `anti_bot_field`, `recaptcha_v3`):
① CSRF ② honeypot (`website`) ③ timing (≥3 วิ) ④ token blocklist (`lxbfyeaa`) ⑤ reCAPTCHA v3 (score < `recaptcha.minScore` = บล็อก)
- ตรวจไม่ผ่าน → silent-drop (แกล้งสำเร็จ ไม่บันทึก ไม่ส่งเมล) + log `warning` พร้อม IP/UA
- keys อยู่ใน `.env` (`recaptcha.*`) ทั้ง local + win-kc (ไม่เข้า git); `minScore=0.3`
- ปรับความเข้ม: แก้ `recaptcha.minScore` ใน prod `.env` แล้ว `php spark cache:clear`
- รายละเอียด: ดู memory `newscience-public-form-antibot`

## ภาคผนวก B — หมายเหตุ environment
- Production = win-kc (Tailscale `100.74.66.65`, IIS, `C:/inetpub/newscience`) เสิร์ฟ `sci.uru.ac.th`
- Deploy: push origin master → `scripts/git-pull-win-kc.sh`
- ระวัง: MySQL เปิดพอร์ต 3306 ออกภายนอก + client ทุกตัว NAT เป็น `10.254.0.2` (เคยโดน host-block) — พิจารณา firewall จำกัด 3306 (ดู memory `newscience-deploy-and-mysql-hostblock`)
