import { test, expect } from '@playwright/test';

const adminEmail =
  process.env.PLAYWRIGHT_ADMIN_EMAIL ?? 'pisit.nak@live.uru.ac.th';
const programId = process.env.PLAYWRIGHT_PROGRAM_ID ?? '1';
const programIdNum = Number(programId);

async function devLoginAsAdmin(page: import('@playwright/test').Page) {
  const q = `email=${encodeURIComponent(adminEmail)}`;
  await page.goto(`dev/login-as-admin?${q}`, {
    waitUntil: 'domcontentloaded',
    timeout: 30_000,
  });
  await page.waitForURL(/dashboard|program-admin|index\.php/i, {
    timeout: 30_000,
  });
  await expect(page.getByRole('heading', { name: 'Not Found' })).toHaveCount(0);
}

test.describe('Program admin — content bundle panel', () => {
  test.beforeEach(async ({ page }) => {
    await devLoginAsAdmin(page);
    await page.goto(`program-admin/edit/${programId}`, {
      waitUntil: 'domcontentloaded',
    });
  });

  test('B1: bundle panel ไม่อยู่แท็บเนื้อหา แต่อยู่แท็บการตั้งค่าเว็บไซต์', async ({
    page,
  }) => {
    await page.locator('.tab-button[data-tab="content"]').click();
    await expect(
      page.locator('#content-tab .program-bundle-panel'),
    ).toHaveCount(0);
    await expect(
      page.locator('#content-tab').getByText('นำเข้า / ส่งออก JSON'),
    ).toHaveCount(0);

    await page.locator('.tab-button[data-tab="website"]').click();
    const panel = page.locator('#website-tab .program-bundle-panel');
    await expect(panel).toBeVisible();
    await expect(
      panel.getByRole('heading', {
        name: /นำเข้า \/ ส่งออก JSON \(ข้อมูลพื้นฐาน · เนื้อหาหลักสูตร · การตั้งค่า\)/,
      }),
    ).toBeVisible();
  });

  test('B2/B3: ลิงก์ดาวน์โหลด bundle และแม่แบบว่าง', async ({ page }) => {
    await page.locator('.tab-button[data-tab="website"]').click();
    const exportLink = page.getByRole('link', {
      name: 'ดาวน์โหลด JSON ปัจจุบัน',
    });
    await expect(exportLink).toHaveAttribute(
      'href',
      new RegExp(`/program-admin/bundle-export/${programId}`),
    );
    const templateLink = page.getByRole('link', {
      name: 'ดาวน์โหลดแม่แบบว่าง',
    });
    await expect(templateLink).toHaveAttribute(
      'href',
      new RegExp(`/program-admin/bundle-template/${programId}`),
    );
  });

  test('B4: ปุ่มดูสรุปฐานปัจจุบัน แสดง grid และข้อความฝั่งขวา', async ({
    page,
  }) => {
    await page.locator('.tab-button[data-tab="website"]').click();
    await page.locator('#bundle-preview-current-btn').click();
    await expect(page.locator('#bundle-preview-wrap')).toBeVisible();
    await expect(page.getByText('(ฝั่งขวา: นำเข้า — ยังไม่มี)')).toBeVisible();
    await expect(page.locator('#bundle-compare-grid')).toContainText('1. ภาพรวม');
  });

  test('B7: อัปโหลดไฟล์ไม่ถูกต้อง แสดงรายการ error และซ่อนปุ่ม commit', async ({
    page,
  }) => {
    const badJson = {
      schema_version: 1,
      program_id: programIdNum,
      basic: {
        credits: -5,
        level: 'wizard',
        website: 'javascript:alert(1)',
      },
      settings: { theme_color: 'red' },
    };
    await page.locator('.tab-button[data-tab="website"]').click();
    await page.locator('#bundle-file-input').setInputFiles({
      name: 'bad.json',
      mimeType: 'application/json',
      buffer: Buffer.from(JSON.stringify(badJson), 'utf-8'),
    });
    await page.locator('#bundle-import-preview-btn').click();
    await expect(page.locator('#bundle-import-msg')).toContainText(
      /นำเข้าไม่ผ่าน|basic\.level|settings\.theme_color/,
    );
    const errList = page.locator('#bundle-import-errors');
    await expect(errList).toBeVisible();
    const n = await errList.locator('li').count();
    expect(n, 'ควรมีอย่างน้อย 1 รายการ error จาก backend').toBeGreaterThanOrEqual(1);
    await expect(page.locator('#bundle-commit-row')).toBeHidden();
  });
});
