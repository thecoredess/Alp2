# UAT Issue Template — Sistem ALP DBKL

Salin blok di bawah untuk setiap isu yang ditemui semasa UAT. Satu isu = satu rekod.

---

```text
UAT ID          : UAT-____              (cth: UAT-001)
Date            : YYYY-MM-DD
Tester          : (nama penuh)
Role            : (Super Admin / System Admin / ALP / Urus Setia ALP / Urus Setia DBKL /
                   Finance Officer / Technical Officer / Approver / Management)
Module          : (Auth / Bajet / Permohonan / Semakan / Kelulusan / Projek / Perbelanjaan /
                   Refund / Penutupan / Laporan / Eksport / Audit / Dashboard / Lain-lain)
Severity        : (Critical / High / Medium / Low)
                   - Critical : kehilangan/kesalahan data kewangan, ledger tidak seimbang,
                                pintasan maker-checker, kebocoran data rentas-ALP
                   - High     : fungsi teras gagal, tiada penyelesaian sementara
                   - Medium   : fungsi gagal tetapi ada penyelesaian sementara
                   - Low      : kosmetik / teks / susun atur

Steps           :
  1.
  2.
  3.

Expected Result :

Actual Result   :

Screenshot      : (pautan / nama fail lampiran)

Status          : (Open / In Progress / Fixed / Verified / Closed / Won't Fix / Duplicate)

Resolution      : (penerangan pembetulan / rujukan; diisi selepas ditangani)
```

---

## Panduan keterukan (Severity) untuk isu kewangan

Sebarang isu berkaitan **integriti kewangan** hendaklah **Critical**, contohnya:

- `Financial Integrity Exceptions > 0` (jalankan `php artisan integrity:check`)
- Rekonsiliasi projek tidak seimbang (`Diluluskan ≠ Baki Komitmen + Net Spent + Dilepaskan`)
- Transaksi ledger berganda / hilang (EXPENDITURE, REFUND, COMMITMENT)
- Maker berjaya meluluskan/mengesahkan permintaan sendiri
- ALP melihat/mengubah data ALP lain

Lampirkan output `php artisan integrity:check` untuk isu kewangan.
