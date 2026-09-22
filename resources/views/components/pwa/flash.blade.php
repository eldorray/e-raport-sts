@php
    $pesanStatus = session('status');
    $pesanError = session('error');
@endphp

@if ($pesanStatus)
    <div x-data="{ tampil: true }" x-show="tampil" x-transition
        class="mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-200">
        <i class="fas fa-circle-check mt-0.5 text-base"></i>
        <p class="flex-1 leading-relaxed">{{ $pesanStatus }}</p>
        <button type="button" @click="tampil = false" aria-label="{{ __('Tutup') }}"
            class="text-emerald-600 transition hover:text-emerald-800 dark:text-emerald-300">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
@endif

@if ($pesanError)
    <div x-data="{ tampil: true }" x-show="tampil" x-transition
        class="mb-4 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/60 dark:text-red-200">
        <i class="fas fa-triangle-exclamation mt-0.5 text-base"></i>
        <p class="flex-1 leading-relaxed">{{ $pesanError }}</p>
        <button type="button" @click="tampil = false" aria-label="{{ __('Tutup') }}"
            class="text-red-500 transition hover:text-red-700 dark:text-red-300">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
@endif

@if ($errors->any())
    <div x-data="{ tampil: true }" x-show="tampil" x-transition
        class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/60 dark:text-red-200">
        <div class="flex items-start gap-3">
            <i class="fas fa-triangle-exclamation mt-0.5 text-base"></i>
            <div class="flex-1 space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="leading-relaxed">{{ $error }}</p>
                @endforeach
            </div>
            <button type="button" @click="tampil = false" aria-label="{{ __('Tutup') }}"
                class="text-red-500 transition hover:text-red-700 dark:text-red-300">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </div>
@endif
