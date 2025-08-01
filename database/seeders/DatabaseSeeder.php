<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            UnitMeasureSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            // InvoiceSeeder::class,
            // InvoiceItemSeeder::class,
            // PosSessionSeeder::class,
            SystemSettingSeeder::class,
            ExampleDashboardWidgetsSeeder::class,
            RolesAndPermissionsSeeder::class,
            ModulesSeeder::class,
        ]);
    }
}
