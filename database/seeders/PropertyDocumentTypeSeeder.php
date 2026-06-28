<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyDocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyDocumentTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the property document types catalog.
     */
    public function run(): void
    {
        $documentTypes = [
            ['code' => 'escritura', 'name' => 'Escritura Pública', 'sort_order' => 1],
            ['code' => 'plano', 'name' => 'Plano Arquitectónico', 'sort_order' => 2],
            ['code' => 'certificado_libertad', 'name' => 'Certificado de Libertad y Tradición', 'sort_order' => 3],
            ['code' => 'recibo_pago', 'name' => 'Recibo de Pago', 'sort_order' => 4],
            ['code' => 'contrato', 'name' => 'Contrato', 'sort_order' => 5],
            ['code' => 'otros', 'name' => 'Otros', 'sort_order' => 6],
        ];

        foreach ($documentTypes as $documentType) {
            PropertyDocumentType::firstOrCreate(
                ['code' => $documentType['code']],
                $documentType
            );
        }
    }
}
