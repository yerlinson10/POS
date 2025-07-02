<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\UnitMeasure;
use App\Models\Product;
use App\Models\Customer;

class POSTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories if they don't exist
        $categories = [
            'Electronics',
            'Clothing',
            'Food & Beverages',
            'Books',
            'Home & Garden',
            'Sports & Outdoors',
            'Automotive',
            'Health & Beauty',
            'Toys & Games',
            'Office Supplies'
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
        }

        // Create unit measures if they don't exist
        $unitMeasures = [
            ['code' => 'PCS', 'name' => 'Pieces'],
            ['code' => 'KG', 'name' => 'Kilograms'],
            ['code' => 'LT', 'name' => 'Liters'],
            ['code' => 'M', 'name' => 'Meters'],
            ['code' => 'BOX', 'name' => 'Boxes'],
        ];

        foreach ($unitMeasures as $unit) {
            UnitMeasure::firstOrCreate(['code' => $unit['code']], $unit);
        }

        // Get all categories and unit measures
        $categoryIds = Category::pluck('id')->toArray();
        $unitMeasureIds = UnitMeasure::pluck('id')->toArray();

        // Create products for testing (simulate large catalog)
        $productCount = Product::count();
        if ($productCount < 5000) {
            $this->command->info('Creating products for POS testing...');

            Product::factory(5000 - $productCount)->create([
                'category_id' => function() use ($categoryIds) {
                    return $categoryIds[array_rand($categoryIds)];
                },
                'unit_measure_id' => function() use ($unitMeasureIds) {
                    return $unitMeasureIds[array_rand($unitMeasureIds)];
                },
            ]);
        }

        // Create test customers
        $customerCount = Customer::count();
        if ($customerCount < 100) {
            $this->command->info('Creating customers for POS testing...');
            Customer::factory(100 - $customerCount)->create();
        }

        $this->command->info('POS test data created successfully!');
        $this->command->info('Products: ' . Product::count());
        $this->command->info('Customers: ' . Customer::count());
        $this->command->info('Categories: ' . Category::count());
        $this->command->info('Unit Measures: ' . UnitMeasure::count());
    }
}
