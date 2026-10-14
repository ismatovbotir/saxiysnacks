@if (session('status'))
    <div class="mb-5 flex items-start gap-2 rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-sm text-leaf-dark" role="status">✓ <span>{{ session('status') }}</span></div>
@endif
@if (session('error'))
    <div class="mb-5 flex items-start gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">⚠ <span>{{ session('error') }}</span></div>
@endif
