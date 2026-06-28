<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the property types catalog.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'apartamento', 'name' => 'Apartamento', 'sort_order' => 1],
            ['code' => 'local', 'name' => 'Local Comercial', 'sort_order' => 2],
            ['code' => 'parqueadero', 'name' => 'Parqueadero', 'sort_order' => 3],
            ['code' => 'deposito', 'name' => 'Depósito', 'sort_order' => 4],
        ];

        foreach ($types as $type) {
            PropertyType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
