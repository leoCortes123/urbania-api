<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class DirectorioSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'propietario', 'name' => 'Propietario', 'sort_order' => 1],
            ['code' => 'residente', 'name' => 'Residente', 'sort_order' => 2],
            ['code' => 'inquilino', 'name' => 'Inquilino', 'sort_order' => 3],
            ['code' => 'familiar', 'name' => 'Familiar', 'sort_order' => 4],
            ['code' => 'contacto_emergencia', 'name' => 'Contacto de Emergencia', 'sort_order' => 5],
            ['code' => 'empleado_domestico', 'name' => 'Empleado(a) Doméstico(a)', 'sort_order' => 6],
        ];

        foreach ($types as $type) {
            DB::table('occupant_types')->insert([
                'id' => Uuid::uuid7()->toString(),
                'code' => $type['code'],
                'name' => $type['name'],
                'sort_order' => $type['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
