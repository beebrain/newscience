import { test, expect } from '@playwright/test';

const programId = process.env.PLAYWRIGHT_PROGRAM_ID ?? '1';

test.describe('Public program SPA (no login)', () => {
  test('B11: โหลดหน้า main และ /data ไม่ error ใน console', async ({
    page,
  }) => {
    const consoleErrors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    const response = await page.goto(`p/${programId}/main`, {
      waitUntil: 'domcontentloaded',
      timeout: 45_000,
    });
    expect(response?.status(), 'หน้า SPA main ควรตอบ 200').toBe(200);

    const dataRes = await page.request.get(`p/${programId}/data`);
    expect(dataRes.ok()).toBeTruthy();
    const body = await dataRes.json();
    expect(body.success).toBe(true);

    const noise = /favicon|ResizeObserver|Failed to load resource.*404/i;
    const serious = consoleErrors.filter((t) => !noise.test(t));
    expect(serious, `console.error: ${serious.join(' | ')}`).toEqual([]);
  });
});
