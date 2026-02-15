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

        .report-photo-item {
            position: relative;
            width: 220px;
            height: 180px;
            border-radius: 10px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.04);
        }

        .report-photo-skeleton {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0.06) 25%,
                rgba(255, 255, 255, 0.14) 37%,
                rgba(255, 255, 255, 0.06) 63%
            );
            background-size: 400% 100%;
            animation: report-photo-shimmer 1.2s ease-in-out infinite;
        }

        .report-photo-error {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 12px;
            color: rgb(156 163 175);
            background: rgba(255, 255, 255, 0.03);
            padding: 8px;
        }

        @@keyframes report-photo-shimmer {
            0% {
                background-position: 100% 0;
            }
            100% {
                background-position: 0 0;
            }
        }

        @@media (max-width: 640px) {
            .report-photo-list {
                display: block;
            }

            .report-photo-item,
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
                @php
                    $photoUrl = route('report-photos.show', $photo);
                @endphp
                <div class="report-photo-item" x-data="{ loaded: false, failed: false }">
                    <div x-show="!loaded && !failed" class="report-photo-skeleton"></div>
                    <div x-show="failed" class="report-photo-error">Failed to load photo</div>
                    <img
                        src="{{ $photoUrl }}"
                        alt="Report photo"
                        title="{{ basename($photo->file_path) }}"
                        loading="lazy"
                        x-on:load="loaded = true"
                        x-on:error="failed = true"
                        x-on:click="if (!failed) { active = {{ \Illuminate\Support\Js::from($photoUrl) }}; open = true }"
                        class="report-photo-thumb"
                        x-show="loaded && !failed"
                        x-transition.opacity.duration.200ms
                    />
                </div>
            @endforeach
        </div>

        <div
            x-show="open"
            x-transition.opacity
            x-on:click="open = false"
            x-on:keydown.escape.window="open = false"
            style="position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999;"
        >
            <img
                :src="active"
                alt="Preview photo"
                x-on:click.stop
                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 92vw; max-height: 90vh; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);"
            />
        </div>
    </div>
@endif
