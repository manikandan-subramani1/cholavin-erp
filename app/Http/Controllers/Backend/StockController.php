<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Setting;
use App\Services\PdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('stock.view');$query=InventoryBalance::query()->with(['product:id,name,sku,reorder_level','godown:id,name'])->accessibleBy($request->user())->when($request->filled('low_stock'),fn($q)=>$q->whereColumn('quantity','<=',
            \DB::raw('(select reorder_level from products where products.id = inventory_balances.product_id)')));
        if($request->ajax())return DataTables::eloquent($query)->addIndexColumn()->addColumn('product_name',fn($r)=>$r->product->name)->addColumn('sku',fn($r)=>$r->product->sku)->addColumn('godown_name',fn($r)=>$r->godown->name)->editColumn('quantity',fn($r)=>number_format((float)$r->quantity,3))->editColumn('average_cost',fn($r)=>number_format((float)$r->average_cost,2))->addColumn('value',fn($r)=>number_format((float)$r->quantity*(float)$r->average_cost,2))->addColumn('status',fn($r)=>'<span class="badge bg-'.((float)$r->quantity<=(float)$r->product->reorder_level?'danger':'success').'">'.((float)$r->quantity<=(float)$r->product->reorder_level?'Low':'Available').'</span>')->rawColumns(['status'])->toJson();
        return view('backend.inventory.stock');
    }
    public function movements(Request $request): JsonResponse
    {
        Gate::authorize('stock.view');$query=InventoryMovement::query()->with(['product:id,name,sku'])->accessibleBy($request->user())->when($request->filled('product_id'),fn($q)=>$q->where('product_id',$request->integer('product_id')))->when($request->filled('from_date'),fn($q)=>$q->whereDate('movement_date','>=',$request->date('from_date')))->when($request->filled('to_date'),fn($q)=>$q->whereDate('movement_date','<=',$request->date('to_date')));
        return DataTables::eloquent($query)->addIndexColumn()->addColumn('product_name',fn($r)=>$r->product->name)->editColumn('movement_date',fn($r)=>$r->movement_date->format('d-m-Y'))->toJson();
    }
    public function pdf(Request $request,PdfService $pdf):Response{Gate::authorize('stock.export');$records=InventoryBalance::with(['product','godown'])->accessibleBy($request->user())->get();$s=Setting::values();return $pdf->reportDownload('pdf.modules.stock',['records'=>$records,'company'=>['name'=>$s['company_name']??'Cholavin','address'=>$s['company_address']??''],'generatedAt'=>now()],'stock-'.now()->format('Ymd-His').'.pdf','Stock Report','L');}
}
