import { expect, type APIRequestContext, type Page } from '@playwright/test';

/** อีเมล user ที่มี personnel + หัวข้อผลงานตีพิมพ์ใน DB local */
export const cvUserEmail =
  process.env.PLAYWRIGHT_CV_EMAIL ?? 'pisit.nak@live.uru.ac.th';

/**
 * ตรวจว่าแอปรันใน development (เปิด /dev/login-as-admin ได้)
 * ใช้ใน beforeAll ของสเปก local — production/staging จะได้ false
 */
export async function isDevelopmentDevLoginAvailable(
  request: APIRequestContext,
): Promise<boolean> {
  const res = await request.get(
    `dev/login-as-admin?email=${encodeURIComponent(cvUserEmail)}`,
    { maxRedirects: 0, timeout: 15_000 },
  );
  const status = res.status();
  return status === 302 || status === 303 || (status >= 200 && status < 400);
}

/**
 * เข้าสู่ระบบผ่าน /dev/login-as-admin (development เท่านั้น)
 * ใช้ session admin_logged_in ตาม LoggedInFilter
 */
export async function devLoginForCv(
  page: Page,
  email: string = cvUserEmail,
): Promise<void> {
  const q = `email=${encodeURIComponent(email)}`;
  const res = await page.goto(`dev/login-as-admin?${q}`, {
    waitUntil: 'domcontentloaded',
    timeout: 30_000,
  });
  if (res?.status() === 404) {
    throw new Error(
      'dev/login-as-admin ไม่พร้อม — ต้อง CI_ENVIRONMENT=development และ PLAYWRIGHT_BASE_URL ชี้ local',
    );
  }
  await page.waitForURL(/dashboard|profile|index\.php/i, {
    timeout: 30_000,
  });
  if (page.url().includes('/admin/login')) {
    throw new Error(
      `dev login ไม่สำเร็จ — redirect ไป admin/login (ตรวจ email ${email} มีใน users)`,
    );
  }
  await expect(page.getByRole('heading', { name: 'Not Found' })).toHaveCount(0);
}

export async function gotoCvSectionsTab(page: Page): Promise<void> {
  await page.goto('dashboard/profile/cv?tab=sections', {
    waitUntil: 'domcontentloaded',
    timeout: 45_000,
  });
  await expect(
    page.getByRole('heading', { name: 'จัดการ CV' }),
  ).toBeVisible({ timeout: 20_000 });
}
