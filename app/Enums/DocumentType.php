<?php

namespace App\Enums;

/** Jenis dokumen sokongan permohonan (boleh dikonfigur kewajipan mengikut jenis). */
enum DocumentType: string
{
    case KERTAS_KERJA = 'kertas_kerja';
    case PECAHAN_BAJET = 'pecahan_bajet';
    case SEBUT_HARGA = 'sebut_harga';
    case SURAT_SOKONGAN = 'surat_sokongan';
    case PELAN_LOKASI = 'pelan_lokasi';
    case GAMBAR = 'gambar';
    case DOKUMEN_TEKNIKAL = 'dokumen_teknikal';
    case LAIN_LAIN = 'lain_lain';
    // URS v1.2 TBL-9
    case PENDAFTARAN_PERTUBUHAN = 'pendaftaran_pertubuhan';
    case BORANG_EFT = 'borang_eft';
    case PENYATA_BANK = 'penyata_bank';
    case SIJIL_ROS = 'sijil_ros';
    // URS v1.2 M07
    case LAPORAN_AKTIVITI = 'laporan_aktiviti';
    case REPORT_CARD = 'report_card';

    public function label(): string
    {
        return match ($this) {
            self::KERTAS_KERJA => 'Kertas Kerja',
            self::PECAHAN_BAJET => 'Pecahan Bajet',
            self::SEBUT_HARGA => 'Sebut Harga',
            self::SURAT_SOKONGAN => 'Surat Sokongan',
            self::PELAN_LOKASI => 'Pelan / Lokasi',
            self::GAMBAR => 'Gambar',
            self::DOKUMEN_TEKNIKAL => 'Dokumen Teknikal',
            self::LAIN_LAIN => 'Lain-lain',
            self::PENDAFTARAN_PERTUBUHAN => 'Salinan Pendaftaran Pertubuhan/Organisasi',
            self::BORANG_EFT => 'Borang Maklumat EFT',
            self::PENYATA_BANK => 'Salinan Penyata Bank Muka Hadapan',
            self::SIJIL_ROS => 'Dokumen/Sijil ROS (sah daftar)',
            self::LAPORAN_AKTIVITI => 'Laporan Aktiviti',
            self::REPORT_CARD => 'Report Card / Kad Prestasi',
        };
    }

    /** Nama ringkas untuk paparan ALP (warga emas). */
    public function simpleLabel(): string
    {
        return match ($this) {
            self::PENDAFTARAN_PERTUBUHAN => 'Pendaftaran Pertubuhan',
            self::BORANG_EFT => 'Borang Maklumat Bank',
            self::PENYATA_BANK => 'Penyata Bank',
            self::KERTAS_KERJA => 'Kertas Kerja',
            self::SIJIL_ROS => 'Sijil ROS',
            default => $this->label(),
        };
    }

    /** Arahan ringkas muat naik. */
    public function simpleHint(): string
    {
        return match ($this) {
            self::PENDAFTARAN_PERTUBUHAN => 'Salinan pendaftaran pertubuhan atau organisasi penerima.',
            self::BORANG_EFT => 'Borang maklumat akaun bank (EFT) yang lengkap.',
            self::PENYATA_BANK => 'Salinan atau gambar muka depan penyata bank.',
            self::KERTAS_KERJA => 'Ringkasan program / aktiviti yang dicadangkan.',
            self::SIJIL_ROS => 'Sijil pendaftaran pertubuhan (ROS) yang masih sah.',
            default => 'Muat naik fail PDF atau gambar.',
        };
    }

    /** @return list<self> */
    public static function reportCardTypes(): array
    {
        return [self::REPORT_CARD, self::LAPORAN_AKTIVITI];
    }

    /** Lampiran wajib Senarai Semak Penyaluran Sumbangan ALP (item 2–6). */
    public static function contributionAttachments(): array
    {
        return [
            self::PENDAFTARAN_PERTUBUHAN,
            self::BORANG_EFT,
            self::PENYATA_BANK,
            self::KERTAS_KERJA,
            self::SIJIL_ROS,
        ];
    }

    public static function contributionOptions(): array
    {
        return collect(self::contributionAttachments())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }

    public static function options(): array
    {
        return self::contributionOptions();
    }
}
