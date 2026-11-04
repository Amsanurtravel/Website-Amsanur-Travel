{{-- Pilgrim photos from config('site.gallery'); below the fold, so lazy-loaded. --}}
@props(['limit' => null, 'sizes' => '(min-width: 1152px) 280px, (min-width: 768px) 25vw, 50vw'])

@if ($photos = array_slice(config('site.gallery', []), 0, $limit))
    <ul {{ $attributes->class('grid grid-cols-2 gap-3 md:gap-4') }}>
        @foreach ($photos as $photo)
            <li>
                <figure>
                    <img
                        src="{{ asset('images/jemaah/'.$photo['file'].'-480.webp') }}"
                        srcset="{{ asset('images/jemaah/'.$photo['file'].'-480.webp') }} 480w, {{ asset('images/jemaah/'.$photo['file'].'-960.webp') }} {{ $photo['width'] }}w"
                        sizes="{{ $sizes }}"
                        width="{{ $photo['width'] }}"
                        height="{{ $photo['height'] }}"
                        alt="{{ $photo['alt'] }}"
                        loading="lazy"
                        decoding="async"
                        class="aspect-[3/2] w-full rounded-xl bg-slate-100 object-cover"
                    >
                    <figcaption class="mt-1.5 text-sm leading-snug text-slate-600">{{ $photo['caption'] }}</figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
@endif
