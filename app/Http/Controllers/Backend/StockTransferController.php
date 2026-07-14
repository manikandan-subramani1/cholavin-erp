<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;use App\Http\Controllers\Controller;use App\Http\Requests\Inventory\StockTransferRequest;use App\Models\Godown;use App\Models\StockTransfer;use App\Services\StockTransferService;use Illuminate\Http\JsonResponse;use Illuminate\Http\Request;use Illuminate\Support\Facades\Gate;use Illuminate\View\View;use Yajra\DataTables\Facades\DataTables;
class StockTransferController extends Controller
{
 public function index(Request $request):View|JsonResponse{Gate::authorize('stock.view');if($request->ajax())return DataTables::eloquent(StockTransfer::with(['fromGodown:id,name','toGodown:id,name'])->withCount('items')->forActiveShop())->addIndexColumn()->editColumn('transfer_date',fn($r)=>$r->transfer_date->format('d-m-Y'))->addColumn('route',fn($r)=>$r->fromGodown->name.' → '.$r->toGodown->name)->toJson();return view('backend.inventory.transfers',['godowns'=>Godown::where('shop_id',session('active_shop_id'))->where('is_active',true)->orderBy('name')->get(['id','name'])]);}
 public function store(StockTransferRequest $request,StockTransferService $service):JsonResponse{return ResponseHelper::success('Stock transfer created successfully.',$service->create($request->validated()),201);}
}
