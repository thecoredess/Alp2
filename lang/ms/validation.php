<?php

/**
 * Mesej pengesahan (validation) Bahasa Melayu.
 * Kunci yang tiada di sini akan jatuh balik ke bahasa Inggeris (fallback).
 */
return [
    'accepted' => 'Medan :attribute mesti diterima.',
    'after' => 'Medan :attribute mesti tarikh selepas :date.',
    'after_or_equal' => 'Medan :attribute mesti tarikh selepas atau sama dengan :date.',
    'before' => 'Medan :attribute mesti tarikh sebelum :date.',
    'before_or_equal' => 'Medan :attribute mesti tarikh sebelum atau sama dengan :date.',
    'confirmed' => 'Pengesahan :attribute tidak sepadan.',
    'current_password' => 'Kata laluan tidak betul.',
    'date' => 'Medan :attribute bukan tarikh yang sah.',
    'different' => 'Medan :attribute dan :other mesti berbeza.',
    'email' => 'Medan :attribute mesti alamat e-mel yang sah.',
    'exists' => ':attribute yang dipilih tidak sah.',
    'in' => ':attribute yang dipilih tidak sah.',
    'integer' => 'Medan :attribute mesti nombor bulat.',
    'max' => [
        'string' => 'Medan :attribute tidak boleh melebihi :max aksara.',
        'numeric' => 'Medan :attribute tidak boleh melebihi :max.',
    ],
    'min' => [
        'string' => 'Medan :attribute mesti sekurang-kurangnya :min aksara.',
        'numeric' => 'Medan :attribute mesti sekurang-kurangnya :min.',
    ],
    'numeric' => 'Medan :attribute mesti nombor.',
    'required' => 'Medan :attribute wajib diisi.',
    'string' => 'Medan :attribute mesti teks.',
    'unique' => ':attribute telah digunakan.',

    'password' => [
        'letters' => 'Kata laluan mesti mengandungi sekurang-kurangnya satu huruf.',
        'mixed' => 'Kata laluan mesti mengandungi huruf besar dan kecil.',
        'numbers' => 'Kata laluan mesti mengandungi sekurang-kurangnya satu nombor.',
        'symbols' => 'Kata laluan mesti mengandungi sekurang-kurangnya satu simbol.',
        'uncompromised' => 'Kata laluan ini telah muncul dalam kebocoran data. Sila pilih kata laluan lain.',
    ],

    'custom' => [
        //
    ],

    'attributes' => [
        'name' => 'nama',
        'email' => 'e-mel',
        'password' => 'kata laluan',
        'current_password' => 'kata laluan semasa',
    ],
];
