<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-brand-700 via-brand-600 to-accent-500 hover:from-brand-800 hover:via-brand-700 hover:to-accent-600 text-white font-semibold text-sm rounded-lg shadow-sm hover:shadow-md active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-accent-400 focus:ring-offset-2 transition-all duration-150']) }}>
    {{ $slot }}
</button>
