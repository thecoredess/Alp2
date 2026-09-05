<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserStatus;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Cuba sahkan kelayakan pengguna, dengan had kadar (throttling).
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Kelayakan yang diberikan tidak sepadan dengan rekod kami.',
            ]);
        }

        // Sekat pengguna tidak aktif walaupun kata laluan betul.
        if (Auth::user()->status !== UserStatus::ACTIVE) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akaun anda telah dinyahaktifkan. Sila hubungi pentadbir sistem.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Pastikan permintaan log masuk tidak melebihi had kadar.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percubaan log masuk. Sila cuba semula dalam {$seconds} saat.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
