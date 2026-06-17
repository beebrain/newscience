import { test, expect, Page } from '@playwright/test';

const BASE = 'http://localhost/newscience/public/scienceweek';
const SS = '/tmp/sw-verify';
// ใช้ค่าไม่ซ้ำต่อรอบ เพื่อกันชน cap ต่อสถาบัน (rov 1/ระดับ, python 2/สถาบัน) เวลารันซ้ำ
const RUN = Date.now();

// ระดับการแข่งขันใช้ radio ที่ถูกซ่อน (input[type=radio]{display:none}) แล้วจัดสไตล์ที่ label
// ดังนั้นต้อง "คลิกที่ label" เหมือนผู้ใช้จริง แทน page.check() ที่ต้องการ element ที่มองเห็น
async function selectLevel(page: Page, value: string) {
  await page.locator(`label:has(input[name="level_key"][value="${value}"])`).click();
}

// ──────────────────────────────────────────────
// 1. หน้า index แสดงครบ 6 กิจกรรม (มีลิงก์สมัครทุกตัว)
// ──────────────────────────────────────────────
test('index page lists all 6 competitions with register links', async ({ page }) => {
  await page.goto(BASE);
  await page.screenshot({ path: `${SS}/01_index.png`, fullPage: true });
  const keys = ['seed_art', 'rov', 'python', 'recycle', 'sci_drawing', 'network_champion'];
  for (const key of keys) {
    await expect(page.locator(`a[href*="register/${key}"]`).first()).toBeVisible();
  }
});

// ──────────────────────────────────────────────
// 2. ERROR: ส่งฟอร์มว่าง (seed_art) → ต้องไม่ไป success
// ──────────────────────────────────────────────
test('seed_art: empty form is blocked (no success)', async ({ page }) => {
  await page.goto(`${BASE}/register/seed_art`);
  await page.click('button[type="submit"]');
  await page.screenshot({ path: `${SS}/02_seed_art_empty_error.png`, fullPage: true });
  expect(page.url()).toContain('seed_art');
  expect(page.url()).not.toContain('success');
});

// ──────────────────────────────────────────────
// 3. ERROR: กรอกโรงเรียนแต่ไม่กรอก participants (seed_art)
// ──────────────────────────────────────────────
test('seed_art: missing participants shows server-side error', async ({ page }) => {
  await page.goto(`${BASE}/register/seed_art`);
  await selectLevel(page, 'primary');
  await page.fill('input[name="school_name"]', `โรงเรียนทดสอบ ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-123456');
  await page.fill('input[name="coach_name"]', 'อาจารย์ทดสอบ');
  // ไม่กรอก participants
  await page.click('button[type="submit"]');
  await page.screenshot({ path: `${SS}/03_seed_art_no_participants_error.png`, fullPage: true });
  expect(page.url()).not.toContain('success');
  // BUG-2 fix: error ต้องแสดงในฟอร์มจริง (กล่องสรุป หรือ inline)
  await expect(page.locator('.alert-danger, .invalid-feedback').first()).toBeVisible();
});

// ──────────────────────────────────────────────
// 4. SUCCESS: seed_art สมัครครบถ้วน (ทีม 3 คน)
// ──────────────────────────────────────────────
test('seed_art: full valid submission → success page', async ({ page }) => {
  await page.goto(`${BASE}/register/seed_art`);
  await selectLevel(page, 'lower_secondary');
  await page.fill('input[name="school_name"]', `โรงเรียนทดสอบสมัครได้ ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-111111');
  await page.fill('input[name="coach_name"]', 'ครูสมัครทดสอบ');
  await page.fill('input[name="participants[0][full_name]"]', 'นักเรียน หนึ่ง');
  await page.fill('input[name="participants[0][level_class]"]', 'ม.1');
  await page.fill('input[name="participants[1][full_name]"]', 'นักเรียน สอง');
  await page.fill('input[name="participants[1][level_class]"]', 'ม.2');
  await page.fill('input[name="participants[2][full_name]"]', 'นักเรียน สาม');
  await page.fill('input[name="participants[2][level_class]"]', 'ม.3');
  await page.click('button[type="submit"]');
  await page.waitForURL(`**/success/**`);
  await page.screenshot({ path: `${SS}/04_seed_art_success.png`, fullPage: true });
  await expect(page.locator('.card-header')).toContainText('ส่งใบสมัครสำเร็จ');
  await expect(page.locator('body')).toContainText('นักเรียน หนึ่ง');
});

// ──────────────────────────────────────────────
// 5. ERROR: ROV กรอก player ไม่ครบ 5 คน
// ──────────────────────────────────────────────
test('rov: only 3 players (need 5) → blocked', async ({ page }) => {
  await page.goto(`${BASE}/register/rov`);
  await selectLevel(page, 'primary_lower');
  await page.fill('input[name="school_name"]', `โรงเรียน ROV Test ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-222222');
  await page.fill('input[name="coach_name"]', 'โค้ชROV');
  for (let i = 0; i < 3; i++) {
    await page.fill(`input[name="participants[${i}][full_name]"]`, `ผู้เล่น ${i + 1}`);
    await page.fill(`input[name="participants[${i}][game_id]"]`, `ROVID${i + 1}`);
  }
  await page.click('button[type="submit"]');
  await page.screenshot({ path: `${SS}/05_rov_incomplete_error.png`, fullPage: true });
  expect(page.url()).toContain('rov');
  expect(page.url()).not.toContain('success');
});

// ──────────────────────────────────────────────
// 6. SUCCESS: ROV สมัครครบ 5 คน + สำรอง 1
// ──────────────────────────────────────────────
test('rov: 5 players + 1 reserve → success', async ({ page }) => {
  await page.goto(`${BASE}/register/rov`);
  await selectLevel(page, 'lower_higher');
  await page.fill('input[name="school_name"]', `มหาวิทยาลัย ROV IT ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-333333');
  await page.fill('input[name="coach_name"]', 'อาจารย์ IT');
  for (let i = 0; i < 5; i++) {
    await page.fill(`input[name="participants[${i}][full_name]"]`, `นักกีฬา ${i + 1}`);
    await page.fill(`input[name="participants[${i}][game_id]"]`, `GameID_${i + 1}`);
  }
  await page.fill('input[name="reserves[0][full_name]"]', 'ตัวสำรอง 1');
  await page.fill('input[name="reserves[0][game_id]"]', 'ReserveID1');
  await page.click('button[type="submit"]');
  await page.waitForURL(`**/success/**`);
  await page.screenshot({ path: `${SS}/06_rov_success.png`, fullPage: true });
  await expect(page.locator('.card-header')).toContainText('ส่งใบสมัครสำเร็จ');
  await expect(page.locator('body')).toContainText('นักกีฬา 1');
  await expect(page.locator('body')).toContainText('GameID_1');
});

// ──────────────────────────────────────────────
// 7. SUCCESS: python สมัคร 2 คน + ที่อยู่แยกช่อง + โทรสาร (ตามใบสมัคร)
// ──────────────────────────────────────────────
test('python: 2 members with structured address + fax → success', async ({ page }) => {
  await page.goto(`${BASE}/register/python`);
  await selectLevel(page, 'secondary');
  await page.fill('input[name="school_name"]', `โรงเรียนโค้ดเดอร์ ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-444444');
  await page.fill('input[name="addr[road]"]', '27 ถนนอินใจมี');
  await page.fill('input[name="addr[subdistrict]"]', 'ท่าอิฐ');
  await page.fill('input[name="addr[district]"]', 'เมือง');
  await page.fill('input[name="addr[province]"]', 'อุตรดิตถ์');
  await page.fill('input[name="addr[postcode]"]', '53000');
  await page.fill('input[name="fax"]', '055-411412');
  await page.fill('input[name="coach_name"]', 'อาจารย์โปรแกรมมิ่ง');
  await page.fill('input[name="participants[0][full_name]"]', 'โปรแกรมเมอร์ 1');
  await page.fill('input[name="participants[0][level_class]"]', 'ม.5');
  await page.fill('input[name="participants[1][full_name]"]', 'โปรแกรมเมอร์ 2');
  await page.fill('input[name="participants[1][level_class]"]', 'ม.6');
  await page.click('button[type="submit"]');
  await page.waitForURL(`**/success/**`);
  await page.screenshot({ path: `${SS}/07_python_success.png`, fullPage: true });
  await expect(page.locator('.card-header')).toContainText('ส่งใบสมัครสำเร็จ');
});

// ──────────────────────────────────────────────
// 8. SUCCESS: recycle (ทีม 3 คน)
// ──────────────────────────────────────────────
test('recycle: team of 3 → success', async ({ page }) => {
  await page.goto(`${BASE}/register/recycle`);
  await selectLevel(page, 'primary');
  await page.fill('input[name="school_name"]', `โรงเรียนรักษ์โลก ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-555555');
  await page.fill('input[name="coach_name"]', 'ครูสิ่งแวดล้อม');
  await page.fill('input[name="participants[0][full_name]"]', 'เด็กรีไซเคิล 1');
  await page.fill('input[name="participants[1][full_name]"]', 'เด็กรีไซเคิล 2');
  await page.fill('input[name="participants[2][full_name]"]', 'เด็กรีไซเคิล 3');
  await page.click('button[type="submit"]');
  await page.waitForURL(`**/success/**`);
  await page.screenshot({ path: `${SS}/08_recycle_success.png`, fullPage: true });
  await expect(page.locator('.card-header')).toContainText('ส่งใบสมัครสำเร็จ');
});

// ──────────────────────────────────────────────
// 9. SUCCESS: sci_drawing (เดี่ยว + อายุ)
// ──────────────────────────────────────────────
test('sci_drawing: solo with age → success', async ({ page }) => {
  await page.goto(`${BASE}/register/sci_drawing`);
  await selectLevel(page, 'lower_secondary');
  await page.fill('input[name="school_name"]', `โรงเรียนศิลป์วิทย์ ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-666666');
  await page.fill('input[name="coach_name"]', 'ครูศิลปะ');
  await page.fill('input[name="participants[0][full_name]"]', 'จิตรกรน้อย');
  await page.fill('input[name="participants[0][age]"]', '14');
  await page.click('button[type="submit"]');
  await page.waitForURL(`**/success/**`);
  await page.screenshot({ path: `${SS}/09_drawing_success.png`, fullPage: true });
  await expect(page.locator('.card-header')).toContainText('ส่งใบสมัครสำเร็จ');
});

// ──────────────────────────────────────────────
// 10. ERROR: sci_drawing ไม่กรอก age (required) → blocked
// ──────────────────────────────────────────────
test('sci_drawing: missing required age → blocked', async ({ page }) => {
  await page.goto(`${BASE}/register/sci_drawing`);
  await selectLevel(page, 'lower_secondary');
  await page.fill('input[name="school_name"]', `โรงเรียนวาดภาพ ${RUN}`);
  await page.fill('input[name="contact_phone"]', '055-777777');
  await page.fill('input[name="coach_name"]', 'ครูวาดภาพ');
  await page.fill('input[name="participants[0][full_name]"]', 'เด็กวาดภาพ');
  // ไม่กรอก age
  await page.click('button[type="submit"]');
  await page.screenshot({ path: `${SS}/10_drawing_no_age_error.png`, fullPage: true });
  expect(page.url()).not.toContain('success');
});

// ──────────────────────────────────────────────
// 11. หน้า verify แสดงรายชื่อทีมที่สมัครแล้ว
// ──────────────────────────────────────────────
test('verify page shows registered teams', async ({ page }) => {
  await page.goto(`${BASE}/verify?competition=seed_art&level=lower_secondary`);
  await page.screenshot({ path: `${SS}/11_verify_list.png`, fullPage: true });
  await expect(page.locator('body')).toContainText('นักเรียน หนึ่ง');
});
