<div class="space-y-5">
    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Log masuk &amp; menu</h3>
        <p class="text-sm text-gray-600">
            Menu <strong>Pengesyoran</strong> / kelulusan. PEPU hanya makluman e-mel — tiada skrin kelulusan.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'menu-pengarah.png'])
    </div>

    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Giliran kelulusan</h3>
        <p class="text-sm text-gray-600">Permohonan menunggu keputusan Pengarah JP selepas semakan Admin JP.</p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'giliran-pengarah.png'])
    </div>

    <div class="card space-y-2 p-5">
        <h3 class="text-sm font-semibold text-gray-900">Skrin kelulusan</h3>
        <p class="text-sm text-gray-600">
            Semak ringkasan kewangan ALP, borang, dan sejarah semakan. Tindakan:
            <strong>Luluskan</strong>, <strong>Tolak</strong>, atau <strong>Kembalikan</strong>.
            Pastikan baki peruntukan mencukupi sebelum lulus.
        </p>
        @include('manual.partials.figure', ['figures' => $manualFiguresByFile, 'file' => 'kelulusan-pengarah.png'])
    </div>
</div>
