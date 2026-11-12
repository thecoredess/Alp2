/**
 * Tangkapan skrin daripada sistem berjalan (XAMPP / staging).
 * Prasyarat: pelayan web aktif, DevSeeder atau SimulationSeeder, tahun kewangan aktif.
 *
 * MANUAL_BASE_URL=http://localhost/Alp2/public
 * MANUAL_UAT_PASSWORD=password
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { chromium } from 'playwright';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
export const assetsDir = path.join(__dirname, 'assets');

const baseUrl = (process.env.MANUAL_BASE_URL || 'http://localhost/Alp2/public').replace(/\/$/, '');
const password = process.env.MANUAL_UAT_PASSWORD || 'password';

const accounts = {
  alp: process.env.MANUAL_ALP_EMAIL || 'alp01@dbkl.test',
  adminJp: process.env.MANUAL_ADMIN_EMAIL || 'adminjp@dbkl.test',
  pengarah: process.env.MANUAL_PELULUS_EMAIL || 'pelulus@dbkl.test',
};

async function login(page, email) {
  await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle', timeout: 60000 });
  await page.locator('#email').fill(email);
  await page.locator('#password').fill(password);
  await page.locator('#login-form button[type="submit"]').click();
  await page.waitForURL((url) => !url.pathname.endsWith('/login'), { timeout: 30000 });
}

async function shotViewport(page, fileName, urlPath) {
  await page.setViewportSize({ width: 1360, height: 860 });
  await page.goto(`${baseUrl}${urlPath}`, { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(400);
  await page.screenshot({ path: path.join(assetsDir, fileName) });
  console.log('Live screenshot (viewport):', fileName);
}

async function shotMain(page, fileName, urlPath) {
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto(`${baseUrl}${urlPath}`, { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(400);
  const main = page.locator('main');
  if ((await main.count()) > 0) {
    await main.screenshot({ path: path.join(assetsDir, fileName) });
  } else {
    await page.screenshot({ path: path.join(assetsDir, fileName), fullPage: true });
  }
  console.log('Live screenshot (main):', fileName);
}

async function captureAlp(browser) {
  const page = await browser.newPage({ deviceScaleFactor: 2 });
  await login(page, accounts.alp);

  await shotViewport(page, 'menu-alp.png', '/dashboard');
  await shotMain(page, 'permohonan-baharu.png', '/permohonan/baharu');

  await page.goto(`${baseUrl}/permohonan/baharu`, { waitUntil: 'networkidle' });
  const shellBtn = page.getByRole('button', { name: /jana pautan persatuan/i });
  if (await shellBtn.isVisible()) {
    await shellBtn.click();
    await page.waitForURL(/\/lampiran$/, { timeout: 30000 });
    await page.waitForTimeout(500);
    await shotMain(page, 'wizard-lampiran.png', page.url().replace(baseUrl, ''));
  } else {
    console.warn('Butang draf persatuan tidak jumpa — langkau wizard-lampiran.png');
  }

  await page.goto(`${baseUrl}/permohonan`, { waitUntil: 'networkidle' });
  const showLink = page.locator('a[href*="/permohonan/"]').filter({ hasNotText: 'baharu' }).first();
  if (await showLink.count()) {
    const href = await showLink.getAttribute('href');
    if (href && !href.includes('lampiran') && !href.includes('borang')) {
      await shotMain(page, 'status-permohonan-alp.png', href.replace(baseUrl, ''));
    }
  }

  await page.close();
}

async function captureAdminJp(browser) {
  const page = await browser.newPage({ deviceScaleFactor: 2 });
  await login(page, accounts.adminJp);

  await shotViewport(page, 'menu-admin-jp.png', '/dashboard');
  await shotMain(page, 'giliran-admin-jp.png', '/semakan/urus-setia');

  await page.goto(`${baseUrl}/semakan/urus-setia`, { waitUntil: 'networkidle' });
  const semak = page.locator('a', { hasText: 'Semak' }).first();
  if (await semak.count()) {
    await semak.click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);
    await shotMain(page, 'semakan-admin-jp.png', page.url().replace(baseUrl, ''));
  } else {
    console.warn('Tiada permohonan Dihantar untuk tangkapan semakan Admin JP.');
  }

  await page.close();
}

async function capturePengarah(browser) {
  const page = await browser.newPage({ deviceScaleFactor: 2 });
  await login(page, accounts.pengarah);

  await shotViewport(page, 'menu-pengarah.png', '/dashboard');
  await shotMain(page, 'giliran-pengarah.png', '/kelulusan');

  await page.goto(`${baseUrl}/kelulusan`, { waitUntil: 'networkidle' });
  const syor = page
    .locator('a', { hasText: /^(Syor|Kelulusan|Semak)/i })
    .first();
  if (await syor.count()) {
    await syor.click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);
    await shotMain(page, 'kelulusan-pengarah.png', page.url().replace(baseUrl, ''));
  } else {
    console.warn('Tiada permohonan menunggu kelulusan untuk tangkapan Pengarah.');
  }

  await page.close();
}

export async function captureLiveScreenshots() {
  fs.mkdirSync(assetsDir, { recursive: true });
  console.log('Live capture base URL:', baseUrl);

  const browser = await chromium.launch();
  try {
    await captureAlp(browser);
    await captureAdminJp(browser);
    await capturePengarah(browser);
  } finally {
    await browser.close();
  }
}

const isDirectRun = process.argv[1] && fileURLToPath(import.meta.url) === path.resolve(process.argv[1]);
if (isDirectRun) {
  captureLiveScreenshots().catch((err) => {
    console.error(err);
    process.exit(1);
  });
}
