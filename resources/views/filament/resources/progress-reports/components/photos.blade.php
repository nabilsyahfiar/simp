@php
    $record = $getRecord();
    $photos = $record?->photos ?? collect();
@endphp

@if ($photos->isEmpty())
    <div class="text-sm text-gray-500">No photos uploaded.</div>
@else
    <style>
        .report-photo-list {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-start;
        }

        .report-photo-thumb {
            display: block;
            width: 220px;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            cursor: zoom-in;
        }

        @media (max-width: 640px) {
            .report-photo-list {
                display: block;
            }

            .report-photo-thumb {
                width: 100%;
                height: 220px;
                margin-bottom: 12px;
            }
        }
    </style>

    <div x-data="{ open: false, active: '' }">
        <div class="report-photo-list">
            @foreach ($photos as $photo)
                <img
                    src="{{ route('report-photos.show', $photo) }}"
                    alt="Report photo"
                    title="{{ basename($photo->file_path) }}"
                    loading="lazy"
                    @click="active = '{{ route('report-photos.show', $photo) }}'; open = true"
                    class="report-photo-thumb"
                />
            @endforeach
        </div>

        <div
            x-show="open"
            x-transition.opacity
            @click="open = false"
            @keydown.escape.window="open = false"
            style="position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999;"
        >
            <img
                :src="active"
                alt="Preview photo"
                @click.stop
                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 92vw; max-height: 90vh; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);"
            />
        </div>
    </div>
@endif
