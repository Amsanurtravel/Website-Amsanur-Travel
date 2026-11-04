{{-- Office opening hours from config('site.office.hours'). --}}
@if ($hours = config('site.office.hours'))
    <dl {{ $attributes->class('grid grid-cols-[auto_1fr] gap-x-4 gap-y-1') }}>
        @foreach ($hours as $row)
            <dt>{{ $row['days'] }}</dt>
            <dd class="font-semibold whitespace-nowrap">{{ $row['opens'] ? str_replace(':', '.', $row['opens']).'–'.str_replace(':', '.', $row['closes']).' WIB' : 'Tutup' }}</dd>
        @endforeach
    </dl>
@endif
