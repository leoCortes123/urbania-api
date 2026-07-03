<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var array<string, string> Caché de IDs de property_types por code */
    private array $propertyTypeIds = [];

    /** @var array<string, string> Caché de IDs de property_statuses por code */
    private array $propertyStatusIds = [];

    /** @var array<string, string> Caché de IDs de occupant_types por code */
    private array $occupantTypeIds = [];

    /** @var array<string, string> Caché de IDs de roles por code */
    private array $roleIds = [];

    /** Timestamp unificado para inserts */
    private string $now;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->now = now()->toDateTimeString();
        $this->warmCatalogs();

        $this->addDataToDefaultOrg();
        $this->createClienteSanRafael();
        $this->createClienteTorresNorte();
        $this->createClienteGreenTower();

        // ═══════════════════════════════════════════════════
        // ⭐ AGREGAR NUEVOS FEATURES/CLIENTES AQUÍ
        // ═══════════════════════════════════════════════════
    }

    // ─────────────────────────────────────────────────────
    // §1 — Datos para la organización por defecto
    // ─────────────────────────────────────────────────────

    private function addDataToDefaultOrg(): void
    {
        $org = DB::table('organizations')->first();
        if ($org === null) {
            return; // sin organización, nada que hacer
        }
        $organizationId = $org->id;

        // Obtener el condominio ya creado por CondominiumSeeder
        $condominium = DB::table('condominiums')
            ->where('nit', '900.000.000-1')
            ->first();

        if ($condominium === null) {
            return;
        }
        $condominiumId = $condominium->id;

        // Usuarios ya creados por DatabaseSeeder
        $adminUser = DB::table('users')->where('email', 'admin@urbania.com')->first();
        $residenteUser = DB::table('users')->where('email', 'residente@urbania.com')->first();

        // ── Torres ──────────────────────────────────────

        $towerAId = $this->findOrCreateTower($condominiumId, 'Torre A', 'A', 5, true, 'Torre principal', 1);
        $towerBId = $this->findOrCreateTower($condominiumId, 'Torre B', 'B', 3, true, 'Torre secundaria', 2);

        // ── Propiedades ─────────────────────────────────

        $aptoTypeId = $this->propertyTypeIds['apartamento'];
        $localTypeId = $this->propertyTypeIds['local'];
        $parqTypeId = $this->propertyTypeIds['parqueadero'];
        $statusOcupadaId = $this->propertyStatusIds['ocupada'];
        $statusVaciaId = $this->propertyStatusIds['vacia'];
        $statusEnVentaId = $this->propertyStatusIds['en_venta'];

        $propertyIds = [];
        $coefficientNormal = 0.027778;
        $coefficientAdjust = 0.027770; // el último apartamento del loop para que suma = 1.000000
        $unitIndex = 0;
        $totalLoopUnits = 34; // 25 (T.A aptos) + 9 (T.B aptos)

        // Torre A: 5 pisos × 5 aptos + 1 local = 26 unidades
        foreach ([1, 2, 3, 4, 5] as $floor) {
            for ($unit = 1; $unit <= 5; $unit++) {
                $unitIndex++;
                $unitNumber = sprintf('%d%02d', $floor, $unit);
                $coeff = ($unitIndex === $totalLoopUnits) ? $coefficientAdjust : $coefficientNormal;

                // Asignar estados específicos
                $statusId = $statusOcupadaId;
                if ($floor === 2 && $unit === 1) {
                    $statusId = $statusVaciaId;
                } elseif ($floor === 3 && $unit === 1) {
                    $statusId = $statusEnVentaId;
                } elseif ($floor === 1 && $unit <= 2) {
                    $statusId = $statusOcupadaId; // 101, 102 ocupadas
                } elseif ($floor === 1 && $unit === 3) {
                    $statusId = $statusVaciaId;
                }

                $propertyId = $this->findOrCreateProperty(
                    $condominiumId, $towerAId, $aptoTypeId, $statusId,
                    $floor, $unitNumber, '60.00', $coeff,
                    2, 1, false
                );
                $propertyIds[$unitNumber] = $propertyId;
            }
        }

        // Local comercial en Torre A, piso 1
        $localAId = $this->findOrCreateProperty(
            $condominiumId, $towerAId, $localTypeId, $statusOcupadaId,
            1, 'LC-101', '30.00', $coefficientNormal,
            0, 1, false
        );

        // Torre B: 3 pisos × 3 aptos + 1 parqueadero = 10 unidades
        foreach ([1, 2, 3] as $floor) {
            for ($unit = 1; $unit <= 3; $unit++) {
                $unitIndex++;
                $unitNumber = sprintf('%d%02d', $floor, $unit);
                $coeff = ($unitIndex === $totalLoopUnits) ? $coefficientAdjust : $coefficientNormal;

                $statusId = $statusOcupadaId;
                if ($floor === 1 && $unit === 1) {
                    $statusId = $statusOcupadaId; // Torre B-101 ocupada
                } elseif ($floor === 2 && $unit === 1) {
                    $statusId = $statusVaciaId;
                }

                $propertyId = $this->findOrCreateProperty(
                    $condominiumId, $towerBId, $aptoTypeId, $statusId,
                    $floor, $unitNumber, '60.00', $coeff,
                    2, 1, false
                );
                $propertyIds["B{$unitNumber}"] = $propertyId;
            }
        }

        // Parqueadero en Torre B
        $parqBId = $this->findOrCreateProperty(
            $condominiumId, $towerBId, $parqTypeId, $statusOcupadaId,
            1, 'P-101', '12.00', $coefficientNormal,
            null, null, true, 'P-101'
        );

        // ── Contactos ───────────────────────────────────

        $adminContactId = $this->findOrCreateContact(
            $adminUser?->id, $organizationId,
            'CC', '1000000001', 'Admin Urbania',
            'admin@urbania.com', '3001111111'
        );

        $residenteContactId = $this->findOrCreateContact(
            $residenteUser?->id, $organizationId,
            'CC', '1000000002', 'Residente Urbania',
            'residente@urbania.com', '3002222222'
        );

        // ── Property Occupants ──────────────────────────
        // Admin como legal_owner + portal_primary del Apto 101
        if (isset($propertyIds['101'])) {
            $this->findOrCreateOccupant($propertyIds['101'], $adminContactId, 'propietario', true, true, true);
        }
        // Residente como ocupante simple (sin roles legales/portal)
        if (isset($propertyIds['B101'])) {
            $this->findOrCreateOccupant($propertyIds['B101'], $residenteContactId, 'residente', true, false, false);
        }

        // ── Role Assignments para los usuarios ──────────

        if ($adminUser !== null) {
            $this->assignRole($adminUser->id, 'admin', 'organization', $organizationId);
        }
        if ($residenteUser !== null) {
            $this->assignRole($residenteUser->id, 'residente', 'organization', $organizationId);
        }

        // ── Comunicaciones ──────────────────────────────
        if ($adminUser !== null) {
            $this->createDefaultCommunications($condominiumId, $adminUser->id, $adminContactId, $residenteContactId);
        }
    }

    // ─────────────────────────────────────────────────────
    // §2 — Cliente San Rafael
    // ─────────────────────────────────────────────────────

    private function createClienteSanRafael(): void
    {
        $organizationId = $this->findOrCreateOrganization(
            'Conjunto Residencial San Rafael', 'edificio_unico',
            '900.123.456-7', 'Colombia'
        );

        // Usuarios
        $adminUserId = $this->findOrCreateUser(
            'Admin San Rafael', 'admin@sanrafael.com', 'Admin2026!',
            'admin', $organizationId
        );
        $residenteUserId = $this->findOrCreateUser(
            'Residente San Rafael', 'residente@sanrafael.com', 'Resident2026!',
            'user', $organizationId
        );

        // Condominio
        $condominiumId = $this->findOrCreateCondominium(
            'Conjunto Residencial San Rafael', 'Bogotá',
            '900.123.456-7', $organizationId
        );

        // Role assignments
        $this->assignRole($adminUserId, 'admin', 'organization', $organizationId);
        $this->assignRole($residenteUserId, 'residente', 'organization', $organizationId);

        // Torres y propiedades
        $aptoTypeId = $this->propertyTypeIds['apartamento'];
        $localTypeId = $this->propertyTypeIds['local'];
        $parqTypeId = $this->propertyTypeIds['parqueadero'];
        $depoTypeId = $this->propertyTypeIds['deposito'];
        $statusOcupadaId = $this->propertyStatusIds['ocupada'];
        $statusVaciaId = $this->propertyStatusIds['vacia'];
        $statusEnVentaId = $this->propertyStatusIds['en_venta'];

        // Torre 1: 5 pisos, ascensor, 5 aptos
        $t1Id = $this->findOrCreateTower($condominiumId, 'Torre 1', 'T1', 5, true, 'Torre principal', 1);
        $propSR = []; // property IDs indexados

        for ($floor = 1; $floor <= 5; $floor++) {
            $unitNumber = sprintf('%d01', $floor);
            $statusId = ($floor === 1) ? $statusOcupadaId : (($floor === 2) ? $statusEnVentaId : $statusOcupadaId);
            $propSR["T1-{$unitNumber}"] = $this->findOrCreateProperty(
                $condominiumId, $t1Id, $aptoTypeId, $statusId,
                $floor, $unitNumber, '55.00', 0.052632,
                2, 1, false
            );
        }
        // Local comercial en Torre 1
        $propSR['T1-LC-101'] = $this->findOrCreateProperty(
            $condominiumId, $t1Id, $localTypeId, $statusOcupadaId,
            1, 'LC-101', '35.00', 0.052632,
            0, 1, false
        );

        // Torre 2: 8 pisos, ascensor, 8 aptos + 1 parqueadero
        $t2Id = $this->findOrCreateTower($condominiumId, 'Torre 2', 'T2', 8, true, 'Torre alta', 2);
        for ($floor = 1; $floor <= 8; $floor++) {
            $unitNumber = sprintf('%d01', $floor);
            $propSR["T2-{$unitNumber}"] = $this->findOrCreateProperty(
                $condominiumId, $t2Id, $aptoTypeId, $statusOcupadaId,
                $floor, $unitNumber, '58.00', 0.052632,
                2, 1, false
            );
        }
        $propSR['T2-P-101'] = $this->findOrCreateProperty(
            $condominiumId, $t2Id, $parqTypeId, $statusOcupadaId,
            1, 'P-101', '12.00', 0.052632,
            null, null, true, 'P-101'
        );

        // Torre 3: 3 pisos, sin ascensor, 3 aptos + 1 depósito
        $t3Id = $this->findOrCreateTower($condominiumId, 'Torre 3', 'T3', 3, false, 'Torre baja', 3);
        for ($floor = 1; $floor <= 3; $floor++) {
            $unitNumber = sprintf('%d01', $floor);
            $propSR["T3-{$unitNumber}"] = $this->findOrCreateProperty(
                $condominiumId, $t3Id, $aptoTypeId, $statusOcupadaId,
                $floor, $unitNumber, '50.00', 0.052632,
                1, 1, false
            );
        }
        // El último ajusta el coeficiente
        $propSR['T3-D-101'] = $this->findOrCreateProperty(
            $condominiumId, $t3Id, $depoTypeId, $statusOcupadaId,
            1, 'D-101', '8.00', 0.052624,
            null, null, false
        );

        // ── 6 contactos (2 vinculados a users, 4 independientes) ──

        $cAdmin = $this->findOrCreateContact(
            $adminUserId, $organizationId,
            'CC', '2000000001', 'Admin San Rafael',
            'admin@sanrafael.com', '3101111111'
        );
        $cResidente = $this->findOrCreateContact(
            $residenteUserId, $organizationId,
            'CC', '2000000002', 'Residente San Rafael',
            'residente@sanrafael.com', '3102222222'
        );
        $cInquilino = $this->findOrCreateContact(
            null, $organizationId,
            'CC', '2000000003', 'Carlos Inquilino',
            'carlos@email.com', '3103333333'
        );
        $cFamiliar = $this->findOrCreateContact(
            null, $organizationId,
            'CC', '2000000004', 'María Familiar',
            'maria@email.com', '3104444444'
        );
        $cEmergencia = $this->findOrCreateContact(
            null, $organizationId,
            'CC', '2000000005', 'Pedro Emergencia',
            null, '3105555555'
        );
        $cEmpleado = $this->findOrCreateContact(
            null, $organizationId,
            'CE', '2000000006', 'Luis Empleado',
            null, '3106666666'
        );

        // ── Occupants con nueva semántica ────────────────
        // Escenario 1: Admin como legal_owner + portal_primary del T1-101 (vive ahí)
        if (isset($propSR['T1-101'])) {
            $this->findOrCreateOccupant($propSR['T1-101'], $cAdmin, 'propietario', true, true, true);
            // Familiar como co-residente sin roles especiales
            $this->findOrCreateOccupant($propSR['T1-101'], $cFamiliar, 'familiar', false, false, false);
        }
        // Escenario 2: Inquilino como portal_primary pero NO legal_owner (dueño arrienda)
        if (isset($propSR['T1-201'])) {
            $this->findOrCreateOccupant($propSR['T1-201'], $cInquilino, 'inquilino', true, false, true);
        }
        // Escenario 3: Residente como legal_owner + portal_primary del T2-101
        if (isset($propSR['T2-101'])) {
            $this->findOrCreateOccupant($propSR['T2-101'], $cResidente, 'residente', true, true, true);
        }
        // Escenario 4: Empleado doméstico sin ningún rol especial
        if (isset($propSR['T2-201'])) {
            $this->findOrCreateOccupant($propSR['T2-201'], $cEmpleado, 'empleado_domestico', false, false, false);
        }
        // Escenario 5: Contacto de emergencia (ni siquiera es is_primary)
        if (isset($propSR['T3-101'])) {
            $this->findOrCreateOccupant($propSR['T3-101'], $cEmergencia, 'contacto_emergencia', false, false, false);
        }

        // ── Comunicaciones ──────────────────────────────

        $this->createSanRafaelCommunications($condominiumId, $adminUserId, $cAdmin, $cResidente);
    }

    // ─────────────────────────────────────────────────────
    // §3 — Cliente Torres del Norte (administradora)
    // ─────────────────────────────────────────────────────

    private function createClienteTorresNorte(): void
    {
        $organizationId = $this->findOrCreateOrganization(
            'Administradora Torres del Norte S.A.S.', 'administradora',
            '900.999.999-9', 'Colombia'
        );

        // Usuarios
        $adminGlobalId = $this->findOrCreateUser(
            'Admin Torres Norte', 'admin@torresnorte.com', 'Admin2026!',
            'admin', $organizationId
        );
        $adminNogalId = $this->findOrCreateUser(
            'Admin El Nogal', 'admin@elnogal.com', 'Admin2026!',
            'admin', $organizationId
        );
        $adminParqueId = $this->findOrCreateUser(
            'Admin Parque Industrial', 'admin@parqueindustrial.com', 'Admin2026!',
            'admin', $organizationId
        );

        // ── Condominio 1: Edificio El Nogal ─────────────

        $nogalCondId = $this->findOrCreateCondominium(
            'Edificio El Nogal', 'Medellín',
            '900.999.999-1', $organizationId
        );

        $this->assignRole($adminGlobalId, 'admin', 'organization', $organizationId);
        $this->assignRole($adminNogalId, 'admin_conjunto', 'condominium', $nogalCondId);

        $aptoTypeId = $this->propertyTypeIds['apartamento'];
        $oficTypeId = $this->propertyTypeIds['local']; // oficinas comerciales usan type "local"
        $statusOcupadaId = $this->propertyStatusIds['ocupada'];
        $statusVaciaId = $this->propertyStatusIds['vacia'];

        $nogalTowerId = $this->findOrCreateTower($nogalCondId, 'Torre Única', 'UNICA', 6, true, 'Torre principal El Nogal', 1);
        $propNogal = [];

        for ($floor = 1; $floor <= 6; $floor++) {
            $unitNumber = sprintf('%d01', $floor);
            $status = ($floor === 6) ? $statusVaciaId : $statusOcupadaId;
            $propNogal["N-{$unitNumber}"] = $this->findOrCreateProperty(
                $nogalCondId, $nogalTowerId, $aptoTypeId, $status,
                $floor, $unitNumber, '70.00', 0.125000,
                3, 2, true
            );
        }
        // 2 oficinas comerciales
        $propNogal['N-OC-101'] = $this->findOrCreateProperty(
            $nogalCondId, $nogalTowerId, $oficTypeId, $statusOcupadaId,
            1, 'OC-101', '45.00', 0.125000,
            0, 1, false
        );
        $propNogal['N-OC-102'] = $this->findOrCreateProperty(
            $nogalCondId, $nogalTowerId, $oficTypeId, $statusOcupadaId,
            1, 'OC-102', '45.00', 0.125000,
            0, 1, false
        );

        // ── Condominio 2: Parque Industrial Occidente ────

        $parqueCondId = $this->findOrCreateCondominium(
            'Parque Industrial Occidente', 'Funza',
            '900.999.999-2', $organizationId
        );

        $this->assignRole($adminParqueId, 'admin_conjunto', 'condominium', $parqueCondId);

        $parqueTowerId = $this->findOrCreateTower($parqueCondId, 'Nave Única', 'NAVE', 2, false, 'Nave industrial', 1);
        $propParque = [];

        $bodegas = [
            [1, 'BD-101', '250.00', 0.250000],
            [1, 'BD-102', '200.00', 0.250000],
            [2, 'BD-201', '250.00', 0.250000],
            [2, 'BD-202', '200.00', 0.250000],
        ];

        foreach ($bodegas as $b) {
            $propParque[] = $this->findOrCreateProperty(
                $parqueCondId, $parqueTowerId, $aptoTypeId, $statusOcupadaId,
                $b[0], $b[1], $b[2], $b[3],
                null, null, false, null, 'Bodega industrial'
            );
        }

        // ── Contactos ───────────────────────────────────

        $cGlobal = $this->findOrCreateContact(
            $adminGlobalId, $organizationId,
            'CC', '3000000001', 'Admin Torres Norte Global',
            'admin@torresnorte.com', '3201111111'
        );
        $cNogal = $this->findOrCreateContact(
            $adminNogalId, $organizationId,
            'CC', '3000000002', 'Admin El Nogal',
            'admin@elnogal.com', '3202222222'
        );
        $cParque = $this->findOrCreateContact(
            $adminParqueId, $organizationId,
            'CC', '3000000003', 'Admin Parque Industrial',
            'admin@parqueindustrial.com', '3203333333'
        );

        // ── Occupants con nueva semántica ────────────────
        if (isset($propNogal['N-101'])) {
            $this->findOrCreateOccupant($propNogal['N-101'], $cNogal, 'propietario', true, true, true);
        }
        if (isset($propNogal['N-OC-101'])) {
            $this->findOrCreateOccupant($propNogal['N-OC-101'], $cGlobal, 'propietario', true, true, true);
        }
        if (isset($propParque[0])) {
            $this->findOrCreateOccupant($propParque[0], $cParque, 'propietario', true, true, true);
        }

        // ── Comunicaciones ──────────────────────────────

        $this->createTorresNorteCommunications($nogalCondId, $adminNogalId);
        $this->createTorresNorteCommunications($parqueCondId, $adminParqueId);
    }

    // ─────────────────────────────────────────────────────
    // §4 — Cliente Green Tower
    // ─────────────────────────────────────────────────────

    private function createClienteGreenTower(): void
    {
        $organizationId = $this->findOrCreateOrganization(
            'Edificio Green Tower', 'edificio_unico',
            '900.888.888-8', 'Colombia'
        );

        $adminUserId = $this->findOrCreateUser(
            'Admin Green Tower', 'admin@greentower.com', 'Admin2026!',
            'admin', $organizationId
        );

        $condominiumId = $this->findOrCreateCondominium(
            'Edificio Green Tower', 'Bogotá',
            '900.888.888-8', $organizationId
        );

        $this->assignRole($adminUserId, 'admin', 'organization', $organizationId);

        $aptoTypeId = $this->propertyTypeIds['apartamento'];
        $localTypeId = $this->propertyTypeIds['local'];
        $statusOcupadaId = $this->propertyStatusIds['ocupada'];
        $statusEnVentaId = $this->propertyStatusIds['en_venta'];

        $towerId = $this->findOrCreateTower($condominiumId, 'Torre Green', 'G', 4, true, 'Torre ecológica', 1);

        // 4 aptos, uno por piso (401-404)
        $aptos = [
            [1, '401', 0.200000],
            [2, '402', 0.200000],
            [3, '403', 0.200000],
            [4, '404', 0.200000],
        ];

        $propGT = [];
        foreach ($aptos as $a) {
            $propGT[] = $this->findOrCreateProperty(
                $condominiumId, $towerId, $aptoTypeId, $statusOcupadaId,
                $a[0], $a[1], '80.00', $a[2],
                3, 2, true
            );
        }
        // Local comercial en primer piso
        $propGT[] = $this->findOrCreateProperty(
            $condominiumId, $towerId, $localTypeId, $statusEnVentaId,
            1, 'LC-101', '40.00', 0.200000,
            0, 1, false
        );

        // Contactos
        $cAdmin = $this->findOrCreateContact(
            $adminUserId, $organizationId,
            'CC', '4000000001', 'Admin Green Tower',
            'admin@greentower.com', '3301111111'
        );

        if (isset($propGT[0])) {
            $this->findOrCreateOccupant($propGT[0], $cAdmin, 'propietario', true, true, true);
        }

        // Comunicaciones minimalistas
        $this->findOrCreateAnnouncement(
            $condominiumId, $adminUserId,
            'Bienvenidos a Green Tower',
            'Estimados residentes, les damos la bienvenida a este edificio ecológico.',
            'todos', 'enviado', null,
            ['whatsapp', 'email']
        );

        $this->findOrCreateChannel($condominiumId, 'whatsapp', 'twilio', [
            'account_sid' => 'AC_demo',
            'from' => '+570000000000',
        ], true);
        $this->findOrCreateChannel($condominiumId, 'email', 'ses', [
            'from_address' => 'greentower@urbania.app',
        ], true);

        $this->findOrCreateTemplate($condominiumId, 'Bienvenida Green Tower', 'bienvenida',
            'Hola {nombre}, bienvenido a Green Tower. Tu apartamento {unidad} ya está disponible.'
        );
        $this->findOrCreateTemplate($condominiumId, 'Recordatorio Asamblea', 'recordatorio',
            'Se le recuerda que la asamblea de copropietarios será el {fecha} a las {hora}.'
        );
    }

    // ═════════════════════════════════════════════════════
    // HELPERS — Catálogos
    // ═════════════════════════════════════════════════════

    /**
     * Carga en caché los catálogos existentes.
     */
    private function warmCatalogs(): void
    {
        foreach (DB::table('property_types')->get() as $row) {
            $this->propertyTypeIds[$row->code] = $row->id;
        }
        foreach (DB::table('property_statuses')->get() as $row) {
            $this->propertyStatusIds[$row->code] = $row->id;
        }
        foreach (DB::table('occupant_types')->get() as $row) {
            $this->occupantTypeIds[$row->code] = $row->id;
        }
        foreach (DB::table('roles')->get() as $row) {
            $this->roleIds[$row->code] = $row->id;
        }
    }

    // ═════════════════════════════════════════════════════
    // HELPERS — Entidades
    // ═════════════════════════════════════════════════════

    private function findOrCreateOrganization(
        string $name, string $type, string $nit,
        string $country = 'Colombia', string $currency = 'COP', string $status = 'activo'
    ): string {
        $existing = DB::table('organizations')->where('nit', $nit)->first();
        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        DB::table('organizations')->insert([
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'nit' => $nit,
            'email' => null,
            'country' => $country,
            'currency' => $currency,
            'status' => $status,
            'logo_url' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $id;
    }

    private function findOrCreateUser(
        string $name, string $email, string $password,
        string $role, string $organizationId, string $status = 'active'
    ): string {
        $existing = DB::table('users')->where('email', $email)->first();
        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        DB::table('users')->insert([
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'phone' => null,
            'avatar_url' => null,
            'password_hash' => Hash::make($password),
            'email_verified_at' => $this->now,
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'mfa_backup_codes' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'password_changed_at' => null,
            'must_change_password' => false,
            'role' => $role,
            'status' => $status,
            'organization_id' => $organizationId,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $id;
    }

    private function findOrCreateCondominium(
        string $name, string $city, ?string $nit, string $organizationId
    ): string {
        $existing = DB::table('condominiums')->where('nit', $nit)->first();
        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        DB::table('condominiums')->insert([
            'id' => $id,
            'name' => $name,
            'address' => null,
            'city' => $city,
            'department' => null,
            'country' => 'Colombia',
            'nit' => $nit,
            'phone' => null,
            'email' => null,
            'legal_representative' => null,
            'total_coefficient' => '1.000000',
            'logo_url' => null,
            'is_active' => true,
            'organization_id' => $organizationId,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $id;
    }

    private function findOrCreateTower(
        string $condominiumId, string $name, string $code,
        int $floorCount, bool $hasElevator, ?string $description = null, int $sortOrder = 0
    ): string {
        $existing = DB::table('towers')
            ->where('condominium_id', $condominiumId)
            ->where('name', $name)
            ->first();

        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        DB::table('towers')->insert([
            'id' => $id,
            'condominium_id' => $condominiumId,
            'name' => $name,
            'code' => $code,
            'floor_count' => $floorCount,
            'has_elevator' => $hasElevator,
            'description' => $description,
            'sort_order' => $sortOrder,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $id;
    }

    /**
     * @param  array<string, mixed>  $extra  Campos adicionales (bedrooms, bathrooms, has_parking, parking_lot, notes)
     */
    private function findOrCreateProperty(
        string $condominiumId, string $towerId, string $propertyTypeId,
        string $propertyStatusId, int $floor, string $unitNumber,
        string $areaM2, float $coefficient, ?int $bedrooms = null,
        ?int $bathrooms = null, bool $hasParking = false,
        ?string $parkingLot = null, ?string $notes = null
    ): string {
        $existing = DB::table('properties')
            ->where('tower_id', $towerId)
            ->where('floor', $floor)
            ->where('unit_number', $unitNumber)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        DB::table('properties')->insert([
            'id' => $id,
            'condominium_id' => $condominiumId,
            'tower_id' => $towerId,
            'property_type_id' => $propertyTypeId,
            'property_status_id' => $propertyStatusId,
            'floor' => $floor,
            'unit_number' => $unitNumber,
            'area_m2' => $areaM2,
            'coefficient' => number_format($coefficient, 6, '.', ''),
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'has_parking' => $hasParking,
            'parking_lot' => $parkingLot,
            'notes' => $notes,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $id;
    }

    private function findOrCreateContact(
        ?string $userId, string $organizationId,
        string $documentType, string $documentNumber, string $fullName,
        ?string $email = null, ?string $phone = null
    ): string {
        // 1. ¿Ya existe contacto vinculado a este usuario?
        if ($userId !== null) {
            $byUser = DB::table('contacts')
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->first();

            if ($byUser !== null) {
                return $byUser->id;
            }
        }

        // 2. ¿Ya existe por tipo y número de documento?
        $existing = DB::table('contacts')
            ->where('document_type', $documentType)
            ->where('document_number', $documentNumber)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        DB::table('contacts')->insert([
            'id' => $id,
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
            'notes' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $id;
    }

    private function findOrCreateOccupant(
        string $propertyId, string $contactId,
        string $occupantTypeCode, bool $isPrimary = false,
        bool $isLegalOwner = false, bool $isPortalPrimary = false
    ): void {
        $occupantTypeId = $this->occupantTypeIds[$occupantTypeCode];

        $existing = DB::table('property_occupants')
            ->where('property_id', $propertyId)
            ->where('contact_id', $contactId)
            ->where('occupant_type_id', $occupantTypeId)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return;
        }

        DB::table('property_occupants')->insert([
            'id' => Uuid::uuid7()->toString(),
            'property_id' => $propertyId,
            'contact_id' => $contactId,
            'occupant_type_id' => $occupantTypeId,
            'is_primary' => $isPrimary,
            'is_legal_owner' => $isLegalOwner,
            'is_portal_primary' => $isPortalPrimary,
            'move_in_date' => '2026-01-01',
            'move_out_date' => null,
            'is_active' => true,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }

    private function assignRole(
        string $userId, string $roleCode, string $scopeType, string $scopeId
    ): void {
        if (! isset($this->roleIds[$roleCode])) {
            return; // rol no existe (RBAC no sembrado aún)
        }
        $roleId = $this->roleIds[$roleCode];

        $existing = DB::table('role_assignments')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return;
        }

        DB::table('role_assignments')->insert([
            'id' => Uuid::uuid7()->toString(),
            'user_id' => $userId,
            'role_id' => $roleId,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'starts_at' => $this->now,
            'ends_at' => null,
            'assigned_by_user_id' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }

    // ═════════════════════════════════════════════════════
    // HELPERS — Comunicaciones (idempotentes)
    // ═════════════════════════════════════════════════════

    private function findOrCreateAnnouncement(
        string $condominiumId, string $authorUserId,
        string $titulo, string $cuerpo, string $segmento,
        string $estado, ?string $targetId = null,
        ?array $canales = null, bool $fijado = false
    ): string {
        $existing = DB::table('announcements')
            ->where('condominium_id', $condominiumId)
            ->where('titulo', $titulo)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        DB::table('announcements')->insert([
            'id' => $id,
            'condominium_id' => $condominiumId,
            'autor_user_id' => $authorUserId,
            'titulo' => $titulo,
            'cuerpo' => $cuerpo,
            'segmento' => $segmento,
            'target_id' => $targetId,
            'estado' => $estado,
            'programado_para' => null,
            'fijado' => $fijado,
            'canales' => $canales !== null ? json_encode($canales) : null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        return $id;
    }

    private function findOrCreateSurvey(
        string $condominiumId, string $pregunta, string $tipo,
        array $options, bool $activa = true
    ): string {
        $existing = DB::table('surveys')
            ->where('condominium_id', $condominiumId)
            ->where('pregunta', $pregunta)
            ->first();

        if ($existing !== null) {
            $id = $existing->id;
        } else {
            $id = Uuid::uuid7()->toString();
            DB::table('surveys')->insert([
                'id' => $id,
                'condominium_id' => $condominiumId,
                'pregunta' => $pregunta,
                'tipo' => $tipo,
                'cierra_el' => null,
                'activa' => $activa,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }

        // Insertar opciones solo si no existen
        $existingOptions = DB::table('survey_options')
            ->where('survey_id', $id)
            ->exists();

        if (! $existingOptions) {
            foreach ($options as $index => $texto) {
                DB::table('survey_options')->insert([
                    'id' => Uuid::uuid7()->toString(),
                    'survey_id' => $id,
                    'texto' => $texto,
                    'orden' => $index + 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]);
            }
        }

        return $id;
    }

    private function findOrCreateChannel(
        string $condominiumId, string $canal, string $provider,
        array $config, bool $activo = true
    ): void {
        $existing = DB::table('communication_channels')
            ->where('condominium_id', $condominiumId)
            ->where('canal', $canal)
            ->first();

        if ($existing !== null) {
            return;
        }

        DB::table('communication_channels')->insert([
            'id' => Uuid::uuid7()->toString(),
            'condominium_id' => $condominiumId,
            'canal' => $canal,
            'provider' => $provider,
            'config' => json_encode($config),
            'activo' => $activo,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }

    private function findOrCreateTemplate(
        string $condominiumId, string $nombre, string $tipo, string $cuerpo
    ): void {
        $existing = DB::table('message_templates')
            ->where('condominium_id', $condominiumId)
            ->where('nombre', $nombre)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return;
        }

        DB::table('message_templates')->insert([
            'id' => Uuid::uuid7()->toString(),
            'condominium_id' => $condominiumId,
            'nombre' => $nombre,
            'tipo' => $tipo,
            'cuerpo' => $cuerpo,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }

    private function findOrCreateAnnouncementDelivery(
        string $announcementId, string $contactId,
        string $canal, string $estado = 'entregado'
    ): void {
        $existing = DB::table('announcement_deliveries')
            ->where('announcement_id', $announcementId)
            ->where('contact_id', $contactId)
            ->where('canal', $canal)
            ->first();

        if ($existing !== null) {
            return;
        }

        DB::table('announcement_deliveries')->insert([
            'id' => Uuid::uuid7()->toString(),
            'announcement_id' => $announcementId,
            'contact_id' => $contactId,
            'canal' => $canal,
            'estado' => $estado,
            'external_id' => null,
            'metadata' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }

    // ═════════════════════════════════════════════════════
    // HELPERS — Paquetes de comunicaciones por cliente
    // ═════════════════════════════════════════════════════

    private function createDefaultCommunications(
        string $condominiumId, string $authorUserId,
        string $adminContactId, string $residenteContactId
    ): void {
        // 2 comunicados
        $a1Id = $this->findOrCreateAnnouncement(
            $condominiumId, $authorUserId,
            'Bienvenidos al Conjunto Urbania',
            "Estimados copropietarios, les damos la bienvenida a nuestro conjunto residencial. Estamos comprometidos con su bienestar y la sana convivencia.\n\nAtentamente,\nLa Administración",
            'todos', 'enviado', null,
            ['whatsapp', 'email'], true
        );

        $a2Id = $this->findOrCreateAnnouncement(
            $condominiumId, $authorUserId,
            'Mantenimiento programado de ascensores',
            'Informamos que el próximo sábado se realizará mantenimiento preventivo a los ascensores de la Torre A entre las 8:00 AM y las 2:00 PM. Disculpen las molestias.',
            'todos', 'enviado', null,
            ['whatsapp', 'email']
        );

        // Deliveries para el primer anuncio (simular entregas a los 2 contactos)
        $this->findOrCreateAnnouncementDelivery($a1Id, $adminContactId, 'email', 'entregado');
        $this->findOrCreateAnnouncementDelivery($a1Id, $residenteContactId, 'email', 'entregado');

        // 1 encuesta con opciones
        $this->findOrCreateSurvey(
            $condominiumId,
            '¿Qué mejoras te gustaría ver en el conjunto?',
            'multiple',
            [
                'Mejorar la zona de juegos infantiles',
                'Instalar gimnasio',
                'Remodelar el salón comunal',
                'Más zonas verdes',
                'Sistema de seguridad con cámaras',
            ],
            true
        );

        // Canales
        $this->findOrCreateChannel($condominiumId, 'whatsapp', 'twilio', [
            'account_sid' => 'AC_demo_default',
            'from' => '+5710000000',
        ], true);
        $this->findOrCreateChannel($condominiumId, 'email', 'ses', [
            'from_address' => 'admin@urbania.app',
        ], true);

        // 2 plantillas
        $this->findOrCreateTemplate($condominiumId, 'Bienvenida nuevo residente', 'bienvenida',
            'Hola {nombre}, te damos la bienvenida al Conjunto Urbania. Tu unidad asignada es {unidad}. Cualquier duda, contáctanos.'
        );
        $this->findOrCreateTemplate($condominiumId, 'Recordatorio de asamblea', 'recordatorio',
            'Se le recuerda que la asamblea general de copropietarios se llevará a cabo el día {fecha} a las {hora} en el salón comunal. Su asistencia es fundamental.'
        );
    }

    private function createSanRafaelCommunications(
        string $condominiumId, string $authorUserId,
        string $adminContactId, string $residenteContactId
    ): void {
        // 1 comunicado enviado
        $aId = $this->findOrCreateAnnouncement(
            $condominiumId, $authorUserId,
            'Bienvenidos al Conjunto San Rafael',
            'La administración les da la más cordial bienvenida. Estamos para servirles.',
            'todos', 'enviado', null,
            ['whatsapp', 'email']
        );

        $this->findOrCreateAnnouncementDelivery($aId, $adminContactId, 'email', 'leido');
        $this->findOrCreateAnnouncementDelivery($aId, $residenteContactId, 'whatsapp', 'entregado');

        // 2 encuestas
        $this->findOrCreateSurvey(
            $condominiumId,
            '¿Cómo califica el servicio de portería?',
            'simple',
            ['Excelente', 'Bueno', 'Regular', 'Deficiente'],
            true
        );
        $this->findOrCreateSurvey(
            $condominiumId,
            '¿Qué actividad comunitaria prefiere?',
            'simple',
            ['Torneo deportivo', 'Integración familiar', 'Cine al aire libre', 'Mercado campesino'],
            true
        );

        // Canales
        $this->findOrCreateChannel($condominiumId, 'whatsapp', 'twilio', [
            'account_sid' => 'AC_san_rafael',
            'from' => '+5720000000',
        ], true);
        $this->findOrCreateChannel($condominiumId, 'email', 'ses', [
            'from_address' => 'sanrafael@urbania.app',
        ], true);

        // 2 plantillas
        $this->findOrCreateTemplate($condominiumId, 'Bienvenida San Rafael', 'bienvenida',
            '¡Bienvenido a Conjunto Residencial San Rafael! Tu apartamento {unidad} está listo para ser habitado. Contáctanos si necesitas algo.'
        );
        $this->findOrCreateTemplate($condominiumId, 'Pago de administración', 'cobro',
            'Estimado {nombre}, le recordamos que su pago de administración del mes de {mes} está pendiente. Valor: ${monto}. Fecha límite: {fecha_limite}.'
        );
    }

    private function createTorresNorteCommunications(
        string $condominiumId, string $authorUserId
    ): void {
        $this->findOrCreateAnnouncement(
            $condominiumId, $authorUserId,
            'Mantenimiento general programado',
            'Se informa a todos los copropietarios que el próximo viernes se realizará jornada de mantenimiento general en áreas comunes.',
            'todos', 'enviado', null,
            ['email']
        );

        $this->findOrCreateChannel($condominiumId, 'whatsapp', 'twilio', [
            'account_sid' => 'AC_torres_norte',
            'from' => '+5730000000',
        ], true);

        $this->findOrCreateTemplate($condominiumId, 'Notificación de visita', 'porteria',
            'Se ha registrado una visita a su unidad {unidad}: {visitante}, documento {documento}. Fecha: {fecha}, Hora: {hora}.'
        );
        $this->findOrCreateTemplate($condominiumId, 'Certificado de paz y salvo', 'paz_y_salvo',
            'Certificamos que el apartamento {unidad} se encuentra a paz y salvo por todo concepto hasta el {fecha}.'
        );
    }
}
