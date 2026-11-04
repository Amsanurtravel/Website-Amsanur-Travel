@props(['count'])

@if ($count)
    <span {{ $attributes->merge(['class' => 'whitespace-nowrap text-gold']) }} role="img" aria-label="Bintang {{ $count }}">{{ str_repeat('★', $count) }}</span>
@endif
