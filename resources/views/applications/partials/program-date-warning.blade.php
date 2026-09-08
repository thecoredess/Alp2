@if (($programDateSubmitErrors ?? []) !== [])
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <strong>Tarikh program perlu dikemaskini.</strong>
        {{ $programDateSubmitErrors[0] }}
        <a href="{{ route('applications.wizard.maklumat', $application) }}" class="ml-1 font-medium underline">Kemaskini borang</a>.
    </div>
@endif
