<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class RbacRoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $permissions = DB::table('permissions')
            ->get()
            ->mapWithKeys(
                static fn (object $permission): array => [
                    "{$permission->resource}.{$permission->action}" => $permission->id,
                ]
            )
            ->all();

        $permId = static function (string $resource, string $action) use ($permissions): string {
            $key = "{$resource}.{$action}";

            if (! isset($permissions[$key])) {
                throw new \RuntimeException("Permission {$key} not found");
            }

            return $permissions[$key];
        };

        $roles = [
            // Súper-admin (operador SaaS)
            'saas_operador' => [
                'name' => 'Operador SaaS',
                'level' => 'organization',
                'permissions' => ['*'],  // todos
            ],

            // Administrador (dueño de la cuenta/edificio)
            'admin' => [
                'name' => 'Administrador',
                'level' => 'organization',
                'permissions' => [
                    'auth.ver', 'auth.crear', 'auth.editar', 'auth.suspender', 'auth.roles',
                    'propiedades.*',
                    'directorio.*',
                    'cobranza.*',
                    'pagos.*',
                    'comunicaciones.*',
                    'reservas.*',
                    'porteria.*',
                    'mantenimiento.*',
                    'reportes.*',
                    'configuracion.*',
                    'tenant.ver', 'tenant.editar', 'tenant.usuarios',
                    'roles.*',
                    'usuarios.*',
                ],
            ],

            // Administrador de conjunto (scope = condominium)
            'admin_conjunto' => [
                'name' => 'Administrador de Conjunto',
                'level' => 'condominium',
                'permissions' => [
                    'auth.ver', 'auth.crear', 'auth.editar',
                    'propiedades.*',
                    'directorio.*',
                    'cobranza.*',
                    'pagos.ver', 'pagos.configurar',
                    'comunicaciones.*',
                    'reservas.ver', 'reservas.aprobar',
                    'porteria.*',
                    'mantenimiento.*',
                    'reportes.*',
                    'configuracion.ver',
                ],
            ],

            // Consejo de administración
            'consejo' => [
                'name' => 'Consejo de Administración',
                'level' => 'condominium',
                'permissions' => [
                    'cobranza.ver', 'cobranza.aprobar',
                    'reportes.ver', 'reportes.exportar',
                    'propiedades.ver',
                    'directorio.ver',
                    'reservas.ver',
                    'configuracion.ver',
                ],
            ],

            // Revisor fiscal
            'revisor_fiscal' => [
                'name' => 'Revisor Fiscal',
                'level' => 'organization',
                'permissions' => [
                    'cobranza.ver',
                    'reportes.ver', 'reportes.exportar',
                    'pagos.ver',
                    'propiedades.ver',
                    'directorio.ver',
                    'configuracion.ver',
                ],
            ],

            // Contador
            'contador' => [
                'name' => 'Contador',
                'level' => 'organization',
                'permissions' => [
                    'cobranza.ver', 'cobranza.facturar', 'cobranza.registrar_pago', 'cobranza.paz_y_salvo',
                    'pagos.ver',
                    'reportes.ver', 'reportes.exportar',
                    'propiedades.ver',
                ],
            ],

            // Vigilante / Portería
            'vigilante' => [
                'name' => 'Vigilante',
                'level' => 'condominium',
                'permissions' => [
                    'porteria.*',
                    'directorio.ver',
                    'propiedades.ver',
                ],
            ],

            // Mantenimiento / Técnico
            'tecnico' => [
                'name' => 'Técnico de Mantenimiento',
                'level' => 'condominium',
                'permissions' => [
                    'mantenimiento.ver', 'mantenimiento.crear',
                    'propiedades.ver',
                ],
            ],

            // Residente
            'residente' => [
                'name' => 'Residente',
                'level' => 'unit',
                'permissions' => [
                    'reservas.ver', 'reservas.crear',
                    'porteria.ver', 'porteria.validar_qr',
                    'mantenimiento.ver', 'mantenimiento.crear',
                    'comunicaciones.ver',
                    'cobranza.ver', 'cobranza.paz_y_salvo',
                    'pagos.ver',
                    'directorio.ver',
                    'propiedades.ver',
                ],
            ],

            // Proveedor
            'proveedor' => [
                'name' => 'Proveedor',
                'level' => 'organization',
                'permissions' => [
                    'mantenimiento.ver',
                    'pagos.ver',
                ],
            ],

            // Aseo / Servicios generales
            'aseo' => [
                'name' => 'Servicios Generales',
                'level' => 'condominium',
                'permissions' => [
                    'mantenimiento.ver',
                    'propiedades.ver',
                ],
            ],
        ];

        $now = now();

        foreach ($roles as $code => $role) {
            $existing = DB::table('roles')->where('code', $code)->first();

            if ($existing === null) {
                $roleId = Uuid::uuid7()->toString();

                DB::table('roles')->insert([
                    'id' => $roleId,
                    'code' => $code,
                    'name' => $role['name'],
                    'level' => $role['level'],
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $roleId = $existing->id;

                DB::table('roles')
                    ->where('id', $roleId)
                    ->update([
                        'name' => $role['name'],
                        'level' => $role['level'],
                        'is_system' => true,
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
            }

            DB::table('role_permissions')->where('role_id', $roleId)->delete();

            $permInsert = [];

            foreach ($role['permissions'] as $permKey) {
                if ($permKey === '*') {
                    foreach ($permissions as $permissionId) {
                        $permInsert[] = [
                            'id' => DB::raw('gen_random_uuid()'),
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    break;
                }

                if (str_ends_with($permKey, '.*')) {
                    $resource = str_replace('.*', '', $permKey);

                    foreach ($permissions as $key => $permissionId) {
                        if (str_starts_with($key, $resource.'.')) {
                            $permInsert[] = [
                                'id' => DB::raw('gen_random_uuid()'),
                                'role_id' => $roleId,
                                'permission_id' => $permissionId,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    continue;
                }

                [$resource, $action] = explode('.', $permKey);
                $permissionId = $permId($resource, $action);

                $permInsert[] = [
                    'id' => DB::raw('gen_random_uuid()'),
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($permInsert)) {
                DB::table('role_permissions')->insert($permInsert);
            }
        }
    }
}
