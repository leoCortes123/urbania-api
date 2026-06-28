<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Condominium;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CondominiumSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the default condominium.
     */
    public function run(): void
    {
        Condominium::firstOrCreate(
            ['nit' => '900.000.000-1'],
            [
                'name' => 'Conjunto Residencial Urbania',
                'address' => 'Calle 123 # 45-67',
                'city' => 'Bogotá',
                'department' => 'Cundinamarca',
                'country' => 'Colombia',
                'phone' => null,
                'email' => null,
                'legal_representative' => null,
                'total_coefficient' => '1.000000',
                'logo_url' => null,
                'is_active' => true,
            ]
        );
    }
}
