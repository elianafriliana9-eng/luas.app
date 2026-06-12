@php
    $type = session('success') ? 'success' : (session('error') ? 'error' : null);
    $message = session('success') ?? session('error') ?? null;
@endphp

@if($type && $message)
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 4000)"
     x-transition:leave.duration.500ms
     @click="show = false"
     role="alert"
     class="fixed top-20 right-4 z-[100] max-w-sm w-full cursor-pointer">
    <div class="{{ $type === 'success' ? 'bg-emerald-600' : 'bg-red-500' }} text-white rounded-xl shadow-lg px-4 py-3 flex items-center gap-3">
        <span class="material-symbols-outlined text-[20px] flex-shrink-0">
            {{ $type === 'success' ? 'check_circle' : 'error' }}
        </span>
        <p class="text-sm font-medium flex-1">{{ $message }}</p>
        <span class="material-symbols-outlined text-[18px] opacity-70 hover:opacity-100 flex-shrink-0">close</span>
    </div>
</div>
@endif
