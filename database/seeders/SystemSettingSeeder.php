<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\SystemSettingOption;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Type of invoice
        $invoiceType = SystemSetting::create([
            'key' => 'invoice_type',
            'label' => 'Tipo de factura',
            'type' => 'select',
            'default' => 'A4',
            'description' => 'Tipo de factura por defecto',
        ]);
        SystemSettingOption::insert([
            ['system_setting_id' => $invoiceType->id, 'value' => 'A4', 'default' => true, 'label' => 'A4'],
            ['system_setting_id' => $invoiceType->id, 'value' => '80mm', 'default' => false, 'label' => '80mm'],
        ]);
    }
}
