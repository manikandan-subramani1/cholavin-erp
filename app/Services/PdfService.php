<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfService
{
    public function reportDownload(
        string $view,
        array $data,
        string $filename,
        string $title,
        string $orientation = 'P',
    ): Response {
        $tempDirectory = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDirectory);

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $orientation,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 28,
            'margin_bottom' => 18,
            'tempDir' => $tempDirectory,
            'default_font' => 'dejavusans',
        ]);

        $company = $data['company'] ?? [];
        $generatedAt = $data['generatedAt'] ?? now();
        $pdf->SetTitle($title);
        $pdf->SetAuthor($company['name'] ?? config('app.name'));
        $pdf->SetHTMLHeader(view('pdf.partials.header', compact('company', 'title'))->render());
        $pdf->SetHTMLFooter(view('pdf.partials.footer', compact('generatedAt'))->render());
        $pdf->WriteHTML(view($view, $data)->render());

        return response($pdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function thermalDownload(
        string $view,
        array $data,
        string $filename,
        int $paperWidth,
        int $itemCount = 0,
    ): Response {
        $tempDirectory = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDirectory);

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [$paperWidth, max(170, 150 + ($itemCount * 12))],
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 8,
            'tempDir' => $tempDirectory,
            'default_font' => 'dejavusans',
        ]);

        $generatedAt = $data['receipt']['generated_at']->format('d-m-Y h:i A');
        $pdf->SetTitle($data['receipt']['title']);
        $pdf->SetAuthor($data['receipt']['company']['name']);
        $pdf->SetHTMLFooter('<div style="font-size:6.5pt;text-align:center;border-top:0.2mm solid #000;padding-top:1mm">Generated '.$generatedAt.' | Page {PAGENO}/{nbpg}</div>');
        $pdf->WriteHTML(view($view, $data)->render());

        return response($pdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
