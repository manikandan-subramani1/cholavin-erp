<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\ThermalReceiptRequest;
use App\Services\PdfService;
use App\Services\ThermalReceiptService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ThermalReceiptController extends Controller
{
    public function index(ThermalReceiptRequest $request, ThermalReceiptService $receipts): View
    {
        return view('backend.sales.thermal-receipt.index', [
            'receipt' => $receipts->sample($request->user()),
            'paperWidth' => $request->paperWidth(),
        ]);
    }

    public function downloadPdf(ThermalReceiptRequest $request, ThermalReceiptService $receipts, PdfService $pdf): Response
    {
        $receipt = $receipts->sample($request->user());

        return $pdf->thermalDownload(
            'pdf.modules.sales-thermal-receipt',
            ['receipt' => $receipt, 'paperWidth' => $request->paperWidth()],
            'sales-receipt-'.$receipt['invoice_number'].'.pdf',
            $request->paperWidth(),
            count($receipt['items']),
        );
    }
}
