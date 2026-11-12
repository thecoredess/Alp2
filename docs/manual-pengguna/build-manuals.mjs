import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { chromium } from 'playwright';
import { captureLiveScreenshots } from './capture-live-screenshots.mjs';
import {
  Document,
  Packer,
  Paragraph,
  TextRun,
  HeadingLevel,
  ImageRun,
  AlignmentType,
  PageBreak,
} from 'docx';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const previewDir = path.join(__dirname, 'preview');
const assetsDir = path.join(__dirname, 'assets');
const alpAssetsDir = path.join(__dirname, '..', 'ringkasan-eksekutif-alp', 'assets');

const LOGIN_URL = 'https://apps.dbkl.gov.my/sumbangan_alp/login';

const localShots = [
  { html: 'preview-permohonan-baharu.html', png: 'permohonan-baharu.png', width: 960 },
  { html: 'preview-wizard-lampiran.html', png: 'wizard-lampiran.png', width: 960 },
  { html: 'preview-menu-alp.html', png: 'menu-alp.png', width: 1000 },
  { html: 'preview-menu-admin-jp.html', png: 'menu-admin-jp.png', width: 1000 },
  { html: 'preview-menu-pengarah.html', png: 'menu-pengarah.png', width: 1000 },
  { html: 'preview-admin-jp-queue.html', png: 'giliran-admin-jp.png', width: 1000 },
  { html: 'preview-admin-jp-semakan.html', png: 'semakan-admin-jp.png', width: 920 },
  { html: 'preview-pengarah-queue.html', png: 'giliran-pengarah.png', width: 1000 },
  { html: 'preview-pengarah-lulus.html', png: 'kelulusan-pengarah.png', width: 920 },
];

function imagePath(pngName) {
  const local = path.join(assetsDir, pngName);
  if (fs.existsSync(local)) {
    return local;
  }
  const ringkasan = path.join(alpAssetsDir, pngName);
  if (fs.existsSync(ringkasan)) {
    return ringkasan;
  }
  return local;
}

function imageFromBestDir(pngName, caption) {
  const buf = fs.readFileSync(imagePath(pngName));
  const transformation = fitImageTransformation(buf);
  const paras = [
    new Paragraph({
      spacing: { before: 200, after: 120 },
      alignment: AlignmentType.CENTER,
      children: [new ImageRun({ data: buf, transformation, type: 'png' })],
    }),
  ];
  if (caption) {
    paras.push(
      new Paragraph({
        spacing: { after: 240 },
        alignment: AlignmentType.CENTER,
        children: [
          new TextRun({ text: caption, italics: true, size: 20, font: 'Calibri', color: '666666' }),
        ],
      }),
    );
  }
  return paras;
}

async function capturePreviewScreenshots() {
  fs.mkdirSync(assetsDir, { recursive: true });
  const browser = await chromium.launch();
  const page = await browser.newPage({ deviceScaleFactor: 2 });

  for (const { html, png, width } of localShots) {
    const fileUrl = 'file:///' + path.join(previewDir, html).replace(/\\/g, '/');
    await page.setViewportSize({ width, height: 800 });
    await page.goto(fileUrl, { waitUntil: 'networkidle' });
    const bodyHeight = await page.evaluate(() => document.body.scrollHeight);
    await page.setViewportSize({ width, height: Math.min(bodyHeight + 48, 2800) });
    await page.goto(fileUrl, { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(assetsDir, png), fullPage: true });
    console.log('Screenshot:', png);
  }

  await browser.close();
}

async function captureScreenshots() {
  if (process.env.USE_PREVIEW_SHOTS === '1') {
    await capturePreviewScreenshots();
    return;
  }

  try {
    await captureLiveScreenshots();
    console.log('Tangkapan skrin sistem (live) selesai.');
  } catch (err) {
    console.warn('Live capture gagal — guna pratonton HTML:', err.message);
    await capturePreviewScreenshots();
  }
}

function p(text, opts = {}) {
  return new Paragraph({
    spacing: { after: opts.after ?? 160 },
    alignment: opts.center ? AlignmentType.CENTER : undefined,
    children: [
      new TextRun({
        text,
        bold: opts.bold,
        italics: opts.italics,
        size: opts.size ?? 22,
        font: 'Calibri',
      }),
    ],
  });
}

function bullet(text) {
  return new Paragraph({
    spacing: { after: 100 },
    indent: { left: 360, hanging: 360 },
    children: [
      new TextRun({ text: '• ', size: 22, font: 'Calibri' }),
      new TextRun({ text, size: 22, font: 'Calibri' }),
    ],
  });
}

function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    spacing: { before: 200, after: 160 },
    children: [new TextRun({ text, bold: true, size: 28, font: 'Calibri' })],
  });
}

function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    spacing: { before: 160, after: 120 },
    children: [new TextRun({ text, bold: true, size: 26, font: 'Calibri' })],
  });
}

function pngPixelSize(buffer) {
  if (buffer.length < 24 || buffer.toString('ascii', 1, 4) !== 'PNG') {
    return { width: 600, height: 400 };
  }
  return { width: buffer.readUInt32BE(16), height: buffer.readUInt32BE(20) };
}

function fitImageTransformation(buffer, maxWidth = 550) {
  const { width: pw, height: ph } = pngPixelSize(buffer);
  const width = Math.min(maxWidth, pw);
  const height = Math.max(1, Math.round((ph / pw) * width));
  return { width, height };
}

function imageFromDir(dir, pngName, caption) {
  const buf = fs.readFileSync(path.join(dir, pngName));
  const transformation = fitImageTransformation(buf);
  const paras = [
    new Paragraph({
      spacing: { before: 200, after: 120 },
      alignment: AlignmentType.CENTER,
      children: [new ImageRun({ data: buf, transformation, type: 'png' })],
    }),
  ];
  if (caption) {
    paras.push(
      new Paragraph({
        spacing: { after: 240 },
        alignment: AlignmentType.CENTER,
        children: [
          new TextRun({ text: caption, italics: true, size: 20, font: 'Calibri', color: '666666' }),
        ],
      }),
    );
  }
  return paras;
}

function cover(title, subtitle) {
  const today = new Date().toLocaleDateString('ms-MY', { day: 'numeric', month: 'long', year: 'numeric' });
  return [
    new Paragraph({
      heading: HeadingLevel.TITLE,
      alignment: AlignmentType.CENTER,
      spacing: { after: 120 },
      children: [new TextRun({ text: title, bold: true, size: 36, font: 'Calibri' })],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { after: 400 },
      children: [new TextRun({ text: subtitle + '\nSistem Sumbangan ALP DBKL', size: 24, font: 'Calibri' })],
    }),
    p(`Tarikh dokumen: ${today}`, { italics: true, size: 20 }),
    new Paragraph({ children: [new PageBreak()] }),
  ];
}

function footerNote() {
  return [
    h1('Rujukan & nota'),
    bullet(`Log masuk: ${LOGIN_URL}`),
    bullet('Kata laluan UAT tempatan (jika berkenaan): rujuk docs/PANDUAN_LOGIN.md'),
    p(
      'Tangkapan skrin dalam manual ini diambil daripada paparan sistem (uji/lokal atau production). Jika data contoh digunakan, label pratonton akan dipaparkan pada skrin ujian.',
      { italics: true, size: 20 },
    ),
  ];
}

function buildAlpChildren() {
  return [
    ...cover('Manual Pengguna', 'Ahli Lembaga Penasihat (ALP)'),
    h1('1. Objektif manual'),
    p(
      'Manual ini menerangkan cara ALP mengemukakan permohonan sumbangan melalui portal dalam talian: mengisi borang, muat naik lampiran, menghantar kepada Jabatan Pentadbiran (JP), dan memantau status sehingga baucar serta laporan aktiviti.',
    ),
    h1('2. Log masuk & menu'),
    bullet('Buka portal log masuk dan gunakan e-mel serta kata laluan yang diberikan oleh pentadbir sistem.'),
    bullet('Menu utama ALP: Dashboard, Permohonan Saya, Bajet Saya, Manual Sistem.'),
    ...imageFromBestDir('menu-alp.png', 'Rajah 1 — Dashboard & menu (paparan sistem ALP)'),
    h1('3. Permohonan baharu (/permohonan/baharu)'),
    p(
      'Dari menu Permohonan Saya → Permohonan Baharu. Halaman pertama ialah Borang Penyaluran (Langkah 1) dengan bar langkah seperti dalam sistem sebenar.',
    ),
    h2('3.1 Dua cara mula permohonan'),
    bullet(
      'Cipta draf & jana pautan persatuan — draf kosong; persatuan isi borang + 4 lampiran melalui pautan (7 hari); ALP semak kemudian hantar ke Admin JP.',
    ),
    bullet(
      'Simpan Draf & Muat Naik Lampiran — ALP isi semua medan a–i (termasuk Kertas Kerja dalam talian medan g)) pada halaman baharu, kemudian terus ke Langkah 2.',
    ),
    ...imageFromBestDir('permohonan-baharu.png', 'Rajah 2 — Skrin Permohonan Baharu (Langkah 1) — paparan sistem'),
    h2('3.2 Medan borang a–i'),
    bullet('g) Kertas Kerja — beberapa textarea (latar belakang, objektif, sasaran, jadual WP KL, pecahan perbelanjaan, impak); bukan satu medan “tujuan” ringkas.'),
    bullet('f) Jumlah dimohon dipaparkan bersama petunjuk baki peruntukan/had jika polisi aktif.'),
    h2('3.3 Langkah 2 & 3'),
    bullet('Langkah 2 — Muat naik lampiran wajib dalam grid (PDF/imej); bar kemajuan n/N.'),
    bullet('Langkah 3 — Semak & Hantar kepada JP: ringkasan borang, bajet, dan butang hantar.'),
    ...imageFromBestDir('wizard-lampiran.png', 'Rajah 3 — Muat naik dokumen (Langkah 2) — paparan sistem'),
    h1('4. Gambaran aliran ALP'),
    ...imageFromBestDir('flow-diagram.png', 'Rajah 4 — Aliran permohonan (perspektif ALP)'),
    h1('5. Selepas hantar — pantau status'),
    p(
      'Dalam paparan ALP, timeline disederhanakan: permohonan dihantar, semakan & kelulusan JP, baucar, dan laporan aktiviti selepas program.',
    ),
    ...imageFromBestDir('status-permohonan-alp.png', 'Rajah 5 — Butiran permohonan / timeline (paparan ALP)'),
    h1('6. Pembetulan & laporan'),
    bullet('Status “Perlu Pembetulan”: ALP boleh edit borang/lampiran dan hantar semula.'),
    bullet('Selepas diluluskan dan baucar disediakan, muat naik laporan aktiviti mengikut tarikh akhir yang dipaparkan.'),
    ...footerNote(),
  ];
}

function buildAdminJpChildren() {
  return [
    ...cover('Manual Pengguna', 'Admin Jabatan Pentadbiran (Admin JP)'),
    h1('1. Peranan Admin JP'),
    p(
      'Admin JP menyemak permohonan yang telah dihantar ALP. Tugas utama: sahkan borang dan lampiran, pastikan ulasan semakan silang JKEW dimuat naik, lengkapkan Senarai Semak JP (UR-M04-001), dan rekod keputusan semakan.',
    ),
    h1('2. Log masuk & menu'),
    bullet('Akaun Admin JP mempunyai kebenaran “Semakan Admin JP” (bukan giliran Pegawai JP untuk permohonan baharu).'),
    bullet('Menu berkaitan: Semakan Admin JP, Semua Permohonan, Semak Laporan Aktiviti, Laporan, Senarai ALP.'),
    ...imageFromBestDir('menu-admin-jp.png', 'Rajah 1 — Dashboard & menu (paparan sistem Admin JP)'),
    h1('3. Giliran semakan'),
    p('Senarai memaparkan permohonan berstatus Dihantar menunggu keputusan Admin JP. Klik Semak untuk buka butiran.'),
    ...imageFromBestDir('giliran-admin-jp.png', 'Rajah 2 — Giliran semakan Admin JP — paparan sistem'),
    h1('4. Skrin semakan & keputusan'),
    p('Pada halaman semakan, semak borang penyaluran, lampiran, kad semakan silang JKEW, dan ringkasan bajet ALP.'),
    ...imageFromBestDir('semakan-admin-jp.png', 'Rajah 3 — Skrin semakan Admin JP — paparan sistem'),
    h2('4.1 Keputusan yang boleh direkod'),
    bullet('Disyorkan — semua item senarai semak “Lengkap” dan ulasan JKEW wujud → permohonan terus ke giliran Kelulusan Pengarah JP.'),
    bullet('Tidak Disyorkan — nasihat ditulis; permohonan tetap maju ke Pengarah mengikut rekod semakan (Pengarah membuat keputusan akhir).'),
    bullet('Kembalikan untuk Pembetulan — ALP perlu betulkan borang/lampiran dan hantar semula.'),
    h1('5. Perbezaan dengan Pegawai JP'),
    bullet(
      'Aliran aktif: Admin JP tidak menghantar ke Pegawai JP untuk permohonan baharu. Pegawai JP hanya bagi rekod legacy (status lama dalam sistem).',
    ),
    ...footerNote(),
  ];
}

function buildPengarahChildren() {
  return [
    ...cover('Manual Pengguna', 'Pengarah Jabatan Pentadbiran (Pengarah JP)'),
    h1('1. Peranan Pengarah JP'),
    p(
      'Pengarah JP (peranan Pelulus) memberikan kelulusan akhir permohonan sumbangan ALP. Matriks kelulusan: satu aras untuk semua jumlah. Selepas lulus, sistem merekod komitmen bajet, memaklumkan ALP & Kewangan, dan menghantar e-mel makluman kepada PEPU.',
    ),
    h1('2. Log masuk & menu'),
    bullet('Menu: Pengesyoran / Kelulusan (giliran permohonan menunggu keputusan).'),
    bullet('PEPU tidak mempunyai skrin kelulusan — hanya menerima makluman e-mel dan boleh lihat status permohonan diluluskan.'),
    ...imageFromBestDir('menu-pengarah.png', 'Rajah 1 — Dashboard & menu (paparan sistem Pengarah JP)'),
    h1('3. Giliran kelulusan'),
    ...imageFromBestDir('giliran-pengarah.png', 'Rajah 2 — Giliran kelulusan — paparan sistem'),
    h1('4. Skrin kelulusan'),
    p('Semak ringkasan kewangan ALP (peruntukan, jumlah dimohon, baki jika diluluskan), borang, dan sejarah semakan Admin JP.'),
    ...imageFromBestDir('kelulusan-pengarah.png', 'Rajah 3 — Skrin kelulusan Pengarah JP — paparan sistem'),
    h2('4.1 Tindakan'),
    bullet('Luluskan — cipta komitmen bajet, status Diluluskan, notifikasi kepada ALP, Kerani Kewangan, dan makluman PEPU.'),
    bullet('Tolak — permohonan ditolak; tiada komitmen bajet.'),
    bullet('Kembalikan untuk Pembetulan — ALP perlu membetulkan dan menghantar semula.'),
    h1('5. Selepas kelulusan'),
    bullet('Kerani Kewangan mengurus baucar melalui modul Pembayaran / Baucar.'),
    bullet('ALP boleh muat turun surat pemakluman dan menjejak baucar melalui portal dbayar (apabila maklumat tersedia).'),
    ...footerNote(),
  ];
}

async function writeDoc(filename, children) {
  const doc = new Document({ sections: [{ properties: {}, children }] });
  const buffer = await Packer.toBuffer(doc);
  const out = path.join(__dirname, filename);
  fs.writeFileSync(out, buffer);
  console.log('Written:', out);
}

async function buildAll() {
  const alpOut = process.env.ALP_DOCX_NAME || 'Manual-Pengguna-ALP.docx';
  const adminOut = process.env.ADMIN_JP_DOCX_NAME || 'Manual-Pengguna-Admin-JP.docx';
  const pengarahOut = process.env.PENGARAH_JP_DOCX_NAME || 'Manual-Pengguna-Pengarah-JP.docx';
  await writeDoc(alpOut, buildAlpChildren());
  await writeDoc(adminOut, buildAdminJpChildren());
  await writeDoc(pengarahOut, buildPengarahChildren());
}

async function main() {
  const shotsOnly = process.argv.includes('--shots-only');
  const skipShots = process.env.SKIP_SHOTS === '1';

  if (!skipShots) {
    await captureScreenshots();
  }
  if (shotsOnly) {
    return;
  }
  await buildAll();
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
