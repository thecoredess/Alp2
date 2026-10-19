import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import pptxgen from 'pptxgenjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const assetsDir = path.join(__dirname, 'assets');
const outPptx = path.join(__dirname, 'Presentasi-Pengurusan-Paparan-ALP.pptx');

const NAVY = '1E2A5A';
const ROYAL = '2E5BFF';
const GRAY = '4B5563';
const LIGHT = 'EEF1F8';

const today = new Date().toLocaleDateString('ms-MY', {
  day: 'numeric',
  month: 'long',
  year: 'numeric',
});

function asset(name) {
  const p = path.join(assetsDir, name);
  if (!fs.existsSync(p)) {
    throw new Error(`Missing asset: ${p}. Run: npm run build (screenshots) first.`);
  }
  return p;
}

function addTitleSlide(pptx) {
  const slide = pptx.addSlide();
  slide.background = { color: NAVY };
  slide.addText('Sistem Sumbangan ALP DBKL', {
    x: 0.6,
    y: 1.6,
    w: 8.8,
    h: 1,
    fontSize: 36,
    bold: true,
    color: 'FFFFFF',
    fontFace: 'Calibri',
  });
  slide.addText('Paparan ALP — Permohonan Sumbangan\nUntuk Pengurusan Tertinggi', {
    x: 0.6,
    y: 2.75,
    w: 8.8,
    h: 1.2,
    fontSize: 22,
    color: 'D5E0FF',
    fontFace: 'Calibri',
  });
  slide.addText(`DBKL · ${today}`, {
    x: 0.6,
    y: 6.8,
    w: 8.8,
    fontSize: 14,
    color: 'AAB9DF',
    fontFace: 'Calibri',
  });
}

function addSectionSlide(pptx, title, subtitle = '') {
  const slide = pptx.addSlide();
  slide.background = { color: LIGHT };
  slide.addShape(pptx.ShapeType.rect, {
    x: 0,
    y: 0,
    w: 10,
    h: 0.35,
    fill: { color: ROYAL },
    line: { color: ROYAL, width: 0 },
  });
  slide.addText(title, {
    x: 0.6,
    y: 0.55,
    w: 8.8,
    h: 0.8,
    fontSize: 28,
    bold: true,
    color: NAVY,
    fontFace: 'Calibri',
  });
  if (subtitle) {
    slide.addText(subtitle, {
      x: 0.6,
      y: 1.35,
      w: 8.8,
      h: 0.5,
      fontSize: 16,
      color: GRAY,
      fontFace: 'Calibri',
    });
  }
  return slide;
}

function addBulletSlide(pptx, title, bullets, note = '') {
  const slide = addSectionSlide(pptx, title);
  const items = bullets.map((t) => ({ text: t, options: { bullet: true, breakLine: true } }));
  slide.addText(items, {
    x: 0.75,
    y: 2.0,
    w: 8.5,
    h: 4.5,
    fontSize: 18,
    color: '111827',
    fontFace: 'Calibri',
    paraSpaceAfter: 10,
  });
  if (note) {
    slide.addText(note, {
      x: 0.6,
      y: 6.5,
      w: 8.8,
      h: 0.6,
      fontSize: 12,
      italic: true,
      color: GRAY,
      fontFace: 'Calibri',
    });
  }
}

function addImageSlide(pptx, title, imageFile, caption) {
  const slide = addSectionSlide(pptx, title);
  slide.addImage({
    path: asset(imageFile),
    x: 0.55,
    y: 1.85,
    w: 8.9,
    h: 4.35,
    sizing: { type: 'contain', w: 8.9, h: 4.35 },
  });
  if (caption) {
    slide.addText(caption, {
      x: 0.6,
      y: 6.35,
      w: 8.8,
      h: 0.55,
      fontSize: 11,
      italic: true,
      color: GRAY,
      align: 'center',
      fontFace: 'Calibri',
    });
  }
}

async function build() {
  const pptx = new pptxgen();
  pptx.layout = 'LAYOUT_16x9';
  pptx.author = 'Sistem Sumbangan ALP DBKL';
  pptx.subject = 'Paparan ALP — permohonan sumbangan';
  pptx.title = 'Presentasi Pengurusan — Paparan ALP';

  addTitleSlide(pptx);

  addBulletSlide(pptx, 'Agenda', [
    'Maklumat umum sistem sumbangan ALP',
    'Skop: hanya pengalaman ALP dalam portal',
    'Proses mohon sumbangan (3 langkah)',
    'Pantau status & laporan selepas program',
    'Manfaat ringkas kepada DBKL',
  ]);

  addBulletSlide(
    pptx,
    'Maklumat Umum',
    [
      'Platform dalam talian untuk permohonan sumbangan program oleh Ahli Lembaga Penasihat (ALP).',
      'Setiap ALP log masuk dengan akaun sendiri — data permohonan diasingkan mengikut ALP.',
      'Peruntukan tahunan dan baki sumbangan dipaparkan semasa ALP mengisi permohonan.',
      'Semakan dalaman JP, kelulusan PEPU, dan baucar diurus pihak DBKL; ALP melihat status ringkas tanpa perlu memahami langkah dalaman.',
    ],
    'Rujukan operasi: https://apps.dbkl.gov.my/sumbangan_alp/login',
  );

  addBulletSlide(pptx, 'Skop Pembentangan Ini', [
    'Fokus 100% pada menu dan skrin yang ALP nampak selepas log masuk.',
    'Tidak merinci workflow Pegawai JP, TP/Pengarah, atau PEPU.',
    'Objektif: pengurusan faham beban kerja ALP dan ketelusan status dari sudut pemohon.',
  ]);

  addBulletSlide(pptx, 'Akses ALP dalam Sistem', [
    'Log masuk → papan pemuka / ringkasan peruntukan (mengikut peranan).',
    'Menu Permohonan Saya — senarai draf, dalam semakan, diluluskan, ditolak.',
    'Permohonan Baharu — mulakan wizard tiga langkah.',
    'Butiran permohonan — borang, lampiran, tab Status (timeline), muat turun PDF borang (jika diluluskan).',
  ]);

  addImageSlide(
    pptx,
    'Gambaran Proses — Sudut ALP',
    'flow-diagram.png',
    'Rajah: tindakan ALP (kiri & bawah) vs proses DBKL (ALP hanya menunggu & pantau).',
  );

  addBulletSlide(pptx, 'Wizard Permohonan — 3 Langkah', [
    'Langkah 1 — Borang Penyaluran Sumbangan (medan a–i): maklumat program & penerima.',
    'Langkah 2 — Muat naik dokumen sokongan (PDF/imej) mengikut senarai semak.',
    'Langkah 3 — Semak ringkasan & hantar kepada Jabatan Pentadbiran (JP).',
    'Selepas hantar: ALP boleh serah salinan fizikal lampiran kepada JP jika dikehendaki proses pejabat.',
  ]);

  addImageSlide(
    pptx,
    'Langkah 1 — Borang Penyaluran',
    'borang-penyaluran.png',
    'Contoh skrin: wizard langkah 1 aktif; medan a–i termasuk ROS, tarikh program, jumlah, tujuan, bank, alamat WP KL.',
  );

  addImageSlide(
    pptx,
    'Langkah 2 — Muat Naik Dokumen',
    'muat-naik-dokumen.png',
    'Contoh skrin: kemajuan lampiran; borang digital tidak perlu dimuat naik semula.',
  );

  addBulletSlide(pptx, 'Langkah 3 — Hantar kepada JP', [
    'Sistem semak lampiran wajib dan baki peruntukan ALP mencukupi.',
    'ALP sahkan dan hantar — status bertukar kepada permohonan dihantar.',
    'Jika JP minta pembetulan: status Perlu Pembetulan — ALP edit borang/lampiran dan hantar semula.',
    'Notis program: tarikh program biasanya ≥ 2 bulan dari tarikh hantar (polisi boleh dikonfigurasi).',
  ]);

  addImageSlide(
    pptx,
    'Pantau Status — Paparan ALP',
    'status-permohonan-alp.png',
    'Timeline ALP: dihantar → semakan jabatan (termasuk kelulusan) → baucar → laporan aktiviti.',
  );

  addBulletSlide(pptx, 'Selepas Program — Tanggungjawab ALP', [
    'Apabila permohonan diluluskan dan program siap, ALP muat naik laporan aktiviti / report card.',
    'Semakan bayaran: ALP boleh rujuk portal dbayar DBKL apabila baucar disedia (maklumat dipaparkan dalam sistem).',
    'Semua rekod kekal dalam Permohonan Saya untuk rujukan audit ALP sendiri.',
  ]);

  addBulletSlide(pptx, 'Manfaat (Perspektif Pengurusan)', [
    'Standard satu borang digital — kurang kekeliruan borang fizikal.',
    'Jejak status masa nyata untuk ALP — kurang pertanyaan berulang ke JP.',
    'Dokumen tersimpan bersama permohonan — sokong audit dan UAT.',
    'Selaras URS: had permohonan/peruntukan boleh dikuatkuasa oleh polisi sistem.',
  ]);

  const closing = pptx.addSlide();
  closing.background = { color: NAVY };
  closing.addText('Ringkasan', {
    x: 0.6,
    y: 1.2,
    w: 8.8,
    fontSize: 32,
    bold: true,
    color: 'FFFFFF',
    fontFace: 'Calibri',
  });
  closing.addText(
    [
      { text: 'ALP: isi borang → lampiran → hantar → pantau status → laporan program.', options: { bullet: true, breakLine: true } },
      { text: 'Pengurusan: fahami skop paparan pemohon; proses kelulusan kekal di tangan DBKL.', options: { bullet: true, breakLine: true } },
      { text: 'Demo / soalan: rujuk manual ALP atau sesi UAT dengan akaun peranan ALP.', options: { bullet: true, breakLine: true } },
    ],
    {
      x: 0.75,
      y: 2.2,
      w: 8.5,
      h: 3.5,
      fontSize: 20,
      color: 'EAF0FF',
      fontFace: 'Calibri',
    },
  );
  closing.addText('Terima kasih', {
    x: 0.6,
    y: 6.2,
    w: 8.8,
    align: 'center',
    fontSize: 24,
    color: 'FFFFFF',
    fontFace: 'Calibri',
  });

  await pptx.writeFile({ fileName: outPptx });
  console.log('Written:', outPptx);
}

build().catch((err) => {
  console.error(err);
  process.exit(1);
});
