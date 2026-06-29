<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            'organization_id' => $this->ensureDefaultOrganization(),
        ];
    }

    /**
     * Devuelve el id de la organización por defecto, creándola si no existe.
     */
    private function ensureDefaultOrganization(): string
    {
        $existing = DB::table('organizations')->first();

        if ($existing !== null) {
            return $existing->id;
        }

        $id = (string) Str::orderedUuid();
        $now = now();

        DB::table('organizations')->insert([
            'id' => $id,
            'name' => 'Urbania Default',
            'type' => 'edificio_unico',
            'nit' => '000000000-0',
            'email' => null,
            'country' => 'Colombia',
            'currency' => 'COP',
            'status' => 'activo',
            'logo_url' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }
}
