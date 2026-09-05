@if (session('status'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

@if (session('warning'))
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        {{ session('warning') }}
    </div>
@endif

{{-- Papar kata laluan sementara sekali sahaja selepas cipta/reset pengguna --}}
@if (session('temp_password'))
    <div class="mb-4 rounded-lg border border-navy-200 bg-navy-50 px-4 py-3 text-sm text-navy-800">
        <p class="font-semibold">Kata laluan sementara dijana</p>
        <p class="mt-1">Akaun: <span class="font-mono">{{ session('temp_password_email') }}</span></p>
        <p>Kata laluan: <span class="font-mono font-semibold select-all">{{ session('temp_password') }}</span></p>
        <p class="mt-1 text-xs text-navy-600">Sila salin dan serahkan kepada pengguna. Ia tidak akan dipaparkan semula. Pengguna wajib menukarnya semasa log masuk pertama.</p>
    </div>
@endif
