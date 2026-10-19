<?php

namespace App\Enums;

/** Jenis program sumbangan URS v1.2 (BR-011). */
enum ProgramCategory: string
{
    case KOMUNITI = 'komuniti';
    case SUKAN = 'sukan';
    case PENDIDIKAN = 'pendidikan';
    case KEMASYARAKATAN = 'kemasyarakatan';

    public function label(): string
    {
        return match ($this) {
            self::KOMUNITI => 'Program komuniti',
            self::SUKAN => 'Program sukan',
            self::PENDIDIKAN => 'Program pendidikan',
            self::KEMASYARAKATAN => 'Program aktiviti kemasyarakatan',
        };
    }

    /** Penjelasan ringkas untuk ALP semasa memilih kategori (BR-011). */
    public function description(): string
    {
        return match ($this) {
            self::KOMUNITI => 'Program khidmat, kebajikan dan bantuan komuniti setempat — contoh: bantuan asnaf, gotong-royong, lawatan sara hidup, program di perumahan atau PPR.',
            self::SUKAN => 'Program sukan dan aktiviti fizikal — contoh: kejohanan/karnival sukan, latihan sukan belia, penyertaan dalam acara sukan amatur.',
            self::PENDIDIKAN => 'Program pembelajaran dan pembangunan ilmu — contoh: bengkel kemahiran, tuisyen, seminar pendidikan, program back-to-school, anugerah pencapaian pelajar.',
            self::KEMASYARAKATAN => 'Program sosial, budaya dan perayaan kemasyarakatan — contoh: karnival perayaan, aktiviti kesenian/kultur, program rukun tetangga, jalinan masyarakat (bukan sukan atau kebajikan semata-mata).',
        };
    }

    /** @return list<array{value: string, label: string, description: string}> */
    public static function guideEntries(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
            ],
            self::cases(),
        );
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
