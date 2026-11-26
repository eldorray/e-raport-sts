@php
    $kelasList = collect($rencana['kelas']);
    $jumlahSiswa = $kelasList->sum(fn (array $k): int => count($k['siswa']));
    $tombolSemua = [
        'naik' => __('Semua naik'),
        'tinggal' => __('Semua tinggal kelas'),
        'lulus' => __('Semua lulus'),
    ];
@endphp

<div class="bg-emerald-50 p-5 text-sm text-emerald-900 max-md:p-4 dark:bg-emerald-900/30 dark:text-emerald-100 rounded-2xl">
    <h2 class="text-base font-semibold">{{ __('Kenaikan kelas') }}</h2>
    <ul class="mt-2 list-disc space-y-1 pl-5 max-md:pl-4">
        <li>{{ __('Periksa nama kelas tujuan dan keputusan setiap siswa: Naik, Tinggal kelas (masuk kelas bernama sama seperti sekarang), atau Lulus (tidak disalin).') }}</li>
        <li><strong>{{ __('Wali kelas tidak ikut disalin') }}</strong> — {{ __('atur wali kelas baru di menu Kelas setelah wizard selesai.') }}</li>
        <li>{{ __('Jadwal mengajar kelas tujuan disalin dari kelas bernama sama di tahun sumber (mis. kelas 2A baru memakai jadwal kelas 2A lama), karena guru mapel biasanya tetap di tingkatnya.') }}</li>
        <li>{{ __('Kelas tujuan hanya dibuat bila ada siswa yang masuk. Kelas untuk siswa baru (mis. kelas 1) dibuat sendiri di menu Kelas.') }}</li>
        <li>{{ __('Siswa di tingkat tertinggi otomatis dipilih Lulus. Ubah bila tidak tepat.') }}</li>
    </ul>
    <p class="mt-3 font-semibold">
        {{ __(':kelas kelas, :siswa siswa aktif.', ['kelas' => $kelasList->count(), 'siswa' => $jumlahSiswa]) }}
        @if ($rencana['siswa_tanpa_kelas'] > 0)
            {{ __(':jumlah siswa aktif belum punya kelas dan tidak ikut disalin.', ['jumlah' => $rencana['siswa_tanpa_kelas']]) }}
        @endif
    </p>
</div>

@foreach ($kelasList as $kelas)
    @php
        $tujuanAwal = (string) old('kelas_tujuan.'.$kelas['id'], $kelas['tujuan']);
    @endphp
    <section x-data="{ tujuan: @js($tujuanAwal) }"
        class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-zinc-800 {{ $errors->has('kelas_tujuan.'.$kelas['id']) ? 'border-red-400 dark:border-red-500' : 'border-zinc-200 dark:border-zinc-700' }}">
        <div class="flex flex-col gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-4 max-md:gap-3 max-md:px-4 dark:border-zinc-700 dark:bg-zinc-900/40 md:flex-row md:items-end md:justify-between">
            <div>
                <h3 class="flex flex-wrap items-center gap-2 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Kelas :nama', ['nama' => $kelas['nama']]) }}
                    <span class="rounded-full bg-zinc-200 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                        {{ __('Tingkat :tingkat', ['tingkat' => $kelas['tingkat'] !== '' ? $kelas['tingkat'] : '—']) }}
                    </span>
                    @if ($kelas['tertinggi'])
                        <span class="pil pil-peringatan px-2 py-0.5">
                            {{ __('Tingkat tertinggi: bawaan Lulus') }}
                        </span>
                    @endif
                </h3>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Wali sekarang: :wali', ['wali' => $kelas['wali'] ?? '—']) }} ·
                    {{ __(':jumlah siswa aktif', ['jumlah' => count($kelas['siswa'])]) }}
                </p>
            </div>
            <div class="w-full md:w-64">
                <label for="kelas_tujuan_{{ $kelas['id'] }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    {{ __('Siswa naik masuk ke kelas') }}
                </label>
                <input id="kelas_tujuan_{{ $kelas['id'] }}" name="kelas_tujuan[{{ $kelas['id'] }}]" type="text"
                    maxlength="{{ \App\Services\TahunAjaranBaruService::MAX_NAMA_KELAS }}" x-model="tujuan"
                    value="{{ $tujuanAwal }}" placeholder="{{ __('mis. 2A') }}"
                    class="isian mt-1 w-full px-4 py-2 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                @error('kelas_tujuan.'.$kelas['id'])
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($kelas['siswa'] === [])
            <p class="px-6 py-6 text-sm text-zinc-600 max-md:px-4 max-md:py-4 dark:text-zinc-400">
                {{ __('Tidak ada siswa aktif di kelas ini.') }}
            </p>
        @else
            <div class="flex flex-wrap items-center gap-2 px-6 pt-4 max-md:px-4">
                <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600 max-md:w-full dark:text-zinc-400">{{ __('Atur cepat') }}</span>
                @foreach ($tombolSemua as $nilai => $label)
                    <button type="button"
                        @click="$root.querySelectorAll('select[data-bawaan]').forEach((s) => s.value = @js($nilai))"
                        class="pil-aksi max-md:min-h-11 max-md:px-4 max-md:text-sm">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            {{-- Di HP setiap baris tabel tampil sebagai kartu (tanpa menggandakan select, sehingga nama isian tetap satu). --}}
            <div class="overflow-x-auto px-6 pb-6 pt-3 max-md:px-4 max-md:pb-4">
                <table class="tabel min-w-full text-left text-sm text-zinc-700 max-md:block max-md:divide-y-0 dark:text-zinc-200">
                    <thead class="max-md:hidden">
                        <tr>
                            <th class="w-12 px-3 py-3">{{ __('No') }}</th>
                            <th class="px-3 py-3">{{ __('NIS') }}</th>
                            <th class="px-3 py-3">{{ __('Nama') }}</th>
                            <th class="px-3 py-3">{{ __('Keputusan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="max-md:block max-md:space-y-3 max-md:divide-y-0">
                        @foreach ($kelas['siswa'] as $siswa)
                            @php
                                $pilihan = (string) old('pilihan_siswa.'.$siswa['id'], $siswa['pilihan']);
                            @endphp
                            <tr class="max-md:grid max-md:grid-cols-[auto_minmax(0,1fr)] max-md:gap-x-3 max-md:gap-y-1 max-md:rounded-xl max-md:border max-md:border-zinc-200 max-md:p-3 max-md:dark:border-zinc-700">
                                <td class="px-3 py-2 max-md:row-span-2 max-md:flex max-md:h-8 max-md:w-8 max-md:items-center max-md:justify-center max-md:rounded-full max-md:bg-zinc-100 max-md:p-0 max-md:text-xs max-md:font-semibold max-md:text-zinc-600 max-md:dark:bg-zinc-700 max-md:dark:text-zinc-300">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2 max-md:col-start-2 max-md:row-start-2 max-md:p-0 max-md:text-xs max-md:text-zinc-600 max-md:dark:text-zinc-400"><span class="md:hidden">{{ __('NIS') }}</span> {{ $siswa['nis'] }}</td>
                                <td class="px-3 py-2 font-medium text-zinc-900 max-md:col-start-2 max-md:row-start-1 max-md:p-0 max-md:break-words dark:text-zinc-100">{{ $siswa['nama'] }}</td>
                                <td class="px-3 py-2 max-md:col-span-2 max-md:mt-2 max-md:p-0">
                                    <select name="pilihan_siswa[{{ $siswa['id'] }}]" data-bawaan="{{ $siswa['bawaan'] }}"
                                        aria-label="{{ __('Keputusan untuk :nama', ['nama' => $siswa['nama']]) }}"
                                        class="isian w-full min-w-[12rem] px-3 py-1.5 text-sm max-md:h-11 max-md:min-w-0 max-md:py-0 max-md:text-base">
                                        <option value="naik" @selected($pilihan === 'naik')
                                            x-text="@js(__('Naik ke')) + ' ' + (tujuan || '…')">
                                            {{ __('Naik ke') }} {{ $tujuanAwal !== '' ? $tujuanAwal : '…' }}
                                        </option>
                                        <option value="tinggal" @selected($pilihan === 'tinggal')>
                                            {{ __('Tinggal kelas di :kelas', ['kelas' => $kelas['nama']]) }}
                                        </option>
                                        <option value="lulus" @selected($pilihan === 'lulus')>
                                            {{ __('Lulus (tidak disalin)') }}
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endforeach
