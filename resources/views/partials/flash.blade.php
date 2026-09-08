@php
    $swalQueue = [];

    if (session('status')) {
        $swalQueue[] = [
            'icon' => 'success',
            'title' => 'Berjaya',
            'text' => session('status'),
        ];
    }

    if (session('error')) {
        $swalQueue[] = [
            'icon' => 'error',
            'title' => 'Ralat',
            'text' => session('error'),
        ];
    }

    if (session('warning')) {
        $swalQueue[] = [
            'icon' => 'warning',
            'title' => 'Perhatian',
            'text' => session('warning'),
        ];
    }

    if (session('temp_password')) {
        $swalQueue[] = [
            'icon' => 'info',
            'title' => 'Kata laluan sementara dijana',
            'html' => '<div class="text-left text-sm space-y-2">'
                .'<p><strong>Akaun:</strong> <span class="font-mono">'.e(session('temp_password_email')).'</span></p>'
                .'<p><strong>Kata laluan:</strong> <span class="font-mono font-semibold select-all">'.e(session('temp_password')).'</span></p>'
                .'<p class="text-xs text-gray-500">Sila salin dan serahkan kepada pengguna. Ia tidak akan dipaparkan semula.</p>'
                .'</div>',
        ];
    }

    if (isset($errors) && $errors->any()) {
        $items = collect($errors->all())->map(fn ($message) => '<li>'.e($message).'</li>')->implode('');
        $swalQueue[] = [
            'icon' => 'error',
            'title' => 'Sila semak maklumat',
            'html' => '<ul class="text-left list-disc pl-5 text-sm space-y-1">'.$items.'</ul>',
        ];
    }
@endphp

@if (count($swalQueue) > 0)
    <script type="application/json" id="app-swal-flash">@json($swalQueue)</script>
@endif
