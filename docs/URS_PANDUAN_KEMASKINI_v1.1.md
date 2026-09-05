# Panduan Kemas Kini URS v1.1 (Hybrid)

> **DIGANTI:** Langkah Hybrid v1.1 **bukan** SSOT. Gunakan URS v1.2 + [PANDUAN_ANALISIS_URS_v1.2.md](PANDUAN_ANALISIS_URS_v1.2.md).

Dokumen ini melengkapkan [URS_ADDENDUM_HYBRID_v1.1.md](URS_ADDENDUM_HYBRID_v1.1.md) dengan langkah praktikal selepas fail Word dijana.  
**Panduan analisis penuh:** [PANDUAN_ANALISIS_URS_HYBRID.md](PANDUAN_ANALISIS_URS_HYBRID.md)

## Fail

| Fail | Keterangan |
|------|------------|
| `URS/..._Standard_v1.0.docx` | URS asal (draf 25 Ogos 2026) |
| `URS/..._Standard_v1.1.docx` | URS v1.1 — dijana oleh skrip (Bah. 6 + Addendum §18) |
| `docs/URS_ADDENDUM_HYBRID_v1.1.md` | Addendum penuh (rujukan teknikal) |

## Jana semula v1.1

```powershell
cd C:\xampp2\htdocs\alp
C:\xampp2\php\php.exe scripts\build_urs_v1_1.php
```

## Semakan manual dalam Word (disyorkan)

1. **Kemas kini Senarai Kandungan** — klik kanan TOC → Update Field.
2. **Bahagian 5 (Skop)** — tambah bullet: ledger bajet, projek, belanja, refund, matriks kelulusan (jika belum tercermin).
3. **Bahagian 6** — sahkan TBL-06 OS-01…10 muncul selepas placeholder.
4. **Bahagian 9 (BR)** — sahkan nota “Polisi URS pilihan” selepas BR-007.
5. **Bahagian 18** — sahkan Addendum Hybrid di hujung dokumen (sebelum atau selepas §17 mengikut susunan editor).
6. **Bahagian 16 (P01–P17)** — tandakan Setuju/Tolak; selaraskan dengan §18.5.
7. **M4 Semakan** — tambah perenggan: semakan Urus Setia + Kewangan + Teknikal.
8. **M6 Pembayaran** — nyatakan status: Menunggu → Baucar Disedia → Dibayar; rekod manual; eksport CSV.
9. **Tandatangan §17** — kemas kini versi dokumen ke v1.1.

## Pemetaan URS ↔ Sistem (ringkas)

| URS | Sistem |
|-----|--------|
| Polisi BR-001…004 | `/tetapan/polisi-urs` (OFF lalai) |
| Notifikasi | `/notifikasi` + loceng header |
| Surat kelulusan | `/permohonan/{id}/surat-kelulusan` |
| Baucar | `/pembayaran` |
| Tertunggak | Dashboard |
| Peraku | Matriks aras 1 + label UI |

## Selepas pengesahan pemilik proses

- Gantikan `v1.0` sebagai dokumen rasmi dengan `v1.1`.
- Kemas kini `docs/UAT_CHECKLIST.md` jika senario URS ON/OFF perlu UAT berasingan.
- Arkibkan addendum MD sebagai lampiran CRS jika perlu.
