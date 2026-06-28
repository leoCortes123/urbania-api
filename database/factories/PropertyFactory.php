<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\Tower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
final class PropertyFactory extends Factory
{
    /** @var class-string<Property> */
    protected $model = Property::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tower = Tower::factory();

        return [
            'condominium_id' => Condominium::factory(),
            'tower_id' => $tower,
            'property_type_id' => PropertyType::factory(),
            'property_status_id' => PropertyStatus::factory(),
            'floor' => $this->faker->numberBetween(0, 30),
            'unit_number' => $this->faker->bothify('###?'),
            'area_m2' => $this->faker->randomFloat(2, 20, 500),
            'coefficient' => $this->faker->randomFloat(6, 0.000001, 1),
            'bedrooms' => $this->faker->optional()->numberBetween(1, 6),
            'bathrooms' => $this->faker->optional()->numberBetween(1, 6),
            'has_parking' => $this->faker->boolean(),
            'parking_lot' => $this->faker->optional()->bothify('P-##'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
