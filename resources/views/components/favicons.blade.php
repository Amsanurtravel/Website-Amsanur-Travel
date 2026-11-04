@php
    // Versioned by file time so browsers fetch the new icon when the file is replaced.
    $icon = fn (string $file): string => asset($file).'?v='.(is_file(public_path($file)) ? filemtime(public_path($file)) : 0);
@endphp
<link rel="icon" href="{{ $icon('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ $icon('apple-touch-icon.png') }}">
