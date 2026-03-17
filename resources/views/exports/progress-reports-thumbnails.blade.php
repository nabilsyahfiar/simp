<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>{{ $title }}</title>
        <style>
            * { font-family: DejaVu Sans, sans-serif; }
            body { font-size: 10px; color: #374151; margin: 0; padding: 10px; }
            h1 { font-size: 16px; margin-bottom: 15px; color: #111827; text-align: center; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; vertical-align: middle; }
            th { background-color: #1e293b; color: #ffffff; font-weight: bold; font-size: 9px; text-transform: uppercase; }
            tbody tr:nth-child(even) { background-color: #f8fafc; }
            .thumb { width: 64px; height: 64px; object-fit: cover; border: 1px solid #e5e7eb; border-radius: 4px; display: block; margin: 0 auto; }
            .thumb-empty { color: #9ca3af; font-size: 9px; font-style: italic; text-align: center; display: block; }
            .center-align { text-align: center; vertical-align: middle; }
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
                        <td class="center-align">{{ $row['photos_count'] }}</td>
                        <td class="center-align">{{ $row['has_photo'] }}</td>
                        <td class="center-align">
                            @if ($row['thumbnail_data_uri'])
                                <img src="{{ $row['thumbnail_data_uri'] }}" alt="thumbnail" class="thumb">
                            @else
                                <span class="thumb-empty">Tidak ada foto</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
