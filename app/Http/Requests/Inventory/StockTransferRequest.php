<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTransferRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('stock.transfer') ?? false; }
    public function rules(): array
    {
        $shop=(int)session('active_shop_id');
        return ['from_godown_id'=>['required',Rule::exists('godowns','id')->where('shop_id',$shop)->where('is_active',true)],'to_godown_id'=>['required','different:from_godown_id',Rule::exists('godowns','id')->where('shop_id',$shop)->where('is_active',true)],'transfer_date'=>['required','date'],'status'=>['required',Rule::in(['draft','completed'])],'notes'=>['nullable','string','max:2000'],'items'=>['required','array','min:1'],'items.*.product_id'=>['required','exists:products,id'],'items.*.quantity'=>['required','numeric','gt:0'],'items.*.batch_number'=>['nullable','string','max:80']];
    }
}
