# PLAN — เลิกใช้ vendored PHPMailer แล้วรวมเส้นเมล Edoc มาใช้ CI4 Email

> สถานะ: รออนุมัติเริ่ม • วันที่: 2026-06-19 • ขอบเขต: ลด dependency โดยไม่กระทบระบบเดิม

## 1. วัตถุประสงค์
- ลบ `app/ThirdParty/PHPMailer/` (vendored ~476KB) ออกจาก repo
- ให้ทั้งระบบเหลือ **กลไกส่งเมลเดียว** = `CodeIgniter\Email` (`Config\Services::email()`)
- ได้ security patch ของ CI4 Email อัตโนมัติ (vendored copy ไม่เคยอัปเดต)
- **เงื่อนไขบังคับ:** พฤติกรรมเมลเดิมต้องไม่เปลี่ยน (subject/body/HTML/BCC/ภาษาไทย/return contract)

## 2. ทำไมความเสี่ยงต่ำกว่าที่เห็น
- `app/Config/Email.php::applyLegacyMailEnvFallbacks()` อ่าน env `mail.*` ชุดเดียวกับ PHPMailer อยู่แล้ว
  (`mail.smtpHost/smtpUsername/smtpPassword/smtpPort/smtpSecure`) → ใช้ Gmail SMTP `465/ssl, datascience@uru.ac.th` เหมือนกัน
- เส้น CI4 Email **พิสูจน์แล้วว่าส่งได้จริง** — เมลแจ้งเตือน complaint (#3–#6) ออกจาก dev เมื่อ 2026-06-19 เช้า
- ⇒ ความเสี่ยงใหญ่สุด (SSL handshake บนพอร์ต 465) ถูก de-risk ไปแล้วก่อนเริ่ม

## 3. Blast radius
| สิ่งที่แตะ | รายละเอียด | หมายเหตุ |
|---|---|---|
| เขียนไส้ในใหม่ | `app/Models/Edoc/SendmailModel.php` — `sendMail()`, `sendMailHTML()`, ctor | คง signature + return string เดิม |
| ลบ (เฟส 2) | `app/ThirdParty/PHPMailer/` | หลังเทสต์เขียวเท่านั้น |
| ตัวเรียก (ไม่แตะ) | `Controllers/Edoc/GeneralController.php` (sendMailHTML) · `Controllers/Evaluate/AdminEvaluateController.php` (sendMail+bcc) · `Controllers/Evaluate/LectureEvaluateController.php` | contract เดิม |
| เส้นเมลที่กระทบ | Edoc general (HTML), Evaluate (text + BCC) | ต้องเทสต์ |
| **ไม่กระทบ** | complaint, certificate | ใช้ CI4 Email อยู่แล้ว |

## 4. Feature parity (PHPMailer → CI4 Email)
| PHPMailer ที่ใช้จริง | CI4 Email เทียบเท่า | ความเสี่ยง |
|---|---|---|
| `isSMTP` + auth + `SMTPSecure=ssl` + `Port=465` | config เดิม (พิสูจน์แล้ว) | 🟢 ต่ำ |
| `CharSet=UTF-8` (ไทย) | `charset=UTF-8` (default) | 🟢 |
| `setFrom(email,name)` | `setFrom(email,name)` | 🟢 |
| `addAddress` + `explode(',')` | `setTo()` รับ comma/array | 🟢 |
| `addBcc` | `setBCC()` | 🟢 |
| `isHTML(true)`+`Body` | `setMailType('html')`+`setMessage()` | 🟢 |
| `AltBody` (text สำรองใน sendMailHTML) | CI4 auto-gen หรือ `setAltMessage()` ถ้ารองรับ | 🟡 ต่ำ |

## 5. Risk register (รายละเอียด + mitigation)

### R1 — Return contract เพี้ยน  · ความถี่ ต่ำ · ผลกระทบ สูง · รวม 🟠 กลาง
- **อะไร:** `GeneralController.php:288` ตัดสิน success/error ด้วย string ตรงตัว `$result['message'] == 'ส่งอีเมล์สำเร็จ!'` ถ้าสตริงเปลี่ยน UI จะโชว์ error ทั้งที่ส่งสำเร็จ
- **mitigation:** เขียนใหม่ให้คืน `['message' => 'ส่งอีเมล์สำเร็จ!']` (success) และ `['message' => 'Error: '.$err]` (fail) **ไบต์ต่อไบต์เหมือนเดิม**
- **detection:** เทสต์เส้น Edoc general แล้วดูว่า UI ขึ้น success
- **rollback trigger:** UI โชว์ error ทั้งที่เมลถึง

### R2 — AltBody (plain-text สำรอง) ของ sendMailHTML หาย/ต่าง  · ความถี่ ต่ำ · ผลกระทบ ต่ำ · รวม 🟢 ต่ำ
- **อะไร:** PHPMailer ตั้ง `AltBody = $textContent` แยกจาก HTML. CI4 Email รุ่นเก่าบางรุ่นไม่มี setter ตรง ๆ จะ auto-gen alt จาก HTML
- **mitigation:** ตรวจ API เวอร์ชัน CI4 ที่ใช้ — ถ้ามี `setAltMessage()` ให้เซ็ต `$textContent`; ถ้าไม่มี ยอมรับ auto-alt (เนื้อหายังครบ แค่ฟอร์แมต plain ต่างเล็กน้อย)
- **detection:** เปิดเมล HTML ใน client ที่ render plain (เช่นตัวอย่างใน Gmail "show original")

### R3 — Error semantics ต่าง (throw vs return bool)  · ความถี่ กลาง · ผลกระทบ กลาง · รวม 🟠 กลาง
- **อะไร:** PHPMailer (`new PHPMailer(true)`) โยน exception เมื่อ fail แล้ว catch อ่าน `$mail->ErrorInfo`. CI4 `send()` คืน `false` ไม่ throw; error อยู่ที่ `printDebugger()`
- **mitigation:** แทนที่ try/catch ด้วย `if (! $email->send(false)) { log_message('error', $email->printDebugger(['headers'])); return ['message'=>'Error: ...']; }` — รักษา log + return เดิม
- **detection:** ทดสอบ fail-path (ตั้ง SMTP ผิดชั่วคราว) → ต้องได้ log + message `Error:` ไม่ใช่ fatal
- **หมายเหตุ:** โค้ดเดิมจับเฉพาะ `PHPMailer\Exception` — ถ้า error ชนิดอื่นจะหลุด (`$data` undefined) ⇒ เวอร์ชันใหม่ครอบดีขึ้นโดยปริยาย (อย่าแก้เกินขอบเขตนี้)

### R4 — Multi-recipient / BCC ส่งไม่ครบ  · ความถี่ ต่ำ · ผลกระทบ กลาง · รวม 🟢 ต่ำ
- **อะไร:** `sendMail()` รับ `$emailAddress` แบบ comma-separated และ `$bcc`
- **mitigation:** `setTo($emailAddress)` (CI4 รับ comma เอง) + `if($bcc!=='') setBCC($bcc)`; เก็บ `trim()` พฤติกรรมเดิมไว้
- **detection:** เทสต์ admin eval ที่มี BCC → ผู้รับ BCC ต้องได้เมล

### R5 — State leak ระหว่างส่งหลายฉบับใน request เดียว  · ความถี่ ต่ำ · ผลกระทบ กลาง · รวม 🟢 ต่ำ
- **อะไร:** CI4 Email instance สะสม recipient ข้ามการเรียกถ้าไม่ล้าง (PHPMailer สร้าง object ใหม่ทุกครั้ง)
- **mitigation:** เรียก `$email->clear(true)` ก่อนตั้งค่าทุกครั้ง หรือ `Services::email(null, false)` ขอ instance ใหม่ต่อการส่ง
- **detection:** เทสต์ส่ง 2 ฉบับติดกันใน request เดียว (เช่น loop) → ผู้รับไม่ปนกัน

### R6 — โปรดักชันใช้ env/พอร์ตต่างจาก dev  · ความถี่ ต่ำ · ผลกระทบ สูง · รวม 🟠 กลาง
- **อะไร:** dev ผ่านไม่ได้แปลว่าโปรดักชัน (win-kc) ผ่าน — อาจตั้ง SMTP คนละค่า/ไฟร์วอลล์ต่าง
- **mitigation:** ก่อน deploy ตรวจ `mail.*` / `email.*` บนโปรดักชันให้ตรง dev; deploy นอกเวลาใช้งาน; มี rollback tag
- **detection:** ยิงเมลทดสอบ 1 ฉบับบนโปรดักชันหลัง deploy ก่อนประกาศเสร็จ

## 6. ขั้นตอน (reversible, แยก 2 commit)
1. **Baseline:** ยิงเมลทดสอบ 3 เส้นบน dev (Edoc general / lecture eval / admin eval+bcc) → ยืนยันได้รับ + เก็บตัวอย่างไว้เทียบ
2. **Commit 1 — เขียนไส้ในใหม่:** `SendmailModel` ใช้ `Services::email()`, คง signature + return string เดิม, ลบ `require_once` 3 บรรทัด (**ยังไม่ลบ lib**)
3. **เทสต์ซ้ำ:** ยิง 3 เส้นอีกครั้ง เทียบ subject/body/HTML/BCC/ไทย กับ baseline + เทสต์ fail-path (R3)
4. **Commit 2 — ลบ lib:** เมื่อเช็กลิสต์เขียวครบ ค่อย `git rm -r app/ThirdParty/PHPMailer/`
5. **Deploy:** ตาม R6 — ตรวจ env โปรดักชัน, deploy นอกเวลา, ยิงเทสต์ 1 ฉบับ

## 7. Verification checklist ("ไม่กระทบระบบเดิม" = ผ่านครบ)
- [ ] เมล Edoc general มาถึง · HTML เรนเดอร์ · ภาษาไทยไม่เพี้ยน (R2)
- [ ] เมล lecture eval มาถึง
- [ ] เมล admin eval มาถึง + **BCC เข้าจริง** (R4)
- [ ] success → คืน `'ส่งอีเมล์สำเร็จ!'` · GeneralController โชว์ success (R1)
- [ ] error path → log + คืน `Error: ...` ไม่ fatal (R3)
- [ ] ส่ง 2 ฉบับติดกันไม่ปนผู้รับ (R5)
- [ ] (โปรดักชัน) ยิงเทสต์ 1 ฉบับผ่าน หลัง deploy (R6)

## 8. Rollback
- เฟส 1 พัง → `git revert <commit1>` (คืน SendmailModel เดิม, lib ยังอยู่ → ใช้งานได้ทันที)
- เฟส 2 พัง → `git revert <commit2>` (คืน vendored PHPMailer)
- โปรดักชัน → กลับ rollback tag ตาม flow git-pull-win-kc

## 9. Out of scope (อย่าทำในรอบนี้)
- รวม env `mail.*` → `email.*` (ทำทีหลังตามคอมเมนต์ใน .env)
- แก้ bug `$data` undefined ของโค้ดเดิม (เวอร์ชันใหม่ครอบให้โดยปริยายอยู่แล้ว)
- รีแฟกเตอร์ controller ตัวเรียก
