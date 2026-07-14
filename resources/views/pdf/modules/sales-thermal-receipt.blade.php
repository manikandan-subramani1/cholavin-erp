<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        *{box-sizing:border-box}body{margin:0;color:#000;font-family:dejavusans,sans-serif;font-size:7.5pt;line-height:1.25}.thermal-receipt{width:100%}.receipt-header{text-align:center}.receipt-header h1{font-size:13pt;margin:0 0 1mm}.receipt-header h2{font-size:9pt;margin:2mm 0 1mm;padding:1mm 0;border-top:.2mm dashed #000;border-bottom:.2mm dashed #000;text-transform:uppercase}.receipt-meta{padding-bottom:1mm;border-bottom:.2mm dashed #000}.receipt-meta div,.receipt-totals div{width:100%;clear:both}.receipt-meta span,.receipt-totals span{float:left}.receipt-meta strong,.receipt-totals strong{float:right;text-align:right}.receipt-items{width:100%;border-collapse:collapse;table-layout:fixed;margin-top:1mm}.receipt-items th,.receipt-items td{padding:.8mm .3mm;vertical-align:top}.receipt-items th{border-bottom:.2mm solid #000;text-align:left}.receipt-items th:first-child,.receipt-items td:first-child{width:42%}.receipt-items .number{text-align:right;white-space:nowrap}.record-count{text-align:right;border-top:.2mm dashed #000;padding:1mm 0}.receipt-totals{margin-left:15%;width:85%}.receipt-totals .grand-total{font-size:9pt;margin:1mm 0;padding:1mm 0;border-top:.2mm solid #000;border-bottom:.5mm double #000}.receipt-footer{text-align:center;border-top:.2mm dashed #000;margin-top:2mm;padding-top:1.5mm}.receipt-footer p{margin:0 0 1mm}
    </style>
</head>
<body>@include('backend.sales.thermal-receipt.partials.receipt')</body>
</html>
