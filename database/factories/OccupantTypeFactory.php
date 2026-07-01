<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OccupantType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OccupantType>
 */
final class OccupantTypeFactory extends Factory
{
    protected $model = OccupantType::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'code' => Str::slug($name),
            'name' => ucfirst($name),
            'description' => $this->faker->optional()->sentence(),
            'sort_order' => $this->faker->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
