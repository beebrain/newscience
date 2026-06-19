# PLAN — Security cleanup + EdocSso retirement (ฉบับชัดสุด)

> วันที่: 2026-06-19 • สถานะ: รอเริ่ม • หลักการ: ทุกสเต็ปย้อนกลับได้ + ไม่กระทบ login/SSO ที่ยังใช้จริง

## 0. สรุปผู้บริหาร
- **EdocSso ไม่จำเป็นแล้ว** (login ใช้ URU Portal, Edoc เป็น sub-app ใน NS) → ปลดได้
- **ปลด EdocSso = ไม่ต้อง rotate `edocsso.sharedSecret`** (ตัดทิ้งแทน) — ลดงาน security
- secret ที่ยังต้อง rotate จริง: **win-kc admin pw, MySQL pw, researchrecordsso secret**
- จุดเสี่ยงที่ตรวจแล้วปลอดภัย: **NS + RR ตั้ง SSO secret แบบ explicit ทั้งคู่** → ปิด EdocSso ไม่ทำ RR พัง

## 1. สถานะปัจจุบัน (verified)
| เรื่อง | สถานะ |
|---|---|
| Login admin | **URU ID Portal OAuth** (`oauth/login`) — prod `uruoauth.enabled=true` |
| EdocSso login (`/admin/portal-login`) | wired แต่**ไม่มีปุ่มในหน้า login → dormant** |
| Edoc | เป็น sub-app ใน NS แล้ว (`/edoc` routes) |
| EdocSso ที่ยังทำงานจริง | แค่ logout iframe/chain ไป edoc ตัวนอก ([login.php:304](app/Views/admin/auth/login.php#L304)) + ลิงก์ legacy ใน dashboard |
| RR SSO secret | **explicit ทั้ง NS + RR** (ไม่พึ่ง edoc fallback) ✓ |
| Secret ที่รั่ว (scrub current แล้ว แต่ยังอยู่ใน git history) | win-kc admin pw, MySQL root+remote_import pw, `pisit_secret` |
| edoc.sci.uru.ac.th (ตัวนอก) | host ยังตอบ (ต้องยืนยันว่ายังมีคนใช้ไหม) |

⚠️ **EdocSso พันกับ ResearchRecordSso** (ที่ยังใช้จริง): `oauthCallback` รับทั้ง `provider=edoc|researchrecord`, `buildLogoutRedirectChain` ไล่ทั้งสอง, `OAuthController:221` + `ResearchRecordSso.php:47` มี fallback ไป `edocsso.sharedSecret` → **ลบโค้ดต้องระวัง ไม่ใช่ rip-out**

---

## 2. Track E — ปลด EdocSso (2 เฟส)

### E1 — ปิดผ่าน env เท่านั้น (ปลอดภัย ย้อนได้ ทำได้เลย)
- **pre-check (ผ่านแล้ว):** `researchrecordsso.sharedSecret` ตั้ง explicit บน NS + `newscience_sso.sharedSecret` บน RR → ปิด edoc ไม่กระทบ RR
- ตั้งใน `.env` (prod + dev): `edocsso.enabled = false`
- กลไก: ปิดแล้ว `logoutUrl` ว่างอัตโนมัติ ([EdocSso.php:72-73]) → logout chain/iframe ข้าม edoc เอง, `portalLogin` คืน "ยังไม่เปิดใช้" (ไม่มีใครเรียกอยู่แล้ว)
- **ตัดสินใจลิงก์ legacy:** [admin/dashboard:212-221](app/Views/admin/dashboard/index.php#L212), [user/dashboard:149-157](app/Views/user/dashboard/index.php#L149) โชว์ลิงก์ไป edoc ตัวนอกจาก `edocsso.baseUrl` — ถ้า edoc ตัวนอกเลิกใช้ ให้ชี้ไป `/edoc` ภายในแทน หรือเอาออก

**E1 verify:**
- [ ] login admin ผ่าน URU Portal ได้ปกติ
- [ ] logout ไม่ค้าง/ไม่ error (ไม่เด้งไป edoc)
- [ ] `/edoc` sub-app ภายใน NS ใช้ได้
- [ ] RR↔NS publication SSO ยังทำงาน (กดปุ่มจาก NS ไป RR แล้วกลับ)
- [ ] log ไม่มี error EdocSso

### E2 — ลบโค้ด/route/view (ทำทีหลัง เมื่อยืนยัน edoc ตัวนอกเลิกแล้ว, commit แยก)
**ลบได้ (edoc-only):**
- routes: `/admin/portal-login`, `/admin/edoc-logout-return` ([Routes.php:79,77])
- methods: `Auth::portalLogin` (193), `Auth::edocLogoutReturn` (109), `Auth::getEdocSsoUrl` (508)
- view: iframe logout ([login.php:304-306]), ลิงก์ legacy ใน 2 dashboard
- `app/Config/EdocSso.php` + คีย์ `edocsso.*` ใน .env ทุก server

**ห้ามลบ / แก้แบบระวัง (ใช้ร่วมกับ RR ที่ยังต้องใช้):**
- `Auth::oauthCallback` (264) — รับ `provider=researchrecord` ด้วย → เก็บไว้ ตัดเฉพาะกิ่ง edoc
- `Auth::portalLoginResearch` (239), `buildLogoutRedirectChain` (122, ข้าม edoc เองอยู่แล้ว) → เก็บ
- `OAuthController.php:221` + `ResearchRecordSso.php:47` → **ตัด fallback `edocsso.sharedSecret` ออก** ให้ RR ใช้ค่า explicit เท่านั้น
- E2 ต้องมีรอบเทสต์ของตัวเอง (login/logout/RR-SSO ครบ)

---

## 3. Track S — Rotate secret ที่ยังรั่ว (ค่าเก่ายังอยู่ใน git history = ถือว่า compromised)

| # | Secret (ค่าเก่า) | หมุนที่ | อัปเดตที่ | หมายเหตุ |
|---|---|---|---|---|
| S1 | win-kc `Administrator` pw | Windows account (`net user`) | local notes; ลบจาก `.claude/settings.local.json` local | deploy ใช้ **key auth** → ไม่พัง |
| S2 | MySQL `root` + `remote_import` pw | `ALTER USER ... IDENTIFIED BY` ทุก MySQL | `database.default.password` ใน `.env` ทุก server + `server.env` local | สคริปต์ grant ใช้ placeholder `CHANGE_ME_*` แล้ว |
| S3 | `researchrecordsso` secret (`pisit_secret`) | สุ่มค่าใหม่ strong | NS `researchrecordsso.sharedSecret` + RR `newscience_sso.sharedSecret` (**ต้องตรงกัน**) | ช่วง mismatch สั้น ๆ SSO ล่ม → ทำนอกเวลา, อัปเดต 2 ฝั่งติดกัน |
| S4 | `edocsso.sharedSecret` | **ไม่หมุน** | — | ตัดทิ้งผ่าน Track E |
| S5 | (optional) git history | `git filter-repo --replace-text` | force-push | ⚠️ ต้อง **re-clone win-kc ทั้ง NS + RR** (pull แบบ ff) → ทำเป็นขั้นสุดท้าย; เพราะ rotate แล้ว ค่าเก่าตายแล้ว นี่แค่เก็บกวาด |

---

## 4. ลำดับทำที่ปลอดภัย (สำคัญ)
1. **E1** ปิด `edocsso.enabled=false` (ตัด edoc ออกจากสมการก่อน — ค่า edoc secret กลายเป็นไม่ใช้)
2. **S3** rotate RR secret 2 ฝั่ง (off-peak) — ยืนยัน RR SSO ยังทำงาน
3. **S1 + S2** rotate รหัส infra (win-kc, MySQL)
4. **E2** ลบโค้ด EdocSso (เมื่อยืนยัน edoc ตัวนอกเลิก)
5. **S5** (ถ้าต้องการ) purge history + re-clone

## 5. Risk register
- **R1 ปิด edoc แล้ว RR พัง** · 🟢 ต่ำ — ตรวจแล้ว RR secret explicit ทั้งสองฝั่ง · mitigation: E1-verify ข้อ RR-SSO
- **R2 rotate RR secret แล้ว 2 ฝั่ง mismatch** · 🟠 กลาง — SSO ล่มชั่วคราว · mitigation: ทำนอกเวลา, อัปเดต NS+RR ติดกัน, เทสต์ทันที, rollback = คืนค่าเก่า 2 ฝั่ง
- **R3 ลบโค้ด E2 ไปโดน path RR ที่ใช้ร่วม** · 🟠 กลาง · mitigation: ทำเฉพาะรายการ "ลบได้", เก็บ oauthCallback/portalLoginResearch/logout-chain, มีรอบเทสต์แยก
- **R4 history purge ทำ win-kc pull พัง** · 🟠 กลาง · mitigation: re-clone NS+RR หลัง force-push; ทำเป็นขั้นสุดท้าย; ถือว่า rotate คือกันชนหลักแล้ว
- **R5 rotate MySQL pw แล้วแอปต่อ DB ไม่ได้** · 🟠 กลาง · mitigation: อัปเดต `.env` ทุก server ทันทีหลัง ALTER, เทสต์ `php spark db:table` / โหลดหน้าเว็บ

## 6. Verification checklist รวม
- [ ] E1: login URU Portal / logout / `/edoc` / RR-SSO / ไม่มี error
- [ ] S3: RR↔NS SSO round-trip ผ่านด้วย secret ใหม่
- [ ] S2: เว็บโหลด + เขียน DB ได้ด้วยรหัสใหม่ (ทุก server)
- [ ] S1: deploy `git-pull-win-kc.sh` ยังรัน (key auth) หลังเปลี่ยนรหัส
- [ ] grep history หลัง S5: ไม่เหลือ secret string

## 7. Rollback
- E1 → ตั้ง `edocsso.enabled=true` คืน
- S3 → คืน secret เก่าทั้ง 2 ฝั่ง
- S1/S2 → คืนรหัสเก่า + `.env` เดิม
- E2 → `git revert` commit ลบโค้ด
- S5 → (ทำเป็นขั้นสุดท้าย ไม่มี rollback ของ force-push — ต้องมั่นใจก่อน)

## 8. ต้องยืนยัน/ตัดสินใจก่อน
1. **edoc.sci.uru.ac.th (ตัวนอก) ยังมีคนใช้ที่ต้อง single-logout ไหม?** → ถ้าเลิกแล้ว ทำ E2 + ตัดลิงก์ legacy ได้
2. ใครรัน rotate (S1–S3) — คุณเอง หรือให้ผมช่วยผ่าน SSH (ผมทำ E1, S2, S3 ผ่าน tailscale ให้ได้ ส่วน S1 รหัส Windows คุณตั้งเอง)
3. ทำ history purge (S5) ไหม — ถ้าทำ ต้องยอมรับ re-clone win-kc

---
**ขั้นถัดไปที่ปลอดภัยสุด = E1** (ปิด edocsso.enabled, ย้อนได้, ลด security surface ทันที). สั่งได้เลยถ้าให้เริ่ม
