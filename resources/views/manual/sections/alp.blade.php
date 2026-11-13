<div class="space-y-5">
    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Log masuk &amp; menu</h3>
        <p class="text-sm text-gray-600">
            Gunakan e-mel dan kata laluan daripada pentadbir. Menu utama: Dashboard, Permohonan Saya, Bajet Saya, Manual Sistem.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'menu-alp.png'])
    </div>

    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Permohonan baharu</h3>
        <p class="text-sm text-gray-600">
            <strong>Permohonan Saya</strong> → <strong>Permohonan Baharu</strong> — isi borang Langkah 1 (a–i) termasuk
            <strong>Kertas Kerja</strong>, atau jana pautan 7 hari untuk persatuan.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'permohonan-baharu.png'])
        <p class="text-sm text-gray-600">
            Langkah 2: muat naik 4 lampiran wajib. Langkah 3: semak ringkasan dan hantar kepada Admin JP.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'wizard-lampiran.png'])
        <p class="text-sm text-gray-600">
            Kongsi
            <a href="{{ route('association-guide.download') }}?v=3" class="font-medium text-royal-600 underline">Panduan Dokumen Persatuan (PDF)</a>
            dengan persatuan untuk lampiran pautan awam.
        </p>
    </div>

    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Gambaran aliran</h3>
        <p class="text-sm text-gray-600">Dari hantar → semakan JP → kelulusan → baucar → laporan aktiviti.</p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'flow-diagram.png'])
    </div>

    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Selepas hantar</h3>
        <p class="text-sm text-gray-600">
            Pantau timeline permohonan, baucar, dan muat naik <strong>Laporan Aktiviti</strong> selepas program.
            Status <em>Perlu Pembetulan</em>: edit borang/lampiran dan hantar semula.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'status-permohonan-alp.png'])
    </div>
</div>
