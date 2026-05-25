import { expect, type Locator, type Page } from '@playwright/test';

/** หัวข้อ publication แรกในแท็บ sections */
export function firstPublicationSection(page: Page): Locator {
  return page.locator('.cv-section-item[data-show-pub="1"]').first();
}

export async function requirePublicationSection(
  page: Page,
): Promise<{ sectionId: string; section: Locator }> {
  const section = firstPublicationSection(page);
  const count = await section.count();
  if (count === 0) {
    throw new Error(
      'ไม่พบหัวข้อผลงานตีพิมพ์ (data-show-pub=1) — ตรวจ personnel/ migrate หรือเปลี่ยน PLAYWRIGHT_CV_EMAIL',
    );
  }
  await expect(section).toBeVisible();
  const sectionId = await section.getAttribute('data-section-id');
  if (!sectionId) {
    throw new Error('ไม่พบ data-section-id บนหัวข้อ publication');
  }
  return { sectionId, section };
}

export async function expandCvSection(
  page: Page,
  sectionId: string,
): Promise<void> {
  const content = page.locator(`#cv-content-${sectionId}`);
  if (await content.isHidden()) {
    await page
      .locator(`#cv-toggle-${sectionId}`)
      .click({ timeout: 10_000 });
    if (await content.isHidden()) {
      await page
        .locator(
          `.cv-section-item[data-section-id="${sectionId}"] .cv-section-head`,
        )
        .click({ timeout: 10_000 });
    }
    await expect(content).toBeVisible({ timeout: 10_000 });
  }
}

/** หา section_id ของหัวข้อ publication ของ user ที่ล็อกอิน (ต้องอยู่แท็บ sections แล้ว) */
export async function resolvePublicationSectionId(page: Page): Promise<string> {
  const fromEnv = process.env.PLAYWRIGHT_CV_SECTION_ID?.trim();
  if (fromEnv) {
    const owned = page.locator(
      `.cv-section-item[data-show-pub="1"][data-section-id="${fromEnv}"]`,
    );
    if ((await owned.count()) > 0) {
      return fromEnv;
    }
  }
  const { sectionId } = await requirePublicationSection(page);
  return sectionId;
}

export async function openPublicationPage(
  page: Page,
  opts: { sectionId?: string; ai?: boolean; fromSectionsTab?: boolean } = {},
): Promise<string> {
  let sectionId = opts.sectionId;
  if (!sectionId || opts.fromSectionsTab) {
    if (!page.url().includes('tab=sections')) {
      await page.goto('dashboard/profile/cv?tab=sections', {
        waitUntil: 'domcontentloaded',
        timeout: 45_000,
      });
    }
    sectionId = await resolvePublicationSectionId(page);
  }

  const qs = new URLSearchParams({ section_id: sectionId });
  if (opts.ai) {
    qs.set('ai', '1');
  }
  await page.goto(`dashboard/profile/cv/publication?${qs.toString()}`, {
    waitUntil: 'domcontentloaded',
    timeout: 45_000,
  });
  return sectionId;
}

export async function expectPublicationEntryPage(page: Page): Promise<void> {
  await expect(page.locator('#cv-pub-form')).toBeVisible({ timeout: 15_000 });
  await expect(page.locator('#cv-pub-ai-panel')).toBeVisible();
  await expect(
    page.getByRole('heading', { name: /เพิ่มผลงานตีพิมพ์|แก้ไขผลงานตีพิมพ์/ }),
  ).toBeVisible();
  await expect(page.locator('body')).not.toContainText('cv-pub-entry-modal');
}

export async function fillMinimalPublicationForm(
  page: Page,
  title: string,
): Promise<void> {
  await page.locator('#cv-p-title').fill(title);
  await page.locator('#cv-p-org').fill('วารสารทดสอบ Playwright');
  await page.locator('#cv-p-pubtype').selectOption('journal');
  await page.locator('#cv-p-year-be').fill('2567');
  await expect(
    page.locator('#cv-p-authors-list [data-author-index]').first(),
  ).toBeVisible({ timeout: 15_000 });
}
