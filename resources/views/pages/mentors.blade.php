<x-layouts.site
    title="Pembimbing Ibadah"
    :description="'Mengenal '.$mentors->pluck('name')->implode(', ').', pembimbing ibadah umroh dan haji khusus '.config('site.brand').'.'.($mentors->first()->affiliation ? ' '.$mentors->first()->affiliation.'.' : '')"
    :canonical="route('mentors')"
    :schema="$schema"
>
    <div class="mx-auto max-w-6xl px-4 py-8 md:py-12">
        <x-breadcrumbs :trail="[['Beranda', route('home')]]" />

        @foreach ($mentors as $mentor)
            {{-- Phone: title, photo, text. From md: photo on the left, title and text on the right. --}}
            <article id="pembimbing-{{ $mentor->id }}" @class([
                'grid scroll-mt-6 gap-x-12 gap-y-6 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] md:grid-rows-[auto_1fr]',
                'mt-6' => $loop->first,
                'mt-16 border-t border-slate-200 pt-12' => ! $loop->first,
            ])>
                <header class="md:col-start-2 md:row-start-1">
                    @if ($loop->first)
                        <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Pembimbing ibadah</p>
                        <h1 class="mt-2 font-serif text-3xl leading-tight font-bold text-brand-dark md:text-4xl">Mengenal Pembimbing Perjalanan Ibadah Spiritual Anda</h1>
                    @endif
                    <h2 @class(['font-serif text-2xl font-bold text-brand-dark', 'mt-5' => $loop->first])>{{ $mentor->name }}</h2>
                    <p class="mt-1 font-semibold text-gold-dark">{{ $mentor->role }}</p>
                    @if ($mentor->affiliation)
                        <p class="mt-1 text-slate-600">{{ $mentor->affiliation }}</p>
                    @endif
                </header>

                <div class="md:col-start-1 md:row-span-2 md:row-start-1">
                    @if ($mentor->photoUrl())
                        <img src="{{ $mentor->photoUrl() }}" alt="Foto {{ $mentor->name }}" width="{{ $mentor->photo_width }}" height="{{ $mentor->photo_height }}" @unless ($loop->first) loading="lazy" @endunless decoding="async" class="mx-auto aspect-[4/5] w-full max-w-xs rounded-3xl bg-cream object-cover md:max-w-none">
                    @else
                        {{-- Placeholder until a photo is uploaded in the admin (Pembimbing). --}}
                        <div class="bg-pattern mx-auto flex aspect-[4/5] w-full max-w-xs items-center justify-center rounded-3xl md:max-w-none" aria-hidden="true">
                            <span class="font-serif text-7xl font-bold text-gold-light md:text-8xl">{{ $mentor->initials() }}</span>
                        </div>
                    @endif
                </div>

                <div class="md:col-start-2 md:row-start-2">
                    <div class="space-y-4 text-lg leading-relaxed text-slate-700">
                        @forelse ($mentor->profileParagraphs() as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @empty
                            @if ($mentor->bio)
                                <p>{{ $mentor->bio }}</p>
                            @endif
                        @endforelse
                    </div>

                    @if ($loop->first)
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('umrah.index') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-brand px-6 font-semibold text-white hover:bg-brand-dark">Lihat jadwal umroh <x-site-icon name="arrow-right" class="size-4" /></a>
                            <x-whatsapp-link class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-wa px-6 font-semibold text-white hover:bg-wa-dark">
                                <x-site-icon name="whatsapp" /> Tanya lewat WhatsApp
                            </x-whatsapp-link>
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</x-layouts.site>
