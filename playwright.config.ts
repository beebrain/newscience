import { defineConfig, devices } from '@playwright/test';

/**
 * ต้องลงท้ายด้วย `/` — ถ้า `page.goto('dev/...')` ใช้ base ไม่มี `/` ท้าย หรือใช้ `goto('/dev/...')`
 * path อาจไปที่ root โฮสต์และได้ Apache 404
 */
function normalizeBaseUrl(u: string): string {
  const t = u.trim();
  return t.endsWith('/') ? t : `${t}/`;
}
const baseURL = normalizeBaseUrl(
  process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost/newScience/public',
);

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [['list'], ['html', { open: 'never', outputFolder: 'playwright-report' }]],
  use: {
    baseURL,
    trace: 'on-first-retry',
    locale: 'th-TH',
    ignoreHTTPSErrors: true,
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
