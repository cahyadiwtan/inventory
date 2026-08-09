<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { font-family: 'Helvetica', 'Arial', sans-serif; }
        body { color: #1a2233; font-size: 11px; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .meta { color: #64748b; font-size: 10px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #1c2b52; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { border: 1px solid #e2e8f0; padding: 5px 8px; }
        tr:nth-child(even) td { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Dibuat: {{ now()->format('d M Y H:i') }} · {{ \App\Support\CompanyProfile::name() }}</div>
    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
