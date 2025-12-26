<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eraport STS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body {
                margin: 0;
                font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            }
        </style>
    @endif
</head>

<body class="min-h-screen bg-slate-950 text-slate-50">
    @php
        $school = \App\Models\SchoolProfile::first();
        $logoUrl = null;
        if ($school?->logo) {
            $logoUrl = filter_var($school->logo, FILTER_VALIDATE_URL)
                ? $school->logo
                : asset('storage/' . $school->logo);
        }
    @endphp

    <div class="relative isolate min-h-screen overflow-hidden">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(59,130,246,0.18),transparent_35%),radial-gradient(circle_at_80%_0%,rgba(14,165,233,0.16),transparent_32%),radial-gradient(circle_at_0%_80%,rgba(99,102,241,0.16),transparent_28%)]">
        </div>
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(115deg,rgba(255,255,255,0.05)_0%,rgba(255,255,255,0)_40%),linear-gradient(245deg,rgba(255,255,255,0.06)_0%,rgba(255,255,255,0)_45%)]">
        </div>

        {{-- Header with School Logo and Name --}}
        <header class="relative z-10 border-b border-white/10">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo Sekolah"
                            class="h-12 w-12 rounded-lg object-contain bg-white/10 p-1">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/20">
                            <i class="fa-solid fa-school text-xl text-blue-300"></i>
                        </div>
                    @endif
                    <div>
                        <p class="font-bold text-white">{{ $school?->name ?? 'Nama Sekolah' }}</p>
                        <p class="text-xs text-slate-400">{{ $school?->city ?? '' }}</p>
                    </div>
                </div>
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-600">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </a>
            </div>
        </header>

        <div
            class="relative mx-auto flex min-h-[calc(100vh-80px)] max-w-6xl flex-col px-6 py-12 lg:flex-row lg:items-center lg:gap-12">
            <div class="flex-1">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-4 py-2 text-xs font-semibold text-blue-200 ring-1 ring-inset ring-blue-400/30">
                    <i class="fa-solid fa-chart-line"></i> Eraport STS
                </span>
                <h1 class="mt-4 text-4xl font-bold leading-tight text-white sm:text-5xl">
                    Kelola penilaian & capaian siswa dengan pengalaman yang rapi.
                </h1>
                <p class="mt-4 max-w-2xl text-base text-slate-200 sm:text-lg">
                    Masukkan nilai sumatif dan STS, catat materi/TP, dan dapatkan deskripsi capaian otomatis. Dirancang
                    selaras dengan panel admin Eraport STS.
                </p>
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-blue-600">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk
                    </a>
                    {{-- @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/15 px-4 py-3 text-sm font-semibold text-white/90 transition hover:-translate-y-0.5 hover:border-white/25 hover:text-white">
                            <i class="fa-solid fa-user-plus"></i> Daftar
                        </a>
                    @endif --}}
                </div>

                <div class="mt-10 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-lg shadow-black/20">
                        <p class="text-xs uppercase tracking-wide text-slate-300">Real-time</p>
                        <p class="mt-1 text-lg font-semibold text-white">Nilai & rata-rata langsung</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-lg shadow-black/20">
                        <p class="text-xs uppercase tracking-wide text-slate-300">Deskripsi</p>
                        <p class="mt-1 text-lg font-semibold text-white">Predikat otomatis per rentang</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-lg shadow-black/20">
                        <p class="text-xs uppercase tracking-wide text-slate-300">Terintegrasi</p>
                        <p class="mt-1 text-lg font-semibold text-white">Guru, kelas, siswa tersinkron</p>
                    </div>
                </div>
            </div>

            <div class="flex-1">
                <div
                    class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-white/10 via-white/5 to-white/5 p-6 shadow-2xl shadow-blue-500/15">
                    <div
                        class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(14,165,233,0.15),transparent_35%),radial-gradient(circle_at_80%_70%,rgba(59,130,246,0.18),transparent_38%)]">
                    </div>
                    <div class="relative space-y-4 text-sm text-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wide text-blue-100">Alur Guru</span>
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">STS</span>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner shadow-black/10">
                            <div class="flex items-center gap-3 text-white">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/80 shadow-lg shadow-blue-500/30">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Pilih Mapel & Kelas</p>
                                    <p class="text-xs text-slate-200">Navigasi dari sidebar mapel guru.</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner shadow-black/10">
                            <div class="flex items-center gap-3 text-white">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/80 shadow-lg shadow-emerald-500/30">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Isi Sumatif & STS</p>
                                    <p class="text-xs text-slate-200">Nilai 0-100, rata-rata rapor otomatis.</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-inner shadow-black/10">
                            <div class="flex items-center gap-3 text-white">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/80 shadow-lg shadow-indigo-500/30">
                                    <i class="fa-solid fa-bullseye"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Materi/TP & Deskripsi</p>
                                    <p class="text-xs text-slate-200">Predikat & kalimat capaian terisi sesuai rentang.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="relative border-t border-white/10 bg-slate-900/80 py-6 text-center text-sm text-slate-400">
            <div class="mx-auto max-w-6xl px-6">
                <p>© {{ date('Y') }} <span class="font-semibold text-white">Eraport STS</span> • Dikembangkan oleh
                    <span class="text-blue-400">Fahmie Al Khudhorie</span>
                </p>
            </div>
        </footer>
    </div>
</body>

</html>
