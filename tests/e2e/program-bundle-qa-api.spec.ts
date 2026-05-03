import { test, expect } from '@playwright/test';

/**
 * รวมเคส QA ที่ยืนยันด้วย APIRequest (ใช้ cookie session เดียวกับหน้าเว็บ)
 * รันแบบ serial — B8/B9 มี commit จริงแล้ว rollback ด้วย export ชุดต้นทาง
 */
test.describe.configure({ mode: 'serial' });

const adminEmail =
  process.env.PLAYWRIGHT_ADMIN_EMAIL ?? 'pisit.nak@live.uru.ac.th';
const programId = process.env.PLAYWRIGHT_PROGRAM_ID ?? '1';
const pid = Number(programId);

async function devLogin(page: import('@playwright/test').Page) {
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

function assertBundleShape(j: Record<string, unknown>) {
  const keys = Object.keys(j).sort();
  expect(keys.join(',')).toContain('basic');
  expect(keys.join(',')).toContain('content');
  expect(keys.join(',')).toContain('settings');
  expect(j).not.toHaveProperty('program');
  expect(j).not.toHaveProperty('page');
  expect(j).not.toHaveProperty('staff');
  expect(j).not.toHaveProperty('news');
  expect(j).not.toHaveProperty('documents');
  expect(j).not.toHaveProperty('activities');
  expect(j).not.toHaveProperty('facilities');
  const basic = j.basic as Record<string, unknown>;
  const content = j.content as Record<string, unknown>;
  const settings = j.settings as Record<string, unknown>;
  expect(Object.keys(basic).length).toBe(10);
  expect(Object.keys(content).length).toBe(22);
  expect(Object.keys(settings).length).toBe(9);
  expect(j.schema_version).toBe(2);
  expect(basic.name_th).toMatchObject({
    source: 'database',
    table: 'programs',
    column: 'name_th',
    importable: true,
  });
  expect(content.careers_json).toMatchObject({
    source: 'database_json_column',
    table: 'program_pages',
    column: 'careers_json',
    importable: true,
  });
  const overlap = Object.keys(content).filter((k) => k in settings);
  expect(overlap).toEqual([]);
}

function fieldValue<T = unknown>(obj: Record<string, unknown>, key: string): T {
  const field = obj[key] as { value?: T } | T;
  if (field && typeof field === 'object' && 'value' in field) {
    return (field as { value: T }).value;
  }
  return field as T;
}

test.describe('QA Bundle v1.1 — API + commit + rollback', () => {
  test('B2 B3 B4 B5 B8 B6 B9 B7 B10 B12 (ครบใน flow เดียว)', async ({
    page,
  }) => {
    await devLogin(page);
    await page.goto(`program-admin/edit/${programId}`, {
      waitUntil: 'domcontentloaded',
    });
    const api = page.context().request;

    const expRes = await api.get(`program-admin/bundle-export/${pid}`);
    expect(expRes.ok(), `bundle-export ${expRes.status()}`).toBeTruthy();
    const bodyOriginal = await expRes.text();
    const original = JSON.parse(bodyOriginal) as Record<string, unknown>;
    assertBundleShape(original);

    const tplRes = await api.get(`program-admin/bundle-template/${pid}`);
    expect(tplRes.ok()).toBeTruthy();
    const tpl = (await tplRes.json()) as Record<string, unknown>;
    expect(typeof tpl.template_note).toBe('string');
    expect((tpl.template_note as string).length).toBeGreaterThan(10);
    const ls = tpl.content as Record<string, unknown>;
    const lss = fieldValue<Record<string, unknown>>(ls, 'learning_standards_json');
    expect(lss.intro).toBe('');
    expect(Array.isArray(lss.standards)).toBeTruthy();
    expect(Array.isArray(lss.mapping)).toBeTruthy();
    expect(fieldValue(tpl.settings as Record<string, unknown>, 'theme_color')).toBe(
      '#1e40af',
    );
    expect(fieldValue(tpl.settings as Record<string, unknown>, 'is_published')).toBe(0);

    const prevRes = await api.get(`program-admin/bundle-preview/${pid}`);
    expect(prevRes.ok()).toBeTruthy();
    const prevJson = (await prevRes.json()) as {
      success: boolean;
      sections: { title: string }[];
    };
    expect(prevJson.success).toBeTruthy();
    const titles = prevJson.sections.map((s) => s.title);
    expect(titles).toContain('1. ภาพรวม');
    expect(titles).toContain('5. เผยแพร่ & หน้าเว็บ');

    const modified = JSON.parse(bodyOriginal) as Record<string, unknown>;
    const basic = { ...(modified.basic as Record<string, unknown>) };
    const content = { ...(modified.content as Record<string, unknown>) };
    const stamp = `QA_PW_${Date.now()}`;
    basic.name_th = {
      ...(basic.name_th as Record<string, unknown>),
      value: stamp,
    };
    content.contact_info = {
      value: 'computed field should not import',
      source: 'computed',
      importable: false,
    };
    modified.basic = basic;
    modified.content = content;

    const p5 = await api.post(`program-admin/bundle-import-preview/${pid}`, {
      multipart: {
        bundle_file: {
          name: 'import.json',
          mimeType: 'application/json',
          buffer: Buffer.from(JSON.stringify(modified), 'utf-8'),
        },
      },
    });
    const j5 = await p5.json();
    expect(j5.success, JSON.stringify(j5)).toBeTruthy();
    expect(j5.legacy).toBeFalsy();
    expect(j5.schema_version).toBe(2);
    expect(j5.ignored_fields).toContain('content.contact_info');
    expect(j5.import_summary.basic_update_count).toBeGreaterThan(0);
    const token5 = j5.token as string;
    expect(token5?.length).toBe(40);

    const c5 = await api.post(`program-admin/bundle-import-commit/${pid}`, {
      form: { token: token5 },
    });
    const c5j = await c5.json();
    expect(c5j.success, JSON.stringify(c5j)).toBeTruthy();

    const afterNew = await (
      await api.get(`program-admin/bundle-export/${pid}`)
    ).json();
    expect(fieldValue(afterNew.basic as Record<string, unknown>, 'name_th')).toBe(stamp);

    const pRb = await api.post(`program-admin/bundle-import-preview/${pid}`, {
      multipart: {
        bundle_file: {
          name: 'restore.json',
          mimeType: 'application/json',
          buffer: Buffer.from(bodyOriginal, 'utf-8'),
        },
      },
    });
    const jRb = await pRb.json();
    expect(jRb.success).toBeTruthy();
    const cRb = await api.post(`program-admin/bundle-import-commit/${pid}`, {
      form: { token: jRb.token as string },
    });
    expect((await cRb.json()).success).toBeTruthy();

    const legacyDoc = {
      schema_version: 1,
      program_id: pid,
      program: {
        id: pid,
        name_th: 'QA LEGACY',
        level: 'bachelor',
        status: 'active',
      },
      page: {
        philosophy: 'ทดสอบ legacy',
        theme_color: '#abcdef',
        hero_image: '',
        is_published: 1,
        slug: 'legacy-test',
      },
    };
    const p6 = await api.post(`program-admin/bundle-import-preview/${pid}`, {
      multipart: {
        bundle_file: {
          name: 'legacy.json',
          mimeType: 'application/json',
          buffer: Buffer.from(JSON.stringify(legacyDoc), 'utf-8'),
        },
      },
    });
    const j6 = await p6.json();
    expect(j6.success, JSON.stringify(j6)).toBeTruthy();
    expect(j6.legacy).toBe(true);
    expect(j6.basic_keys).toEqual(['name_th', 'level']);
    const c6 = await api.post(`program-admin/bundle-import-commit/${pid}`, {
      form: { token: j6.token as string },
    });
    expect((await c6.json()).success).toBeTruthy();
    const afterLeg = await (
      await api.get(`program-admin/bundle-export/${pid}`)
    ).json();
    expect(fieldValue(afterLeg.basic as Record<string, unknown>, 'name_th')).toBe(
      'QA LEGACY',
    );
    expect(fieldValue(afterLeg.settings as Record<string, unknown>, 'theme_color')).toBe(
      '#abcdef',
    );
    expect(fieldValue(afterLeg.settings as Record<string, unknown>, 'is_published')).toBe(1);
    expect(fieldValue(afterLeg.settings as Record<string, unknown>, 'slug')).toBe(
      'legacy-test',
    );

    const pRb2 = await api.post(`program-admin/bundle-import-preview/${pid}`, {
      multipart: {
        bundle_file: {
          name: 'restore2.json',
          mimeType: 'application/json',
          buffer: Buffer.from(bodyOriginal, 'utf-8'),
        },
      },
    });
    const jRb2 = await pRb2.json();
    expect(jRb2.success).toBeTruthy();
    const cRb2 = await api.post(`program-admin/bundle-import-commit/${pid}`, {
      form: { token: jRb2.token as string },
    });
    expect((await cRb2.json()).success).toBeTruthy();

    const badJson = {
      schema_version: 1,
      program_id: pid,
      basic: {
        credits: -5,
        level: 'wizard',
        website: 'javascript:alert(1)',
      },
      settings: { theme_color: 'red' },
    };
    const p7 = await api.post(`program-admin/bundle-import-preview/${pid}`, {
      multipart: {
        bundle_file: {
          name: 'bad.json',
          mimeType: 'application/json',
          buffer: Buffer.from(JSON.stringify(badJson), 'utf-8'),
        },
      },
    });
    const j7 = await p7.json();
    expect(j7.success).toBeFalsy();
    expect(Array.isArray(j7.errors)).toBeTruthy();

    const cur = original.basic as Record<string, unknown>;
    const b10Name = `${String(fieldValue(cur, 'name_th'))} [B10]`;
    const b10 = await api.post(`program-admin/update/${pid}`, {
      form: {
        name_th: b10Name,
        name_en: String(fieldValue(cur, 'name_en') ?? ''),
        level: String(fieldValue(cur, 'level') ?? 'bachelor'),
        status: 'active',
      },
    });
    expect(b10.status(), `B10 update ${b10.status()}`).toBeLessThan(400);

    const b10w = await api.post(`program-admin/update-website/${pid}`, {
      form: {
        theme_color: '#112233',
        text_color: '',
        background_color: '',
      },
    });
    expect(b10w.status(), `B10 website ${b10w.status()}`).toBeLessThan(400);

    const pRb3 = await api.post(`program-admin/bundle-import-preview/${pid}`, {
      multipart: {
        bundle_file: {
          name: 'restore3.json',
          mimeType: 'application/json',
          buffer: Buffer.from(bodyOriginal, 'utf-8'),
        },
      },
    });
    const jRb3 = await pRb3.json();
    expect(jRb3.success).toBeTruthy();
    const cRb3 = await api.post(`program-admin/bundle-import-commit/${pid}`, {
      form: { token: jRb3.token as string },
    });
    expect((await cRb3.json()).success).toBeTruthy();

    const b12 = await api.get(`program-admin/bundle-preview/999999`);
    expect(b12.status()).toBe(404);
  });
});
