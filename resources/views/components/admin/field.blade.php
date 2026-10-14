@props(['label', 'name', 'hint' => null])

<label class="block">
    <span class="mb-1 block text-sm font-medium">{{ $label }}</span>
    {{ $slot }}
    @if ($hint)
        <span class="mt-1 block text-xs text-black/50">{{ $hint }}</span>
    @endif
    @error($name)
        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
    @enderror
</label>
