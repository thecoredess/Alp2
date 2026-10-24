<?php

namespace App\Support;

/** Kandungan Notis Perlindungan Data Peribadi DBKL (rujuk portal rasmi). */
final class DbklPersonalDataProtectionNotice
{
    public const OFFICIAL_URL = 'https://www.dbkl.gov.my/notis-perlindungan-data-peribadi';

    public const LAST_UPDATED = '12 Mac 2025';

    /**
     * @return list<array{title: string, body: list<string>, items?: list<string>}>
     */
    public static function sections(): array
    {
        return [
            [
                'title' => 'Pengenalan',
                'body' => [
                    'Datuk Bandar Kuala Lumpur komited untuk melindungi Data Peribadi anda menurut Akta Perlindungan Data Peribadi 2010 (Akta 709). Notis ini menjelaskan bagaimana Datuk Bandar Kuala Lumpur memproses data peribadi anda bermula daripada pengumpulan, penggunaan, perkongsian dan pemusnahan serta langkah keselamatan yang diambil untuk memastikan data peribadi anda dilindungi sebaiknya.',
                    'Borang permohonan sumbangan ALP dalam talian ini dikendalikan oleh Dewan Bandaraya Kuala Lumpur. Maklumat yang anda isi (termasuk borang persatuan/NGO dan lampiran) diproses mengikut notis ini.',
                ],
            ],
            [
                'title' => 'Pengumpulan',
                'body' => [
                    'Kami mengumpul data peribadi merujuk kepada apa-apa maklumat yang membezakan atau mengenal pasti anda, termasuk tetapi tidak terhad kepada nama, alamat, jantina, nombor kad pengenalan, nombor pasport, nombor telefon, alamat IP, nombor pendaftaran kenderaan, imej (gambar/rakaman, gambar kenderaan dan lain-lain), maklumat pekerjaan dan kewangan, data peribadi anggota keluarga, waris, keadaan kesihatan fizikal, kepercayaan agama dan penyuaraan pendapat.',
                ],
            ],
            [
                'title' => 'Sumber Pengumpulan',
                'items' => [
                    'Borang pendaftaran pengguna baru di semua aplikasi dalam talian.',
                    'Borang permohonan / pembayaran perkhidmatan DBKL.',
                ],
            ],
            [
                'title' => 'Tujuan Pengumpulan',
                'items' => [
                    'Memproses permohonan perkhidmatan kami.',
                    'Menghantar produk/perkhidmatan kami kepada lokasi pilihan anda.',
                    'Menyelesaikan aduan / masalah yang berkaitan (jika berlaku).',
                    'Menghantar pemakluman perkhidmatan kami.',
                    'Berkongsi maklumat dengan Agensi Kerajaan yang lain.',
                    'Aktiviti audit, penilaian risiko, tinjauan, penyelidikan berhubung polisi yang dikeluarkan oleh DBKL dan pelepasan kewajipan kawal selia/perundangan.',
                ],
            ],
            [
                'title' => 'Bagaimana Diproses',
                'body' => [
                    'Kami memproses data peribadi anda hanya di dalam Malaysia sahaja. Tiada data peribadi yang dipindahkan ke luar Malaysia.',
                ],
            ],
            [
                'title' => 'Penzahiran',
                'body' => [
                    'Kami akan menzahirkan data peribadi anda kepada:',
                ],
                'items' => [
                    'Pihak ketiga yang dilantik oleh Datuk Bandar Kuala Lumpur bagi melaksanakan kerja bagi pihak Datuk Bandar.',
                    'Pihak berkuasa untuk tujuan perundangan.',
                ],
            ],
            [
                'title' => 'Langkah Keselamatan',
                'items' => [
                    'Memastikan data peribadi anda disimpan dengan sebaiknya mengikut kehendak Akta 709.',
                    'Memastikan kakitangan kami tidak menyalahgunakan data peribadi anda.',
                    'Mempunyai kontrak perlindungan data peribadi dengan pihak ketiga yang dilantik oleh Datuk Bandar Kuala Lumpur.',
                ],
                'body' => [
                    'Walau bagaimanapun, anda bertanggungjawab untuk menjaga kata laluan dengan baik dan tidak menzahirkannya kepada pihak lain bagi mengelakkan risiko pencerobohan terhadap data peribadi anda.',
                ],
            ],
            [
                'title' => 'Tempoh Penyimpanan',
                'body' => [
                    'Semua Data Peribadi yang diperolehi daripada anda akan disimpan selagi mana diperlukan oleh Datuk Bandar Kuala Lumpur. Datuk Bandar Kuala Lumpur akan mengambil tindakan munasabah untuk memastikan Data Peribadi anda akan dimusnahkan atau dihapuskan secara kekal, jika ia tidak lagi diperlukan.',
                ],
            ],
            [
                'title' => 'Hak Anda',
                'items' => [
                    'Membetulkan / mengemaskini data peribadi anda.',
                    'Mengakses data peribadi anda yang kami simpan.',
                    'Anda mempunyai pilihan untuk menyediakan Data Peribadi anda kepada Datuk Bandar Kuala Lumpur, namun keengganan anda mungkin menghalang Datuk Bandar Kuala Lumpur daripada menyediakan perkhidmatan yang anda perlukan.',
                ],
            ],
            [
                'title' => 'Hubungi Kami',
                'body' => [
                    'Anda boleh hubungi kami berkaitan data peribadi anda melalui:',
                    'Dewan Bandaraya Kuala Lumpur, Menara DBKL 1, Jalan Raja Laut, 50350 Kuala Lumpur.',
                    'No. Tel: 03-2617 9000 · No. Faks: 03-2698 0460 · E-mel: dbkl@dbkl.gov.my',
                    'Setelah menerima permintaan bertulis anda, Datuk Bandar Kuala Lumpur mungkin memerlukan pengesahan identiti anda sebelum meneruskan untuk mengambil langkah-langkah yang diperlukan untuk memenuhi permintaan anda.',
                ],
            ],
        ];
    }
}
