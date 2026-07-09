@props(['model', 'value' => ''])

<div
    wire:ignore
    x-data="{}"
    x-init="
        let editor = new Quill($refs.editor, {
            theme: 'snow',
            placeholder: 'Escribe el contenido del módulo...',
            modules: {
                toolbar: [['bold', 'italic'], ['link'], [{ list: 'ordered' }, { list: 'bullet' }]],
            },
        });
        editor.root.innerHTML = @js($value);
        editor.on('text-change', () => $wire.set('{{ $model }}', editor.root.innerHTML, false));
    "
    {{ $attributes }}
>
    <div x-ref="editor" style="min-height: 260px;" class="bg-white text-sm"></div>
</div>
