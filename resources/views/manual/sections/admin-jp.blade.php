<div class="space-y-5">
    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Log masuk &amp; menu</h3>
        <p class="text-sm text-gray-600">
            Akaun Admin JP: menu <strong>Semakan Admin JP</strong>, Semua Permohonan, Semak Laporan Aktiviti, Laporan, Senarai ALP.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'menu-admin-jp.png'])
    </div>

    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Giliran semakan</h3>
        <p class="text-sm text-gray-600">
            Senarai permohonan berstatus <strong>Dihantar</strong>. Klik <strong>Semak</strong> untuk buka butiran.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'giliran-admin-jp.png'])
    </div>

    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Skrin semakan &amp; keputusan</h3>
        <p class="text-sm text-gray-700">
            Semak borang, lampiran, ulasan JKEW, dan lengkapkan Senarai Semak UR-M04-001. Keputusan:
            <strong>Disyorkan</strong> / <strong>Tidak Disyorkan</strong> / <strong>Kembalikan</strong>
            → giliran Pengarah JP (bukan Pegawai JP untuk permohonan baharu).
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'semakan-admin-jp.png'])
    </div>
</div>
