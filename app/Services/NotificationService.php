<?php

namespace App\Services;

use App\Models\CommercialDocument;
use App\Models\Delivery;
use App\Models\InventoryBalance;
use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function refreshFor(User $user, int $shopId): int
    {
        $notifications = collect();

        InventoryBalance::query()
            ->with('product:id,name,reorder_level')
            ->accessibleBy($user)
            ->get()
            ->filter(fn (InventoryBalance $balance) => (float) $balance->quantity <= (float) $balance->product->reorder_level)
            ->each(fn (InventoryBalance $balance) => $notifications->push([
                'key' => 'low-stock-'.$balance->id,
                'type' => 'low_stock',
                'title' => 'Low stock: '.$balance->product->name,
                'message' => "Available {$balance->quantity}; reorder level {$balance->product->reorder_level}.",
                'data' => ['product_id' => $balance->product_id, 'godown_id' => $balance->godown_id],
            ]));

        InventoryBalance::query()
            ->with('product:id,name')
            ->accessibleBy($user)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->where('quantity', '>', 0)
            ->get()
            ->each(fn (InventoryBalance $balance) => $notifications->push([
                'key' => 'expiry-'.$balance->id,
                'type' => 'expiry',
                'title' => 'Batch expiry: '.$balance->product->name,
                'message' => "Batch {$balance->batch_number} expires on {$balance->expiry_date->format('d-m-Y')}.",
                'data' => ['product_id' => $balance->product_id, 'godown_id' => $balance->godown_id, 'batch_number' => $balance->batch_number],
            ]));

        CommercialDocument::query()
            ->with('party:id,name')
            ->accessibleBy($user)
            ->whereIn('type', ['sales_invoice', 'pos_invoice', 'purchase_bill'])
            ->where('status', 'posted')
            ->where('balance_amount', '>', 0)
            ->whereDate('due_date', '<', today())
            ->get()
            ->each(fn (CommercialDocument $document) => $notifications->push([
                'key' => 'payment-due-'.$document->id,
                'type' => 'payment_reminder',
                'title' => 'Payment overdue: '.$document->number,
                'message' => ($document->party?->name ?? 'Cash party').' has an outstanding balance of Rs. '.number_format((float) $document->balance_amount, 2).'.',
                'data' => ['commercial_document_id' => $document->id, 'party_id' => $document->party_id],
            ]));

        Delivery::query()
            ->with('document:id,number')
            ->forActiveShop()
            ->whereNotIn('status', ['delivered', 'returned'])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now()->addDay())
            ->get()
            ->each(fn (Delivery $delivery) => $notifications->push([
                'key' => 'delivery-'.$delivery->id,
                'type' => 'pending_delivery',
                'title' => 'Delivery pending: '.$delivery->document->number,
                'message' => 'Scheduled delivery is '.$delivery->status.'.',
                'data' => ['delivery_id' => $delivery->id, 'commercial_document_id' => $delivery->commercial_document_id],
            ]));

        foreach ($notifications as $notification) {
            UserNotification::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'shop_id' => $shopId,
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                ],
                [
                    'channel' => 'in_app',
                    'message' => $notification['message'],
                    'data' => $notification['data'] + ['key' => $notification['key']],
                    'sent_at' => now(),
                ]
            );
        }

        return $notifications->count();
    }
}
