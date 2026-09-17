<?php

namespace App\Http\Requests;

use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Models\Application;
use App\Support\JpReviewChecklist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ApplicationReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = (string) $this->route('type');

        // Jenis bukan JP: biar controller abort 404 (jangan 403 di sini).
        if ($type !== ReviewType::SECRETARIAT->value) {
            return true;
        }

        return $this->user()?->can(ReviewType::SECRETARIAT->permission()) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ((string) $this->route('type') !== ReviewType::SECRETARIAT->value) {
            return;
        }

        // Pegawai JP: tiada UI lengkap/tidak lengkap — auto-isi checklist lengkap untuk perakuan.
        if ($this->user()?->canMakeFullJpReviewDecision()) {
            return;
        }

        $checklist = collect(JpReviewChecklist::keys())
            ->mapWithKeys(fn (string $k) => [$k => JpReviewChecklist::LENGKAP])
            ->all();

        $this->merge(['checklist' => $checklist]);
    }

    public function rules(): array
    {
        // Skip validasi checklist untuk route legacy (404 di controller).
        if ((string) $this->route('type') !== ReviewType::SECRETARIAT->value) {
            return [];
        }

        $keys = JpReviewChecklist::keys();
        $fullDecision = $this->user()?->canMakeFullJpReviewDecision() ?? false;
        $allowed = $fullDecision
            ? ReviewDecision::cases()
            : ReviewDecision::forPegawaiJp();

        $rules = [
            'decision' => ['required', Rule::enum(ReviewDecision::class), Rule::in(array_map(
                fn (ReviewDecision $d) => $d->value,
                $allowed,
            ))],
            'comments' => [
                Rule::requiredIf(fn () => $this->input('decision') !== ReviewDecision::RECOMMEND->value),
                'nullable', 'string', 'max:2000',
            ],
        ];

        if ($fullDecision) {
            $rules['checklist'] = ['required', 'array'];
            foreach ($keys as $k) {
                $rules["checklist.$k"] = ['required', Rule::in([JpReviewChecklist::LENGKAP, JpReviewChecklist::TIDAK_LENGKAP])];
            }
        } else {
            $rules['checklist'] = ['nullable', 'array'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        if ((string) $this->route('type') !== ReviewType::SECRETARIAT->value) {
            return;
        }

        $validator->after(function (Validator $v) {
            if ($this->input('decision') !== ReviewDecision::RECOMMEND->value) {
                return;
            }

            /** @var Application $application */
            $application = $this->route('application');
            if (! $application->hasJkewCrosscheckDocument()) {
                $v->errors()->add(
                    'crosscheck',
                    'Sila muat naik Borang Ulasan JKEW sebelum hantar keputusan Disyorkan.',
                );
            }

            // Hanya Admin JP wajib lengkapkan senarai semak secara manual.
            if (! ($this->user()?->canMakeFullJpReviewDecision() ?? false)) {
                return;
            }

            $checklist = $this->input('checklist', []);
            if (! is_array($checklist) || ! JpReviewChecklist::allLengkap($checklist)) {
                $v->errors()->add(
                    'checklist',
                    'Semua item senarai semak mesti ditanda Lengkap sebelum hantar ke perakuan (UR-M04-001).',
                );
            }
        });
    }

    public function attributes(): array
    {
        $attrs = ['decision' => 'keputusan', 'comments' => 'ulasan', 'checklist' => 'senarai semak'];
        foreach (JpReviewChecklist::items() as $key => $label) {
            $attrs["checklist.$key"] = $label;
        }

        return $attrs;
    }

    public function messages(): array
    {
        return [
            'comments.required' => 'Sila nyatakan ulasan/sebab.',
            'checklist.required' => 'Sila lengkapkan senarai semak JP.',
            'decision.in' => 'Pegawai JP hanya boleh syor kepada Pengarah JP atau kembalikan untuk pembetulan.',
        ];
    }
}
