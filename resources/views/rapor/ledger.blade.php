<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger Kelas {{ $kelas->nama }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            color: #111;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 100%;
            padding: 12mm 14mm;
            position: relative;
            overflow: hidden;
        }

        h1 {
            text-align: center;
            margin: 0 0 6px;
            font-size: 20px;
            letter-spacing: 0.5px;
        }

        .subtitle {
            text-align: center;
            margin: 0 0 12px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 4px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        .no-border {
            border: none !important;
        }

        .header-table td {
            border: none;
            padding: 2px 4px;
            font-size: 12px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .footer {
            margin-top: 28px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            font-size: 11px;
        }

        .signature {
            text-align: left;
        }

        .watermark {
            position: absolute;
            inset: 0;
            opacity: 0.08;
            pointer-events: none;
            background-size: 37mm 26mm;
            background-repeat: repeat;
            background-position: 0 0;
        }
    </style>
</head>

<body>
    <div class="page">
        @if ($watermarkDataUrl)
            <div class="watermark" style="background-image: url('{{ $watermarkDataUrl }}');"></div>
        @endif
        <h1>LEGGER KELAS</h1>
        <p class="subtitle">Kelas: {{ $kelas->nama ?? '-' }} &nbsp;•&nbsp; Tahun Pelajaran:
            {{ $tahun->nama ?? $tahunId }}
            &nbsp;•&nbsp; Semester: {{ ucfirst($semester) }}</p>

        <table class="header-table" style="margin-bottom: 10px;">
            <tr>
                <td style="width: 80px;">Kelas</td>
                <td style="width: 6px;">:</td>
                <td>{{ $kelas->nama ?? '-' }}</td>
                <td style="width: 120px;">Tahun Pelajaran</td>
                <td style="width: 6px;">:</td>
                <td>{{ $tahun->nama ?? $tahunId }}</td>
            </tr>
            <tr>
                <td>Madrasah</td>
                <td>:</td>
                <td>{{ $school->name ?? '—' }}</td>
                <td>Semester</td>
                <td>:</td>
                <td>{{ ucfirst($semester) }}</td>
            </tr>
        </table>

        @php
            $mapelList = $mapels->map(fn($m) => $m->mataPelajaran)->filter()->values();
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th style="width:200px;">Nama</th>
                    @foreach ($mapelList as $mapel)
                        <th class="nowrap">{{ $mapel->kode ?? $mapel->nama_mapel }}</th>
                    @endforeach
                    <th style="width:60px;">Total</th>
                    <th style="width:50px;">Ranking</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nilai as $idx => $row)
                    @php($student = $row['siswa'])
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $student->nama }}</td>
                        @foreach ($mapelList as $mapel)
                            @php($v = $row['mapels'][$mapel->id] ?? null)
                            <td class="text-center">{{ $v !== null ? $v : '' }}</td>
                        @endforeach
                        <td class="text-center">{{ $row['total'] ?: '' }}</td>
                        <td class="text-center">{{ $row['ranking'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <div class="signature">
                <div>Mengetahui</div>
                <div>Kepala Madrasah</div>
                <div style="margin-top:32px; text-decoration: underline; font-weight: bold;">
                    {{ $school->headmaster ?? '____________________' }}</div>
                <div>NIP. {{ $school->nip_headmaster ?? '____________________' }}</div>
            </div>
            <div class="signature" style="text-align:right;">
                <div>{{ $printPlace }}, {{ optional($printDate)->translatedFormat('d F Y') }}</div>
                <div>Wali Kelas:</div>
                <div style="margin-top:32px; text-decoration: underline; font-weight: bold;">
                    {{ $kelas->guru->nama ?? '____________________' }}</div>
                <div>NIP. {{ $kelas->guru->nip ?? '____________________' }}</div>
            </div>
        </div>
    </div>
</body>

</html>
