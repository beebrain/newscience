import { test, expect } from '@playwright/test';

/**
 * Smoke / regression บน production (หรือ staging ที่ตั้ง PLAYWRIGHT_BASE_URL)
 *
 * รัน:
 *   PLAYWRIGHT_BASE_URL=https://sci.uru.ac.th/ npm run test:e2e:production
 *
 * ทดสอบหน้า CV หลังล็อกอิน (optional):
 *   npx playwright codegen https://sci.uru.ac.th --save-storage=playwright/.auth/user.json
 *   PLAYWRIGHT_STORAGE_STATE=playwright/.auth/user.json npm run test:e2e:production
 */
const cvEmail =
  process.env.PLAYWRIGHT_CV_EMAIL ?? 'pisit.nak@live.uru.ac.th';
const cvEmailEncoded = encodeURIComponent(cvEmail);
const storageState = process.env.PLAYWRIGHT_STORAGE_STATE;

function seriousConsoleErrors(errors: string[]): string[] {
  const noise =
    /favicon|ResizeObserver|Failed to load resource.*404|third-party|google|gtag|analytics/i;
  return errors.filter((t) => !noise.test(t));
}

test.describe('Production — public pages', () => {
  test('P1: หน้าแรกโหลดได้ (200)', async ({ page }) => {
    const consoleErrors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    const res = await page.goto('/', {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });
    expect(res?.status()).toBe(200);
    await expect(page).toHaveTitle(/คณะวิทยาศาสตร์|วิทยาศาสตร์/i);
    expect(seriousConsoleErrors(consoleErrors)).toEqual([]);
  });

  test('P2: CV สาธารณะโหลดได้และมีโครงสร้าง CV', async ({ page }) => {
    const consoleErrors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    const res = await page.goto(`personnel-cv/${cvEmailEncoded}`, {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });
    expect(res?.status()).toBe(200);
    await expect(page.locator('.personnel-cv-doc')).toBeVisible();
    await expect(page.locator('.personnel-cv-doc h1').first()).toBeVisible();
    expect(seriousConsoleErrors(consoleErrors)).toEqual([]);
  });

  test('P3: หัวข้อผลงานตีพิมพ์/งานวิจัย (ถ้ามี) แสดงคอลัมน์ผู้แต่งและมีอย่างน้อย 1 แถว', async ({
    page,
  }) => {
    await page.goto(`personnel-cv/${cvEmailEncoded}`, {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });

    const pubSectionTitle = /งานวิจัย|ผลงานตีพิมพ์|บทความ|กบศ/i;
    const researchHeading = page
      .locator('.cv-section-block h2')
      .filter({ hasText: pubSectionTitle });
    const count = await researchHeading.count();
    test.skip(count === 0, 'ไม่มีหัวข้อผลงานตีพิมพ์/งานวิจัย public สำหรับอีเมลนี้');

    const block = page.locator('.cv-section-block').filter({
      has: page.locator('h2', { hasText: pubSectionTitle }),
    });
    const table = block.locator('table.cv-data-table');
    await expect(table).toBeVisible();
    await expect(table.locator('thead th', { hasText: 'ผู้แต่ง' })).toBeVisible();
    await expect(table.locator('tbody tr')).not.toHaveCount(0);

    const firstTitle = table.locator('tbody tr').first().locator('.cv-title-cell');
    await expect(firstTitle).not.toBeEmpty();
  });
});

test.describe('Production — auth gate', () => {
  test('P4: /dashboard/profile/cv ต้องล็อกอิน (redirect ออกจากหน้า CV)', async ({
    page,
  }) => {
    await page.goto('dashboard/profile/cv', {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });
    await expect(page.getByRole('heading', { name: 'จัดการ CV' })).toHaveCount(0);
    expect(page.url()).toMatch(/oauth|login|portal/i);
  });
});

test.describe('Production — CV manage (logged in)', () => {
  test.skip(!storageState, 'ตั้ง PLAYWRIGHT_STORAGE_STATE หลัง login ด้วย codegen');

  test.use({ storageState: storageState! });

  test('P5: หน้าจัดการ CV โหลดได้', async ({ page }) => {
    const res = await page.goto('dashboard/profile/cv', {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });
    expect(res?.status()).toBeLessThan(400);
    await expect(
      page.getByRole('heading', { name: 'จัดการ CV' }),
    ).toBeVisible({ timeout: 15_000 });
  });

  test('P8: จัดการ CV ไม่มี modal publication เก่า', async ({ page }) => {
    await page.goto('dashboard/profile/cv?tab=sections', {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });
    const html = await page.content();
    expect(html).not.toContain('cv-pub-entry-modal');
    expect(html).not.toContain('id="cv-ai-modal"');
    const addPub = page
      .locator('.cv-section-item[data-show-pub="1"]')
      .first()
      .getByRole('link', { name: /เพิ่มผลงาน/ });
    if ((await addPub.count()) > 0) {
      await expect(addPub.first()).toHaveAttribute(
        'href',
        /\/dashboard\/profile\/cv\/publication/,
      );
    }
  });

  test('P6: แท็บหัวข้อและรายการ — งานวิจัยมีฟิลด์ publication_type', async ({
    page,
  }) => {
    await page.goto('dashboard/profile/cv?tab=sections', {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });
    await expect(
      page.getByRole('heading', { name: 'จัดการ CV' }),
    ).toBeVisible();

    const researchSection = page.locator('[data-section-type="research"]').first();
    const hasResearch = (await researchSection.count()) > 0;
    test.skip(!hasResearch, 'ไม่มีหัวข้อประเภทงานวิจัย');

    await expect(researchSection).toBeVisible();
    await expect(
      researchSection.getByText(/publication_type|ประเภทการเผยแพร่/i).first(),
    ).toBeVisible();
  });
});
