<?php
/**
 * Jana URS v1.1 dari v1.0 + sisipkan kandungan Addendum Hybrid.
 * Jalankan: php scripts/build_urs_v1_1.php
 */

$base = dirname(__DIR__);
$src = $base.'/URS/URS_Sistem_Pengurusan_Permohonan_Peruntukan_Sumbangan_ALP_DBKL_Standard_v1.0.docx';
$dst = $base.'/URS/URS_Sistem_Pengurusan_Permohonan_Peruntukan_Sumbangan_ALP_DBKL_Standard_v1.1.docx';
$addendumMd = $base.'/docs/URS_ADDENDUM_HYBRID_v1.1.md';

if (! file_exists($src)) {
    fwrite(STDERR, "Sumber tidak dijumpai: $src\n");
    exit(1);
}

copy($src, $dst);

function xmlEsc(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function wp(string $text, bool $bold = false): string
{
    $rPr = $bold ? '<w:rPr><w:b/></w:rPr>' : '';
    $parts = preg_split('/(\n)/', $text);
    $xml = '';
    foreach ($parts as $part) {
        if ($part === "\n") {
            $xml .= '<w:r><w:br/></w:r>';
            continue;
        }
        if ($part === '') {
            continue;
        }
        $xml .= '<w:r>'.$rPr.'<w:t xml:space="preserve">'.xmlEsc($part).'</w:t></w:r>';
    }

    return '<w:p><w:pPr><w:spacing w:after="120"/></w:pPr>'.$xml.'</w:p>';
}

function wh(string $text): string
{
    return '<w:p><w:pPr><w:pStyle w:val="Heading1"/><w:spacing w:before="240" w:after="120"/></w:pPr>'
        .'<w:r><w:t>'.xmlEsc($text).'</w:t></w:r></w:p>';
}

function wh2(string $text): string
{
    return '<w:p><w:pPr><w:pStyle w:val="Heading2"/><w:spacing w:before="200" w:after="80"/></w:pPr>'
        .'<w:r><w:t>'.xmlEsc($text).'</w:t></w:r></w:p>';
}

$section6 = wh2('TBL-06 Perkara di Luar Skop (Dikemas kini v1.1)')
    .wp('Sistem ALP DBKL Hybrid tidak mencakupi perkara berikut pada skop semasa. Keputusan ini menggantikan status "Untuk pengesahan pemilik proses" dalam draf v1.0.', true)
    .wp('OS-01: Integrasi langsung dengan sistem kewangan DBKL / e-baucar berpusat / ERP. Status baucar direkod secara manual.')
    .wp('OS-02: Pemindahan dana elektronik automatik (GIRO/FPX) kepada ALP. Hanya status dan rujukan direkod.')
    .wp('OS-03: Pendaftaran kendiri (self-register) ALP awam tanpa kelulusan pentadbir. Akaun dicipta oleh pentadbir.')
    .wp('OS-04: Aplikasi mudah alih asli (iOS/Android). Web responsif sahaja.')
    .wp('OS-05: Portal awam tanpa log masuk untuk semak status permohonan.')
    .wp('OS-06: Pengurusan inventori / aset fizikal program.')
    .wp('OS-07: Modul penggajian / elaun ALP selain sumbangan program.')
    .wp('OS-08: Penukaran mata wang asing. RM sahaja.')
    .wp('OS-09: Tandatangan digital / e-meterai sah di sisi undang-undang. Surat kelulusan boleh dicetak.')
    .wp('OS-10: Arkib eDokumen DBKL / pengurusan fail fizikal. Muat naik dokumen sokongan dalam sistem sahaja.')
    .wp('Sebarang item di atas yang dimasukkan skop memerlukan CRS / URS tambahan.');

$brNote = wp('Nota v1.1 (Model Hybrid): BR-001 hingga BR-004 dikuatkuasakan hanya apabila Polisi URS diaktifkan melalui Tetapan Sistem. Apabila dimatikan, aliran projek/ledger korporat kekal tanpa had RM30,000 / RM3,000 / kuota tempoh.', true);

$appendix = wh('18. Addendum Model Hybrid (v1.1)')
    .wp('Bahagian ini direkodkan selepas pelaksanaan Fasa A–C sistem Laravel ALP DBKL (Ogos 2026) dan hendaklah dibaca bersama URS v1.0.')
    .wh2('18.1 Keputusan Arah Hybrid')
    .wp('Platform korporat (ledger bajet, maker-checker, modul projek, perbelanjaan, refund) KEKAL. Lapisan polisi sumbangan URS (BR-001–004) ditambah sebagai tetapan pilihan (lalai DIMATIKAN).')
    .wh2('18.2 Dwi-aliran Kewangan')
    .wp('Aliran A — Sumbangan ALP: permohonan → semakan → Peraku/Pelulus → COMMITMENT → status baucar (Menunggu → Baucar Disedia → Dibayar).')
    .wp('Aliran B — Belanja projek: perbelanjaan maker-checker → EXPENDITURE ledger. Baucar (Aliran A) TIDAK sama dengan belanja projek (Aliran B).')
    .wh2('18.3 Skop Tambahan Rasmi (Lebihan Hybrid)')
    .wp('Semakan Kewangan dan Teknikal (selain Urus Setia JP).')
    .wp('Ledger peruntukan dengan maker-checker (cadangan & kelulusan peruntukan).')
    .wp('Modul projek, perbelanjaan, refund, penutupan, laporan eksekutif/kewangan.')
    .wp('Matriks kelulusan berbilang aras (Peraku / Pelulus).')
    .wh2('18.4 Kemas Kini Modul (Ringkas)')
    .wp('M2 Dashboard: notifikasi, tertunggak, kuota tempoh (jika polisi ON).')
    .wp('M5 Kelulusan: label Peraku/Pelulus; surat kelulusan cetak.')
    .wp('M6 Pembayaran: status baucar + eksport CSV; tiada integrasi bank.')
    .wp('M10 Tetapan: Polisi URS (had tahunan, had permohonan, kuota tempoh, ambang tertunggak).')
    .wp('Notifikasi: in-app + e-mel (hantar, kembalikan, lulus, tolak, baucar, dibayar).')
    .wh2('18.5 Keputusan Cadangan P01–P17')
    .wp('P01 Hybrid rasmi — kedua-dua model wujud; polisi URS pilihan.')
    .wp('P02 Semakan Kewangan & Teknikal — dalam skop (perluas M4).')
    .wp('P03 Pendaftaran kendiri ALP — luar skop (OS-03); akaun oleh pentadbir.')
    .wp('P04 Had kuasa pelulus — matriks kelulusan boleh dikonfigurasi.')
    .wp('P05–P06 Medan & dokumen — kekal seperti wizard sistem.')
    .wp('P07 Notifikasi e-mel — ya (DB + mail).')
    .wp('P08 Tertunggak — ambang hari boleh dikonfigurasi (lalai 7).')
    .wp('P09 Surat kelulusan — template sistem; letterhead DBKL kemudian.')
    .wp('P10 Integrasi baucar DBKL — luar skop (OS-01); rekod manual.')
    .wp('P11 CSV pembayaran — dalam skop.')
    .wp('P12 Polisi URS lalai — OFF (UAT/projek besar); ON untuk sumbangan ketat.')
    .wp('P13 Tempoh 4-bulan — guna tahun kewangan.year sebagai tahun kalendar.')
    .wp('P14 Baucar vs belanja — berasingan (baucar ≠ EXPENDITURE).')
    .wp('P15 Label Peraku — label/matriks; tiada role baharu.')
    .wp('P16 App mudah alih — luar skop (OS-04).')
    .wp('P17 Perubahan BR — Tetapan Polisi URS + audit; CRS jika formula berubah.')
    .wp('Rujukan teknikal penuh: docs/URS_ADDENDUM_HYBRID_v1.1.md', true);

$zip = new ZipArchive();
if ($zip->open($dst) !== true) {
    fwrite(STDERR, "Gagal buka $dst\n");
    exit(1);
}

$xml = $zip->getFromName('word/document.xml');
if ($xml === false) {
    fwrite(STDERR, "Tiada word/document.xml\n");
    exit(1);
}

// Versi dokumen
$xml = str_replace('Standard_v1.0', 'Standard_v1.1', $xml);
$xml = str_replace('Standard v1.0', 'Standard v1.1', $xml);
$xml = str_replace('Draf, 25 Ogos 2026', 'Dikemas kini 27 Ogos 2026 (Addendum Hybrid v1.1)', $xml);

$old6 = 'Dokumen sumber tidak menetapkan perkara di luar skop secara khusus.';
if (str_contains($xml, $old6)) {
    $xml = str_replace($old6, 'Perkara di luar skop ditetapkan dalam TBL-06 di bawah (Addendum Hybrid v1.1).', $xml);
    echo "OK: Bahagian 6 placeholder dikemas kini.\n";
} else {
    echo "AMARAN: placeholder Bahagian 6 tidak dijumpai — sisipan manual mungkin perlu.\n";
}

$oldStatus6 = '6. Perkara di Luar SkopStatus Bahagian  Untuk pengesahan pemilik proses.';
if (str_contains($xml, $oldStatus6)) {
    $xml = str_replace($oldStatus6, '6. Perkara di Luar Skop', $xml);
}

if (str_contains($xml, 'BR-007Permohonan yang melebihi baki kelayakan tidak akan dipertimbangkan.')) {
    $xml = str_replace(
        'BR-007Permohonan yang melebihi baki kelayakan tidak akan dipertimbangkan.',
        'BR-007Permohonan yang melebihi baki kelayakan tidak akan dipertimbangkan.'.$brNote,
        $xml
    );
    echo "OK: Nota BR Hybrid disisipkan.\n";
}

$insert = $section6.$appendix;
$marker = '</w:body>';
if (! str_contains($xml, $marker)) {
    fwrite(STDERR, "Penanda </w:body> tidak dijumpai.\n");
    exit(1);
}
$xml = str_replace($marker, $insert.$marker, $xml);

$zip->addFromString('word/document.xml', $xml);

// core properties
$core = $zip->getFromName('docProps/core.xml');
if ($core !== false) {
    $core = str_replace('v1.0', 'v1.1', $core);
    $core = preg_replace('/<dc:title>[^<]*<\/dc:title>/', '<dc:title>URS ALP DBKL Standard v1.1 (Hybrid)</dc:title>', $core, 1) ?? $core;
    $zip->addFromString('docProps/core.xml', $core);
}

$zip->close();

echo "Dijana: $dst\n";
echo "Addendum MD: $addendumMd\n";
