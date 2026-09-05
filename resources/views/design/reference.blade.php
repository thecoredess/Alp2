@extends('layouts.app')
@section('title', 'Templat Rujukan Reka Bentuk')
@section('heading', 'Templat Rujukan Reka Bentuk')
@section('subheading', 'Rujuk halaman ini untuk warna, tipografi, kad, jadual & komponen UI')

@section('content')
<div class="space-y-10">

    {{-- Nota --}}
    <div class="rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-800">
        Halaman ini ialah <strong>rujukan reka bentuk</strong> Sistem ALP DBKL.
        Gunakan token warna, kelas komponen (<code class="rounded bg-white/80 px-1">.card</code>, <code class="rounded bg-white/80 px-1">.btn-primary</code>, dll.) dan corak di bawah untuk semua skrin baharu.
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         1. WARNA
         ═══════════════════════════════════════════════════════════ --}}
    <section>
        <h2 class="mb-1 text-lg font-semibold text-gray-900">1. Warna</h2>
        <p class="mb-4 text-sm text-gray-500">Ditakrif dalam <code class="text-xs">resources/css/app.css</code> (<code class="text-xs">@theme</code>).</p>

        <h3 class="mb-3 text-sm font-semibold text-gray-700">Navy (Primary / DBKL)</h3>
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5 lg:grid-cols-10">
            @foreach ([
                '50' => '#eef1f8', '100' => '#d5dcef', '200' => '#aab9df', '300' => '#7f95cf', '400' => '#556fbd',
                '500' => '#33509f', '600' => '#263d7a', '700' => '#1e2a5a', '800' => '#172144', '900' => '#10162e',
            ] as $shade => $hex)
                <div>
                    <div class="h-14 rounded-lg border border-gray-200 bg-navy-{{ $shade }} {{ (int)$shade >= 500 ? 'ring-1 ring-black/5' : '' }}"></div>
                    <p class="mt-1.5 text-xs font-medium text-gray-800">navy-{{ $shade }}</p>
                    <p class="font-mono text-[10px] text-gray-500">{{ $hex }}</p>
                    @if ($shade === '700')
                        <p class="text-[10px] font-semibold text-navy-700">utama</p>
                    @endif
                </div>
            @endforeach
        </div>

        <h3 class="mb-3 text-sm font-semibold text-gray-700">Royal (Secondary / Aksi)</h3>
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5 lg:grid-cols-10">
            @foreach ([
                '50' => '#eaf0ff', '100' => '#d5e0ff', '200' => '#adc2ff', '300' => '#85a3ff', '400' => '#5c84ff',
                '500' => '#2e5bff', '600' => '#1f47db', '700' => '#1737ad', '800' => '#122a85', '900' => '#0d1f63',
            ] as $shade => $hex)
                <div>
                    <div class="h-14 rounded-lg border border-gray-200 bg-royal-{{ $shade }}"></div>
                    <p class="mt-1.5 text-xs font-medium text-gray-800">royal-{{ $shade }}</p>
                    <p class="font-mono text-[10px] text-gray-500">{{ $hex }}</p>
                    @if ($shade === '500')
                        <p class="text-[10px] font-semibold text-royal-600">utama</p>
                    @endif
                </div>
            @endforeach
        </div>

        <h3 class="mb-3 text-sm font-semibold text-gray-700">Status & Latar</h3>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ([
                ['label' => 'success', 'hex' => '#16a34a', 'bg' => 'bg-success', 'usage' => 'Diluluskan / OK'],
                ['label' => 'warning', 'hex' => '#f59e0b', 'bg' => 'bg-warning', 'usage' => 'Amaran / Menunggu'],
                ['label' => 'danger',  'hex' => '#dc2626', 'bg' => 'bg-danger',  'usage' => 'Tolak / Ralat'],
                ['label' => 'info',    'hex' => '#2563eb', 'bg' => 'bg-info',    'usage' => 'Maklumat'],
                ['label' => 'canvas',  'hex' => '#f4f6fb', 'bg' => 'bg-canvas',  'usage' => 'Latar halaman'],
            ] as $c)
                <div class="card p-3">
                    <div class="h-10 rounded-md {{ $c['bg'] }} border border-gray-200"></div>
                    <p class="mt-2 text-xs font-semibold text-gray-900">{{ $c['label'] }}</p>
                    <p class="font-mono text-[10px] text-gray-500">{{ $c['hex'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-500">{{ $c['usage'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         2. TIPOGRAFI
         ═══════════════════════════════════════════════════════════ --}}
    <section>
        <h2 class="mb-1 text-lg font-semibold text-gray-900">2. Tipografi</h2>
        <p class="mb-4 text-sm text-gray-500">Fon: <strong>Inter</strong> (<code class="text-xs">font-sans</code>). Teks badan: <code class="text-xs">text-gray-800</code>.</p>

        <div class="card divide-y divide-gray-100 overflow-hidden">
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="text-3xl font-semibold text-navy-700">Paparan besar / Stat</p>
                <code class="shrink-0 text-xs text-gray-400">text-3xl font-semibold text-navy-700</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="text-lg font-semibold text-gray-900">Tajuk seksyen</p>
                <code class="shrink-0 text-xs text-gray-400">text-lg font-semibold text-gray-900</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="text-base font-semibold text-gray-900">Tajuk halaman (header)</p>
                <code class="shrink-0 text-xs text-gray-400">text-base font-semibold text-gray-900</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="text-sm font-semibold text-gray-900">Tajuk kecil / label kumpulan</p>
                <code class="shrink-0 text-xs text-gray-400">text-sm font-semibold text-gray-900</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="text-sm text-gray-800">Teks badan biasa — kandungan borang & jadual.</p>
                <code class="shrink-0 text-xs text-gray-400">text-sm text-gray-800</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="text-sm text-gray-500">Teks sekunder / petunjuk</p>
                <code class="shrink-0 text-xs text-gray-400">text-sm text-gray-500</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="text-xs text-gray-500">Meta / caption / subheading</p>
                <code class="shrink-0 text-xs text-gray-400">text-xs text-gray-500</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <p class="font-mono text-xs text-gray-700">ALP-2026-00042 — nombor rujukan</p>
                <code class="shrink-0 text-xs text-gray-400">font-mono text-xs text-gray-700</code>
            </div>
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <a href="#" class="text-sm font-medium text-royal-600 hover:text-royal-700" onclick="return false">Pautan tindakan →</a>
                <code class="shrink-0 text-xs text-gray-400">text-royal-600 hover:text-royal-700 font-medium</code>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. KAD
         ═══════════════════════════════════════════════════════════ --}}
    <section>
        <h2 class="mb-1 text-lg font-semibold text-gray-900">3. Kad</h2>
        <p class="mb-4 text-sm text-gray-500">Kelas: <code class="text-xs">.card</code> → <code class="text-xs">rounded-xl border border-gray-200 bg-white shadow-sm</code></p>

        <h3 class="mb-3 text-sm font-semibold text-gray-700">Kad statistik</h3>
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card p-5">
                <p class="text-sm text-gray-500">Jumlah Permohonan</p>
                <p class="mt-2 text-3xl font-semibold text-navy-700">128</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-gray-500">Dalam Proses</p>
                <p class="mt-2 text-3xl font-semibold text-blue-600">34</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-gray-500">Diluluskan</p>
                <p class="mt-2 text-3xl font-semibold text-green-600">81</p>
            </div>
            <a href="#" class="card p-5 hover:bg-gray-50" onclick="return false">
                <p class="text-sm text-gray-500">Perlu Tindakan</p>
                <p class="mt-2 text-3xl font-semibold text-royal-600">7</p>
            </a>
        </div>

        <h3 class="mb-3 text-sm font-semibold text-gray-700">Kad kandungan</h3>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Tajuk kad</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Gunakan kad ini untuk borang, ringkasan, atau blok maklumat.
                    Padding biasa: <code class="text-xs">p-5</code> atau <code class="text-xs">p-6</code>.
                </p>
                <div class="mt-4 flex gap-2">
                    <button type="button" class="btn-primary">Simpan</button>
                    <button type="button" class="btn-white">Batal</button>
                </div>
            </div>
            <div class="card overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">Kad dengan header</h3>
                </div>
                <div class="space-y-2 p-5 text-sm text-gray-600">
                    <div class="flex justify-between"><span class="text-gray-500">Peruntukan</span><span class="font-medium text-navy-700">RM 250,000.00</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Digunakan</span><span class="font-medium text-gray-900">RM 120,500.00</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Baki</span><span class="font-semibold text-green-600">RM 129,500.00</span></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         4. JADUAL
         ═══════════════════════════════════════════════════════════ --}}
    <section>
        <h2 class="mb-1 text-lg font-semibold text-gray-900">4. Jadual</h2>
        <p class="mb-4 text-sm text-gray-500">Bungkus jadual dalam <code class="text-xs">.card overflow-x-auto</code>. Corak standard senarai ALP.</p>

        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">No. Rujukan</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">ALP-2026-00001</td>
                        <td class="px-4 py-3 text-gray-900">Program Komuniti Cheras</td>
                        <td class="px-4 py-3"><x-status-badge label="CSR" classes="bg-purple-100 text-purple-700" /></td>
                        <td class="px-4 py-3 text-right text-gray-900">RM 45,000.00</td>
                        <td class="px-4 py-3"><x-status-badge label="Diluluskan" classes="bg-green-100 text-green-700" /></td>
                        <td class="px-4 py-3 text-right"><a href="#" class="font-medium text-royal-600 hover:text-royal-700" onclick="return false">Lihat</a></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">ALP-2026-00002</td>
                        <td class="px-4 py-3 text-gray-900">Naik Taraf Surau</td>
                        <td class="px-4 py-3"><x-status-badge label="Infrastruktur" classes="bg-blue-100 text-blue-700" /></td>
                        <td class="px-4 py-3 text-right text-gray-900">RM 120,000.00</td>
                        <td class="px-4 py-3"><x-status-badge label="Dalam Semakan" classes="bg-amber-100 text-amber-800" /></td>
                        <td class="px-4 py-3 text-right"><a href="#" class="font-medium text-royal-600 hover:text-royal-700" onclick="return false">Lihat</a></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">ALP-2026-00003</td>
                        <td class="px-4 py-3 text-gray-900">Bantuan Pendidikan</td>
                        <td class="px-4 py-3"><x-status-badge label="Pendidikan" classes="bg-teal-100 text-teal-700" /></td>
                        <td class="px-4 py-3 text-right text-gray-900">RM 15,500.00</td>
                        <td class="px-4 py-3"><x-status-badge label="Perlu Pembetulan" classes="bg-orange-100 text-orange-700" /></td>
                        <td class="px-4 py-3 text-right"><a href="#" class="font-medium text-royal-600 hover:text-royal-700" onclick="return false">Lihat</a></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">ALP-2026-00004</td>
                        <td class="px-4 py-3 text-gray-900">Program Sukan Belia</td>
                        <td class="px-4 py-3"><x-status-badge label="Sukan" classes="bg-indigo-100 text-indigo-700" /></td>
                        <td class="px-4 py-3 text-right text-gray-900">RM 8,200.00</td>
                        <td class="px-4 py-3"><x-status-badge label="Ditolak" classes="bg-red-100 text-red-700" /></td>
                        <td class="px-4 py-3 text-right"><a href="#" class="font-medium text-royal-600 hover:text-royal-700" onclick="return false">Lihat</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-gray-400">Keadaan kosong: <code>px-4 py-10 text-center text-gray-400</code> — “Tiada rekod.”</p>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         5. BUTANG
         ═══════════════════════════════════════════════════════════ --}}
    <section>
        <h2 class="mb-1 text-lg font-semibold text-gray-900">5. Butang</h2>
        <p class="mb-4 text-sm text-gray-500">Kelas komponen dalam <code class="text-xs">app.css</code>.</p>

        <div class="card p-6">
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="btn-primary">.btn-primary</button>
                <button type="button" class="btn-navy">.btn-navy</button>
                <button type="button" class="btn-white">.btn-white</button>
                <button type="button" class="btn-danger">.btn-danger</button>
                <button type="button" class="btn-primary !px-3 !py-1 text-xs">Kecil</button>
            </div>
            <dl class="mt-5 grid gap-2 text-xs text-gray-500 sm:grid-cols-2">
                <div><dt class="font-semibold text-gray-700">btn-primary</dt><dd>Aksi utama (royal)</dd></div>
                <div><dt class="font-semibold text-gray-700">btn-navy</dt><dd>Aksi rasmi / serius (navy)</dd></div>
                <div><dt class="font-semibold text-gray-700">btn-white</dt><dd>Sekunder / batal / tapis</dd></div>
                <div><dt class="font-semibold text-gray-700">btn-danger</dt><dd>Nyahaktif / padam / tolak</dd></div>
            </dl>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         6. BADGE & BORANG
         ═══════════════════════════════════════════════════════════ --}}
    <section>
        <h2 class="mb-1 text-lg font-semibold text-gray-900">6. Badge &amp; Borang</h2>

        <div class="mb-6 card p-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">Status badge (<code class="text-xs font-normal">&lt;x-status-badge&gt;</code>)</h3>
            <div class="flex flex-wrap gap-2">
                <x-status-badge label="Draf" classes="bg-gray-100 text-gray-700" />
                <x-status-badge label="Dalam Proses" classes="bg-blue-100 text-blue-700" />
                <x-status-badge label="Menunggu" classes="bg-amber-100 text-amber-800" />
                <x-status-badge label="Perlu Pembetulan" classes="bg-orange-100 text-orange-700" />
                <x-status-badge label="Diluluskan" classes="bg-green-100 text-green-700" />
                <x-status-badge label="Ditolak" classes="bg-red-100 text-red-700" />
                <x-status-badge label="Aktif" classes="bg-emerald-100 text-emerald-700" />
            </div>
        </div>

        <div class="card p-6">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Input (<code class="text-xs font-normal">.inp</code>)</h3>
            <div class="grid max-w-xl gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" class="inp" placeholder="Contoh teks…">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select class="inp">
                        <option>Semua</option>
                        <option>Aktif</option>
                        <option>Tidak Aktif</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         7. AMARAN
         ═══════════════════════════════════════════════════════════ --}}
    <section>
        <h2 class="mb-1 text-lg font-semibold text-gray-900">7. Amaran / Notis</h2>
        <div class="space-y-3">
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Amaran — contoh: tiada tahun kewangan aktif.
            </div>
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                Berjaya — rekod telah disimpan.
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                Ralat — tindakan tidak dibenarkan.
            </div>
            <div class="rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-800">
                Maklumat — petunjuk atau rujukan.
            </div>
        </div>
    </section>

    {{-- Cheat sheet --}}
    <section class="card p-6">
        <h2 class="mb-3 text-lg font-semibold text-gray-900">Ringkasan kelas pantas</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                        <th class="py-2 pr-4">Elemen</th>
                        <th class="py-2">Kelas / corak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <tr><td class="py-2 pr-4 font-medium">Latar app</td><td class="py-2 font-mono text-xs">bg-canvas / body default</td></tr>
                    <tr><td class="py-2 pr-4 font-medium">Sidebar</td><td class="py-2 font-mono text-xs">bg-navy-700 text-navy-50</td></tr>
                    <tr><td class="py-2 pr-4 font-medium">Kad</td><td class="py-2 font-mono text-xs">card p-5</td></tr>
                    <tr><td class="py-2 pr-4 font-medium">Stat nombor</td><td class="py-2 font-mono text-xs">text-3xl font-semibold text-navy-700</td></tr>
                    <tr><td class="py-2 pr-4 font-medium">Jadual wrapper</td><td class="py-2 font-mono text-xs">card overflow-x-auto</td></tr>
                    <tr><td class="py-2 pr-4 font-medium">Header jadual</td><td class="py-2 font-mono text-xs">bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500</td></tr>
                    <tr><td class="py-2 pr-4 font-medium">Pautan</td><td class="py-2 font-mono text-xs">text-royal-600 hover:text-royal-700 font-medium</td></tr>
                    <tr><td class="py-2 pr-4 font-medium">Butang utama</td><td class="py-2 font-mono text-xs">btn-primary</td></tr>
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
