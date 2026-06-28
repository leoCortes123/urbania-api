<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Condominium>
 */
final class CondominiumFactory extends Factory
{
    /** @var class-string<Condominium> */
    protected $model = Condominium::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'department' => $this->faker->state(),
            'country' => 'Colombia',
            'nit' => (string) $this->faker->unique()->numerify('900.###.###-#'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->companyEmail(),
            'legal_representative' => $this->faker->name(),
            'total_coefficient' => '1.000000',
            'logo_url' => null,
            'is_active' => true,
        ];
    }
}
