<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class TenancyBootstrapSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crea la organización por defecto y asigna todos los registros
     * huérfanos de users, condominiums y contacts a ella.
     *
     * Idempotente: puede ejecutarse múltiples veces sin duplicar datos.
     */
    public function run(): void
    {
        $defaultOrganization = $this->ensureDefaultOrganization();

        if ($defaultOrganization === null) {
            return;
        }

        $organizationId = $defaultOrganization->id;

        DB::table('users')
            ->whereNull('organization_id')
            ->update(['organization_id' => $organizationId, 'updated_at' => now()]);

        DB::table('condominiums')
            ->whereNull('organization_id')
            ->update(['organization_id' => $organizationId, 'updated_at' => now()]);

        DB::table('contacts')
            ->whereNull('organization_id')
            ->update(['organization_id' => $organizationId, 'updated_at' => now()]);
    }

    /**
     * Crea la organización por defecto si no existe ninguna.
     *
     * @return object|null Objeto con la propiedad id, o null si no se pudo crear.
     */
    private function ensureDefaultOrganization(): ?object
    {
        $existing = DB::table('organizations')->first();

        if ($existing !== null) {
            return $existing;
        }

        $id = Uuid::uuid7()->toString();
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

        return DB::table('organizations')->where('id', $id)->first();
    }
}
