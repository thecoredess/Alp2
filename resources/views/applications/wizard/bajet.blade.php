@extends('layouts.app')
@section('title', 'Pecahan Bajet')
@section('heading', 'Permohonan — '.$application->application_number)
@section('subheading', $application->application_type->label().' · Draf')

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="card overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3 text-right">Kuantiti</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3 text-right">Harga Seunit</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($application->budgetItems as $item)
                            <tr>
                                <td class="px-4 py-3 text-gray-900">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $item->unit ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-gray-600"><x-money :value="$item->unit_cost" /></td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900"><x-money :value="$item->total" /></td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('applications.items.destroy', [$application, $item]) }}" onsubmit="return confirm('Buang item ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-danger hover:text-red-700 text-xs font-medium">Buang</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada item bajet.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr class="text-sm font-semibold">
                            <td class="px-4 py-3" colspan="4">Jumlah Permohonan</td>
                            <td class="px-4 py-3 text-right text-navy-700"><x-money :value="$application->budgetItemsTotal()" /></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Tambah item --}}
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Tambah Item Bajet</h3>
                <form method="POST" action="{{ route('applications.items.store', $application) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                    @csrf
                    <div class="sm:col-span-5">
                        <input name="description" type="text" placeholder="Keterangan item" value="{{ old('description') }}" required class="inp">
                        @error('description')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <input name="quantity" type="number" min="1" step="1" placeholder="Kuantiti" value="{{ old('quantity', 1) }}" required class="inp">
                        @error('quantity')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <input name="unit" type="text" placeholder="Unit" value="{{ old('unit') }}" class="inp">
                    </div>
                    <div class="sm:col-span-2">
                        <input name="unit_cost" type="number" min="0" step="0.01" placeholder="Harga (RM)" value="{{ old('unit_cost') }}" required class="inp">
                        @error('unit_cost')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-1">
                        <button class="btn-primary w-full">+</button>
                    </div>
                </form>
                <p class="mt-2 text-[11px] text-gray-400">Jumlah setiap item dikira oleh sistem (kuantiti × harga seunit) — tidak boleh ditaip manual.</p>
            </div>

            <div class="flex justify-between gap-3">
                <a href="{{ route('applications.wizard.objektif', $application) }}" class="btn-white">← Sebelumnya</a>
                <a href="{{ route('applications.wizard.dokumen', $application) }}" class="btn-primary">Seterusnya →</a>
            </div>
        </div>

        <div>
            <x-budget-position :position="$position" />
        </div>
    </div>
@endsection
