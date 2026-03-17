<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>{{ $title }}</title>
        <style>
            * { font-family: DejaVu Sans, sans-serif; }
            body { font-size: 11px; color: #374151; margin: 0; padding: 10px; }
            h1 { font-size: 16px; margin-bottom: 15px; color: #111827; text-align: center; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
            th { background-color: #1e293b; color: #ffffff; font-weight: bold; font-size: 10px; text-transform: uppercase; }
            tbody tr:nth-child(even) { background-color: #f8fafc; }
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
