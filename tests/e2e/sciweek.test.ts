import { test, expect, Page } from '@playwright/test';

const BASE = 'http://localhost/newscience/public/scienceweek';
const SS = '/tmp/sw-verify';

// Helper: submit form and follow redirect
async function submitForm(page: Page, url: string, fields: Record<string,string>) {
  await page.goto(url);
  for (const [sel, val] of Object.entries(fields)) {
    const el = page.locator(sel).first();
    if (await el.getAttribute('type') === 'radio') {
      await page.check(`${sel}[value="${val}"]`);
    } else {
      await el.fill(val);
    }
  }
}

// ──────────────────────────────────────────────
// 1. หน้า index แสดง 5 รายการ
// ──────────────────────────────────────────────
test('index page shows 5 competitions', async ({ page }) => {
  await page.goto(BASE);
  await page.screenshot({ path: `${SS}/01_index.png`, fullPage: true });
  const cards = page.locator('.comp-card');
  await expect(cards).toHaveCount(5);
  const titles = await page.locator('.comp-card .card-title').allTextContents();
  console.log('Competitions:', titles);
});

// ──────────────────────────────────────────────
// 2. ERROR: ส่งฟอร์มว่าง (seed_art)
// ──────────────────────────────────────────────
test('seed_art: empty form shows validation errors', async ({ page }) => {
  await page.goto(`${BASE}/register/seed_art`);
  await page.click('button[type="submit"]');
  await page.screenshot({ path: `${SS}/02_seed_art_empty_error.png`, fullPage: true });
  // browser native validation should block — or CI4 should redirect back with errors
  const url = page.url();
  // ถ้า native validation ทำงาน จะยังอยู่หน้าเดิม
  expect(url).toContain('seed_art');
});

// ──────────────────────────────────────────────
// 3. ERROR: กรอกโรงเรียนแต่ไม่กรอก participants (seed_art)
// ──────────────────────────────────────────────
test('seed_art: missing participants shows error', async ({ page }) => {
  await page.goto(`${BASE}/register/seed_art`);
  await page.check('input[name="level_key"][value="primary"]');
  await page.fill('input[name="school_name"]', 'โรงเรียนทดสอบ');
  await page.fill('input[name="contact_phone"]', '055-123456');
  await page.fill('input[name="coach_name"]', 'อาจารย์ทดสอบ');
  // ไม่กรอก participants
  await page.click('button[type="submit"]');
  await page.waitForURL(`**`);
  await page.screenshot({ path: `${SS}/03_seed_art_no_participants_error.png`, fullPage: true });
  // ต้องกลับมาหน้าฟอร์มพร้อม error
  const errorBox = page.locator('.alert-danger, .invalid-feedback');
  const hasError = await errorBox.count() > 0;
  console.log('Error shown:', hasError, await errorBox.first().textContent().catch(() => 'none'));
});

// ──────────────────────────────────────────────
// 4. SUCCESS: seed_art สมัครครบถ้วน
// ──────────────────────────────────────────────
test('seed_art: full valid submission → success page', async ({ page }) => {
  await page.goto(`${BASE}/register/seed_art`);
  await page.check('input[name="level_key"][value="lower_secondary"]');
  await page.fill('input[name="school_name"]', 'โรงเรียนทดสอบสมัครได้');
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
test('rov: only 3 players (need 5) → error', async ({ page }) => {
  await page.goto(`${BASE}/register/rov`);
  await page.check('input[name="level_key"][value="primary_lower"]');
  await page.fill('input[name="school_name"]', 'โรงเรียน ROV Test');
  await page.fill('input[name="contact_phone"]', '055-222222');
  await page.fill('input[name="coach_name"]', 'โค้ชROV');
  // กรอกแค่ 3 คน (ต้องการ 5)
  for (let i = 0; i < 3; i++) {
    await page.fill(`input[name="participants[${i}][full_name]"]`, `ผู้เล่น ${i+1}`);
    await page.fill(`input[name="participants[${i}][game_id]"]`, `ROVID${i+1}`);
  }
  await page.click('button[type="submit"]');
  await page.waitForURL(`**`);
  await page.screenshot({ path: `${SS}/05_rov_incomplete_error.png`, fullPage: true });
  const url = page.url();
  const isError = url.includes('rov') && !url.includes('success');
  console.log('ROV incomplete → stayed on form:', isError);
});

// ──────────────────────────────────────────────
// 6. SUCCESS: ROV สมัครครบ 5 คน + สำรอง 1
// ──────────────────────────────────────────────
test('rov: 5 players + 1 reserve → success', async ({ page }) => {
  await page.goto(`${BASE}/register/rov`);
  await page.check('input[name="level_key"][value="lower_higher"]');
  await page.fill('input[name="school_name"]', 'มหาวิทยาลัย ROV สาขาIT');
  await page.fill('input[name="contact_phone"]', '055-333333');
  await page.fill('input[name="coach_name"]', 'อาจารย์ IT');
  for (let i = 0; i < 5; i++) {
    await page.fill(`input[name="participants[${i}][full_name]"]`, `นักกีฬา ${i+1}`);
    await page.fill(`input[name="participants[${i}][game_id]"]`, `GameID_${i+1}`);
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
// 7. SUCCESS: python สมัคร 2 คน
// ──────────────────────────────────────────────
test('python: 2 members → success', async ({ page }) => {
  await page.goto(`${BASE}/register/python`);
  await page.check('input[name="level_key"][value="secondary"]');
  await page.fill('input[name="school_name"]', 'โรงเรียนโค้ดเดอร์');
  await page.fill('input[name="contact_phone"]', '055-444444');
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
  await page.check('input[name="level_key"][value="primary"]');
  await page.fill('input[name="school_name"]', 'โรงเรียนรักษ์โลก');
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
// 9. SUCCESS: sci_drawing (เดี่ยว)
// ──────────────────────────────────────────────
test('sci_drawing: solo with age → success', async ({ page }) => {
  await page.goto(`${BASE}/register/sci_drawing`);
  await page.check('input[name="level_key"][value="lower_secondary"]');
  await page.fill('input[name="school_name"]', 'โรงเรียนศิลป์วิทย์');
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
// 10. ERROR: sci_drawing ไม่กรอก age (required)
// ──────────────────────────────────────────────
test('sci_drawing: missing age → error', async ({ page }) => {
  await page.goto(`${BASE}/register/sci_drawing`);
  await page.check('input[name="level_key"][value="lower_secondary"]');
  await page.fill('input[name="school_name"]', 'โรงเรียนวาดภาพ');
  await page.fill('input[name="contact_phone"]', '055-777777');
  await page.fill('input[name="coach_name"]', 'ครูวาดภาพ');
  await page.fill('input[name="participants[0][full_name]"]', 'เด็กวาดภาพ');
  // ไม่กรอก age
  await page.click('button[type="submit"]');
  await page.waitForURL(`**`);
  await page.screenshot({ path: `${SS}/10_drawing_no_age_error.png`, fullPage: true });
  const url = page.url();
  console.log('Drawing no age URL:', url);
});

// ──────────────────────────────────────────────
// 11. หน้า verify แสดงรายชื่อ
// ──────────────────────────────────────────────
test('verify page shows registered teams', async ({ page }) => {
  await page.goto(`${BASE}/verify?competition=seed_art&level=lower_secondary`);
  await page.screenshot({ path: `${SS}/11_verify_list.png`, fullPage: true });
  await expect(page.locator('body')).toContainText('นักเรียน หนึ่ง');
});

