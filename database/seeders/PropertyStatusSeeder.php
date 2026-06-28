<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyStatusSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the property statuses catalog.
     */
    public function run(): void
    {
        $statuses = [
            ['code' => 'ocupada', 'name' => 'Ocupada', 'allows_residents' => true, 'sort_order' => 1],
            ['code' => 'vacia', 'name' => 'Vacía', 'allows_residents' => false, 'sort_order' => 2],
            ['code' => 'en_venta', 'name' => 'En Venta', 'allows_residents' => true, 'sort_order' => 3],
            ['code' => 'en_remodelacion', 'name' => 'En Remodelación', 'allows_residents' => false, 'sort_order' => 4],
        ];

        foreach ($statuses as $status) {
            PropertyStatus::firstOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
