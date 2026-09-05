<?php

namespace Database\Seeders;

use App\Enums\AlpStatus;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\Alp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Import senarai rasmi 15 ALP Bandaraya Kuala Lumpur (kemaskini Jun 2026).
 * Sumber: SENARAI MAKLUMAT AHLI LEMBAGA PENASIHAT … 15 ALP (2026).xlsx
 *
 * Akaun log masuk (dev/UAT): alp01@dbkl.test … alp15@dbkl.test / password
 */
class OfficialAlp2026Seeder extends Seeder
{
    private const DEV_PASSWORD = 'password';

    public function run(): void
    {
        $rows = $this->alpRows();

        foreach ($rows as $row) {
            $alp = Alp::updateOrCreate(
                ['ref_code' => $row['ref_code']],
                [
                    'name' => $row['name'],
                    'portfolio_zone' => $row['profession'],
                    'appointment_start' => $row['start'],
                    'appointment_end' => $row['end'],
                    'status' => AlpStatus::ACTIVE,
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'remarks' => $row['remarks'],
                ]
            );

            $loginEmail = 'alp'.substr($row['ref_code'], 4).'@dbkl.test';

            $user = User::updateOrCreate(
                ['email' => $loginEmail],
                [
                    'name' => $row['name'],
                    'password' => Hash::make(self::DEV_PASSWORD),
                    'status' => UserStatus::ACTIVE,
                    'must_change_password' => false,
                    'alp_id' => $alp->id,
                    'unit' => null,
                ]
            );

            // Pastikan alp_id sentiasa dikaitkan (jika akaun sudah wujud dari DevSeeder).
            if ($user->alp_id !== $alp->id) {
                $user->forceFill(['alp_id' => $alp->id, 'name' => $row['name']])->save();
            }

            $user->syncRoles([RoleName::ALP->value]);

            $this->command?->info("{$row['ref_code']} · {$row['name']} · {$loginEmail}");
        }

        $this->command?->info('OfficialAlp2026Seeder selesai: 15 ALP + akaun log masuk alp01–alp15@dbkl.test / password');
    }

    /**
     * @return list<array{
     *   ref_code: string,
     *   name: string,
     *   profession: string,
     *   start: string|null,
     *   end: string|null,
     *   phone: string|null,
     *   email: string|null,
     *   remarks: string
     * }>
     */
    private function alpRows(): array
    {
        return [
            [
                'ref_code' => 'ALP-01',
                'name' => 'YBhg. Datuk Muhammad Azmi bin Mohd Zain',
                'profession' => 'Ketua Pengarah (Jabatan Wilayah Persekutuan)',
                'start' => '2026-02-09',
                'end' => '2028-02-08',
                'phone' => '013-3977697',
                'email' => 'azmizain@jwp.gov.my',
                'remarks' => "IC: 710803035319\nPA: Puan Syika · norsyika@jwp.gov.my\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-02',
                'name' => 'Y.A.D Raja Dato\' Muzaffar bin Raja Redzwa',
                'profession' => 'Orang Besar Daerah Hulu Selangor',
                'start' => '2023-11-09', // Excel 45239
                'end' => null,
                'phone' => '013-3516079',
                'email' => 'muzaffri888@gmail.com',
                'remarks' => "IC: 580429106355\nTamat lantikan: sehingga dan selagi perwakilan itu diperkenan oleh Raja dalam Mesyuarat\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-03',
                'name' => 'Y.A.D Dato\' Setia Haji Haris bin Kasim',
                'profession' => '—',
                'start' => '2023-11-09',
                'end' => null,
                'phone' => '012-2937007',
                'email' => 'silverpatris@gmail.com',
                'remarks' => "IC: 630129085209\nTamat lantikan: sehingga dan selagi perwakilan itu diperkenan oleh Raja dalam Mesyuarat\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-04',
                'name' => 'YBhg. Dato\' Sri Ab Rahim bin Ab Rahman',
                'profession' => 'Timbalan Ketua Setiausaha Perbendaharaan (Pengurusan) Kementerian Kewangan',
                'start' => '2026-05-15',
                'end' => '2028-05-14',
                'phone' => '019-6237576',
                'email' => 'ab.rahim@treasury.gov.my',
                'remarks' => "IC: 721210035253\nEmel PA: norasakina.teramuji@treasury.gov.my\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-05',
                'name' => 'YBrs. Encik Che Kodir bin Baharum',
                'profession' => 'Pengarah Bahagian Perkhidmatan Sosial Kementerian Ekonomi',
                'start' => '2024-09-01',
                'end' => '2026-08-31',
                'phone' => '019-2762237',
                'email' => 'Kodir.baharum@ekonomi.gov.my',
                'remarks' => "IC: 710925025821\nEmel PA: norahayu.zakaria@ekonomi.gov.my\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-06',
                'name' => 'YBhg. Datuk Tengku Azman bin Tengku Zainol Abidin',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '019-3670036',
                'email' => 'tengkuazman@yahoo.com',
                'remarks' => "IC: 691223715077\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-07',
                'name' => 'YBhg. Datuk Azizulrahman bin Mohd Hussain Malim',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '019-2290470',
                'email' => 'cukupcekap@gmail.com',
                'remarks' => "IC: 700723075469\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-08',
                'name' => 'YBhg. Datuk Tong Nguen Khoong',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '012-2096686',
                'email' => 'nkt@bukitkiara.com',
                'remarks' => "IC: 680213105685\nEmel lain: janiechio@bukitkiara.com\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-09',
                'name' => 'YBrs. Encik Ahmad Asri bin Talib',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '016-2507539',
                'email' => 'asritalib@amanah.org.my',
                'remarks' => "IC: 690803086161\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-10',
                'name' => 'YBrs. Dr. Pua Eng Teck',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '016-3220909',
                'email' => 'Ronaldpua19@gmail.com',
                'remarks' => "IC: 820419105471\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-11',
                'name' => 'YBrs. Encik Mohd Ashraf bin Mazlan',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '017-2337467',
                'email' => 'Ashraf.mazlan@gmail.com',
                'remarks' => "IC: 810725105059\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-12',
                'name' => 'YBrs. Puan Idawate binti Pariman',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '010-5502416',
                'email' => 'aidareez@yahoo.com',
                'remarks' => "IC: 750909135824\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-13',
                'name' => 'YBrs. Puan Choo Chen Leece',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '016-2582280',
                'email' => 'Janicechoo2@gmail.com',
                'remarks' => "IC: 871003145584\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-14',
                'name' => 'YBrs. Encik Lee Bing Hong',
                'profession' => 'Ahli Profesional',
                'start' => '2025-09-10',
                'end' => '2027-09-09',
                'phone' => '012-3375968',
                'email' => 'terencelbh@gmail.com',
                'remarks' => "IC: 850301146371\nSumber: Senarai ALP DBKL Jun 2026",
            ],
            [
                'ref_code' => 'ALP-15',
                'name' => 'YBrs. Encik Thiyagaraj Sankaranarayanan',
                'profession' => 'Ahli Profesional',
                'start' => '2026-05-01',
                'end' => '2028-04-30',
                'phone' => '016-4756587',
                'email' => 'thiyagaraj.harapan@gmail.com',
                'remarks' => "IC: 810808075465\nSumber: Senarai ALP DBKL Jun 2026",
            ],
        ];
    }
}
