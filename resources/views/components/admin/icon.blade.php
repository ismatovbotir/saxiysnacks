@props(['name'])

<svg {{ $attributes->class('size-5 shrink-0') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20h5v-6h4v6h5V9.5"/>@break
        @case('receipt')<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6M9 16h3"/>@break
        @case('users')<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.6 3.4-5.5 6.5-5.5s5.7 1.9 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.8c1.8.8 3 2.6 3.5 5.2"/>@break
        @case('box')<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>@break
        @case('tag')<path d="M3 12V3h9l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>@break
        @case('globe')<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/>@break
        @case('send')<path d="M21 3 3 10.5l7 2.5 2.5 7z"/><path d="m10 13 5-5"/>@break
        @case('shield')<path d="M12 3 4 6v6c0 4.5 3.4 8 8 9 4.6-1 8-4.5 8-9V6z"/><path d="m9 12 2 2 4-4"/>@break
        @case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
        @case('dots')<circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/>@break
        @case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>@break
        @case('logout')<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>@break
        @case('external')<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v6H4V6h6"/>@break
        @case('plus')<path d="M12 5v14M5 12h14"/>@break
        @case('close')<path d="M6 6l12 12M18 6 6 18"/>@break
        @case('chevron')<path d="m9 6 6 6-6 6"/>@break
        @case('warehouse')<path d="M3 21V8l9-5 9 5v13"/><path d="M7 21v-8h10v8M7 17h10"/>@break
        @case('list')<path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4.5" cy="6" r="1"/><circle cx="4.5" cy="12" r="1"/><circle cx="4.5" cy="18" r="1"/>@break
        @case('wallet')<path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H18v4"/><path d="M4 6.5V18a2 2 0 0 0 2 2h14V8H6.5A2.5 2.5 0 0 1 4 6.5z"/><circle cx="16" cy="14" r="1.2"/>@break
        @case('alert')<path d="M12 3 2 20h20z"/><path d="M12 10v4M12 17v.5"/>@break
        @case('minus')<circle cx="12" cy="12" r="9"/><path d="M8 12h8"/>@break
        @case('chart')<path d="M4 20V4M4 20h16"/><path d="M8 16v-5M12 16V8M16 16v-3"/>@break
        @case('trash')<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>@break
        @case('key')<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3M14 9l2 2"/>@break
        @case('lock')<rect x="5" y="11" width="14" height="10" rx="1.5"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>@break
        @case('file')<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>@break
        @case('printer')<path d="M7 9V3h10v6M7 17H4v-7h16v7h-3"/><path d="M7 14h10v7H7z"/>@break
        @case('check')<path d="m5 12 5 5 9-10"/>@break
        @case('edit')<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m14 6 4 4"/>@break
        @case('download')<path d="M12 4v11M7 10l5 5 5-5"/><path d="M4 19h16"/>@break
        @case('upload')<path d="M12 15V4M7 9l5-5 5 5"/><path d="M4 19h16"/>@break
        @case('scale')<path d="M12 4v16M6 20h12M5 8h14"/><path d="m5 8-3 6h6zM19 8l-3 6h6z"/>@break
        @case('back')<path d="M15 6l-6 6 6 6"/>@break
        @case('undo')<path d="M9 7 4 12l5 5"/><path d="M4 12h11a5 5 0 0 1 0 10h-2"/>@break
        @case('phone')<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2z"/>@break
    @endswitch
</svg>
