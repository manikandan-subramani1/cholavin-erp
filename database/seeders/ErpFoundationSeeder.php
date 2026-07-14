<?php

namespace Database\Seeders;

use App\Models\LedgerAccount;
use App\Models\ReferenceMaster;
use Illuminate\Database\Seeder;

class ErpFoundationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['payment_method', 'CASH', 'Cash'], ['payment_method', 'BANK', 'Bank Transfer'], ['payment_method', 'UPI', 'UPI'], ['unit', 'PCS', 'Pieces'], ['unit', 'KG', 'Kilogram'], ['tax_rate', 'GST0', 'GST 0%', 0], ['tax_rate', 'GST5', 'GST 5%', 5], ['tax_rate', 'GST12', 'GST 12%', 12], ['tax_rate', 'GST18', 'GST 18%', 18]] as $r) ReferenceMaster::updateOrCreate(['type' => $r[0], 'code' => $r[1]], ['name' => $r[2], 'percentage' => $r[3] ?? null, 'is_active' => true]);
        foreach ([['asset', 'CASH', 'Cash in Hand', 'debit'], ['asset', 'BANK', 'Bank Account', 'debit'], ['asset', 'AR', 'Accounts Receivable', 'debit'], ['liability', 'AP', 'Accounts Payable', 'credit'], ['income', 'SALES', 'Sales', 'credit'], ['income', 'OTHER_INCOME', 'Other Income', 'credit'], ['expense', 'SALES_RETURN', 'Sales Returns', 'debit'], ['expense', 'PURCHASE', 'Purchases', 'debit'], ['income', 'PURCHASE_RETURN', 'Purchase Returns', 'credit'], ['expense', 'EXPENSE', 'General Expenses', 'debit']] as $a) LedgerAccount::updateOrCreate(['shop_id' => null, 'code' => $a[1]], ['type' => $a[0], 'name' => $a[2], 'balance_type' => $a[3], 'is_active' => true]);
    }
}
