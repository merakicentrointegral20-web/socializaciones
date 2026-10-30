@php
    $empresa = config('xr_rrhh.empresa_nombre', 'MERAKI');
    $candidatos = [
        config('xr_rrhh.empresa_logo_web'),
        'img/logo.jpg',
        'img/logo.png',
        'img/logo.webp',
        'logo.jpg',
    ];
    $logoWeb = null;
    foreach ($candidatos as $candidato) {
        if ($candidato && file_exists(public_path($candidato))) {
            $logoWeb = $candidato;
            break;
        }
    }
@endphp

@if ($logoWeb)
    <img src="{{ asset($logoWeb) }}" {{ $attributes->merge(['class' => 'h-10 w-auto object-contain rounded-lg', 'alt' => $empresa]) }}>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-[#681878] via-[#791a88] to-[#e6007e] flex items-center justify-center text-white font-bold text-lg shadow-sm">
            {{ strtoupper(substr($empresa, 0, 1)) }}
        </div>
        <div class="flex flex-col leading-none">
            <span class="font-extrabold text-lg tracking-tight text-[#681878]">{{ $empresa }}</span>
            <span class="text-[9px] font-semibold tracking-widest text-[#e6007e] uppercase">Capacitación</span>
        </div>
    </div>
@endif
