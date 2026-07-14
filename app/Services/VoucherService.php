<?php
namespace App\Services;
use App\Models\Voucher;use Illuminate\Support\Facades\DB;
class VoucherService{public function create(array $data):Voucher{return DB::transaction(function()use($data){$lines=$data['lines'];unset($data['lines']);$total=collect($lines)->sum(fn($x)=>(float)($x['debit']??0));$number='VCH-'.now()->format('ym').'-'.str_pad((string)((int)Voucher::where('shop_id',session('active_shop_id'))->lockForUpdate()->max('id')+1),5,'0',STR_PAD_LEFT);$v=Voucher::create($data+['shop_id'=>session('active_shop_id'),'number'=>$number,'total_debit'=>$total,'total_credit'=>$total,'created_by'=>auth()->id()]);$v->lines()->createMany($lines);return $v->load('lines.account');});}}
