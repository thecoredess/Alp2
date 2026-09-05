@extends('layouts.app')
@section('title', $project->project_number)
@section('heading', $project->project_name)
@section('subheading', $project->project_number.' · '.$project->project_type->label())

@php $u = auth()->user(); @endphp

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
        </a>
        <div class="flex items-center gap-3">
            <x-status-badge :label="$project->status->label()" :classes="$project->status->badgeClasses()" class="text-sm px-3 py-1" />
            @can('update', $project)
                @if ($project->status === \App\Enums\ProjectStatus::NOT_STARTED)
                    <form method="POST" action="{{ route('projects.start', $project) }}" onsubmit="return confirm('Mulakan projek?')">@csrf<button class="btn-primary">Mula Projek</button></form>
                @endif
            @endcan
            @can('complete', $project)
                @if ($project->status->isActive())
                    <form method="POST" action="{{ route('projects.complete', $project) }}" onsubmit="return confirm('Tandakan projek Selesai?')">@csrf<button class="btn-primary">Tandakan Selesai</button></form>
                @endif
            @endcan
            @can('close', $project)
                @if ($project->status === \App\Enums\ProjectStatus::COMPLETED)
                    <form method="POST" action="{{ route('projects.close', $project) }}" onsubmit="return confirm('Tutup projek? Baki komitmen akan dilepaskan.')">@csrf<button class="btn-navy">Tutup Projek</button></form>
                @endif
            @endcan
        </div>
    </div>

    <x-project-finance :finance="$finance" />

    <div class="mt-6" x-data="{ tab: 'ringkasan' }">
        <div class="mb-4 flex flex-wrap gap-1 border-b border-gray-200">
            @foreach (['ringkasan' => 'Ringkasan', 'milestone' => 'Milestone', 'perbelanjaan' => 'Perbelanjaan', 'penutupan' => 'Dokumen Penutupan', 'laporan' => 'Laporan', 'aktiviti' => 'Aktiviti'] as $key => $label)
                <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'border-navy-700 text-navy-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 px-4 py-2 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Ringkasan --}}
        <div x-show="tab === 'ringkasan'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="card p-6 lg:col-span-2">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">ALP</dt><dd class="text-gray-900">{{ $project->alp->ref_code }} — {{ $project->alp->name }}</dd></div>
                    <div><dt class="text-gray-500">No. Permohonan</dt><dd class="font-mono text-gray-900">{{ $project->application->application_number }}</dd></div>
                    <div><dt class="text-gray-500">Tahun Kewangan</dt><dd class="text-gray-900">{{ $project->financialYear->year }}</dd></div>
                    <div><dt class="text-gray-500">Kemajuan</dt><dd class="text-gray-900">{{ $project->progress_percent }}%</dd></div>
                    <div><dt class="text-gray-500">Tarikh Cadangan</dt><dd class="text-gray-900">{{ $project->start_date?->format('d/m/Y') ?? '—' }} – {{ $project->end_date?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Tarikh Sebenar</dt><dd class="text-gray-900">{{ $project->actual_start_date?->format('d/m/Y') ?? '—' }} – {{ $project->actual_completion_date?->format('d/m/Y') ?? '—' }}</dd></div>
                </dl>
            </div>
            @can('progress', $project)
                <div class="card p-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Kemas Kini Kemajuan</h3>
                    <form method="POST" action="{{ route('projects.progress', $project) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <input type="number" name="progress_percent" min="0" max="100" value="{{ $project->progress_percent }}" required class="inp" placeholder="Peratus (0-100)">
                        <select name="status" class="inp">
                            <option value="">— Kekalkan status —</option>
                            <option value="in_progress">Dalam Pelaksanaan</option>
                            <option value="delayed">Lewat</option>
                        </select>
                        <textarea name="remarks" rows="2" class="inp" placeholder="Catatan"></textarea>
                        <button class="btn-primary w-full">Kemas Kini</button>
                    </form>
                </div>
            @endcan
        </div>

        {{-- Milestone --}}
        <div x-show="tab === 'milestone'" x-cloak class="space-y-4">
            <div class="card overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Milestone</th><th class="px-4 py-3">Tarikh Sasaran</th><th class="px-4 py-3">Status</th>@can('milestones', $project)<th class="px-4 py-3"></th>@endcan
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($project->milestones as $m)
                            <tr>
                                <td class="px-4 py-3 text-gray-900">{{ $m->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $m->target_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3"><x-status-badge :label="$m->status->label()" :classes="$m->status->badgeClasses()" /></td>
                                @can('milestones', $project)
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('projects.milestones.update', [$project, $m]) }}" class="flex items-center gap-2">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="name" value="{{ $m->name }}">
                                            <select name="status" class="inp !py-1 text-xs">
                                                @foreach (\App\Enums\MilestoneStatus::options() as $val => $label)<option value="{{ $val }}" @selected($m->status->value === $val)>{{ $label }}</option>@endforeach
                                            </select>
                                            <button class="text-royal-600 hover:text-royal-700 text-xs font-medium">Simpan</button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tiada milestone.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @can('milestones', $project)
                <div class="card p-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Tambah Milestone</h3>
                    <form method="POST" action="{{ route('projects.milestones.store', $project) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @csrf
                        <input name="name" type="text" placeholder="Nama milestone" required class="inp sm:col-span-2">
                        <input name="target_date" type="date" class="inp">
                        <button class="btn-primary sm:col-span-3">Tambah</button>
                    </form>
                </div>
            @endcan
        </div>

        {{-- Perbelanjaan --}}
        <div x-show="tab === 'perbelanjaan'" x-cloak class="space-y-4">
            @can('expenses.create')
                @if ($project->status->isActive())
                    <div class="flex justify-end"><a href="{{ route('expenses.create', $project) }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Perbelanjaan Baharu</a></div>
                @endif
            @endcan
            <div class="card overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Tarikh</th><th class="px-4 py-3">Rujukan</th><th class="px-4 py-3">Penerima</th>
                        <th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Tindakan</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($project->expenses as $e)
                            <tr>
                                <td class="px-4 py-3 text-gray-600">{{ $e->expense_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $e->reference_number }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $e->payee ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$e->amount" /></td>
                                <td class="px-4 py-3"><x-status-badge :label="$e->status->label()" :classes="$e->status->badgeClasses()" /></td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('expenses.show', $e) }}" class="text-royal-600 hover:text-royal-700 font-medium">Lihat</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tiada perbelanjaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Dokumen Penutupan --}}
        <div x-show="tab === 'penutupan'" x-cloak class="space-y-4">
            @if ($missingClosureDocuments->isNotEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p class="font-semibold">Dokumen penutupan wajib belum lengkap:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($missingClosureDocuments as $t)<li>{{ $t->label() }}</li>@endforeach
                    </ul>
                    <p class="mt-1 text-xs">Projek tidak boleh ditutup sehingga semua dokumen wajib dimuat naik.</p>
                </div>
            @else
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">Semua dokumen penutupan wajib telah lengkap.</div>
            @endif

            @include('projects._documents', [
                'documents' => $closureDocuments,
                'uploadRoute' => route('projects.closure-documents.store', $project),
                'documentTypes' => \App\Enums\ProjectDocumentType::forCategory('closure_evidence'),
                'canManage' => auth()->user()->can('manageClosureDocuments', $project),
                'title' => 'Dokumen Bukti Penutupan',
                'emptyText' => 'Tiada dokumen penutupan dimuat naik.',
            ])
        </div>

        {{-- Laporan --}}
        <div x-show="tab === 'laporan'" x-cloak class="card p-6">
            @if ($project->report && $project->report->submitted_at)
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Laporan Akhir</h3>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-gray-500">Ringkasan</dt><dd class="text-gray-900 whitespace-pre-line">{{ $project->report->summary ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Hasil / Outcome</dt><dd class="text-gray-900 whitespace-pre-line">{{ $project->report->outcome ?? '—' }}</dd></div>
                    @if($project->report->beneficiary_count)<div><dt class="text-gray-500">Penerima Manfaat</dt><dd class="text-gray-900">{{ number_format($project->report->beneficiary_count) }} orang</dd></div>@endif
                    <div class="text-xs text-gray-500">Dihantar: {{ $project->report->submitted_at->format('d/m/Y H:i') }}</div>
                </dl>
            @else
                <p class="text-sm text-gray-400">Laporan akhir belum dihantar.</p>
            @endif
            @can('report', $project)
                @if ($project->status === \App\Enums\ProjectStatus::COMPLETED)
                    <a href="{{ route('projects.report.edit', $project) }}" class="btn-primary mt-4">{{ $project->report ? 'Kemas Kini Laporan' : 'Sediakan Laporan Akhir' }}</a>
                @endif
            @endcan
        </div>

        {{-- Aktiviti --}}
        <div x-show="tab === 'aktiviti'" x-cloak class="card p-6">
            <ol class="space-y-3">
                @foreach ($project->statusHistories as $h)
                    <li class="flex gap-3 text-sm">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-navy-500"></span>
                        <div><p class="text-gray-900">{{ $h->from_status?->label() ?? 'Baharu' }} → <span class="font-medium">{{ $h->to_status->label() }}</span></p>
                        <p class="text-xs text-gray-500">{{ $h->created_at?->format('d/m/Y H:i') }} · {{ $h->changedBy?->name }} @if($h->remarks) · {{ $h->remarks }} @endif</p></div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection
