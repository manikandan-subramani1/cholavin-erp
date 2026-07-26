<?php

namespace Database\Seeders;

use App\Models\Godown;
use App\Models\Product;
use App\Models\ReferenceMaster;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $shop = Shop::updateOrCreate(['code' => 'NML'], [
                'name' => 'Namakkal Shop',
                'address' => 'Rice Market Road, Namakkal, Tamil Nadu',
                'is_active' => true,
            ]);

            Godown::updateOrCreate(['code' => 'NML-GD1'], [
                'shop_id' => $shop->id,
                'name' => 'Main Godown',
                'address' => 'Warehouse Lane, Namakkal, Tamil Nadu',
                'is_active' => true,
            ]);

            $masters = [
                ['category', 'RICE', 'Rice'], ['category', 'PULSES', 'Pulses'],
                ['brand', 'CHOLAVIN', 'Cholavin'], ['brand', 'GOLD-HARVEST', 'Gold Harvest'],
                ['unit', 'BAG', 'Bag'], ['unit', 'KG', 'Kilogram'],
                ['grade', 'GR-A', 'Grade A'], ['grade', 'GR-A1', 'Premium A1'],
                ['tax_rate', 'GST5', 'GST 5%'], ['tax_rate', 'GST0', 'GST 0%'],
            ];

            foreach ($masters as [$type, $code, $name]) {
                ReferenceMaster::updateOrCreate(['type' => $type, 'code' => $code], [
                    'name' => $name,
                    'description' => $name.' master record for Cholavin ERP.',
                    'percentage' => $type === 'tax_rate' ? (int) str($name)->between('GST', '%')->toString() : null,
                    'is_active' => true,
                ]);
            }

            foreach ([
                ['ITEM-0001', '8906101000011', 'Ponni Rice (25kg)', 1550, 'Premium everyday rice', 'RICE', 'CHOLAVIN'],
                ['ITEM-0002', '8906101000028', 'Basmati Rice (20kg)', 2450, 'Extra-long grain aromatic rice', 'RICE', 'GOLD-HARVEST'],
                ['ITEM-0003', '8906101000035', 'Idli Rice (25kg)', 1650, 'Soft idli rice', 'RICE', 'CHOLAVIN'],
                ['ITEM-0004', '8906101000042', 'Raw Rice (25kg)', 1450, 'Cleaned raw rice', 'RICE', 'GOLD-HARVEST'],
            ] as $row) {
                Product::updateOrCreate(['sku' => $row[0]], [
                    'name' => $row[2], 'slug' => str($row[0])->lower()->toString(), 'barcode' => $row[1],
                    'category_id' => ReferenceMaster::where(['type' => 'category', 'code' => $row[5]])->value('id'),
                    'brand_id' => ReferenceMaster::where(['type' => 'brand', 'code' => $row[6]])->value('id'),
                    'unit_id' => ReferenceMaster::where(['type' => 'unit', 'code' => 'BAG'])->value('id'),
                    'grade_id' => ReferenceMaster::where(['type' => 'grade', 'code' => 'GR-A'])->value('id'),
                    'tax_rate_id' => ReferenceMaster::where(['type' => 'tax_rate', 'code' => 'GST5'])->value('id'),
                    'sub_title' => $row[4], 'description' => $row[4].'. Quality checked and packed by Cholavin Foods.',
                    'price' => $row[3], 'purchase_price' => $row[3] - 150, 'sale_price' => $row[3],
                    'opening_stock' => 120, 'reorder_level' => 20, 'unit' => 'Bag',
                    'is_active' => true, 'show_on_homepage' => true, 'sort_order' => 1,
                ]);
            }
        });
    }
}
