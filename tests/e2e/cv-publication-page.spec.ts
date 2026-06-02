import { test, expect } from '@playwright/test';
import {
  cvUserEmail,
  devLoginForCv,
  gotoCvSectionsTab,
  isDevelopmentDevLoginAvailable,
} from './helpers/cv-auth';
import {
  expandCvSection,
  expectPublicationEntryPage,
  fillMinimalPublicationForm,
  openPublicationPage,
  requirePublicationSection,
} from './helpers/cv-publication';

/**
 * E2E หน้าเพิ่มผลงานตีพิมพ์ (publication page แทน modal)
 *
 * รัน local (ต้อง development + Docker/nginx):
 *   PLAYWRIGHT_BASE_URL=http://localhost/newscience/public/ npm run test:e2e:cv
 *   npm run test:e2e:cv:headed
 *
 * ตัวแปร:
 *   PLAYWRIGHT_CV_EMAIL — user ที่มี personnel + หัวข้องานวิจัย
 *   PLAYWRIGHT_CV_SECTION_ID — ข้ามการหา section จาก UI (เช่น 874)
 *   PLAYWRIGHT_LIVE_N8N=1 — รันเทสต์ AI จริง (ช้า, ต้อง n8n + baseURL เข้าถึงได้)
 */
test.describe('CV publication page — local (dev login)', () => {
  let devLoginReady = false;

  test.beforeAll(async ({ request }) => {
    devLoginReady = await isDevelopmentDevLoginAvailable(request);
  });

  test.beforeEach(async ({ page }) => {
    test.skip(
      !devLoginReady,
      'ต้องรันแอปใน development (CI_ENVIRONMENT=development ใน .env) และ PLAYWRIGHT_BASE_URL ชี้ local',
    );
    await devLoginForCv(page, cvUserEmail);
  });

  test('L1: แท็บหัวข้อ — เพิ่มผลงานไป กบศ + AI ใน NS (ไม่มี modal เก่า)', async ({
    page,
  }) => {
    await gotoCvSectionsTab(page);
    await expect(page.locator('body')).not.toContainText('cv-pub-entry-modal');
    await expect(page.locator('#cv-ai-modal')).toHaveCount(0);

    const { sectionId, section } = await requirePublicationSection(page);

    const addLink = section.locator('a[href*="rr-publication/create"]').filter({
      hasText: /เพิ่มผลงาน/,
    });
    await expect(addLink).toHaveCount(1);
    await expect(addLink).toHaveAttribute(
      'href',
      new RegExp(`/dashboard/profile/cv/rr-publication/create\\?section_id=${sectionId}`),
    );

    const aiLink = section.locator('a[href*="cv/publication"]').filter({
      hasText: /ช่วยเติมด้วย AI/,
    });
    await expect(aiLink).toHaveAttribute('href', /[?&]ai=1/);
  });

  test('L2: เปิดหน้า publication (AI) — ฟอร์ม + แผง AI', async ({ page }) => {
    await gotoCvSectionsTab(page);
    const sectionId = await openPublicationPage(page, { fromSectionsTab: true, ai: true });
    await expectPublicationEntryPage(page);
    await expect(page.locator('#cv-pub-form')).toHaveAttribute(
      'data-open-ai',
      '0',
    );
    await expect(page.getByRole('link', { name: 'จัดการ CV' })).toBeVisible();
  });

  test('L3: ?ai=1 เปิดแผง AI และปุ่มวิเคราะห์ (ถ้า AI_CV เปิด)', async ({
    page,
  }) => {
    await gotoCvSectionsTab(page);
    await openPublicationPage(page, { fromSectionsTab: true, ai: true });
    await expect(page.locator('#cv-pub-form')).toHaveAttribute(
      'data-open-ai',
      '1',
    );
    await expect(page.locator('#cv-pub-ai-panel')).toBeVisible();
    await expect(page.locator('#cv-pub-ai-run')).toBeVisible();
    const aiReady = await page.evaluate(
      () => (window as Window & { CV_PUB_PAGE?: { aiReady?: boolean } }).CV_PUB_PAGE?.aiReady,
    );
    if (aiReady) {
      await expect(page.locator('#cv-pub-ai-run')).toBeEnabled();
    } else {
      await expect(page.locator('#cv-pub-ai-run')).toBeDisabled();
    }
  });

  test('L3b: autocomplete ผู้แต่ง — พิมพ์ชื่อแล้วเห็นรายชื่อ', async ({ page }) => {
    await gotoCvSectionsTab(page);
    await openPublicationPage(page, { fromSectionsTab: true, ai: true });

    const nameInput = page.locator(
      '#cv-p-authors-list .cv-author-name',
    ).first();
    await expect(nameInput).toBeVisible({ timeout: 10_000 });

    const apiCheck = await page.evaluate(async () => {
      const ep = (window as Window & { CV_AUTHOR_SEARCH_ENDPOINTS?: { names?: string; name?: string } })
        .CV_AUTHOR_SEARCH_ENDPOINTS;
      const base = ep?.names || ep?.name || '';
      const res = await fetch(
        `${base}?name=${encodeURIComponent('พิ')}&limit=10`,
        { credentials: 'same-origin', headers: { Accept: 'application/json' } },
      );
      return res.json() as Promise<{ success?: boolean; results?: { name?: string }[] }>;
    });
    expect(apiCheck.success).toBe(true);
    expect((apiCheck.results ?? []).length).toBeGreaterThan(0);

    await nameInput.clear();
    await nameInput.fill('พิ');
    await page.waitForTimeout(450);

    const dropdown = page.locator('.cv-author-search-dropdown');
    await expect(dropdown).toBeVisible({ timeout: 10_000 });
    await expect(
      dropdown.locator('.cv-author-search-item').first(),
    ).toBeVisible();
    await expect(dropdown).toContainText(/พิศิษฐ์|นาคใจ|พิชิต/);
  });

  test('L4a: ส่งฟอร์มไม่ครบ → แสดง validation ไม่ออกจากหน้า', async ({ page }) => {
    await gotoCvSectionsTab(page);
    await openPublicationPage(page, { fromSectionsTab: true, ai: true });

    await page.locator('#cv-p-title').fill('');
    await page.locator('#cv-p-org').fill('');
    await page.locator('#cv-p-year-be').fill('');

    await page.locator('#cv-pub-submit').click();
    await expect(page).toHaveURL(/\/cv\/publication/);
    await expect(page.locator('#cv-pub-form-errors')).toBeVisible();
    await expect(page.locator('#cv-pub-form-errors')).not.toBeEmpty();
    await expect(page.locator('#cv-p-title.cv-pub-field--invalid')).toBeVisible();
    await expect(page.locator('#cv-p-org.cv-pub-field--invalid')).toBeVisible();
  });

  test('L4: บันทึกผลงานใหม่ (AI หน้า NS) → กลับแท็บหัวข้อ', async ({
    page,
  }) => {
    test.slow();
    await gotoCvSectionsTab(page);
    await openPublicationPage(page, { fromSectionsTab: true, ai: true });

    const title = `E2E Playwright ${Date.now()}`;
    await fillMinimalPublicationForm(page, title);
    await page.locator('#cv-pub-submit').click();

    await page.waitForURL(/dashboard\/profile\/cv.*tab=sections.*open_section=/, {
      timeout: 45_000,
    });
    await expect(
      page.locator('.rounded-xl').filter({ hasText: /บันทึกผลงานสำเร็จ|บันทึกข้อมูลสำเร็จ/ }).first(),
    ).toBeVisible({ timeout: 15_000 });
    const entryLine = page.locator('.cv-entry-row__title-line', { hasText: title });
    await expect(entryLine).toHaveCount(1);
    await expect(entryLine.first()).toBeVisible({ timeout: 10_000 });
  });

  test('L5: หัวข้อทั่วไปยังใช้ modal เดิม (ไม่ใช่ลิงก์ publication)', async ({
    page,
  }) => {
    await gotoCvSectionsTab(page);
    const general = page
      .locator('.cv-section-item[data-show-pub="0"]')
      .filter({ has: page.getByRole('button', { name: /เพิ่มรายการ/ }) })
      .first();
    test.skip((await general.count()) === 0, 'ไม่มีหัวข้อทั่วไปใน CV นี้');

    const sid = await general.getAttribute('data-section-id');
    await expandCvSection(page, sid!);
    await expect(
      general.getByRole('button', { name: /เพิ่มรายการ/ }),
    ).toBeVisible();
    await expect(general.getByRole('link', { name: /เพิ่มผลงาน/ })).toHaveCount(
      0,
    );
  });
});

test.describe('CV publication page — auth gate', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('L0: ไม่ล็อกอิน → ไม่เห็นหน้า publication', async ({ page }) => {
    await page.goto('dashboard/profile/cv/publication?section_id=1', {
      waitUntil: 'domcontentloaded',
      timeout: 30_000,
    });
    await expect(page.locator('#cv-pub-form')).toHaveCount(0);
    expect(page.url()).toMatch(/oauth|login|admin\/login/i);
  });
});

test.describe('CV publication AI — live n8n', () => {
  test.skip(
    process.env.PLAYWRIGHT_LIVE_N8N !== '1',
    'ตั้ง PLAYWRIGHT_LIVE_N8N=1 เพื่อทดสอบ n8n จริง',
  );

  test.beforeEach(async ({ page }) => {
    await devLoginForCv(page, cvUserEmail);
  });

  test('L6: วิเคราะห์จาก URL ตัวอย่าง (dummy PDF)', async ({ page }) => {
    test.setTimeout(180_000);
    await gotoCvSectionsTab(page);
    await openPublicationPage(page, { fromSectionsTab: true, ai: true });
    await expect(page.locator('#cv-pub-ai-run')).toBeEnabled({ timeout: 10_000 });

    await page
      .locator('#cv-pub-ai-url')
      .fill(
        'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
      );
    await page.locator('#cv-pub-ai-run').click();
    await expect(page.locator('#cv-p-title')).not.toHaveValue('', {
      timeout: 120_000,
    });
  });
});
