<?php

namespace App\Services;

use App\Models\Delivery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeliveryService
{
    public function save(array $data, ?Delivery $delivery = null, ?UploadedFile $proof = null): Delivery
    {
        return DB::transaction(function () use ($data, $delivery, $proof) {
            $delivery ??= new Delivery(['shop_id' => session('active_shop_id')]);

            if ($proof) {
                if ($delivery->proof_path) {
                    Storage::disk('public')->delete($delivery->proof_path);
                }
                $data['proof_path'] = $proof->store('delivery-proofs/'.now()->format('Y/m'), 'public');
            }
            if (($data['status'] ?? null) === 'delivered' && empty($data['delivered_at'])) {
                $data['delivered_at'] = now();
            }
            if (($data['status'] ?? null) !== 'delivered') {
                $data['delivered_at'] = null;
            }

            $delivery->fill($data)->save();

            return $delivery->refresh()->load(['document.party', 'vehicle', 'driver', 'route']);
        });
    }
}
