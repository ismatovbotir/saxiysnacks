@props(['label', 'name', 'hint' => null])

<label {{ $attributes->class('block') }}>
    <span class="admin-label">{{ $label }}</span>
    {{ $slot }}
    @if ($hint)
        <span class="mt-1 block text-xs text-black/50">{{ $hint }}</span>
    @endif
    @error($name)
        <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>
    @enderror
</label>
