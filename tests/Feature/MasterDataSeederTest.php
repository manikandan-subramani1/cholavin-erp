<?php

namespace Tests\Feature;

use App\Models\Godown;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Party;
use App\Models\PartyAddress;
use App\Models\Product;
use App\Models\ReferenceMaster;
use App\Models\Shop;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_master_has_five_complete_idempotent_records(): void
    {
        $this->seed([AccessControlSeeder::class, MasterDataSeeder::class]);
        $this->seed(MasterDataSeeder::class);

        foreach (collect(config('erp_modules.reference'))->pluck('type') as $type) {
            $records = ReferenceMaster::where('type', $type)->get();
            $this->assertCount(5, $records, "{$type} must contain five records.");
            $this->assertTrue($records->every(fn ($record) => filled($record->name) && filled($record->code) && filled($record->description) && is_array($record->metadata) && $record->is_active));
        }

        $this->assertSame(5, Shop::count());
        $this->assertSame(5, Godown::count());
        $this->assertSame(5, ReferenceMaster::where('type', 'subcategory')->whereNotNull('parent_id')->count());
        $this->assertSame(15, ReferenceMaster::whereIn('type', ['invoice_sequence', 'bank_account', 'number_sequence'])->whereNotNull('shop_id')->count());
        $this->assertSame(10, ReferenceMaster::whereIn('type', ['tax_rate', 'hsn_sac'])->whereNotNull('percentage')->count());

        $products = Product::where('sku', 'like', 'DEMO-%')->get();
        $this->assertCount(5, $products);
        $this->assertTrue($products->every(fn ($product) => $product->barcode && $product->category_id && $product->brand_id && $product->variant_id && $product->grade_id && $product->unit_id && $product->tax_rate_id && $product->hsn_sac_id));

        $parties = Party::whereIn('type', ['customer', 'supplier'])->get();
        $this->assertCount(10, $parties);
        $this->assertTrue($parties->every(fn ($party) => $party->group_id && $party->mobile && $party->email && $party->gstin && $party->pan));
        $this->assertSame(10, PartyAddress::where('is_default', true)->count());
        $this->assertSame(5, InventoryBalance::where('batch_number', 'OPENING')->count());
        $this->assertSame(5, InventoryMovement::where('reference_number', 'like', 'SEED-OPEN-%')->count());
    }
}
