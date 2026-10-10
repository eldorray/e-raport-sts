{{-- Isi rapor satu siswa (dua halaman). Dipakai rapor.print dan rapor.print-kelas agar keduanya identik. --}}
<div class="page">
    @if ($watermarkDataUrl)
        <div class="watermark" style="background-image: url('{{ $watermarkDataUrl }}');"></div>
    @endif
    <header>
        <img src="{{ $school?->logo ? asset('storage/' . $school->logo) : asset('images/default-school.png') }}"
            alt="logo" class="logo">
        <div class="title-block">
            @if ($namaYayasan)
                <h1>{{ $namaYayasan }}</h1>
            @endif
            <h2>{{ $school->name ?? 'Nama Madrasah' }}</h2>
            <p>{{ $school->address ?? '-' }}</p>
            <p>{{ $school->district ?? '' }} {{ $school->city ? '• ' . $school->city : '' }}
                {{ $school->province ? '• ' . $school->province : '' }}</p>
        </div>
        <img src="{{ $school?->logo_right ? asset('storage/' . $school->logo_right) : asset('images/logo-kemenag.png') }}" alt="Logo Kanan" class="logo">
    </header>

    <table class="info-table">
        <tr>
            <td class="label">Nama</td>
            <td>: {{ $siswa->nama }}</td>
            <td class="label">Kelas</td>
            <td>: {{ $kelas->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NISN</td>
            <td>: {{ $siswa->nisn ?? '-' }}</td>
            <td class="label">Semester</td>
            <td>: {{ ucfirst($semester) }}</td>
        </tr>
        <tr>
            <td class="label">Madrasah</td>
            <td>: {{ $school->name ?? '-' }}</td>
            <td class="label">Tahun Ajaran</td>
            <td>: {{ $tahun?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td colspan="3">: {{ $school->address ?? '-' }}</td>
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
                        <th style="width:15%">Predikat</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ekskul as $idx => $item)
                        <tr>
                            <td style="text-align:center">{{ $idx + 1 }}</td>
                            <td>{{ $item->ekskul?->nama ?? '-' }}</td>
                            <td style="text-align:center">{{ $item->predikat ?? '-' }}</td>
                            <td>{{ $item->predikat_keterangan ?? '' }}</td>
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

    {{-- Tanda tangan: Orang Tua/Wali (kiri), tanggal + Wali Kelas (kanan), Kepala Madrasah (tengah bawah) --}}
    <div class="ttd">
        <div class="ttd-baris">
            <div class="ttd-blok">
                <div>&nbsp;</div>
                <div>Orang Tua/Wali</div>
                <div class="ttd-garis"></div>
            </div>
            <div class="ttd-blok">
                <div>{{ $printPlace }}, {{ optional($raporDate)->translatedFormat('d F Y') }}</div>
                <div>Wali Kelas</div>
                <div class="ttd-nama">{{ $wali->nama ?? '—' }}</div>
                <div>NIP. {{ $wali?->nip ?: '-' }}</div>
            </div>
        </div>
        <div class="ttd-tengah">
            <div class="ttd-blok">
                <div>Mengetahui</div>
                <div>Kepala Madrasah</div>
                <div class="ttd-nama">{{ $school?->headmaster ?: '—' }}</div>
                <div>NIP. {{ $school?->nip_headmaster ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
