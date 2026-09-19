<?php

namespace App\Http\Requests;

use App\Support\ProfileAvatarIcons;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $hasAlp = (bool) $user?->alp_id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'avatar_icon' => ['nullable', 'string', Rule::in(array_keys(ProfileAvatarIcons::options()))],
            'remove_avatar' => ['nullable', 'boolean'],
        ];

        if ($hasAlp) {
            $rules['phone'] = ['nullable', 'string', 'max:30'];
            $rules['address'] = ['nullable', 'string', 'max:500'];
        } else {
            $rules['unit'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'e-mel log masuk',
            'unit' => 'unit',
            'phone' => 'telefon',
            'address' => 'alamat',
            'avatar' => 'gambar profil',
            'avatar_icon' => 'ikon profil',
        ];
    }
}
