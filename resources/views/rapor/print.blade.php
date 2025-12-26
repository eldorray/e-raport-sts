<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor {{ $siswa->nama }}</title>
    <style>
        * {
            box-sizing: border-box, ;
        }

        body {
            font-family: 'Times New Roman', serif;
            color: #111;
            margin: 0;
            padding: 0;
        }

        @page {
            size: 210mm 330mm portrait;
            margin: 10mm;
        }

        html,
        body {
            width: 210mm;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 210mm;
            min-height: calc(330mm - 20mm);
            padding: 10mm;
            margin: 0 auto;
            position: relative;
            background: #fff;
            page-break-inside: avoid;
        }

        .page-break:not(:last-child) {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .watermark {
            position: absolute;
            inset: 0;
            opacity: 0.12;
            pointer-events: none;
            background-size: 37mm 26mm;
            background-repeat: repeat;
            background-position: 0 0;
            background-attachment: scroll;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @media print {
            .watermark {
                background-size: 37mm 26mm !important;
                background-position: 0 0 !important;
                background-repeat: repeat !important;
                background-attachment: scroll !important;
                transform: none !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #111;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .title-block {
            flex: 1;
            text-align: center;
        }

        .title-block h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .title-block h2 {
            margin: 2px 0;
            font-size: 16px;
            text-transform: uppercase;
        }

        .title-block p {
            margin: 0;
            font-size: 12px;
            line-height: 1.3;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .info-table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .info-table td.label {
            width: 120px;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0 6px;
            text-transform: uppercase;
        }

        .nilai-table th,
        .nilai-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .nilai-table th {
            text-align: center;
            font-weight: 700;
        }

        .nilai-table td.mapel {
            width: 50%;
        }

        .nilai-table td.nilai {
            width: 12%;
            text-align: center;
        }

        .nilai-table td.deskripsi {
            width: 38%;
        }

        .group-row td {
            font-weight: bold;
            background: #f6f6f6;
        }

        .sub {
            padding-left: 14px;
        }

        .two-col {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 10px;
        }

        .block {
            border: 1px solid #000;
            padding: 8px;
        }

        .block h3 {
            margin: 0 0 6px;
            font-size: 13px;
            text-transform: uppercase;
            text-align: center;
        }

        .small-table th,
        .small-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 18px;
            font-size: 12px;
        }

        .signature {
            text-align: center;
            min-height: 130px;
            position: relative;
        }

        .signature .name {
            margin-top: 64px;
            font-weight: 700;
            text-decoration: underline;
        }

        .signature .nip {
            margin-top: 2px;
        }

        .qr-box {
            border: 1px solid #000;
            width: 90px;
            height: 90px;
            margin: 6px auto 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .note {
            font-size: 11px;
            line-height: 1.4;
            border: 1px solid #000;
            padding: 8px;
            min-height: 60px;
        }

        .mt-8 {
            margin-top: 8px;
        }

        .mt-12 {
            margin-top: 12px;
        }

        .mb-4 {
            margin-bottom: 4px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="page">
        @if ($watermarkDataUrl)
            <div class="watermark" style="background-image: url('{{ $watermarkDataUrl }}');"></div>
        @endif
        <header>
            <img src="{{ $school?->logo ? asset('storage/' . $school->logo) : asset('images/default-school.png') }}"
                alt="logo" class="logo">
            <div class="title-block">
                <h1>YAYASAN PENDIDIKAN DAARUL HIKMAH AL MADANI</h1>
                <h2>{{ $school->name ?? 'Nama Madrasah' }}</h2>
                <p>{{ $school->address ?? '-' }}</p>
                <p>{{ $school->district ?? '' }} {{ $school->city ? '• ' . $school->city : '' }}
                    {{ $school->province ? '• ' . $school->province : '' }}</p>
            </div>
            <img src="{{ asset('images/logo-kemenag.png') }}" alt="Logo Kemenag" class="logo">
        </header>

        <table class="info-table">
            <tr>
                <td class="label">Nama</td>
                <td>: {{ $siswa->nama }}</td>
                <td class="label">Kelas</td>
                <td>: {{ $kelas->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">NIS/NISN</td>
                <td>: {{ $siswa->nis ?? '-' }} / {{ $siswa->nisn ?? '-' }}</td>
                <td class="label">Fase</td>
                <td>: {{ $kelas->tingkat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Madrasah</td>
                <td>: {{ $school->name ?? '-' }}</td>
                <td class="label">Semester</td>
                <td>: {{ ucfirst($semester) }}</td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td>: {{ $school->address ?? '-' }}</td>
                <td class="label">Tahun Ajaran</td>
                <td>: {{ $tahun?->nama ?? '-' }}</td>
            </tr>
        </table>
        <hr style="margin:12px 0; border: none; border-top: 1px solid #000;" />
        <div class="section-title">Capaian Hasil Belajar Asessment Tengah Semester (ASTS)</div>
        <table class="nilai-table">
            <thead>
                <tr>
                    <th style="width:6%">No</th>
                    <th style="width:48%">Mata Pelajaran</th>
                    <th style="width:10%">Nilai Akhir</th>
                    <th>Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentGroup = null;
                    $i = 1;
                @endphp
                @forelse($nilai as $row)
                    @php $kel = $row['kelompok'] ?? null; @endphp
                    @if ($kel !== $currentGroup)
                        <tr class="group-row">
                            <td colspan="4">{{ $kel ?? 'Mata Pelajaran' }}</td>
                        </tr>
                        @php $currentGroup = $kel; @endphp
                    @endif
                    <tr>
                        <td style="text-align:center">{{ $i++ }}</td>
                        <td class="mapel">{{ $row['mapel']?->nama_mapel ?? '-' }}</td>
                        <td class="nilai">{{ $row['rapor'] ?? '-' }}</td>
                        <td class="deskripsi">
                            @if (!empty($row['descriptor']))
                                <div><strong>{{ $row['descriptor']['predikat'] }} •
                                        {{ $row['descriptor']['keterangan'] }}</strong></div>
                                <div>{{ $row['descriptor']['kalimat'] }}</div>
                            @else
                                {{ $row['deskripsi'] ?? '' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:12px;">Belum ada nilai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page">
        @if ($watermarkDataUrl)
            <div class="watermark" style="background-image: url('{{ $watermarkDataUrl }}');"></div>
        @endif
        <div class="two-col">
            <div class="block">
                <h3>Ekstrakurikuler</h3>
                <table class="small-table">
                    <thead>
                        <tr>
                            <th style="width:8%">No</th>
                            <th style="width:42%">Kegiatan</th>
                            <th style="width:15%">Nilai</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ekskul as $idx => $item)
                            <tr>
                                <td style="text-align:center">{{ $idx + 1 }}</td>
                                <td>{{ $item->ekskul?->nama ?? '-' }}</td>
                                <td style="text-align:center">{{ $item->nilai ?? '-' }}</td>
                                <td>{{ $item->catatan ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding:10px;">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="block">
                <h3>Prestasi</h3>
                <table class="small-table">
                    <thead>
                        <tr>
                            <th style="width:8%">No</th>
                            <th style="width:35%">Jenis Prestasi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prestasi as $idx => $p)
                            <tr>
                                <td style="text-align:center">{{ $idx + 1 }}</td>
                                <td>{{ $p['jenis'] ?? '' }}</td>
                                <td>{{ $p['keterangan'] ?? '' }}</td>
                            </tr>
                        @empty
                            @for ($k = 1; $k <= 3; $k++)
                                <tr>
                                    <td style="text-align:center">{{ $k }}</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endfor
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="two-col mt-12">
            <div class="block">
                <h3>Ketidakhadiran</h3>
                <table class="small-table">
                    <tbody>
                        <tr>
                            <td style="width:60%">Sakit</td>
                            <td style="text-align:center">{{ $meta->sakit ?? 0 }}</td>
                            <td>Hari</td>
                        </tr>
                        <tr>
                            <td>Izin</td>
                            <td style="text-align:center">{{ $meta->izin ?? 0 }}</td>
                            <td>Hari</td>
                        </tr>
                        <tr>
                            <td>Alpa</td>
                            <td style="text-align:center">{{ $meta->alpa ?? 0 }}</td>
                            <td>Hari</td>
                        </tr>
                    </tbody>
                </table>
                <h3 class="mt-8">Catatan Wali Kelas</h3>
                <div class="note">{!! nl2br(e($meta->catatan_wali ?? '')) !!}</div>
            </div>
            <div class="block">
                <h3>Tanggapan Orang Tua/Wali</h3>
                <div class="note" style="height:120px;">{!! nl2br(e($meta->tanggapan_ortu ?? '')) !!}</div>
            </div>
        </div>

        <div class="signature-row" style="margin-top:20px; grid-template-columns: repeat(3, 1fr);">
            <div class="signature" style="text-align:left;">
                <div>{{ $printPlace }}, {{ optional($raporDate)->translatedFormat('d F Y') }}</div>
                <div style="margin-top:4px;">Orang Tua/Wali</div>
                <div class="name" style="margin-top:70px;">___________________</div>
            </div>
            <div class="signature">
                <div>Mengetahui</div>
                <div style="margin-top:2px;">Kepala Madrasah</div>
                <div class="name" style="margin-top:70px;">{{ $school->headmaster ?? '—' }}</div>
                <div class="nip">NIP. {{ $school->nip_headmaster ?? '-' }}</div>
            </div>
            <div class="signature" style="text-align:right;">
                <div>&nbsp;</div>
                <div style="margin-top:4px;">Wali Kelas</div>
                <div class="name" style="margin-top:70px;">{{ $wali->nama ?? '—' }}</div>
                <div class="nip">NIP. {{ $wali->nip ?? '-' }}</div>
            </div>
        </div>
    </div>
</body>

</html>
