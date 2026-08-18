<?php

namespace Database\Factories;

use App\Models\ImportedDssdData;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ImportedDssdData> */
class ImportedDssdDataFactory extends Factory
{
    protected $model = ImportedDssdData::class;

    public function definition(): array
    {
        return [
            'kode_dssd' => '35.07.' . fake()->unique()->numerify('##.##.####'),
            'uraian_dssd' => fake()->sentence(),
            'produsen_data' => fake()->company(),
            'ketersediaan_data' => fake()->randomElement(['ada', 'tidak']),
            'ketersediaan_source' => 'manual',
            'jenis_data' => fake()->randomElement(['Sektoral', 'Spasial', null]),
            'jenis_produsen' => fake()->randomElement(['OPD', 'Non-OPD', null]),
            'tahun' => fake()->numberBetween(2020, 2026),
            'satuan' => null,
            'definisi_operasional' => null,
            'tag_urusan' => null,
            'info_sub_kegiatan' => null,
            'keterangan' => null,
            'raw_data' => null,
        ];
    }

    public function available(): static
    {
        return $this->state(['ketersediaan_data' => 'ada']);
    }

    public function unavailable(): static
    {
        return $this->state(['ketersediaan_data' => 'tidak']);
    }
}
