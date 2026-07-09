@props(['categoria'])

@php
    $config = match ($categoria) {
        'RRHH' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
        'Medicina' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'Seguridad Industrial' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600'],
        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-500'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-xl shrink-0 {$config['bg']} {$config['text']}"]) }}>
    @switch($categoria)
        @case('RRHH')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                <circle cx="9" cy="8" r="3" />
                <path stroke-linecap="round" d="M3.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5" />
                <circle cx="17" cy="9" r="2.3" />
                <path stroke-linecap="round" d="M14.8 13.2c2.4.2 4.2 2 4.2 4.4" />
            </svg>
            @break

        @case('Medicina')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                <circle cx="12" cy="12" r="8.25" />
                <path stroke-linecap="round" d="M12 8.5v7M8.5 12h7" />
            </svg>
            @break

        @case('Seguridad Industrial')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5l6.5 2.6v5.2c0 4-2.7 7.3-6.5 8.2-3.8-.9-6.5-4.2-6.5-8.2V6.1L12 3.5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.3 12.2l1.8 1.8 3.6-3.8" />
            </svg>
            @break

        @default
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                <circle cx="12" cy="12" r="8.25" />
            </svg>
    @endswitch
</span>
