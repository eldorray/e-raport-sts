@props(['label', 'name', 'value' => null])

<label for="{{ $name }}"
    {{ $attributes->merge(['class' => 'flex items-center text-sm text-zinc-700 dark:text-zinc-300']) }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}"
        {{ $attributes }} class="accent-accent h-4 w-4 mr-1">
    {{ $label }}
</label>

@error($name)
    <span class="text-red-500">{{ $message }}</span>
@enderror
