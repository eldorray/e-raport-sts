<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport Tahfidz - {{ $siswa->nama }}</title>
    @php
        \Carbon\Carbon::setLocale('id');
    @endphp
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            color: #111;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        @page {
            size: 210mm 330mm portrait;
            margin: 10mm;
        }

        html, body {
            width: 210mm;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 210mm;
            min-height: calc(330mm - 20mm);
            padding: 10mm;
            margin: 0 auto;
            background: #fff;
        }

        /* Header */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 3px double #111;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .title-block {
            flex: 1;
            text-align: center;
        }

        .title-block h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .title-block h2 {
            margin: 4px 0;
            font-size: 16px;
            font-weight: bold;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .info-table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .info-table td.label {
            width: 160px;
        }

        .info-table td.separator {
            width: 10px;
        }

        /* Pengetahuan Table */
        .section-title {
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            margin: 12px 0 8px;
        }

        .penilaian-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .penilaian-table th,
        .penilaian-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        .penilaian-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .penilaian-table td.center {
            text-align: center;
        }

        /* Surah Grid */
        .surah-section {
            margin-top: 8px;
        }

        .surah-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border: 1px solid #000;
        }

        .surah-item {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border: 1px solid #000;
            font-size: 11px;
        }

        .surah-checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        .surah-checkbox.checked {
            background: #ffd700;
        }

        /* Deskripsi */
        .deskripsi-section {
            margin-top: 16px;
        }

        .deskripsi-title {
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            border: 1px solid #000;
            border-bottom: none;
            padding: 6px;
        }

        .deskripsi-content {
            border: 1px solid #000;
            padding: 10px 12px;
            text-align: justify;
            line-height: 1.6;
            min-height: 80px;
        }

        .note {
            margin-top: 8px;
            font-size: 11px;
            font-style: italic;
        }

        /* Signature */
        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 24px;
        }

        .signature {
            text-align: center;
        }

        .signature.left {
            text-align: left;
        }

        .signature.right {
            text-align: right;
        }

        .signature .title {
            margin-bottom: 4px;
        }

        .signature .name {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature .role {
            margin-top: 2px;
        }

        /* Three column signature */
        .signature-row-three {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 24px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="page">
        <!-- Header -->
        <header>
            <img src="{{ $school?->logo ? asset('storage/' . $school->logo) : asset('images/default-school.png') }}"
                alt="logo" class="logo">
            <div class="title-block">
                <h1>{{ $school->name ?? 'MI DAARUL HIKMAH' }}</h1>
                <h2>RAPORT TAHFIDZ AL-QUR'AN</h2>
            </div>
            <img src="{{ $school?->logo_right ? asset('storage/' . $school->logo_right) : asset('images/logo-kemenag.png') }}"
                alt="logo kanan" class="logo">
        </header>

        <!-- Info Siswa -->
        <table class="info-table">
            <tr>
                <td class="label">Nama Sekolah</td>
                <td class="separator">:</td>
                <td>{{ $school->name ?? '-' }}</td>
                <td class="label">Kelas</td>
                <td class="separator">:</td>
                <td>{{ $kelas->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td class="separator">:</td>
                <td>{{ $school->address ?? '-' }}</td>
                <td class="label">Semester</td>
                <td class="separator">:</td>
                <td>{{ $semester == 'ganjil' ? '1 (Satu)' : '2 (Dua)' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Peserta Didik</td>
                <td class="separator">:</td>
                <td>{{ $siswa->nama }}</td>
                <td class="label">Tahun Pelajaran</td>
                <td class="separator">:</td>
                <td>{{ $tahun->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Induk/NISN</td>
                <td class="separator">:</td>
                <td>{{ $siswa->nisn ?? '-' }}</td>
                <td colspan="3"></td>
            </tr>
        </table>

        <!-- Tabel Pengetahuan -->
        <table class="penilaian-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 30px;">No</th>
                    <th rowspan="2" style="width: 150px;">Mata Pelajaran</th>
                    <th colspan="2">Pengetahuan</th>
                </tr>
                <tr>
                    <th style="width: 60px;">Predikat</th>
                    <th>deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td>Adab</td>
                    <td class="center"><strong>{{ $penilaian->predikat_adab ?? '-' }}</strong></td>
                    <td>{{ $penilaian->deskripsi_adab ?? 'Baik' }}</td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td>Tajwid</td>
                    <td class="center"><strong>{{ $penilaian->predikat_tajwid ?? '-' }}</strong></td>
                    <td>{{ $penilaian->deskripsi_tajwid ?? 'Baik' }}</td>
                </tr>
                <tr>
                    <td class="center">3</td>
                    <td>Makhorijul Huruf</td>
                    <td class="center"><strong>{{ $penilaian->predikat_makhorijul ?? '-' }}</strong></td>
                    <td>{{ $penilaian->deskripsi_makhorijul ?? 'Cukup' }}</td>
                </tr>
                <tr>
                    <td class="center">4</td>
                    <td>Pencapaian Target Hafalan</td>
                    <td colspan="2" style="padding: 0;">
                        <!-- Surah Grid -->
                        <div class="surah-section">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="vertical-align: top; padding: 4px; width: 33%; border-right: 1px solid #000;">
                                        @php 
                                            $surahArray = array_keys($surahList);
                                            $surahHafalan = $penilaian->surah_hafalan ?? [];
                                        @endphp
                                        <table style="width: 100%; font-size: 11px;">
                                            <tr><td colspan="2" style="font-weight: bold; text-align: center; padding: 4px;">Surah</td></tr>
                                            @for ($i = 0; $i < 13; $i++)
                                                @php $key = $surahArray[$i] ?? null; @endphp
                                                @if ($key)
                                                    <tr>
                                                        <td style="width: 18px; padding: 2px;">
                                                            <span class="surah-checkbox {{ in_array($key, $surahHafalan) ? 'checked' : '' }}">
                                                                {{ in_array($key, $surahHafalan) ? '✓' : '' }}
                                                            </span>
                                                        </td>
                                                        <td style="padding: 2px;">{{ ($i + 1) }}. {{ $surahList[$key] }}</td>
                                                    </tr>
                                                @endif
                                            @endfor
                                        </table>
                                    </td>
                                    <td style="vertical-align: top; padding: 4px; width: 33%; border-right: 1px solid #000;">
                                        <table style="width: 100%; font-size: 11px;">
                                            <tr><td colspan="2">&nbsp;</td></tr>
                                            @for ($i = 13; $i < 26; $i++)
                                                @php $key = $surahArray[$i] ?? null; @endphp
                                                @if ($key)
                                                    <tr>
                                                        <td style="width: 18px; padding: 2px;">
                                                            <span class="surah-checkbox {{ in_array($key, $surahHafalan) ? 'checked' : '' }}">
                                                                {{ in_array($key, $surahHafalan) ? '✓' : '' }}
                                                            </span>
                                                        </td>
                                                        <td style="padding: 2px;">{{ ($i + 1) }}. {{ $surahList[$key] }}</td>
                                                    </tr>
                                                @endif
                                            @endfor
                                        </table>
                                    </td>
                                    <td style="vertical-align: top; padding: 4px; width: 33%;">
                                        <table style="width: 100%; font-size: 11px;">
                                            <tr><td colspan="2">&nbsp;</td></tr>
                                            @for ($i = 26; $i < 38; $i++)
                                                @php $key = $surahArray[$i] ?? null; @endphp
                                                @if ($key)
                                                    <tr>
                                                        <td style="width: 18px; padding: 2px;">
                                                            <span class="surah-checkbox {{ in_array($key, $surahHafalan) ? 'checked' : '' }}">
                                                                {{ in_array($key, $surahHafalan) ? '✓' : '' }}
                                                            </span>
                                                        </td>
                                                        <td style="padding: 2px;">{{ ($i + 1) }}. {{ $surahList[$key] }}</td>
                                                    </tr>
                                                @endif
                                            @endfor
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Deskripsi -->
        <div class="deskripsi-section">
            <div class="deskripsi-title">Deskripsi</div>
            <div class="deskripsi-content">
                {{ $penilaian->deskripsi }}
            </div>
        </div>

        <p class="note">
            NB: Jika salah lebih dari 3 kalimat maka dianggap belum hafal, sehingga kotak dikosongkan.
        </p>

        <!-- Tanda Tangan -->
        <div class="signature-row">
            <div class="signature left">
                <div class="title">Mengetahui</div>
                <div>Orang Tua/Wali</div>
                <div class="name">___________________</div>
            </div>
            <div class="signature right">
                <div class="title">{{ $printPlace }}, {{ optional($raporDate)->translatedFormat('F Y') }}</div>
                <div>Pembimbing Tahfizh</div>
                <div class="name">{{ $penilaian->pembimbing?->nama ?? '___________________' }}</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 24px;">
            <div>Mengetahui,</div>
            <div>Kepala {{ $school->name ?? 'MI Daarul Hikmah' }}</div>
            <div class="signature">
                <div class="name" style="margin-top: 60px;">{{ $school->headmaster ?? '-' }}</div>
                @if ($school->nip_headmaster ?? null)
                    <div>NIP. {{ $school->nip_headmaster }}</div>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
