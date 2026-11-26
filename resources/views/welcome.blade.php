<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $sekolah = \App\Models\SchoolProfile::first();
        $namaSekolah = $sekolah?->name ?? config('app.name', 'e-Raport');
        $logoUrl = $sekolah?->logo ? asset('storage/'.$sekolah->logo) : asset('images/eraport-icon.png');
        $tahunAktif = \App\Models\TahunAjaran::where('is_active', true)->orderByDesc('tahun_mulai')->first();
        $alamat = $sekolah?->address ?: $sekolah?->city;

        $peran = [
            'guru' => [
                'Guru mata pelajaran',
                'Mengisi nilai sumatif dan STS untuk setiap kelas yang diajar. Nilai akhir dan predikat dihitung otomatis.',
                ['Nilai sumatif dan STS per kelas', 'Bobot penilaian diatur per akun', 'Nilai ekskul untuk pembina'],
            ],
            'wali' => [
                'Wali kelas',
                'Melengkapi absen, prestasi, dan catatan siswa, lalu mencetak rapor dan leger kelas.',
                ['Data absen: sakit, izin, alpa', 'Prestasi dan catatan wali kelas', 'Cetak rapor per siswa atau satu kelas'],
            ],
            'tahfidz' => [
                'Pembimbing tahfidz',
                'Mencatat hafalan surah setiap siswa dan mencetak rapor tahfidz.',
                ['Hafalan surah per siswa', 'Rapor tahfidz siap cetak'],
            ],
            'admin' => [
                'Admin dan TU',
                'Menyiapkan tahun ajaran, data siswa dan guru, kelas, serta jadwal mengajar.',
                ['Tahun ajaran baru dan kenaikan kelas', 'Data siswa, guru, kelas, dan rombel', 'Koreksi nilai, pengaturan cetak, dan backup'],
            ],
        ];
        $alur = [
            ['Admin menyiapkan', 'Tahun ajaran, data siswa dan guru, kelas, serta jadwal mengajar.'],
            ['Guru mengisi nilai', 'Nilai sumatif dan STS per kelas. Nilai akhir dan predikat dihitung otomatis.'],
            ['Wali kelas melengkapi', 'Absen, prestasi, dan catatan untuk setiap siswa di kelasnya.'],
            ['Rapor dicetak', 'Per siswa atau satu kelas sekaligus, lengkap dengan leger kelas.'],
        ];
    @endphp

    <title>e-Raport · {{ $namaSekolah }}</title>
    <meta name="description" content="e-Raport {{ $namaSekolah }}: pengisian nilai sumatif dan STS oleh guru, serta penyusunan rapor oleh wali kelas.">
    <meta name="theme-color" content="#F4F4F5" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#18181B" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" href="{{ asset('images/eraport-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/eraport-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ponytail: halaman depan berdiri sendiri (tanpa Vite), jadi tetap tampil walau aset build belum ada --}}
    <style>
        :root {
            color-scheme: light dark;
            --latar: #F4F4F5;
            --permukaan: #FFFFFF;
            --isian: #F4F4F5;
            --tinta: #18181B;
            --redup: #3F3F46;
            --samar: #5F5F68;
            --garis: #E4E4E7;
            --gelap: #18181B;
            --di-gelap: #FFFFFF;
            --aksen: #006FEE;
            --aksen-kuat: #005BC4;
            --aksen-lembut: #E0EDFD;
            --lentur: cubic-bezier(.32, .72, 0, 1);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --latar: #18181B;
                --permukaan: #27272A;
                --isian: #3F3F46;
                --tinta: #FAFAFA;
                --redup: #D4D4D8;
                --samar: #A1A1AA;
                --garis: #3F3F46;
                --gelap: #09090B;
                --aksen-kuat: #7CC0FF;
                --aksen-lembut: #1E3A5F;
            }
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--latar);
            color: var(--tinta);
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        img {
            display: block;
            max-width: 100%;
        }

        h1,
        h2,
        h3,
        p,
        ul {
            margin: 0;
        }

        ul {
            padding: 0;
            list-style: none;
        }

        a {
            color: var(--aksen-kuat);
        }

        :focus-visible {
            outline: 2px solid var(--aksen);
            outline-offset: 2px;
        }

        .sr {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip-path: inset(50%);
            white-space: nowrap;
        }

        .wadah {
            width: 100%;
            max-width: 1168px;
            margin-inline: auto;
            padding-inline: 24px;
        }

        .bagian {
            padding-top: 104px;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .bagian__kepala {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 620px;
        }

        h2 {
            font-size: clamp(28px, 4vw, 40px);
            line-height: 1.1;
            font-weight: 600;
            letter-spacing: -0.03em;
            text-wrap: balance;
        }

        .bagian__kepala p {
            color: var(--redup);
        }

        .kartu {
            background: var(--permukaan);
            border-radius: 24px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
        }

        /* Tombol: mengecil sedikit saat ditekan */
        .tombol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 52px;
            padding: 0 24px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: transform .25s var(--lentur), opacity .2s ease;
        }

        .tombol:hover {
            opacity: .88;
        }

        .tombol:active {
            transform: scale(.97);
        }

        .tombol svg {
            width: 18px;
            height: 18px;
        }

        .tombol--aksen {
            background: var(--aksen);
            color: #FFFFFF;
        }

        .tombol--gelap {
            background: var(--tinta);
            color: var(--latar);
        }

        .tombol--terang {
            background: #FFFFFF;
            color: #18181B;
        }

        .tombol--garis {
            background: var(--permukaan);
            border: 1px solid var(--garis);
            color: var(--tinta);
            font-weight: 500;
        }

        .tombol--kecil {
            height: 40px;
            padding: 0 18px;
            border-radius: 12px;
            font-size: 14px;
        }

        /* Kepala halaman */
        .kepala {
            position: sticky;
            top: 0;
            z-index: 10;
            background: color-mix(in srgb, var(--latar) 72%, transparent);
            backdrop-filter: blur(14px) saturate(1.6);
            -webkit-backdrop-filter: blur(14px) saturate(1.6);
        }

        .kepala__isi {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-block: 14px;
        }

        .identitas {
            display: flex;
            align-items: center;
            gap: 12px;
            color: inherit;
            text-decoration: none;
        }

        .identitas img {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #FFFFFF;
            object-fit: contain;
        }

        .identitas__nama {
            display: block;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.01em;
            line-height: 1.25;
        }

        .identitas__kota {
            display: block;
            font-size: 12px;
            line-height: 1.25;
            color: var(--samar);
        }

        .menu {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;
        }

        .menu__tautan {
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--redup);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color .2s ease, color .2s ease;
        }

        .menu__tautan:hover {
            background: var(--garis);
            color: var(--tinta);
        }

        .menu .tombol {
            margin-left: 8px;
        }

        /* Pembuka */
        .pembuka {
            padding-top: 72px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            text-align: center;
        }

        .tanda {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 32px;
            padding: 0 14px;
            border-radius: 999px;
            background: var(--permukaan);
            border: 1px solid var(--garis);
            font-size: 13px;
            font-weight: 500;
        }

        .tanda::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #17C964;
        }

        h1 {
            max-width: 820px;
            font-size: clamp(40px, 6vw, 68px);
            line-height: 1.05;
            font-weight: 600;
            letter-spacing: -0.04em;
            text-wrap: balance;
        }

        .pengantar {
            max-width: 600px;
            font-size: 18px;
            color: var(--redup);
            text-wrap: pretty;
        }

        .aksi {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .catatan {
            font-size: 13px;
            color: var(--samar);
        }

        /* Panel contoh di bawah pembuka (hiasan) */
        .contoh {
            width: 100%;
            margin-top: 24px;
            padding: 40px 32px;
            border-radius: 32px;
            background: var(--gelap);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr));
            gap: 20px;
            align-items: center;
            text-align: left;
        }

        .contoh__kartu {
            background: #FFFFFF;
            color: #18181B;
            border-radius: 24px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            animation: apung 7s ease-in-out infinite;
        }

        .contoh__kartu:nth-child(2) {
            align-items: center;
            animation-delay: -2.3s;
        }

        .contoh__kartu:nth-child(3) {
            animation-delay: -4.6s;
        }

        .contoh__kepala {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .contoh__judul {
            display: block;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.3;
        }

        .contoh__ket {
            display: block;
            font-size: 12px;
            color: #5F5F68;
        }

        .chip {
            flex: none;
            padding: 4px 10px;
            border-radius: 999px;
            background: #F4F4F5;
            color: #3F3F46;
            font-size: 12px;
            font-weight: 600;
        }

        .chip--hijau {
            background: #E8FAF0;
            color: #0E793C;
        }

        .contoh__baris {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 14px;
            background: #F4F4F5;
            font-size: 13px;
            font-weight: 500;
        }

        .contoh__baris b,
        .contoh__angka b {
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .contoh__daftar,
        .contoh__tiga {
            display: grid;
            gap: 8px;
        }

        .contoh__tiga {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .contoh__angka {
            padding: 12px;
            border-radius: 14px;
            background: #F4F4F5;
            font-size: 12px;
            color: #5F5F68;
        }

        .contoh__angka b {
            display: block;
            font-size: 20px;
            color: #18181B;
        }

        .cincin {
            position: relative;
            width: 132px;
            height: 132px;
        }

        .cincin svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .cincin circle {
            fill: none;
            stroke-width: 10;
        }

        .cincin circle:first-child {
            stroke: #E4E4E7;
        }

        .cincin circle:last-child {
            stroke: var(--aksen);
            stroke-linecap: round;
            stroke-dasharray: 327;
            stroke-dashoffset: 82;
            animation: cincin 1.2s var(--lentur) .6s both;
        }

        .cincin span {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
        }

        .cincin small {
            font-size: 14px;
            font-weight: 500;
            color: #5F5F68;
        }

        /* Siapa mengerjakan apa: tab dari tombol radio, tanpa JavaScript */
        .peran {
            padding: 24px;
            border-radius: 28px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .tab__gulir {
            overflow-x: auto;
        }

        .tab {
            position: relative;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            min-width: 600px;
            padding: 4px;
            border-radius: 15px;
            background: var(--isian);
        }

        .tab__penanda {
            display: none;
        }

        .tab label {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            padding: 0 12px;
            border-radius: 11px;
            color: var(--samar);
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            cursor: pointer;
            transition: color .2s ease;
        }

        .tab input:focus-visible+label {
            outline: 2px solid var(--aksen);
            outline-offset: 2px;
        }

        .panel {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 32px;
            padding: 8px;
        }

        .panel>div {
            flex: 1 1 320px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .panel h3 {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .panel p {
            color: var(--redup);
            text-wrap: pretty;
        }

        .panel ul {
            flex: 1 1 320px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .panel li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            background: var(--isian);
            font-size: 15px;
            font-weight: 500;
        }

        .panel li::before {
            content: '';
            flex: none;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: var(--aksen-lembut) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23006FEE' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12l5 5 9-10'/%3E%3C/svg%3E") center / 13px no-repeat;
        }

        /* Tanpa :has() keempat panel tampil berurutan, seperti daftar biasa */
        @supports selector(:has(a)) {
            .tab__penanda {
                display: block;
                position: absolute;
                top: 4px;
                bottom: 4px;
                left: 4px;
                width: calc((100% - 8px) / 4);
                border-radius: 11px;
                background: var(--permukaan);
                box-shadow: 0 1px 3px rgba(0, 0, 0, .14);
                transition: transform .35s var(--lentur);
            }

            .peran:has(#peran-wali:checked) .tab__penanda {
                transform: translateX(100%);
            }

            .peran:has(#peran-tahfidz:checked) .tab__penanda {
                transform: translateX(200%);
            }

            .peran:has(#peran-admin:checked) .tab__penanda {
                transform: translateX(300%);
            }

            .tab input:checked+label {
                color: var(--tinta);
                font-weight: 600;
            }

            .panel {
                display: none;
            }

            .peran:has(#peran-guru:checked) .panel[data-peran=guru],
            .peran:has(#peran-wali:checked) .panel[data-peran=wali],
            .peran:has(#peran-tahfidz:checked) .panel[data-peran=tahfidz],
            .peran:has(#peran-admin:checked) .panel[data-peran=admin] {
                display: flex;
                animation: panel .4s var(--lentur) both;
            }
        }

        @supports not selector(:has(a)) {
            .tab__gulir {
                display: none;
            }
        }

        /* Alur */
        .alur {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr));
            gap: 16px;
        }

        .alur li {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: transform .3s var(--lentur), box-shadow .3s ease;
        }

        .alur li:hover {
            transform: translateY(-3px);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04), 0 16px 36px rgba(0, 0, 0, .08);
        }

        .alur__nomor {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: var(--tinta);
            color: var(--latar);
            font-size: 15px;
            font-weight: 600;
        }

        .alur li:last-child .alur__nomor {
            background: var(--aksen);
            color: #FFFFFF;
        }

        .alur h3 {
            margin-top: 4px;
            font-size: 17px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .alur p {
            font-size: 14px;
            color: var(--redup);
        }

        /* Aplikasi HP */
        .hp {
            padding: 40px;
            border-radius: 32px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 40px;
        }

        .hp__teks {
            flex: 1 1 360px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .hp__teks p {
            max-width: 460px;
            color: var(--redup);
            text-wrap: pretty;
        }

        .hp__layar {
            flex: 0 1 300px;
            margin-inline: auto;
            padding: 16px;
            border-radius: 36px;
            background: var(--isian);
            border: 1px solid var(--garis);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .hp__baris {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 18px;
            background: var(--permukaan);
            font-size: 14px;
            font-weight: 500;
        }

        .hp__baris b {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 40px;
            border-radius: 12px;
            background: var(--isian);
            font-size: 15px;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .hp__baris--aktif {
            box-shadow: 0 0 0 2px var(--aksen);
        }

        .hp__simpan {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 48px;
            border-radius: 16px;
            background: var(--aksen);
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 600;
        }

        /* Ajakan penutup */
        .penutup {
            padding: 64px 32px;
            border-radius: 32px;
            background: var(--gelap);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            text-align: center;
        }

        .penutup h2 {
            max-width: 640px;
            font-size: clamp(28px, 4vw, 44px);
        }

        .penutup p {
            font-size: 13px;
            color: #A1A1AA;
        }

        .kaki {
            margin-top: auto;
        }

        .kaki__isi {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 12px;
            padding-block: 56px 40px;
            font-size: 13px;
            color: var(--samar);
        }

        /* Gerak */
        @keyframes naik {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
        }

        @keyframes panel {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.99);
            }
        }

        @keyframes apung {
            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes cincin {
            from {
                stroke-dashoffset: 327;
            }
        }

        .pembuka>* {
            animation: naik .6s var(--lentur) both;
        }

        .pembuka>:nth-child(2) {
            animation-delay: 80ms;
        }

        .pembuka>:nth-child(3) {
            animation-delay: 160ms;
        }

        .pembuka>:nth-child(4) {
            animation-delay: 240ms;
        }

        .pembuka>:nth-child(5) {
            animation-delay: 320ms;
        }

        .pembuka>:nth-child(6) {
            animation-delay: 400ms;
        }

        /* Bagian di bawah pembuka muncul saat digulir; browser tanpa dukungan langsung menampilkannya */
        @supports (animation-timeline: view()) {
            .muncul {
                animation: naik linear both;
                animation-timeline: view();
                animation-range: entry 0% entry 40%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
            }
        }

        @media (max-width: 640px) {
            .menu__tautan {
                display: none;
            }

            .contoh,
            .hp {
                padding: 24px 16px;
            }

            .peran {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <header class="kepala">
        <div class="wadah kepala__isi">
            <a class="identitas" href="{{ url('/') }}">
                <img src="{{ $logoUrl }}" alt="">
                <span>
                    <span class="identitas__nama">{{ $namaSekolah }}</span>
                    @if ($sekolah?->city)
                        <span class="identitas__kota">{{ $sekolah->city }}</span>
                    @endif
                </span>
            </a>

            <nav class="menu" aria-label="Navigasi halaman">
                <a class="menu__tautan" href="#peran">Siapa mengerjakan apa</a>
                <a class="menu__tautan" href="#alur">Alur rapor</a>
                <a class="menu__tautan" href="#hp">Aplikasi HP</a>
                @auth
                    <a class="tombol tombol--gelap tombol--kecil" href="{{ route('dashboard') }}">Buka dashboard</a>
                @else
                    <a class="tombol tombol--gelap tombol--kecil" href="{{ route('login') }}">Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="wadah pembuka">
            @if ($tahunAktif)
                <p class="tanda">Tahun ajaran
                    {{ $tahunAktif->nama }}{{ $tahunAktif->semester ? ', semester '.$tahunAktif->semester : '' }}</p>
            @endif

            <h1>Laporan Hasil Belajar, dari input nilai sampai cetak.</h1>

            <p class="pengantar">
                e-Raport Kurikulum Merdeka {{ $namaSekolah }}. Guru mengisi nilai, wali kelas melengkapi, rapor siap dicetak.
            </p>

            <div class="aksi">
                @auth
                    <a class="tombol tombol--aksen" href="{{ route('dashboard') }}">
                        Buka dashboard
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                @else
                    <a class="tombol tombol--aksen" href="{{ route('login') }}">
                        Masuk ke e-Raport
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                @endauth
                <a class="tombol tombol--garis" href="{{ route('guru.pwa.beranda') }}">Aplikasi guru di HP</a>
            </div>

            <p class="catatan">Akun dibuat oleh admin atau TU madrasah. Lupa kata sandi? Hubungi admin atau TU.</p>

            {{-- Hiasan: contoh tampilan, bukan data sungguhan --}}
            <div class="contoh" aria-hidden="true">
                <div class="contoh__kartu">
                    <div class="contoh__kepala">
                        <span>
                            <span class="contoh__judul">Matematika · Kelas 4A</span>
                            <span class="contoh__ket">Nilai Rapor</span>
                        </span>
                        <span class="chip chip--hijau">Lengkap</span>
                    </div>
                    <div class="contoh__daftar">
                        <div class="contoh__baris">Ahmad <b>88</b></div>
                        <div class="contoh__baris">Aisyah <b>92</b></div>
                        <div class="contoh__baris">Bayu <b>79</b></div>
                    </div>
                </div>

                <div class="contoh__kartu">
                    <div class="contoh__kepala"><span class="contoh__judul">Rapor siap cetak</span></div>
                    <div class="cincin">
                        <svg viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="52" />
                            <circle cx="60" cy="60" r="52" />
                        </svg>
                        <span>21<small>/28</small></span>
                    </div>
                    <span class="contoh__ket">7 siswa menunggu nilai dari guru mapel</span>
                </div>

                <div class="contoh__kartu">
                    <div class="contoh__kepala">
                        <span>
                            <span class="contoh__judul">Data Absen</span>
                            <span class="contoh__ket">Diisi wali kelas</span>
                        </span>
                        <span class="chip">Semester ini</span>
                    </div>
                    <div class="contoh__tiga">
                        <div class="contoh__angka">Sakit <b>1</b></div>
                        <div class="contoh__angka">Izin <b>0</b></div>
                        <div class="contoh__angka">Alpa <b>0</b></div>
                    </div>
                    <div class="contoh__baris">Rajin dan aktif di kelas.</div>
                </div>
            </div>
        </section>

        <section id="peran" class="wadah bagian" aria-labelledby="judul-peran">
            <div class="bagian__kepala muncul">
                <h2 id="judul-peran">Siapa mengerjakan apa</h2>
                <p>Setiap peran hanya melihat menu yang ia butuhkan.</p>
            </div>

            <div class="kartu peran muncul">
                <div class="tab__gulir">
                    <div class="tab" role="radiogroup" aria-label="Peran pengguna">
                        <span class="tab__penanda" aria-hidden="true"></span>
                        @foreach ($peran as $kunci => [$nama])
                            <input class="sr" type="radio" name="peran" id="peran-{{ $kunci }}" @checked($loop->first)>
                            <label for="peran-{{ $kunci }}">{{ $nama }}</label>
                        @endforeach
                    </div>
                </div>

                @foreach ($peran as $kunci => [$nama, $uraian, $butir])
                    <div class="panel" data-peran="{{ $kunci }}">
                        <div>
                            <h3>{{ $nama }}</h3>
                            <p>{{ $uraian }}</p>
                        </div>
                        <ul>
                            @foreach ($butir as $isi)
                                <li>{{ $isi }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="alur" class="wadah bagian" aria-labelledby="judul-alur">
            <div class="bagian__kepala muncul">
                <h2 id="judul-alur">Alur rapor satu semester</h2>
                <p>Empat langkah, masing-masing dikerjakan oleh peran yang berbeda.</p>
            </div>

            <ol class="alur" style="margin: 0; padding: 0; list-style: none">
                @foreach ($alur as [$judul, $uraian])
                    <li class="kartu muncul">
                        <span class="alur__nomor" aria-hidden="true">{{ $loop->iteration }}</span>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $uraian }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <section id="hp" class="wadah bagian" aria-labelledby="judul-hp">
            <div class="kartu hp muncul">
                <div class="hp__teks">
                    <h2 id="judul-hp">Aplikasi guru di HP</h2>
                    <p>Isi nilai, nilai ekskul, dan data wali kelas langsung dari HP. Bisa dipasang ke layar utama seperti aplikasi biasa.</p>
                    <a class="tombol tombol--gelap" style="height: 48px; font-size: 15px" href="{{ route('guru.pwa.beranda') }}">Buka aplikasi guru</a>
                </div>

                {{-- Hiasan: contoh layar isi nilai, bukan data sungguhan --}}
                <div class="hp__layar" aria-hidden="true">
                    <div class="contoh__kepala" style="padding: 8px 6px 4px">
                        <span>
                            <span class="contoh__judul" style="font-size: 15px">Matematika</span>
                            <span class="contoh__ket" style="color: var(--samar)">Kelas 4A · Rerata Sumatif</span>
                        </span>
                    </div>
                    <div class="hp__baris">Ahmad <b>86</b></div>
                    <div class="hp__baris">Aisyah <b>90</b></div>
                    <div class="hp__baris hp__baris--aktif">Bayu <b style="color: var(--samar)">–</b></div>
                    <div class="hp__simpan">Simpan Nilai</div>
                </div>
            </div>
        </section>

        <section class="wadah bagian">
            <div class="penutup muncul">
                <h2>Lanjutkan pengisian rapor semester ini.</h2>
                @auth
                    <a class="tombol tombol--terang" href="{{ route('dashboard') }}">Buka dashboard</a>
                @else
                    <a class="tombol tombol--terang" href="{{ route('login') }}">
                        Masuk ke e-Raport
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                    <p>Belum punya akun? Hubungi admin atau TU madrasah.</p>
                @endauth
            </div>
        </section>
    </main>

    <footer class="kaki">
        <div class="wadah kaki__isi">
            <span>{{ $namaSekolah }}{{ $alamat ? ' · '.$alamat : '' }}</span>
            <span>&copy; {{ date('Y') }} e-Raport {{ $namaSekolah }}</span>
        </div>
    </footer>
</body>

</html>
