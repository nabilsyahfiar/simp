<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ $title }}</title>
        <style>
            * { font-family: DejaVu Sans, sans-serif; }
            body { font-size: 11px; color: #111827; }
            h1 { font-size: 16px; margin-bottom: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; vertical-align: top; }
            th { background: #f9fafb; font-weight: 600; }
            .thumb { width: 84px; height: 84px; object-fit: cover; border: 1px solid #d1d5db; border-radius: 4px; display: block; }
            .thumb-empty { color: #6b7280; font-size: 10px; }
        </style>
    </head>
    <body>
        <h1>{{ $title }}</h1>
        <table>
            <thead>
                <tr>
                    <th>Tanggal Laporan</th>
                    <th>Proyek</th>
                    <th>Unit</th>
                    <th>Mandor</th>
                    <th>Progres</th>
                    <th>Status</th>
                    <th>Jumlah Foto</th>
                    <th>Ada Foto</th>
                    <th>Thumbnail</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['report_date'] }}</td>
                        <td>{{ $row['project'] }}</td>
                        <td>{{ $row['unit'] }}</td>
                        <td>{{ $row['foreman'] }}</td>
                        <td>{{ $row['progress'] }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ $row['photos_count'] }}</td>
                        <td>{{ $row['has_photo'] }}</td>
                        <td>
                            @if ($row['thumbnail_data_uri'])
                                <img src="{{ $row['thumbnail_data_uri'] }}" alt="thumbnail" class="thumb">
                            @else
                                <span class="thumb-empty">Tidak ada foto</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
