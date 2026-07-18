<?php

namespace App\Http\Requests\Delivery;

use App\Http\Requests\Concerns\ValidatesBusinessContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DeliveryRequest extends FormRequest
{
    use ValidatesBusinessContext;

    public function authorize(): bool
    {
        return $this->user()?->can('deliveries.'.($this->route('delivery') ? 'update' : 'create')) ?? false;
    }

    public function rules(): array
    {
        $shopId = (int) session('active_shop_id');

        return [
            'commercial_document_id' => ['required', Rule::exists('commercial_documents', 'id')->where('shop_id', $shopId)->where('financial_year_id', session('active_financial_year_id'))->whereIn('type', ['sales_invoice', 'delivery_challan', 'pos_invoice'])],
            'vehicle_id' => ['nullable', Rule::exists('reference_masters', 'id')->where('type', 'vehicle')],
            'driver_id' => ['nullable', Rule::exists('reference_masters', 'id')->where('type', 'driver')],
            'route_id' => ['nullable', Rule::exists('reference_masters', 'id')->where('type', 'delivery_route')],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(['pending', 'assigned', 'out_for_delivery', 'delivered', 'failed', 'returned'])],
            'scheduled_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],
            'cash_collected' => ['nullable', 'numeric', 'min:0'],
            'delivery_expense' => ['nullable', 'numeric', 'min:0'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateBusinessContext($validator)];
    }
}
