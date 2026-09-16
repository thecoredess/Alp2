@extends('layouts.app')
@section('title', 'Profil')
@section('heading', 'Profil')
@section('subheading', 'Maklumat akaun, hubungan dan kata laluan')

@section('content')
    <div class="page-shell space-y-6">
        <div class="grid gap-6 xl:grid-cols-2">
            <x-page-card title="Profil Saya" description="Maklumat akaun dan hubungan" icon="user-cog">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5"
                      x-data="{ preview: null, icon: @js(old('avatar_icon', $user->avatar_icon)) }">
                @csrf
                @method('PUT')

                {{-- Avatar --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="shrink-0">
                        <template x-if="preview">
                            <img :src="preview" alt="Pratonton" class="h-20 w-20 rounded-full object-cover ring-1 ring-black/10">
                        </template>
                        <div x-show="!preview">
                            <x-user-avatar :user="$user" size="lg" />
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 space-y-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Gambar &amp; ikon profil</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Muat naik foto (JPG/PNG/WebP, maks 2 MB) atau pilih ikon pratetap.</p>
                        </div>

                        <x-field label="Muat naik gambar" name="avatar">
                            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="inp"
                                   @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null; icon = null">
                        </x-field>

                        <div>
                            <p class="mb-2 text-xs font-medium text-gray-600">Atau pilih ikon</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($avatarIcons as $code => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="avatar_icon" value="{{ $code }}" class="peer sr-only"
                                               x-model="icon"
                                               @change="preview = null; document.getElementById('avatar').value = ''"
                                               @checked(old('avatar_icon', $user->avatar_icon) === $code)>
                                        <span class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-navy-700 transition peer-checked:border-royal-500 peer-checked:bg-royal-50 peer-checked:ring-2 peer-checked:ring-royal-200"
                                              title="{{ $label }}">
                                            <x-profile-icon :name="$code" class="h-5 w-5" />
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('avatar_icon')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>

                        @if ($user->avatar_path || $user->avatar_icon)
                            <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                <input type="checkbox" name="remove_avatar" value="1" class="rounded border-gray-300 text-danger focus:ring-danger">
                                Buang gambar / ikon (kembali ke inisial nama)
                            </label>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold text-gray-900">Akaun log masuk</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Maklumat yang digunakan untuk mengakses sistem.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field label="Nama" name="name" :required="true" class="sm:col-span-2">
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="inp">
                    </x-field>

                    <x-field label="E-mel Log Masuk" name="email" :required="true" class="sm:col-span-2" hint="Digunakan untuk log masuk dan notifikasi.">
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="inp">
                    </x-field>

                    @if (! $user->alp_id)
                        <x-field label="Unit / Bahagian" name="unit" class="sm:col-span-2">
                            <input id="unit" name="unit" type="text" value="{{ old('unit', $user->unit) }}" class="inp">
                        </x-field>
                    @endif

                    <div class="sm:col-span-2 rounded-lg bg-slate-50 px-3.5 py-3 text-xs text-slate-600 ring-1 ring-slate-200/80">
                        <span class="font-medium text-slate-700">Peranan:</span>
                        {{ $user->roles->first()?->name ? \App\Enums\RoleName::from($user->roles->first()->name)->label() : '—' }}
                        @if ($user->alp)
                            <span class="mx-1.5 text-slate-300">·</span>
                            <span class="font-medium text-slate-700">Kod ALP:</span>
                            {{ $user->alp->ref_code }}
                        @endif
                    </div>
                </div>

                @if ($user->alp)
                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="text-sm font-semibold text-gray-900">Maklumat hubungan ALP</h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Kod rujukan, portfolio dan tarikh lantikan hanya boleh dikemas kini oleh pentadbir.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-field label="Telefon" name="phone">
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->alp->phone) }}" class="inp">
                        </x-field>

                        <x-field label="E-mel Hubungan" name="contact_email" hint="E-mel rasmi / hubungan (boleh berbeza daripada e-mel log masuk).">
                            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $user->alp->email) }}" class="inp">
                        </x-field>

                        <x-field label="Alamat" name="address" class="sm:col-span-2">
                            <input id="address" name="address" type="text" value="{{ old('address', $user->alp->address) }}" class="inp">
                        </x-field>

                        <div class="sm:col-span-2 grid gap-2 rounded-lg border border-gray-100 bg-white px-3.5 py-3 text-xs text-gray-600 sm:grid-cols-2">
                            <div>
                                <span class="font-medium text-gray-700">Portfolio / Profesion</span>
                                <p class="mt-0.5">{{ $user->alp->portfolio_zone ?: '—' }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Tempoh lantikan</span>
                                <p class="mt-0.5">
                                    {{ $user->alp->appointment_start?->format('d/m/Y') ?? '—' }}
                                    –
                                    {{ $user->alp->appointment_end?->format('d/m/Y') ?? 'terbuka' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('dashboard') }}" class="btn-white">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Profil</button>
                </div>
                </form>
            </x-page-card>

            <x-page-card title="Tukar Kata Laluan" description="Minimum 8 aksara, huruf dan nombor" icon="check" class="scroll-mt-6 xl:sticky xl:top-[4.5rem] xl:self-start" id="kata-laluan">
            @if ($user->must_change_password)
                <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Demi keselamatan, anda perlu menetapkan kata laluan baharu sebelum meneruskan.
                </div>
            @endif

            <form method="POST" action="{{ route('password.change.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-field label="Kata Laluan Semasa" name="current_password" :required="true">
                    <x-password-input id="current_password" name="current_password" autocomplete="current-password" :required="true" />
                </x-field>

                <x-field label="Kata Laluan Baharu" name="password" :required="true">
                    <x-password-input id="password" name="password" autocomplete="new-password" :required="true" />
                </x-field>

                <x-field label="Sahkan Kata Laluan Baharu" name="password_confirmation" :required="true">
                    <x-password-input id="password_confirmation" name="password_confirmation" autocomplete="new-password" :required="true" />
                </x-field>

                <div class="flex justify-end border-t border-gray-100 pt-5">
                    <button type="submit" class="btn-primary">Kemas Kini Kata Laluan</button>
                </div>
            </form>
            </x-page-card>
        </div>
    </div>
@endsection
