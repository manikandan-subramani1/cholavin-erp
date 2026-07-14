<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Shop;
use App\Models\User;

class ThermalReceiptService
{
    /**
     * Provide the first receipt sample. A real Sale can later be mapped to this
     * same array contract without changing either print layout.
     */
    public function sample(User $user): array
    {
        $settings = Setting::values();
        $shop = session('active_shop_id')
            ? Shop::query()->select(['id', 'name', 'code', 'address'])->find(session('active_shop_id'))
            : null;

        $items = collect([
            ['name' => 'Premium Ponni Rice 5 kg', 'quantity' => 2, 'rate' => 425.00],
            ['name' => 'Seeraga Samba Rice 1 kg', 'quantity' => 1, 'rate' => 190.00],
            ['name' => 'Idli Rice 5 kg', 'quantity' => 1, 'rate' => 310.00],
        ])->map(function (array $item): array {
            $item['amount'] = round($item['quantity'] * $item['rate'], 2);

            return $item;
        })->all();

        $subtotal = collect($items)->sum('amount');
        $discount = 50.00;
        $tax = 0.00;
        $roundOff = 0.00;
        $grandTotal = round($subtotal - $discount + $tax + $roundOff, 2);

        return [
            'company' => [
                'name' => $settings['company_name'] ?? 'Cholavin',
                'tagline' => $settings['tagline'] ?? 'Quality you can trust',
                'address' => $shop?->address ?: ($settings['company_address'] ?? 'Company address'),
                'branch' => $shop?->name,
                'phone' => $settings['contact_phone'] ?? null,
                'email' => $settings['contact_email'] ?? null,
            ],
            'title' => 'Sales Receipt - Sample',
            'invoice_number' => 'SAMPLE-0001',
            'sold_at' => now(),
            'generated_at' => now(),
            'cashier' => $user->name,
            'customer' => ['name' => 'Walk-in Customer', 'mobile' => '9876543210'],
            'items' => $items,
            'totals' => [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'round_off' => $roundOff,
                'grand_total' => $grandTotal,
                'paid' => $grandTotal,
                'balance' => 0.00,
            ],
            'payment_mode' => 'Cash',
            'notes' => 'This is a sample receipt. Thank you for shopping with us!',
        ];
    }
}
