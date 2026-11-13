# Manual Pengguna — ALP, Admin JP, Pengarah JP

Tiga dokumen Word dengan teks panduan dan tangkapan skrin (pratonton paparan sistem).

## Fail keluaran

| Dokumen | Fail |
|---------|------|
| ALP | `Manual-Pengguna-ALP.docx` |
| Admin JP | `Manual-Pengguna-Admin-JP.docx` |
| Pengarah JP | `Manual-Pengguna-Pengarah-JP.docx` |

## Jana semula manual

```bash
cd docs/manual-pengguna
npm install
npx playwright install chromium   # sekali sahaja

# Tangkapan skrin daripada sistem berjalan (disyorkan)
set MANUAL_BASE_URL=http://localhost/Alp2/public
npm run build:live
set SKIP_SHOTS=1
npm run build:from-assets

# Atau satu perintah (live gagal dalam build → pratonton HTML)
npm run build
```

- **Live:** XAMPP/Apache aktif, log masuk `alp01@dbkl.test` / `adminjp@dbkl.test` / `pelulus@dbkl.test`, kata laluan `password`. Jalankan `SimulationSeeder` jika giliran Admin/Pengarah kosong.
- **Pratonton HTML:** `set USE_PREVIEW_SHOTS=1` sebelum `npm run build`
- Keluaran PNG ke `assets/`, dimasukkan ke fail `.docx`.
- Skrin ALP Langkah 1–2: `preview-permohonan-baharu.html` & `preview-wizard-lampiran.html` (selaras `/permohonan/baharu`).
- Timeline & rajah aliran: imej dalam `docs/ringkasan-eksekutif-alp/assets/`.
- Langkau tangkapan (guna PNG sedia ada): `set SKIP_SHOTS=1` kemudian `npm run build:from-assets` (Windows).
- Jika fail `.docx` dikunci (Word/Cursor buka fail), jana ke `Manual-Pengguna-*-terkini.docx` — muat turun sistem akan guna salinan terkini jika lebih baharu.

## Struktur

- `preview/` — HTML pratonton UI sistem
- `assets/` — PNG untuk Admin JP & Pengarah (dan menu)
- `build-manuals.mjs` — skrip jana `.docx` (pakej `docx`)

## Nota kandungan

Manual mengikut aliran kelulusan semasa:

**Admin JP** → syor → **Pengarah JP** lulus (satu aras) → **PEPU** makluman e-mel sahaja.
