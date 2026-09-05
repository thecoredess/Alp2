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

    /** @return list<self> */
    public static function reportCardTypes(): array
    {
        return [self::REPORT_CARD, self::LAPORAN_AKTIVITI];
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}
