import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { chromium } from 'playwright';
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
const outDocx = path.join(__dirname, 'Ringkasan-Eksekutif-Flow-ALP-Mohon-Sumbangan.docx');

const shots = [
  { html: 'preview-flow-diagram.html', png: 'flow-diagram.png', width: 920 },
  { html: 'preview-borang.html', png: 'borang-penyaluran.png', width: 960 },
  { html: 'preview-dokumen.html', png: 'muat-naik-dokumen.png', width: 960 },
  { html: 'preview-alp-timeline.html', png: 'status-permohonan-alp.png', width: 760 },
];

async function captureScreenshots() {
  fs.mkdirSync(assetsDir, { recursive: true });
  const browser = await chromium.launch();
  const page = await browser.newPage({ deviceScaleFactor: 2 });

  for (const { html, png, width } of shots) {
    const fileUrl = 'file:///' + path.join(previewDir, html).replace(/\\/g, '/');
    await page.setViewportSize({ width, height: 800 });
    await page.goto(fileUrl, { waitUntil: 'networkidle' });
    const bodyHeight = await page.evaluate(() => document.body.scrollHeight);
    await page.setViewportSize({ width, height: Math.min(bodyHeight + 40, 2400) });
    await page.goto(fileUrl, { waitUntil: 'networkidle' });
    await page.screenshot({
      path: path.join(assetsDir, png),
      fullPage: true,
    });
    console.log('Screenshot:', png);
  }

  await browser.close();
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

/** PNG IHDR width/height (pixels). */
function pngPixelSize(buffer) {
  if (buffer.length < 24 || buffer.toString('ascii', 1, 4) !== 'PNG') {
    return { width: 600, height: 400 };
  }
  return {
    width: buffer.readUInt32BE(16),
    height: buffer.readUInt32BE(20),
  };
}

function fitImageTransformation(buffer, maxWidth = 550) {
  const { width: pw, height: ph } = pngPixelSize(buffer);
  const width = Math.min(maxWidth, pw);
  const height = Math.max(1, Math.round((ph / pw) * width));
  return { width, height };
}

function imageParagraph(pngName, caption) {
  const buf = fs.readFileSync(path.join(assetsDir, pngName));
  const transformation = fitImageTransformation(buf);
  const children = [
    new ImageRun({
      data: buf,
      transformation,
      type: 'png',
    }),
  ];
  const paras = [
    new Paragraph({
      spacing: { before: 200, after: 120 },
      alignment: AlignmentType.CENTER,
      children,
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

async function buildDocument() {
  const today = new Date().toLocaleDateString('ms-MY', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });

  const children = [
    new Paragraph({
      heading: HeadingLevel.TITLE,
      alignment: AlignmentType.CENTER,
      spacing: { after: 120 },
      children: [
        new TextRun({
          text: 'Ringkasan Eksekutif',
          bold: true,
          size: 36,
          font: 'Calibri',
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { after: 400 },
      children: [
        new TextRun({
          text: 'Aliran Permohonan Sumbangan — Perspektif ALP\nSistem Sumbangan ALP DBKL',
          size: 24,
          font: 'Calibri',
        }),
      ],
    }),
    p(`Tarikh dokumen: ${today}`, { italics: true, size: 20 }),
    new Paragraph({ children: [new PageBreak()] }),

    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      children: [new TextRun({ text: '1. Tujuan', bold: true, size: 28, font: 'Calibri' })],
    }),
    p(
      'Dokumen ini menerangkan secara ringkas bagaimana Ahli Lembaga Penasihat (ALP) mengemukakan permohonan sumbangan melalui sistem dalam talian. Skop terhad kepada tindakan ALP sahaja — semakan dalaman Jabatan Pentadbiran (JP), kelulusan PEPU, dan proses baucar diurus oleh pihak DBKL; ALP hanya memantau status dan melengkapkan laporan selepas program.',
    ),

    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      children: [new TextRun({ text: '2. Ringkasan Eksekutif', bold: true, size: 28, font: 'Calibri' })],
    }),
    p(
      'ALP log masuk ke portal sumbangan, mengisi Borang Penyaluran Sumbangan (medan a–i), muat naik dokumen sokongan mengikut senarai semak, kemudian menghantar permohonan kepada JP. Selepas hantar, ALP boleh menjejak kemajuan — semakan jabatan, kelulusan, penyediaan baucar, dan keperluan laporan aktiviti — melalui halaman butiran permohonan. Polisi umum (contoh: tarikh program sekurang-kurangnya dua bulan dari hari hantar, had jumlah dimohon mengikut peruntukan tahunan) dikenakan semasa pengisian borang.',
    ),

    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      children: [new TextRun({ text: '3. Gambaran Aliran (ALP)', bold: true, size: 28, font: 'Calibri' })],
    }),
    p('Rajah di bawah merangkumi tiga fasa utama: persediaan ALP, tempoh menunggu kelulusan DBKL, dan serahan laporan selepas program.'),
    ...imageParagraph('flow-diagram.png', 'Rajah 1 — Aliran permohonan sumbangan (fokus ALP)'),

    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      children: [
        new TextRun({ text: '4. Wizard Permohonan — Tiga Langkah ALP', bold: true, size: 28, font: 'Calibri' }),
      ],
    }),
    bullet('Langkah 1 — Borang Penyaluran: maklumat ALP, persatuan penerima (ROS), tarikh program, kategori, jumlah dimohon, tujuan, akaun bank, dan alamat (WP Kuala Lumpur).'),
    bullet('Langkah 2 — Muat Naik Dokumen: lampiran digital (PDF/imej) seperti sijil ROS, pendaftaran pertubuhan, borang EFT, penyata bank, kertas kerja, dan lain-lain mengikut keperluan jenis permohonan.'),
    bullet('Langkah 3 — Hantar kepada JP: semak ringkasan, pastikan baki peruntukan mencukupi, kemudian hantar; status bertukar kepada permohonan dihantar.'),
    p('Jika JP meminta pembetulan, status “Perlu Pembetulan” membolehkan ALP mengedit borang/lampiran dan menghantar semula.'),

    new Paragraph({
      heading: HeadingLevel.HEADING_2,
      children: [
        new TextRun({ text: '4.1 Borang Permohonan (Langkah 1)', bold: true, size: 26, font: 'Calibri' }),
      ],
    }),
    ...imageParagraph(
      'borang-penyaluran.png',
      'Rajah 2 — Pratonton skrin Borang Penyaluran Sumbangan (medan a–i)',
    ),

    new Paragraph({
      heading: HeadingLevel.HEADING_2,
      children: [
        new TextRun({ text: '4.2 Muat Naik Dokumen (Langkah 2)', bold: true, size: 26, font: 'Calibri' }),
      ],
    }),
    ...imageParagraph('muat-naik-dokumen.png', 'Rajah 3 — Pratonton skrin muat naik lampiran'),

    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      children: [
        new TextRun({ text: '5. Selepas Hantar — Pantau Status', bold: true, size: 28, font: 'Calibri' }),
      ],
    }),
    p(
      'Dalam paparan ALP, timeline status disederhanakan: (1) Permohonan dihantar, (2) Semakan Jabatan — merangkumi proses kelulusan dalaman sehingga diluluskan, (3) Baucar disedia — bayaran boleh disemak melalui portal dbayar DBKL apabila maklumat tersedia, (4) Laporan aktiviti — muat naik report card/laporan selepas program siap.',
    ),
    ...imageParagraph(
      'status-permohonan-alp.png',
      'Rajah 4 — Pratonton timeline status permohonan (paparan ALP)',
    ),

    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      children: [new TextRun({ text: '6. Rujukan Sistem', bold: true, size: 28, font: 'Calibri' })],
    }),
    bullet('Log masuk: https://apps.dbkl.gov.my/sumbangan_alp/login'),
    bullet('Menu ALP: Permohonan Saya → Permohonan Baharu / buka draf sedia ada.'),
    bullet('Manual video ALP (borang langkah 1): docs/manual-video-alp/03-borang-penyaluran-langkah1-SKRIP.md'),
    p(
      'Nota: Tangkapan skrin dalam dokumen ini dijana daripada pratonton reka bentuk sistem (data contoh). Paparan sebenar mungkin berbeza sedikit mengikut versi UAT/production.',
      { italics: true, size: 20 },
    ),
  ];

  const doc = new Document({
    sections: [{ properties: {}, children }],
  });

  const buffer = await Packer.toBuffer(doc);
  fs.writeFileSync(outDocx, buffer);
  console.log('Written:', outDocx);
}

async function main() {
  if (process.env.SKIP_SHOTS !== '1') {
    await captureScreenshots();
  }
  await buildDocument();
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
