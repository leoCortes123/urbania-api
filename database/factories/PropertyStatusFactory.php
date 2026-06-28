<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PropertyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyStatus>
 */
final class PropertyStatusFactory extends Factory
{
    /** @var class-string<PropertyStatus> */
    protected $model = PropertyStatus::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'allows_residents' => $this->faker->boolean(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
