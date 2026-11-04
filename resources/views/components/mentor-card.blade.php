@props(['mentor', 'headingLevel' => 'h3'])

{{-- The whole card opens the mentor's full profile in a new tab (client's request, November 2026). --}}
<a href="{{ route('mentors') }}#pembimbing-{{ $mentor->id }}" target="_blank" rel="noopener" class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-5 hover:border-gold hover:shadow-md">
    @if ($mentor->photoUrl())
        <img src="{{ $mentor->photoUrl() }}" alt="Foto {{ $mentor->name }}" width="{{ $mentor->photo_width }}" height="{{ $mentor->photo_height }}" loading="lazy" decoding="async" class="size-20 shrink-0 rounded-full object-cover">
    @else
        <span class="flex size-20 shrink-0 items-center justify-center rounded-full bg-brand-dark font-serif text-2xl font-bold text-gold-light" aria-hidden="true">{{ $mentor->initials() }}</span>
    @endif

    <div>
        <p class="text-xs font-bold tracking-wide text-gold-dark uppercase">{{ $mentor->role }}</p>
        <{{ $headingLevel }} class="mt-1 font-serif text-lg leading-snug font-bold text-brand-dark">{{ $mentor->name }}</{{ $headingLevel }}>
        @if ($mentor->affiliation)
            <p class="mt-1 text-sm text-slate-600">{{ $mentor->affiliation }}</p>
        @endif
        @if ($mentor->bio)
            <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $mentor->bio }}</p>
        @endif
        <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand group-hover:underline">Lihat profil lengkap <x-site-icon name="external" class="size-4" /><span class="sr-only">(membuka tab baru)</span></span>
    </div>
</a>
