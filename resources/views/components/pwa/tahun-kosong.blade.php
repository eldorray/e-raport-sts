{{-- Keadaan kosong saat sesi belum berisi tahun ajaran; membuka pemilih tahun ajaran milik layout PWA. --}}
@props([
    'pesan' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-200']) }}>
    <p class="font-semibold">{{ __('Tahun ajaran belum dipilih.') }}</p>
    <p class="mt-1 leading-relaxed">
        {{ $pesan ?? __('Pilih tahun ajaran dan semester untuk mulai.') }}
    </p>
    <button type="button" @click="bukaTahun()" aria-haspopup="dialog"
        class="mt-3 inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 text-sm font-semibold text-white transition active:scale-[0.99]">
        <i class="fas fa-calendar-days"></i>{{ __('Pilih Tahun Ajaran') }}
    </button>
</div>
