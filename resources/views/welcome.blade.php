<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $sekolah = \App\Models\SchoolProfile::first();
        $namaSekolah = $sekolah?->name ?? config('app.name', 'e-Raport');
        $logoUrl = $sekolah?->logo ? asset('storage/'.$sekolah->logo) : null;
        $tahunAktif = \App\Models\TahunAjaran::where('is_active', true)->orderByDesc('tahun_mulai')->first();
        $alamat = $sekolah?->address ?: $sekolah?->city;
    @endphp

    <title>e-Raport · {{ $namaSekolah }}</title>
    <meta name="description" content="e-Raport {{ $namaSekolah }}: pengisian nilai sumatif dan STS oleh guru, serta penyusunan rapor oleh wali kelas.">
    <meta name="theme-color" content="#1E4D3A">
    <link rel="icon" type="image/png" href="{{ asset('images/eraport-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/eraport-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Readex+Pro:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- ponytail: halaman depan berdiri sendiri (tanpa Vite), jadi tetap tampil walau aset build belum ada --}}
    <style>
        :root {
            --kertas: #F7F4EC;
            --tinta: #1F2A24;
            --redup: #56625B;
            --hijau: #1E4D3A;
            --hijau-tua: #163A2C;
            --emas: #C9A24E;
            --daun: #E3EADB;
            --garis: #D9D6CA;
            --judul: 'Marcellus', Georgia, serif;
            --badan: 'Readex Pro', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--kertas);
            color: var(--tinta);
            font-family: var(--badan);
            font-size: 17px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        img {
            display: block;
            max-width: 100%;
        }

        :focus-visible {
            outline: 3px solid var(--hijau);
            outline-offset: 3px;
            border-radius: 8px;
        }

        .wadah {
            max-width: 1120px;
            margin: 0 auto;
            padding-inline: 24px;
        }

        /* Kepala halaman */
        .kepala {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-block: 20px;
        }

        .identitas {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            color: inherit;
            text-decoration: none;
        }

        .identitas__logo {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            border-radius: 12px;
            object-fit: contain;
        }

        .identitas__logo--bawaan {
            display: grid;
            place-items: center;
            background: var(--hijau);
            color: var(--emas);
        }

        .identitas__nama {
            display: block;
            font-family: var(--judul);
            font-size: 20px;
            font-weight: 400;
            line-height: 1.15;
        }

        .identitas__kota {
            display: block;
            font-size: 14px;
            color: var(--redup);
        }

        /* Tombol */
        .tombol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 48px;
            padding: 0 24px;
            border: 1.5px solid var(--hijau);
            border-radius: 999px;
            font: 600 16px/1 var(--badan);
            text-decoration: none;
            white-space: nowrap;
            transition: background-color .2s ease, color .2s ease;
        }

        .tombol--isi {
            background: var(--hijau);
            color: #fff;
        }

        .tombol--isi:hover {
            background: var(--hijau-tua);
            border-color: var(--hijau-tua);
        }

        .tombol--garis {
            background: transparent;
            color: var(--hijau);
        }

        .tombol--garis:hover {
            background: var(--daun);
        }

        .tombol--kecil {
            min-height: 44px;
            padding: 0 20px;
            font-size: 15px;
        }

        /* Pembuka */
        .pembuka {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr);
            align-items: center;
            gap: 56px;
            padding-block: 40px 80px;
        }

        .tanda {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 18px;
            font-size: 15px;
            font-weight: 500;
            color: var(--hijau);
        }

        .tanda svg {
            color: var(--emas);
        }

        h1 {
            margin: 0;
            font-family: var(--judul);
            font-size: clamp(2.5rem, 5.4vw, 4.25rem);
            font-weight: 400;
            line-height: 1.05;
            letter-spacing: -0.01em;
            text-wrap: balance;
        }

        .h1-atas {
            display: block;
            margin-bottom: .2em;
            font-size: .5em;
            font-weight: 400;
            letter-spacing: .02em;
            color: var(--hijau);
        }

        .pengantar {
            max-width: 36ch;
            margin: 24px 0 0;
            font-size: 19px;
            color: var(--redup);
            text-wrap: pretty;
        }

        .aksi {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 32px;
        }

        .catatan {
            margin: 20px 0 0;
            font-size: 14px;
            color: var(--redup);
        }

        /* Buku rapor */
        .meja {
            display: grid;
            place-items: center;
            min-height: 540px;
            padding: 56px 24px;
            border-radius: 32px;
            background: var(--daun);
        }

        .buku {
            position: relative;
            width: min(320px, 70vw);
            aspect-ratio: 3 / 4;
            font-size: clamp(11px, 3.5vw, 16px);
            transform: rotate(-3deg);
            transition: transform .45s cubic-bezier(.2, .7, .2, 1);
            animation: buku-datang .9s cubic-bezier(.2, .7, .2, 1) backwards;
        }

        .meja:hover .buku {
            transform: rotate(-1deg) translateY(-6px);
        }

        @keyframes buku-datang {
            from {
                opacity: 0;
                transform: translateY(28px) rotate(-8deg);
            }
        }

        /* Tumpukan halaman di sisi kanan */
        .buku::before {
            content: '';
            position: absolute;
            top: 8px;
            right: -11px;
            bottom: 8px;
            width: 15px;
            border-radius: 0 4px 4px 0;
            background: repeating-linear-gradient(90deg, #FBF8F1 0 2px, #E4DDCC 2px 3px);
            box-shadow: 0 12px 24px -10px rgba(22, 40, 30, .35);
        }

        .sampul {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.1em;
            padding: 3.5em 2.5em 3em 3.4em;
            border-radius: 4px 12px 12px 4px;
            text-align: center;
            color: var(--emas);
            background-color: var(--hijau);
            background-image:
                linear-gradient(90deg, rgba(0, 0, 0, .3) 0 .9em, rgba(255, 255, 255, .07) .9em 1em, transparent 1em),
                repeating-linear-gradient(45deg, rgba(255, 255, 255, .035) 0 1px, transparent 1px 4px),
                repeating-linear-gradient(-45deg, rgba(0, 0, 0, .07) 0 1px, transparent 1px 4px);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .08),
                0 40px 60px -24px rgba(22, 40, 30, .55),
                0 14px 24px -12px rgba(22, 40, 30, .35);
        }

        .bingkai {
            position: absolute;
            inset: 1.25em 1.25em 1.25em 2.1em;
            border: 1.5px solid rgba(201, 162, 78, .75);
            border-radius: 6px;
            pointer-events: none;
        }

        .bingkai::after {
            content: '';
            position: absolute;
            inset: 5px;
            border: 1px solid rgba(201, 162, 78, .4);
            border-radius: 3px;
        }

        .bingkai svg {
            position: absolute;
            width: 1.15em;
            height: 1.15em;
            background: var(--hijau);
        }

        .bingkai svg:nth-child(1) {
            top: 0;
            left: 0;
            transform: translate(-50%, -50%);
        }

        .bingkai svg:nth-child(2) {
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
        }

        .bingkai svg:nth-child(3) {
            bottom: 0;
            left: 0;
            transform: translate(-50%, 50%);
        }

        .bingkai svg:nth-child(4) {
            right: 0;
            bottom: 0;
            transform: translate(50%, 50%);
        }

        .emas {
            background: linear-gradient(100deg, #B98C35 0%, #F1DC9F 42%, #C79A45 62%, #E6C98A 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .sampul__judul {
            margin: 0;
            font-family: var(--judul);
            font-size: 1.375em;
            font-weight: 400;
            line-height: 1.35;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .sampul__lambang {
            display: grid;
            place-items: center;
            width: 6em;
            height: 6em;
        }

        .sampul__lambang img {
            width: 100%;
            height: 100%;
            padding: .6em;
            border-radius: 50%;
            object-fit: contain;
            background: #F3EBD3;
            box-shadow: 0 0 0 2px var(--emas);
        }

        .sampul__sekolah {
            margin: 0;
            font-family: var(--judul);
            font-size: 1.25em;
            font-weight: 400;
            line-height: 1.25;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .sampul__tahun {
            margin: -.4em 0 0;
            font-size: .8em;
            letter-spacing: .2em;
            color: rgba(232, 210, 154, .85);
        }

        /* Peran */
        .peran {
            padding-block: 72px;
            border-top: 1px solid var(--garis);
        }

        .peran h2 {
            margin: 0 0 36px;
            font-family: var(--judul);
            font-size: clamp(1.75rem, 3vw, 2.25rem);
            font-weight: 400;
            line-height: 1.2;
        }

        .peran ul {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 36px 40px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .peran li {
            padding-top: 18px;
            border-top: 1px solid var(--garis);
        }

        .peran h3 {
            margin: 0 0 6px;
            font-family: var(--judul);
            font-size: 21px;
            font-weight: 400;
            color: var(--hijau);
        }

        .peran p {
            margin: 0;
            font-size: 16px;
            color: var(--redup);
        }

        /* Kaki */
        .kaki {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 8px 24px;
            padding-block: 28px 44px;
            border-top: 1px solid var(--garis);
            font-size: 14px;
            color: var(--redup);
        }

        .kaki p {
            margin: 0;
        }

        @media (max-width: 1000px) {
            .peran ul {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .pembuka {
                grid-template-columns: minmax(0, 1fr);
                gap: 40px;
                padding-block: 16px 56px;
            }

            .meja {
                min-height: 400px;
                padding: 48px 16px;
            }
        }

        @media (max-width: 480px) {
            .peran ul {
                grid-template-columns: minmax(0, 1fr);
            }

            .identitas__kota {
                display: none;
            }

            .aksi .tombol {
                flex: 1 1 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .buku {
                animation: none;
                transition: none;
            }
        }
    </style>
</head>

<body>
    {{-- Bintang segi delapan, motif hias sampul rapor --}}
    <svg width="0" height="0" style="position: absolute" aria-hidden="true">
        <symbol id="bintang" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12 1 15.2 4.2H19.8V8.8L23 12 19.8 15.2V19.8H15.2L12 23 8.8 19.8H4.2V15.2L1 12 4.2 8.8V4.2H8.8Z" />
        </symbol>
        <symbol id="bintang-garis" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width=".9">
                <path d="M12 1.5 22.5 12 12 22.5 1.5 12Z" />
                <path d="M4.6 4.6H19.4V19.4H4.6Z" />
                <circle cx="12" cy="12" r="3.2" />
            </g>
        </symbol>
    </svg>

    <header class="wadah kepala">
        <a class="identitas" href="{{ url('/') }}">
            @if ($logoUrl)
                <img class="identitas__logo" src="{{ $logoUrl }}" alt="">
            @else
                <span class="identitas__logo identitas__logo--bawaan" aria-hidden="true">
                    <svg width="26" height="26"><use href="#bintang-garis" /></svg>
                </span>
            @endif
            <span>
                <span class="identitas__nama">{{ $namaSekolah }}</span>
                @if ($sekolah?->city)
                    <span class="identitas__kota">{{ $sekolah->city }}</span>
                @endif
            </span>
        </a>

        @auth
            <a class="tombol tombol--isi tombol--kecil" href="{{ route('dashboard') }}">Buka dashboard</a>
        @else
            <a class="tombol tombol--isi tombol--kecil" href="{{ route('login') }}">Masuk</a>
        @endauth
    </header>

    <main>
        <section class="wadah pembuka">
            <div>
                @if ($tahunAktif)
                    <p class="tanda">
                        <svg width="14" height="14" aria-hidden="true"><use href="#bintang" /></svg>
                        Tahun ajaran {{ $tahunAktif->nama }}{{ $tahunAktif->semester ? ', semester '.$tahunAktif->semester : '' }}
                    </p>
                @endif

                <h1>
                    <span class="h1-atas">e-Raport</span>
                    {{ $namaSekolah }}
                </h1>

                <p class="pengantar">
                    Tempat guru mengisi nilai sumatif dan STS, dan wali kelas menyusun rapor siswa. Bisa dari laptop maupun HP.
                </p>

                <div class="aksi">
                    @auth
                        <a class="tombol tombol--isi" href="{{ route('dashboard') }}">Buka dashboard</a>
                    @else
                        <a class="tombol tombol--isi" href="{{ route('login') }}">Masuk ke e-Raport</a>
                    @endauth
                    <a class="tombol tombol--garis" href="{{ route('guru.pwa.beranda') }}">Aplikasi guru di HP</a>
                </div>

                <p class="catatan">Akun dibuat oleh admin atau TU madrasah. Lupa kata sandi? Hubungi admin atau TU.</p>
            </div>

            <div class="meja" aria-hidden="true">
                <div class="buku">
                    <div class="sampul">
                        <div class="bingkai">
                            <svg><use href="#bintang" /></svg>
                            <svg><use href="#bintang" /></svg>
                            <svg><use href="#bintang" /></svg>
                            <svg><use href="#bintang" /></svg>
                        </div>

                        <p class="sampul__judul emas">Laporan<br>Hasil Belajar</p>

                        <div class="sampul__lambang">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="">
                            @else
                                <svg width="100%" height="100%"><use href="#bintang-garis" /></svg>
                            @endif
                        </div>

                        <p class="sampul__sekolah emas">{{ $namaSekolah }}</p>

                        @if ($tahunAktif)
                            <p class="sampul__tahun">{{ $tahunAktif->nama }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="wadah peran" aria-labelledby="judul-peran">
            <h2 id="judul-peran">Siapa mengerjakan apa</h2>
            <ul>
                <li>
                    <h3>Guru mata pelajaran</h3>
                    <p>Mengisi nilai sumatif dan STS untuk setiap kelas yang diajar. Nilai akhir dan predikat dihitung otomatis.</p>
                </li>
                <li>
                    <h3>Wali kelas</h3>
                    <p>Melengkapi absen, prestasi, dan catatan siswa, lalu mencetak rapor dan leger kelas.</p>
                </li>
                <li>
                    <h3>Pembimbing tahfidz</h3>
                    <p>Mencatat hafalan surah setiap siswa dan mencetak rapor tahfidz.</p>
                </li>
                <li>
                    <h3>Admin dan TU</h3>
                    <p>Menyiapkan tahun ajaran, data siswa dan guru, kelas, serta jadwal mengajar.</p>
                </li>
            </ul>
        </section>
    </main>

    <footer class="wadah kaki">
        <p>{{ $namaSekolah }}{{ $alamat ? ' · '.$alamat : '' }}</p>
        <p>&copy; {{ date('Y') }} e-Raport {{ $namaSekolah }}</p>
    </footer>
</body>

</html>
