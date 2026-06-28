<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PropertyDocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyDocumentType>
 */
final class PropertyDocumentTypeFactory extends Factory
{
    /** @var class-string<PropertyDocumentType> */
    protected $model = PropertyDocumentType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
