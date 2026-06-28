<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\Tower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tower>
 */
final class TowerFactory extends Factory
{
    /** @var class-string<Tower> */
    protected $model = Tower::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'name' => 'Torre '.$this->faker->randomLetter(),
            'code' => $this->faker->bothify('T#'),
            'floor_count' => $this->faker->numberBetween(1, 30),
            'has_elevator' => $this->faker->boolean(),
            'description' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
