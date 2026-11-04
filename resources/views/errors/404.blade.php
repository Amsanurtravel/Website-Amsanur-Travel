<x-layouts.site title="Halaman tidak ditemukan" noindex>
    <section class="mx-auto max-w-3xl px-4 py-16 text-center md:py-24">
        <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Halaman 404</p>
        <h1 class="mt-2 font-serif text-3xl font-bold text-brand-dark md:text-4xl">Halaman tidak ditemukan</h1>
        <p class="mx-auto mt-4 max-w-xl leading-relaxed text-slate-700">Alamat yang Anda buka mungkin salah ketik, atau paketnya sudah tidak tersedia. Silakan lihat jadwal terbaru atau tanyakan langsung kepada kami.</p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('umrah.index') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-brand px-6 font-semibold text-white hover:bg-brand-dark">Lihat jadwal umroh <x-site-icon name="arrow-right" class="size-4" /></a>
            <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border-2 border-brand px-6 font-semibold text-brand hover:bg-brand hover:text-white">Ke beranda</a>
        </div>
    </section>
</x-layouts.site>
