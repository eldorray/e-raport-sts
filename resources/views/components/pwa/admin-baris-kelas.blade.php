{{-- Isi satu baris progres kelas di beranda aplikasi admin. --}}
@props([
    'baris',
])

@php
    $keadaan = $baris['target'] > 0 && $baris['lengkap'] >= $baris['target']
        ? 'lengkap'
        : ($baris['lengkap'] > 0 ? 'sebagian' : 'kosong');
    $warnaBatang = match ($keadaan) {
        'lengkap' => 'bg-emerald-500',
        'sebagian' => 'bg-amber-400',
        default => 'bg-slate-300 dark:bg-slate-600',
    };
    $warnaLencana = match ($keadaan) {
        'lengkap' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'sebagian' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        default => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    };
@endphp

<span
    class="flex h-11 min-w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 px-1.5 text-sm font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
    {{ $baris['kelas']->nama }}
</span>

<div class="min-w-0 flex-1">
    <p class="truncate text-xs font-semibold">
        @if ($baris['penugasan'] > 0)
            {{ __(':lengkap dari :target nilai lengkap', ['lengkap' => $baris['lengkap'], 'target' => $baris['target']]) }}
        @else
            {{ __('Belum ada penugasan mengajar') }}
        @endif
    </p>
    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
        <div class="h-full rounded-full {{ $warnaBatang }}" style="width: {{ $baris['persen'] }}%"></div>
    </div>
    <p class="mt-1 truncate text-[11px] text-slate-500 dark:text-slate-400">
        {{ __(':mapel mapel • :siswa siswa', ['mapel' => $baris['penugasan'], 'siswa' => $baris['siswa']]) }}
    </p>
</div>

<span
    class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold tabular-nums {{ $warnaLencana }}">{{ $baris['persen'] }}%</span>
