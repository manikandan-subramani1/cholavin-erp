<?php

namespace Database\Seeders;

use App\Models\Godown;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Party;
use App\Models\Product;
use App\Models\ReferenceMaster;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $shops = $this->seedShops();
            $godowns = $this->seedGodowns($shops);
            $this->seedReferenceMasters($shops);
            $products = $this->seedProducts();
            $this->seedParties($shops->first());
            $this->seedOpeningStock($shops->first(), $godowns->first(), $products);
        });
    }

    private function seedShops()
    {
        return collect([
            ['CHN', 'Chennai Head Office', '12 Rice Mill Road, Red Hills, Chennai, Tamil Nadu - 600052'],
            ['MDU', 'Madurai Branch', '48 West Masi Street, Madurai, Tamil Nadu - 625001'],
            ['CBE', 'Coimbatore Branch', '105 Avinashi Road, Coimbatore, Tamil Nadu - 641018'],
            ['TRY', 'Tiruchirappalli Branch', '22 Thillai Nagar Main Road, Tiruchirappalli, Tamil Nadu - 620018'],
            ['SLM', 'Salem Branch', '76 Omalur Main Road, Salem, Tamil Nadu - 636009'],
        ])->map(fn (array $row) => Shop::updateOrCreate(
            ['code' => $row[0]],
            ['name' => $row[1], 'address' => $row[2], 'is_active' => true]
        ));
    }

    private function seedGodowns($shops)
    {
        $data = [
            ['CHN-GD1', 'Chennai Central Godown', 'No. 8 Warehouse Lane, Red Hills, Chennai - 600052'],
            ['MDU-GD1', 'Madurai Main Godown', '19 Market Yard, Mattuthavani, Madurai - 625007'],
            ['CBE-GD1', 'Coimbatore Main Godown', '31 SIDCO Industrial Estate, Coimbatore - 641021'],
            ['TRY-GD1', 'Trichy Main Godown', '14 Ariyamangalam Warehouse Road, Trichy - 620010'],
            ['SLM-GD1', 'Salem Main Godown', '9 Steel Plant Road, Salem - 636030'],
        ];

        return collect($data)->map(fn (array $row, int $index) => Godown::updateOrCreate(
            ['code' => $row[0]],
            ['shop_id' => $shops[$index]->id, 'name' => $row[1], 'address' => $row[2], 'is_active' => true]
        ));
    }

    private function seedReferenceMasters($shops): void
    {
        $definitions = $this->referenceDefinitions();
        foreach ($definitions as $type => $rows) {
            foreach ($rows as $index => $row) {
                ReferenceMaster::updateOrCreate(
                    ['type' => $type, 'code' => $row['code']],
                    [
                        'parent_id' => null,
                        'shop_id' => in_array($type, ['invoice_sequence', 'bank_account', 'number_sequence'], true) ? $shops[$index]->id : null,
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'percentage' => $row['percentage'] ?? null,
                        'metadata' => $row['metadata'],
                        'is_active' => true,
                    ]
                );
            }
        }

        $categories = ReferenceMaster::ofType('category')->get()->keyBy('code');
        foreach ([
            ['BASMATI', 'Basmati Rice', 'Premium long-grain aromatic rice', 'RICE'],
            ['PONNI', 'Ponni Rice', 'Popular Tamil Nadu everyday rice', 'RICE'],
            ['MILLET', 'Millet Products', 'Nutritious traditional millet products', 'MILLET-CAT'],
            ['FLOUR', 'Speciality Flour', 'Stone-ground and speciality flour', 'FLOUR-CAT'],
            ['ORGANIC', 'Organic Staples', 'Certified organic staple foods', 'ORGANIC-CAT'],
        ] as $index => $row) {
            ReferenceMaster::updateOrCreate(
                ['type' => 'subcategory', 'code' => $row[0]],
                [
                    'parent_id' => $categories[$row[3]]->id,
                    'shop_id' => null,
                    'name' => $row[1],
                    'description' => $row[2],
                    'percentage' => null,
                    'metadata' => ['display_order' => $index + 1, 'catalog_visible' => true],
                    'is_active' => true,
                ]
            );
        }
    }

    private function referenceDefinitions(): array
    {
        return [
            'financial_year' => $this->rows([
                ['FY2022', '2022-2023'], ['FY2023', '2023-2024'], ['FY2024', '2024-2025'], ['FY2025', '2025-2026'], ['FY2026', '2026-2027'],
            ], fn ($i) => ['start_date' => (2022 + $i).'-04-01', 'end_date' => (2023 + $i).'-03-31', 'is_closed' => $i < 4]),
            'invoice_sequence' => $this->rows([
                ['INV-SEQ-1', 'Chennai Sales Invoice'], ['INV-SEQ-2', 'Madurai Sales Invoice'], ['INV-SEQ-3', 'Coimbatore Sales Invoice'], ['INV-SEQ-4', 'Trichy Sales Invoice'], ['INV-SEQ-5', 'Salem Sales Invoice'],
            ], fn ($i) => ['document_type' => 'sales_invoice', 'prefix' => ['CHN','MDU','CBE','TRY','SLM'][$i].'/INV/', 'next_number' => 1, 'padding' => 5]),
            'tax_configuration' => $this->rows([
                ['GST-REG', 'Regular GST'], ['GST-COMP', 'Composition GST'], ['GST-EXPORT', 'Export GST'], ['GST-SEZ', 'SEZ GST'], ['GST-UNREG', 'Unregistered Supply'],
            ], fn ($i) => ['registration_type' => ['regular','composition','export','sez','unregistered'][$i], 'state_code' => '33', 'country' => 'India']),
            'bank_account' => $this->rows([
                ['BANK-CHN', 'HDFC Current Account'], ['BANK-MDU', 'Indian Bank Current Account'], ['BANK-CBE', 'ICICI Current Account'], ['BANK-TRY', 'SBI Current Account'], ['BANK-SLM', 'Axis Current Account'],
            ], fn ($i) => ['bank_name' => ['HDFC Bank','Indian Bank','ICICI Bank','State Bank of India','Axis Bank'][$i], 'account_number' => '620000000'.($i + 1), 'ifsc' => ['HDFC0000123','IDIB000M001','ICIC0000456','SBIN0000789','UTIB0000321'][$i], 'branch' => ['Chennai','Madurai','Coimbatore','Trichy','Salem'][$i]]),
            'payment_method' => $this->rows([
                ['CASH', 'Cash'], ['BANK', 'Bank Transfer'], ['UPI', 'UPI'], ['CARD', 'Credit / Debit Card'], ['CHEQUE', 'Cheque'],
            ], fn ($i) => ['mode' => ['cash','bank','upi','card','cheque'][$i], 'requires_reference' => $i > 0, 'settlement_days' => [0,1,0,2,3][$i]]),
            'category' => $this->rows([
                ['RICE', 'Rice'], ['PULSES', 'Pulses'], ['MILLET-CAT', 'Millets'], ['FLOUR-CAT', 'Flour'], ['ORGANIC-CAT', 'Organic Foods'],
            ], fn ($i) => ['display_order' => $i + 1, 'catalog_visible' => true, 'color' => ['#8f0028','#c89216','#5e001b','#a77400','#287a4d'][$i]]),
            'brand' => $this->rows([
                ['CHOLAVIN', 'Cholavin'], ['GOLD-HARVEST', 'Gold Harvest'], ['ANNA-SELECT', 'Anna Select'], ['NATURE-CROP', 'Nature Crop'], ['ROYAL-GRAIN', 'Royal Grain'],
            ], fn ($i) => ['country' => 'India', 'manufacturer' => ['Cholavin Foods','Cholavin Foods','Anna Agro','Nature Crop Organics','Royal Grain Mills'][$i], 'display_order' => $i + 1]),
            'unit' => $this->rows([
                ['PCS', 'Pieces'], ['KG', 'Kilogram'], ['BAG', 'Bag'], ['BOX', 'Box'], ['TON', 'Metric Ton'],
            ], fn ($i) => ['symbol' => ['pcs','kg','bag','box','t'][$i], 'decimal_places' => [0,3,0,0,3][$i], 'base_factor' => 1]),
            'variant' => $this->rows([
                ['V-1KG', '1 Kilogram Pack'], ['V-5KG', '5 Kilogram Pack'], ['V-10KG', '10 Kilogram Pack'], ['V-25KG', '25 Kilogram Bag'], ['V-50KG', '50 Kilogram Bag'],
            ], fn ($i) => ['option_name' => 'Pack Size', 'option_value' => ['1 kg','5 kg','10 kg','25 kg','50 kg'][$i], 'weight_kg' => [1,5,10,25,50][$i]]),
            'grade' => $this->rows([
                ['GR-A1', 'Premium A1'], ['GR-A', 'Grade A'], ['GR-B', 'Grade B'], ['GR-C', 'Commercial Grade'], ['GR-ORG', 'Certified Organic'],
            ], fn ($i) => ['quality_rank' => $i + 1, 'inspection_required' => true, 'certification' => $i === 4 ? 'Organic' : 'Internal QC']),
            'price_list' => $this->rows([
                ['RETAIL', 'Retail Price'], ['WHOLESALE', 'Wholesale Price'], ['DISTRIBUTOR', 'Distributor Price'], ['INSTITUTION', 'Institutional Price'], ['FESTIVAL', 'Festival Offer Price'],
            ], fn ($i) => ['customer_type' => ['retail','wholesale','distributor','institution','promotional'][$i], 'discount_percentage' => [0,4,7,9,5][$i], 'currency' => 'INR']),
            'tax_rate' => $this->percentageRows([
                ['GST0', 'GST 0%', 0], ['GST5', 'GST 5%', 5], ['GST12', 'GST 12%', 12], ['GST18', 'GST 18%', 18], ['GST28', 'GST 28%', 28],
            ], fn ($rate) => ['cgst' => $rate / 2, 'sgst' => $rate / 2, 'igst' => $rate]),
            'hsn_sac' => $this->percentageRows([
                ['100630', 'Rice - Semi/Wholly Milled', 5], ['100610', 'Rice in Husk', 5], ['071390', 'Dried Pulses', 5], ['110100', 'Wheat or Meslin Flour', 5], ['100829', 'Other Millets', 5],
            ], fn ($rate, $i) => ['classification' => 'goods', 'gst_rate' => $rate, 'chapter' => substr(['100630','100610','071390','110100','100829'][$i], 0, 2)]),
            'customer_group' => $this->rows([
                ['RETAIL-CUS', 'Retail Customers'], ['WHOLE-CUS', 'Wholesale Customers'], ['DIST-CUS', 'Distributors'], ['HOTEL-CUS', 'Hotels and Restaurants'], ['INST-CUS', 'Institutions'],
            ], fn ($i) => ['credit_days' => [0,15,30,20,30][$i], 'default_discount' => [0,2,5,3,4][$i], 'price_list' => ['RETAIL','WHOLESALE','DISTRIBUTOR','INSTITUTION','INSTITUTION'][$i]]),
            'supplier_group' => $this->rows([
                ['FARM-SUP', 'Farmers'], ['MILL-SUP', 'Rice Mills'], ['PACK-SUP', 'Packaging Suppliers'], ['TRANS-SUP', 'Transport Vendors'], ['SERVICE-SUP', 'Service Providers'],
            ], fn ($i) => ['payment_days' => [7,15,30,15,30][$i], 'tds_applicable' => $i >= 3, 'category' => ['raw_material','manufacturer','packaging','logistics','service'][$i]]),
            'vehicle' => $this->rows([
                ['TN01AB1001', 'Tata Ace - Chennai'], ['TN58CD2002', 'Ashok Leyland Dost - Madurai'], ['TN37EF3003', 'Mahindra Bolero Pickup - Coimbatore'], ['TN45GH4004', 'Eicher Pro - Trichy'], ['TN30JK5005', 'Tata 407 - Salem'],
            ], fn ($i) => ['registration_number' => ['TN01AB1001','TN58CD2002','TN37EF3003','TN45GH4004','TN30JK5005'][$i], 'vehicle_type' => ['mini_truck','mini_truck','pickup','truck','truck'][$i], 'capacity_kg' => [750,1500,1700,3500,2500][$i], 'insurance_expiry' => '2027-03-31']),
            'driver' => $this->rows([
                ['DRV-001', 'Arun Kumar'], ['DRV-002', 'Bala Murugan'], ['DRV-003', 'Chandran R'], ['DRV-004', 'Dinesh Kumar'], ['DRV-005', 'Elango S'],
            ], fn ($i) => ['mobile' => '98765000'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), 'license_number' => 'TN'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT).'20200001234', 'license_expiry' => '2029-12-31', 'emergency_contact' => '90000000'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)]),
            'delivery_route' => $this->rows([
                ['RT-CHN-01', 'Chennai North Route'], ['RT-MDU-01', 'Madurai City Route'], ['RT-CBE-01', 'Coimbatore Industrial Route'], ['RT-TRY-01', 'Trichy Central Route'], ['RT-SLM-01', 'Salem City Route'],
            ], fn ($i) => ['origin' => ['Red Hills','Mattuthavani','SIDCO','Ariyamangalam','Omalur'][$i], 'destination' => ['Chennai North','Madurai City','Coimbatore City','Trichy City','Salem City'][$i], 'distance_km' => [45,32,38,29,35][$i], 'estimated_minutes' => [120,90,105,80,95][$i]]),
            'invoice_template' => $this->rows([
                ['TPL-STD', 'Standard Tax Invoice'], ['TPL-POS', 'POS Thermal Invoice'], ['TPL-WHOLE', 'Wholesale Invoice'], ['TPL-EXPORT', 'Export Invoice'], ['TPL-DELIVERY', 'Delivery Challan'],
            ], fn ($i) => ['paper_size' => ['A4','80mm','A4','A4','A4'][$i], 'theme' => ['corporate','thermal','compact','export','delivery'][$i], 'show_logo' => true, 'show_bank_details' => $i !== 1]),
            'number_sequence' => $this->rows([
                ['SEQ-SALE', 'Sales Invoice Sequence'], ['SEQ-PUR', 'Purchase Bill Sequence'], ['SEQ-PAY', 'Payment Sequence'], ['SEQ-TRF', 'Stock Transfer Sequence'], ['SEQ-VCH', 'Voucher Sequence'],
            ], fn ($i) => ['document_type' => ['sales_invoice','purchase_bill','payment','stock_transfer','voucher'][$i], 'prefix' => ['INV','PUR','PAY','TRF','VCH'][$i].'/26-27/', 'next_number' => 1, 'padding' => 5, 'reset_frequency' => 'financial_year']),
            'notification_template' => $this->rows([
                ['NT-PAY-DUE', 'Payment Due Reminder'], ['NT-LOW-STOCK', 'Low Stock Alert'], ['NT-DELIVERY', 'Delivery Status Update'], ['NT-EXPIRY', 'Batch Expiry Alert'], ['NT-INVOICE', 'Invoice Sharing'],
            ], fn ($i) => ['channel' => ['whatsapp','in_app','sms','email','email'][$i], 'subject' => ['Payment reminder','Low stock alert','Delivery update','Batch expiry alert','Invoice from Cholavin'][$i], 'body' => ['Dear {party}, payment {amount} is due for {document}.','{product} stock is {quantity}.','Your delivery {document} is {status}.','Batch {batch} of {product} expires on {date}.','Please find invoice {document} for {amount}.'][$i], 'variables' => [['party','amount','document'],['product','quantity'],['document','status'],['batch','product','date'],['document','amount']][$i]]),
        ];
    }

    private function seedProducts()
    {
        $lookup = fn (string $type, string $code) => ReferenceMaster::where(['type' => $type, 'code' => $code])->value('id');
        $data = [
            ['DEMO-RICE-001', '8906101000011', 'Cholavin Premium Ponni Rice', 'Premium aged Ponni boiled rice', '25 kg Bag', 1425, 1650, 100, 20, 'RICE', 'CHOLAVIN', 'V-25KG', 'GR-A1', 'BAG', 'GST5', '100630'],
            ['DEMO-RICE-002', '8906101000028', 'Cholavin Basmati Rice', 'Extra-long grain aromatic basmati rice', '10 kg Bag', 1180, 1395, 80, 15, 'RICE', 'GOLD-HARVEST', 'V-10KG', 'GR-A', 'BAG', 'GST5', '100630'],
            ['DEMO-PULSE-003', '8906101000035', 'Premium Toor Dal', 'Cleaned and graded protein-rich toor dal', '5 kg Pack', 690, 790, 60, 12, 'PULSES', 'ANNA-SELECT', 'V-5KG', 'GR-A', 'KG', 'GST5', '071390'],
            ['DEMO-MILLET-004', '8906101000042', 'Organic Little Millet', 'Certified organic little millet', '1 kg Pack', 92, 125, 50, 10, 'MILLET-CAT', 'NATURE-CROP', 'V-1KG', 'GR-ORG', 'KG', 'GST5', '100829'],
            ['DEMO-FLOUR-005', '8906101000059', 'Stone Ground Wheat Flour', 'Fresh stone-ground whole wheat flour', '5 kg Pack', 245, 310, 75, 15, 'FLOUR-CAT', 'ROYAL-GRAIN', 'V-5KG', 'GR-A', 'KG', 'GST5', '110100'],
        ];

        return collect($data)->map(function (array $row, int $index) use ($lookup) {
            return Product::updateOrCreate(['sku' => $row[0]], [
                'name' => $row[2], 'slug' => strtolower($row[0]), 'barcode' => $row[1],
                'category_id' => $lookup('category', $row[9]), 'brand_id' => $lookup('brand', $row[10]),
                'variant_id' => $lookup('variant', $row[11]), 'grade_id' => $lookup('grade', $row[12]),
                'unit_id' => $lookup('unit', $row[13]), 'tax_rate_id' => $lookup('tax_rate', $row[14]),
                'hsn_sac_id' => $lookup('hsn_sac', $row[15]), 'sub_title' => $row[3],
                'description' => $row[3].'. Quality checked and packed by Cholavin Foods.', 'image' => null,
                'price' => $row[6], 'purchase_price' => $row[5], 'sale_price' => $row[6],
                'opening_stock' => $row[7], 'reorder_level' => $row[8], 'unit' => $row[4],
                'is_active' => true, 'show_on_homepage' => $index < 3, 'sort_order' => $index + 1,
            ]);
        });
    }

    private function seedParties(Shop $shop): void
    {
        $customers = [
            ['CUS-001', 'Sri Lakshmi Supermarket', '9840010001', 'accounts@lakshmisupermarket.test', '33AAECS1001A1Z1', 'AAECS1001A', 100000, 12500, 'Chennai'],
            ['CUS-002', 'Madurai Annapoorna Stores', '9840010002', 'billing@annapoornastores.test', '33AAECS1002B1Z2', 'AAECS1002B', 75000, 8400, 'Madurai'],
            ['CUS-003', 'Kovai Fresh Mart', '9840010003', 'purchase@kovaifreshmart.test', '33AAECS1003C1Z3', 'AAECS1003C', 125000, 15200, 'Coimbatore'],
            ['CUS-004', 'Trichy Grand Hotel', '9840010004', 'stores@trichygrand.test', '33AAECS1004D1Z4', 'AAECS1004D', 150000, 21800, 'Tiruchirappalli'],
            ['CUS-005', 'Salem Community Canteen', '9840010005', 'admin@salemcanteen.test', '33AAECS1005E1Z5', 'AAECS1005E', 50000, 5600, 'Salem'],
        ];
        $suppliers = [
            ['SUP-001', 'Cauvery Delta Farmers', '9865010001', 'sales@cauveryfarmers.test', '33AAEFC2001A1Z1', 'AAEFC2001A', 200000, 25000, 'Thanjavur'],
            ['SUP-002', 'Vaigai Rice Mills', '9865010002', 'accounts@vaigairicemills.test', '33AAEFV2002B1Z2', 'AAEFV2002B', 250000, 32000, 'Madurai'],
            ['SUP-003', 'Kongu Agro Products', '9865010003', 'orders@konguagro.test', '33AAEFK2003C1Z3', 'AAEFK2003C', 180000, 18500, 'Erode'],
            ['SUP-004', 'Tamil Pack Solutions', '9865010004', 'sales@tamilpack.test', '33AAEFT2004D1Z4', 'AAEFT2004D', 100000, 9800, 'Chennai'],
            ['SUP-005', 'South India Logistics', '9865010005', 'billing@southlogistics.test', '33AAEFS2005E1Z5', 'AAEFS2005E', 75000, 7200, 'Salem'],
        ];

        $this->upsertPartyRows($shop, $customers, 'customer', 'customer_group', ['RETAIL-CUS','WHOLE-CUS','DIST-CUS','HOTEL-CUS','INST-CUS'], 'receivable');
        $this->upsertPartyRows($shop, $suppliers, 'supplier', 'supplier_group', ['FARM-SUP','MILL-SUP','PACK-SUP','TRANS-SUP','SERVICE-SUP'], 'payable');
    }

    private function upsertPartyRows(Shop $shop, array $rows, string $type, string $groupType, array $groups, string $balanceType): void
    {
        foreach ($rows as $index => $row) {
            $party = Party::updateOrCreate(['shop_id' => $shop->id, 'code' => $row[0]], [
                'group_id' => ReferenceMaster::where(['type' => $groupType, 'code' => $groups[$index]])->value('id'),
                'type' => $type, 'name' => $row[1], 'mobile' => $row[2], 'email' => $row[3],
                'gstin' => $row[4], 'pan' => $row[5], 'credit_limit' => $row[6],
                'opening_balance' => $row[7], 'balance_type' => $balanceType, 'is_active' => true,
            ]);
            $party->addresses()->updateOrCreate(['type' => 'billing', 'is_default' => true], [
                'contact_name' => $row[1], 'mobile' => $row[2],
                'address' => (10 + $index).', Market Main Road', 'city' => $row[8],
                'state' => 'Tamil Nadu', 'postal_code' => '6000'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'country' => 'India',
            ]);
        }
    }

    private function seedOpeningStock(Shop $shop, Godown $godown, $products): void
    {
        $userId = User::where('username', 'superadmin')->value('id') ?? User::value('id');
        if (! $userId) return;

        foreach ($products as $product) {
            $reference = 'SEED-OPEN-'.$product->sku;
            if (InventoryMovement::where('reference_number', $reference)->exists()) continue;
            InventoryBalance::firstOrCreate(
                ['shop_id' => $shop->id, 'godown_id' => $godown->id, 'product_id' => $product->id, 'batch_number' => 'OPENING'],
                ['expiry_date' => null, 'quantity' => $product->opening_stock, 'average_cost' => $product->purchase_price]
            );
            InventoryMovement::create([
                'shop_id' => $shop->id, 'godown_id' => $godown->id, 'product_id' => $product->id,
                'commercial_document_id' => null, 'type' => 'opening_stock', 'movement_date' => today(),
                'reference_number' => $reference, 'batch_number' => 'OPENING', 'expiry_date' => null,
                'quantity' => $product->opening_stock, 'rate' => $product->purchase_price,
                'value' => (float) $product->opening_stock * (float) $product->purchase_price,
                'notes' => 'Initial stock created by MasterDataSeeder.', 'created_by' => $userId,
            ]);
        }
    }

    private function rows(array $rows, callable $metadata): array
    {
        return collect($rows)->map(fn (array $row, int $index) => [
            'code' => $row[0], 'name' => $row[1],
            'description' => $row[1].' master record for Cholavin ERP.',
            'percentage' => null, 'metadata' => $metadata($index),
        ])->all();
    }

    private function percentageRows(array $rows, callable $metadata): array
    {
        return collect($rows)->map(fn (array $row, int $index) => [
            'code' => $row[0], 'name' => $row[1],
            'description' => $row[1].' classification used by Cholavin ERP.',
            'percentage' => $row[2], 'metadata' => $metadata($row[2], $index),
        ])->all();
    }
}
