<?php

namespace Database\Seeders;

use App\Models\UnitMeasure;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UnitMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['code' => 'KG',  'name' => 'Kilogramo'],
            ['code' => 'G',   'name' => 'Gramo'],
            ['code' => 'MG',  'name' => 'Miligramo'],
            ['code' => 'LB',  'name' => 'Libra'],
            ['code' => 'OZ',  'name' => 'Onza'],
            ['code' => 'L',   'name' => 'Litro'],
            ['code' => 'ML',  'name' => 'Mililitro'],
            ['code' => 'M',   'name' => 'Metro'],
            ['code' => 'CM',  'name' => 'Centímetro'],
            ['code' => 'MM',  'name' => 'Milímetro'],
            ['code' => 'FT',  'name' => 'Pie'],
            ['code' => 'IN',  'name' => 'Pulgada'],
            ['code' => 'UNIT','name' => 'Unidad'],
            ['code' => 'PACK','name' => 'Paquete'],
        ];

        foreach ($units as $u) {
            UnitMeasure::updateOrCreate(
                ['code' => $u['code']],
                ['name' => $u['name']]
            );
        }
    }
}
