<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Rapor Kelas :kelas', ['kelas' => $kelas->nama]) }}</title>
    @php
        // Set locale ke Indonesia untuk format tanggal
        \Carbon\Carbon::setLocale('id');
    @endphp
    @include('rapor.partials.lembar-style')
    <style>
        /* Setiap siswa setelah yang pertama mulai di halaman cetak baru */
        .ganti-halaman {
            break-before: page;
            page-break-before: always;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            margin: 0 0 8mm;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 13px;
        }

        .toolbar-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .toolbar-info strong {
            font-size: 15px;
        }

        .toolbar-info span {
            color: #4b5563;
        }

        .toolbar-aksi {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-aksi a,
        .toolbar-aksi button {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 6px;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar-aksi a {
            color: #374151;
            background: #fff;
            border: 1px solid #d1d5db;
        }

        .toolbar-aksi button {
            color: #fff;
            background: #2563eb;
            border: 1px solid #2563eb;
        }

        .toolbar-aksi button:hover {
            background: #1d4ed8;
        }

        .kosong {
            padding: 24px;
            text-align: center;
            background: #fff;
            border: 1px dashed #9ca3af;
            border-radius: 8px;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 14px;
            color: #4b5563;
        }

        @media screen {
            body {
                background: #e5e7eb;
                padding: 8mm 0;
            }

            .rapor-siswa+.rapor-siswa {
                margin-top: 8mm;
            }
        }

        @media print {
            .toolbar {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <div class="toolbar-info">
            <strong>{{ __('Kelas :kelas', ['kelas' => $kelas->nama]) }}</strong>
            <span>{{ __('Tahun Ajaran :tahun • Semester :semester', ['tahun' => $tahun->nama, 'semester' => ucfirst($semester)]) }}</span>
            <span>{{ __(':jumlah siswa', ['jumlah' => $lembars->count()]) }}</span>
        </div>
        <div class="toolbar-aksi">
            <a href="{{ route('rapor.index', ['kelas_id' => $kelas->id]) }}">{{ __('Kembali') }}</a>
            @if ($lembars->isNotEmpty())
                <button type="button" onclick="window.print()">{{ __('Cetak / Simpan PDF') }}</button>
            @endif
        </div>
    </div>

    @forelse ($lembars as $lembar)
        <section class="rapor-siswa{{ $loop->first ? '' : ' ganti-halaman' }}">
            @include('rapor.partials.lembar', $lembar)
        </section>
    @empty
        <div class="kosong">{{ __('Belum ada siswa aktif di kelas ini.') }}</div>
    @endforelse
</body>

</html>
