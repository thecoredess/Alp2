<form method="POST" action="{{ route('reviews.borang.update', [$application, $reviewType->value]) }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">a) Nama ALP</p>
        <p class="mt-1.5 font-medium text-gray-900">{{ $application->alp->ref_code }} — {{ $application->alp->name }}</p>
    </div>

    <div class="rounded-xl border border-gray-100 p-4">
        <x-field label="b) Nama Persatuan" name="recipient_name" :required="true">
            <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name', $application->recipient_name) }}" required class="inp">
        </x-field>
    </div>

    <div class="rounded-xl border border-gray-100 p-4">
        <x-field label="c) No. ROS (PPM-0XX-XX-XXXXXXXX)" name="recipient_ros_number" :required="true" hint="Contoh: PPM-060-01-12345678">
            <input id="recipient_ros_number" name="recipient_ros_number" type="text"
                   value="{{ old('recipient_ros_number', $application->recipient_ros_number) }}"
                   placeholder="PPM-060-01-12345678" required class="inp font-mono uppercase">
        </x-field>
    </div>

    <div class="rounded-xl border border-gray-100 p-4">
        <x-field
            label="d) Tarikh Program"
            name="program_date"
            :required="true"
            :hint="($waiveProgramLeadTime ?? false)
                ? 'Admin JP: tarikh program boleh kurang dari 2 bulan (notis pendek).'
                : 'Sekurang-kurangnya 2 bulan dari hari ini ('.\Carbon\Carbon::parse($programDateMin)->format('d/m/Y').')'"
        >
            <input id="program_date" name="program_date" type="date"
                   value="{{ old('program_date', $application->program_date?->format('Y-m-d')) }}"
                   min="{{ $programDateMin }}" required class="inp">
        </x-field>
    </div>

    <div class="rounded-xl border border-gray-100 p-4">
        <x-field label="e) Jenis/Kategori Program" name="program_category" :required="true">
            <select id="program_category" name="program_category" required class="inp">
                <option value="">— Pilih kategori —</option>
                @foreach (\App\Enums\ProgramCategory::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('program_category', $application->program_category?->value) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-field>
    </div>

    <div class="rounded-xl border border-royal-100 bg-gradient-to-br from-royal-50 to-navy-50 p-4">
        <x-application-amount-field :limits="$amountLimits" :value="$application->requested_amount" />
    </div>

    <div class="rounded-xl border border-gray-100 p-4">
        <x-field label="g) Tujuan Sumbangan" name="purpose" :required="true">
            <textarea id="purpose" name="purpose" rows="4" required class="inp">{{ old('purpose', $application->purpose) }}</textarea>
        </x-field>
    </div>

    <div class="rounded-xl border border-gray-100 p-4">
        <x-field label="h) No. Akaun Penerima Sumbangan" name="recipient_bank_account" :required="true">
            <input id="recipient_bank_account" name="recipient_bank_account" type="text"
                   value="{{ old('recipient_bank_account', $application->recipient_bank_account) }}" required class="inp">
        </x-field>
    </div>

    <div class="rounded-xl border border-gray-100 p-4">
        <x-field label="i) Alamat Persatuan" name="recipient_address" :required="true" hint="Alamat mesti dalam Wilayah Persekutuan Kuala Lumpur">
            <textarea id="recipient_address" name="recipient_address" rows="3" required class="inp">{{ old('recipient_address', $application->recipient_address) }}</textarea>
        </x-field>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="button" class="btn-white flex-1" @click="editing = false">Batal</button>
        <button type="submit" class="btn-primary flex-1">Simpan</button>
    </div>
</form>
