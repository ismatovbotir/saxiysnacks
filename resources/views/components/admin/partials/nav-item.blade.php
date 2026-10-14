<a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
   @class([
       'relative flex items-center gap-2.5 rounded-md px-2.5 py-2 text-[13px] font-medium transition-colors',
       'bg-white/10 text-white before:absolute before:inset-y-1.5 before:-left-2.5 before:w-0.5 before:rounded-r before:bg-brand' => $item['active'],
       'text-white/70 hover:bg-white/5 hover:text-white' => ! $item['active'],
   ])>
    <x-admin.icon :name="$item['icon']" :class="'size-[18px] '.($item['active'] ? 'text-brand' : 'text-white/55')" />
    <span class="flex-1">{{ $item['label'] }}</span>
    @if ($item['badge'])
        <span class="min-w-5 rounded bg-brand px-1.5 py-px text-center text-[11px] font-bold text-ink" title="{{ __('Needs attention') }}">{{ $item['badge'] }}</span>
    @endif
</a>
