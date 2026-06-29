<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => 'edificio_unico',
            'nit' => fake()->unique()->numerify('##########-#'),
            'email' => fake()->companyEmail(),
            'country' => 'Colombia',
            'currency' => 'COP',
            'status' => 'activo',
        ];
    }

    /**
     * @return OrganizationFactory
     */
    public function administradora(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'administradora',
        ]);
    }

    /**
     * @return OrganizationFactory
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trial',
        ]);
    }

    /**
     * @return OrganizationFactory
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'activo',
        ]);
    }

    /**
     * @return OrganizationFactory
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspendido',
        ]);
    }
}
