<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $ingredients = [
            // ===== SAUS PADANG =====
            [
                'code' => 'ING-001',
                'name' => 'Cabai Merah',
                'unit' => 'kg',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 45000,
            ],
            [
                'code' => 'ING-002',
                'name' => 'Bawang Putih',
                'unit' => 'kg',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 38000,
            ],
            [
                'code' => 'ING-003',
                'name' => 'Bawang Bombay',
                'unit' => 'kg',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 30000,
            ],

            // ===== SAUS & CAIRAN =====
            [
                'code' => 'ING-004',
                'name' => 'Saus Tomat',
                'unit' => 'sachet',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 3000,
            ],
            [
                'code' => 'ING-005',
                'name' => 'Saus Sambal',
                'unit' => 'sachet',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 3000,
            ],
            [
                'code' => 'ING-006',
                'name' => 'Saus Tiram',
                'unit' => 'botol',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 18000,
            ],

            // ===== PELENGKAP =====
            [
                'code' => 'ING-007',
                'name' => 'Gula Pasir',
                'unit' => 'kg',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 14000,
            ],
            [
                'code' => 'ING-008',
                'name' => 'Garam',
                'unit' => 'kg',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 8000,
            ],
            [
                'code' => 'ING-009',
                'name' => 'Minyak Goreng',
                'unit' => 'liter',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 18000,
            ],
            [
                'code' => 'ING-010',
                'name' => 'Cuka',
                'unit' => 'botol',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 12000,
            ],
            [
                'code' => 'ING-011',
                'name' => 'Tepung Maizena',
                'unit' => 'kg',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 20000,
            ],
            [
                'code' => 'ING-012',
                'name' => 'Minyak Wijen',
                'unit' => 'botol',
                'stock' => 100,
                'min_stock' => 10,
                'price' => 25000,
            ],
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::create($ingredient);
        }
    }
}
