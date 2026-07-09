@props(['on' => false, 'disabled' => false])

<button
    type="button"
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'group inline-flex items-center gap-2 '.($disabled ? 'cursor-not-allowed opacity-50' : '')]) }}
>
    <span class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition {{ $on ? 'bg-brand-600' : 'bg-gray-200' }}">
        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition {{ $on ? 'translate-x-4' : 'translate-x-1' }}"></span>
    </span>
    <span class="text-xs {{ $on ? 'text-gray-800 font-medium' : 'text-gray-500' }} {{ $disabled ? '' : 'group-hover:text-gray-900' }}">
        {{ $slot }}
    </span>
</button>
