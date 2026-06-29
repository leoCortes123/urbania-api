<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacPermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $permissions = [
            // Auth
            ['auth', 'ver', 'Ver usuarios'],
            ['auth', 'crear', 'Crear usuarios'],
            ['auth', 'editar', 'Editar usuarios'],
            ['auth', 'suspender', 'Suspender usuarios'],
            ['auth', 'roles', 'Gestionar roles'],

            // Propiedades
            ['propiedades', 'ver', 'Ver propiedades'],
            ['propiedades', 'crear', 'Crear propiedades'],
            ['propiedades', 'editar', 'Editar propiedades'],
            ['propiedades', 'eliminar', 'Eliminar propiedades'],
            ['propiedades', 'cambiar_estado', 'Cambiar estado de propiedad'],

            // Directorio
            ['directorio', 'ver', 'Ver directorio'],
            ['directorio', 'crear', 'Crear contactos'],
            ['directorio', 'editar', 'Editar contactos'],
            ['directorio', 'eliminar', 'Eliminar contactos'],
            ['directorio', 'vincular', 'Vincular contactos a unidades'],

            // Cobranza
            ['cobranza', 'ver', 'Ver cartera'],
            ['cobranza', 'facturar', 'Generar facturación'],
            ['cobranza', 'registrar_pago', 'Registrar pagos'],
            ['cobranza', 'aprobar', 'Aprobar operaciones'],
            ['cobranza', 'paz_y_salvo', 'Generar paz y salvo'],

            // Pagos
            ['pagos', 'ver', 'Ver transacciones'],
            ['pagos', 'configurar', 'Configurar pasarela'],
            ['pagos', 'reembolsar', 'Reembolsar pagos'],

            // Comunicaciones
            ['comunicaciones', 'ver', 'Ver comunicados'],
            ['comunicaciones', 'crear', 'Redactar comunicados'],
            ['comunicaciones', 'encuestas', 'Gestionar encuestas'],

            // Reservas
            ['reservas', 'ver', 'Ver reservas'],
            ['reservas', 'crear', 'Crear reservas'],
            ['reservas', 'aprobar', 'Aprobar reservas'],

            // Portería
            ['porteria', 'ver', 'Ver minuta'],
            ['porteria', 'registrar', 'Registrar visitas'],
            ['porteria', 'validar_qr', 'Validar QR de acceso'],

            // Mantenimiento
            ['mantenimiento', 'ver', 'Ver solicitudes'],
            ['mantenimiento', 'crear', 'Crear órdenes'],
            ['mantenimiento', 'asignar', 'Asignar técnicos'],

            // Reportes
            ['reportes', 'ver', 'Ver dashboard'],
            ['reportes', 'exportar', 'Exportar reportes'],

            // Configuración
            ['configuracion', 'ver', 'Ver configuración'],
            ['configuracion', 'editar', 'Editar configuración'],

            // Tenant/SaaS
            ['tenant', 'ver', 'Ver organización'],
            ['tenant', 'editar', 'Editar organización'],
            ['tenant', 'usuarios', 'Gestionar usuarios de la org'],
        ];

        $now = now();

        foreach ($permissions as [$resource, $action, $name]) {
            $existing = DB::table('permissions')
                ->where('resource', $resource)
                ->where('action', $action)
                ->first();

            if ($existing === null) {
                DB::table('permissions')->insert([
                    'id' => DB::raw('gen_random_uuid()'),
                    'resource' => $resource,
                    'action' => $action,
                    'name' => $name,
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('permissions')
                ->where('id', $existing->id)
                ->update([
                    'name' => $name,
                    'is_system' => true,
                    'updated_at' => $now,
                ]);
        }
    }
}
