@php
    $kelasList = collect($rencana['kelas']);
    $jumlahSiswa = $kelasList->sum(fn (array $k): int => count($k['siswa']));
    $tombolSemua = [
        'naik' => __('Semua naik'),
        'tinggal' => __('Semua tinggal kelas'),
        'lulus' => __('Semua lulus'),
    ];
@endphp

<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100">
    <h2 class="text-base font-semibold">{{ __('Kenaikan kelas') }}</h2>
    <ul class="mt-2 list-disc space-y-1 pl-5">
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
        class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-gray-800 {{ $errors->has('kelas_tujuan.'.$kelas['id']) ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700' }}">
        <div class="flex flex-col gap-4 border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40 md:flex-row md:items-end md:justify-between">
            <div>
                <h3 class="flex flex-wrap items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Kelas :nama', ['nama' => $kelas['nama']]) }}
                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                        {{ __('Tingkat :tingkat', ['tingkat' => $kelas['tingkat'] !== '' ? $kelas['tingkat'] : '—']) }}
                    </span>
                    @if ($kelas['tertinggi'])
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                            {{ __('Tingkat tertinggi: bawaan Lulus') }}
                        </span>
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Wali sekarang: :wali', ['wali' => $kelas['wali'] ?? '—']) }} ·
                    {{ __(':jumlah siswa aktif', ['jumlah' => count($kelas['siswa'])]) }}
                </p>
            </div>
            <div class="w-full md:w-64">
                <label for="kelas_tujuan_{{ $kelas['id'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Siswa naik masuk ke kelas') }}
                </label>
                <input id="kelas_tujuan_{{ $kelas['id'] }}" name="kelas_tujuan[{{ $kelas['id'] }}]" type="text"
                    maxlength="{{ \App\Services\TahunAjaranBaruService::MAX_NAMA_KELAS }}" x-model="tujuan"
                    value="{{ $tujuanAwal }}" placeholder="{{ __('mis. 2A') }}"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                @error('kelas_tujuan.'.$kelas['id'])
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($kelas['siswa'] === [])
            <p class="px-6 py-6 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Tidak ada siswa aktif di kelas ini.') }}
            </p>
        @else
            <div class="flex flex-wrap items-center gap-2 px-6 pt-4">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Atur cepat') }}</span>
                @foreach ($tombolSemua as $nilai => $label)
                    <button type="button"
                        @click="$root.querySelectorAll('select[data-bawaan]').forEach((s) => s.value = @js($nilai))"
                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="overflow-x-auto px-6 pb-6 pt-3">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                    <thead class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                        <tr>
                            <th class="w-12 px-3 py-3">{{ __('No') }}</th>
                            <th class="px-3 py-3">{{ __('NIS') }}</th>
                            <th class="px-3 py-3">{{ __('Nama') }}</th>
                            <th class="px-3 py-3">{{ __('Keputusan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($kelas['siswa'] as $siswa)
                            @php
                                $pilihan = (string) old('pilihan_siswa.'.$siswa['id'], $siswa['pilihan']);
                            @endphp
                            <tr>
                                <td class="px-3 py-2">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2">{{ $siswa['nis'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $siswa['nama'] }}</td>
                                <td class="px-3 py-2">
                                    <select name="pilihan_siswa[{{ $siswa['id'] }}]" data-bawaan="{{ $siswa['bawaan'] }}"
                                        aria-label="{{ __('Keputusan untuk :nama', ['nama' => $siswa['nama']]) }}"
                                        class="w-full min-w-[12rem] rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
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
