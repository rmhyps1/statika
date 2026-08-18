<?php

namespace Database\Factories;

use App\Models\DssdOpd;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DssdOpd> */
class DssdOpdFactory extends Factory
{
    protected $model = DssdOpd::class;

    public function definition(): array
    {
        return [
            'kode_dssd' => fake()->unique()->numerify('OPD-####'),
            'uraian_dssd' => fake()->sentence(),
            'produsen_data' => fake()->company(),
            'jenis_data' => fake()->randomElement(['Sektoral', 'Spasial', null]),
            'jenis_produsen' => fake()->randomElement(['OPD', 'Non-OPD', null]),
            'tahun' => fake()->numberBetween(2020, 2026),
        ];
    }
}
