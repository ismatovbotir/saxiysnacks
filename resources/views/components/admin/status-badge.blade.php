@props(['status'])

<span @class([
    'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
    'bg-amber-100 text-amber-800' => in_array($status->value, ['new', 'reseller']),
    'bg-green-100 text-green-800' => in_array($status->value, ['active', 'confirmed', 'completed']),
    'bg-blue-100 text-blue-800' => $status->value === 'shipped',
    'bg-black/5 text-black/70' => $status->value === 'consumer',
    'bg-red-100 text-red-800' => in_array($status->value, ['blocked', 'cancelled']),
])>{{ $status->emoji() }} {{ $status->label() }}</span>
