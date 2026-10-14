@if (session('status'))
    <div class="mb-5 flex items-start gap-2.5 print:hidden rounded-md border border-leaf/30 border-l-4 border-l-leaf bg-white px-4 py-2.5 text-sm text-leaf-dark shadow-xs" role="status"><span class="font-bold">✓</span> <span>{{ session('status') }}</span></div>
@endif
@if (session('error'))
    <div class="mb-5 flex items-start gap-2.5 print:hidden rounded-md border border-red-200 border-l-4 border-l-red-600 bg-white px-4 py-2.5 text-sm text-red-800 shadow-xs" role="alert"><span class="font-bold">!</span> <span>{{ session('error') }}</span></div>
@endif
