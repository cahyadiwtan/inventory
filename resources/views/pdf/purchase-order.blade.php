<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->number }}</title>
    <style>
        * { font-family: 'Helvetica', 'Arial', sans-serif; box-sizing: border-box; }
        body { color: #0b1c30; font-size: 11px; margin: 0; padding: 32px; }
        .pl-company { border-bottom: 2px solid #0b1c30; padding-bottom: 10px; margin-bottom: 16px; }
        .pl-company-name { font-size: 18px; font-weight: 700; color: #0b1c30; }
        .pl-company-addr, .pl-company-contact { font-size: 10px; color: #45474c; margin-top: 2px; }
        .pl-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
        .pl-title { font-size: 20px; font-weight: 700; color: #0b1c30; text-transform: uppercase; letter-spacing: 0.12em; }
        .pl-meta { font-size: 11px; text-align: right; }
        .pl-meta div { margin-bottom: 2px; }
        .pl-meta span { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        table th { border-bottom: 2px solid #0b1c30; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
        table td { border-bottom: 1px solid #c5c6cd; padding: 6px 8px; }
        table th.r, table td.r { text-align: right; }
        .pl-summary { margin-left: auto; width: 260px; font-size: 11px; margin-top: 12px; }
        .pl-summary .row { display: flex; justify-content: space-between; padding: 3px 0; }
        .pl-summary .row.total { border-top: 2px solid #0b1c30; margin-top: 4px; padding-top: 6px; font-weight: 700; font-size: 13px; }
        .pl-notes { margin-top: 16px; font-size: 11px; color: #45474c; }
        .pl-notes b { text-transform: uppercase; font-size: 9px; }
        .pl-sign { display: flex; gap: 48px; margin-top: 48px; }
        .pl-sign .col { flex: 1; }
        .pl-sign .lbl { font-size: 10px; text-transform: uppercase; color: #45474c; margin-bottom: 28px; }
        .pl-sign .line { border-bottom: 1px solid #94a3b8; height: 32px; }
        .pl-sign .sub { font-size: 10px; color: #45474c; margin-top: 4px; }
    </style>
</head>
<body>
    @include('pdf.partials.laser-purchase-order', ['purchaseOrder' => $purchaseOrder, 'company' => $company, 'logo' => $logo ?? null])
</body>
</html>
