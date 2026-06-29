<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacMigrationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = now();

        // Mapeo de users.role → role.code
        $roleMap = [
            'admin' => 'admin',
            'user' => 'residente',
        ];

        foreach ($roleMap as $oldRole => $newRoleCode) {
            $roleId = DB::table('roles')->where('code', $newRoleCode)->value('id');

            if ($roleId === null) {
                continue;
            }

            // Para cada user con ese role, crear una role_assignment si no existe
            $users = DB::table('users')
                ->where('role', $oldRole)
                ->whereNull('deleted_at')
                ->get();

            foreach ($users as $user) {
                $orgId = $user->organization_id;

                // Scope = la organización del user
                $existingAssignment = DB::table('role_assignments')
                    ->where('user_id', $user->id)
                    ->where('role_id', $roleId)
                    ->where('scope_type', 'organization')
                    ->where('scope_id', $orgId)
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $existingAssignment) {
                    DB::table('role_assignments')->insert([
                        'id' => DB::raw('gen_random_uuid()'),
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'scope_type' => 'organization',
                        'scope_id' => $orgId,
                        'starts_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
