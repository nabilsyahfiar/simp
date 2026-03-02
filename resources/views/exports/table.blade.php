<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>{{ $title }}</title>
        <style>
            * { font-family: DejaVu Sans, sans-serif; }
            body { font-size: 12px; color: #111827; }
            h1 { font-size: 16px; margin-bottom: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; vertical-align: top; }
            th { background: #f9fafb; font-weight: 600; }
        </style>
    </head>
    <body>
        <h1>{{ $title }}</h1>
        <table>
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
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
                    <tr>
                        <td colspan="{{ count($headers) }}">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
