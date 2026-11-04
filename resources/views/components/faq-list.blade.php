@props(['items', 'headingLevel' => 'h3'])

<div {{ $attributes->merge(['class' => 'divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white']) }}>
    @foreach ($items as $item)
        <details{!! isset($item['id']) ? ' id="'.e($item['id']).'"' : '' !!}{{ ($item['open'] ?? false) ? ' open' : '' }} class="faq-item group scroll-mt-6">
            <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left">
                <{{ $headingLevel }} class="font-semibold text-brand-dark">{{ $item['q'] }}</{{ $headingLevel }}>
                <x-site-icon name="chevron-right" class="faq-chevron size-5 text-gold-dark transition-transform" />
            </summary>
            <div class="px-5 pb-5 leading-relaxed text-slate-700">{{ $item['a'] }}</div>
        </details>
    @endforeach
</div>
