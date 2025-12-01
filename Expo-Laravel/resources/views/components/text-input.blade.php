@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge([
        'class' => '
            w-full px-3 py-2
            bg-black text-white
            border border-zinc-700
            focus:border-red-500 focus:ring-red-500
            rounded-none
            placeholder-zinc-500
        '
    ]) !!}
/>
