<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mis módulos
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-9">

            <p class="text-sm text-gray-500">
                Revisa el contenido y el material de cada módulo antes de tu evaluación.
            </p>

            @forelse ($modulos->groupBy('categoria') as $categoria => $modulosDeCategoria)
                @php
                    $tema = match ($categoria) {
                        'RRHH' => ['punto' => 'bg-blue-400', 'borde' => 'border-t-blue-400'],
                        'Medicina' => ['punto' => 'bg-emerald-400', 'borde' => 'border-t-emerald-400'],
                        'Seguridad Industrial' => ['punto' => 'bg-orange-400', 'borde' => 'border-t-orange-400'],
                        default => ['punto' => 'bg-gray-300', 'borde' => 'border-t-gray-300'],
                    };
                @endphp
                <div class="space-y-3">
                    <h3 class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wide px-1">
                        <span class="h-1.5 w-1.5 rounded-full {{ $tema['punto'] }}"></span>
                        {{ $categoria }}
                    </h3>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        @foreach ($modulosDeCategoria as $modulo)
                            @php
                                $leido = $modulo->lecturas->isNotEmpty() && $modulo->lecturas->first()->leido_at;
                                $certificado = $modulo->certificados->first();
                                $ultimoIntento = $modulo->evaluacionIntentos->first();
                                $vencido = $modulo->fecha_limite && ! $leido && $modulo->fecha_limite->isPast();
                                $proximoAVencer = $modulo->fecha_limite && ! $leido && ! $vencido && now()->diffInDays($modulo->fecha_limite, false) <= 7;

                                $estado = match (true) {
                                    (bool) $certificado => ['Certificado', 'bg-purple-50 text-purple-700'],
                                    (bool) $ultimoIntento?->aprobado => ['Aprobado', 'bg-blue-50 text-blue-700'],
                                    (bool) $leido => ['Leído', 'bg-green-50 text-green-700'],
                                    $vencido => ['Vencido', 'bg-red-50 text-red-600'],
                                    $proximoAVencer => ['Vence pronto', 'bg-amber-50 text-amber-700'],
                                    default => ['Pendiente', 'bg-gray-100 text-gray-500'],
                                };

                                $preview = Str::limit(trim(strip_tags((string) $modulo->contenido)), 70);
                            @endphp
                            <a
                                href="{{ route('mis-modulos.show', $modulo) }}"
                                wire:navigate
                                class="group bg-white rounded-xl border border-gray-100 border-t-4 {{ $tema['borde'] }} shadow-sm hover:shadow-md hover:-translate-y-0.5 transition p-3 flex flex-col gap-2"
                            >
                                <div class="flex items-start justify-between gap-1">
                                    <x-category-icon :categoria="$modulo->categoria" class="h-8 w-8" />
                                    <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium shrink-0 {{ $estado[1] }}">
                                        {{ $estado[0] }}
                                    </span>
                                </div>

                                <h4 class="text-xs font-semibold text-gray-800 leading-snug group-hover:text-brand-700">{{ $modulo->titulo }}</h4>

                                <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2 flex-1">
                                    {{ $preview !== '' ? $preview : 'Sin contenido escrito.' }}
                                </p>

                                @if ($modulo->fecha_limite && ! $leido)
                                    <p class="text-[10px] text-gray-400">Vence {{ $modulo->fecha_limite->format('d/m/Y') }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center text-sm text-gray-400">
                    Todavía no hay módulos disponibles.
                </div>
            @endforelse
        </div>
    </div>
</div>
