<x-layouts.app>
    @php
        $saran = $usulan[$sumberId] ?? null;
        $isi = fn (string $kunci, mixed $bawaan) => (string) old($kunci, request()->query($kunci, $bawaan));
        $awal = [
            'sumber' => (string) $sumberId,
            'pilihan' => $isi('tujuan_pilihan', $saran && $saran['tujuan_id'] ? 'ada' : 'baru'),
            'tujuanId' => $isi('tujuan_id', $saran['tujuan_id'] ?? ''),
            'nama' => $isi('tujuan_nama', $saran['nama'] ?? ''),
            'mulai' => $isi('tujuan_tahun_mulai', $saran['tahun_mulai'] ?? ''),
            'selesai' => $isi('tujuan_tahun_selesai', $saran['tahun_selesai'] ?? ''),
            'semester' => $isi('tujuan_semester', $saran['semester'] ?? ''),
        ];
        $inputClass = 'mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    @endphp

    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Tahun Ajaran Baru') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Pindahkan seluruh sekolah ke semester atau tahun ajaran berikutnya sekaligus: kelas, rombel siswa, dan jadwal mengajar.') }}
            </p>
        </div>
        <a href="{{ route('tahun-ajaran.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300">
            <i class="fas fa-arrow-left"></i> {{ __('Kembali ke Tahun Ajaran') }}
        </a>
    </div>

    @include('lembaga.tahun-ajaran-baru-langkah', ['langkah' => 1])

    <div class="grid gap-6 lg:grid-cols-[1fr,380px]">
        <form method="GET" action="{{ route('tahun-ajaran-baru.preview') }}"
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            x-data="{
                ...@js($awal),
                usulan: @js((object) $usulan),
                terapkanUsulan() {
                    const saran = this.usulan[this.sumber];
                    if (! saran) return;
                    this.nama = saran.nama;
                    this.mulai = String(saran.tahun_mulai);
                    this.selesai = String(saran.tahun_selesai);
                    this.semester = saran.semester;
                    this.pilihan = saran.tujuan_id ? 'ada' : 'baru';
                    this.tujuanId = saran.tujuan_id ? String(saran.tujuan_id) : '';
                },
            }">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900/40">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Pilih Tahun Ajaran') }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Tahun ajaran sumber hanya dibaca dan tidak berubah. Tahun ajaran tujuan harus masih kosong.') }}
                </p>
            </div>

            <div class="space-y-6 px-6 py-6">
                <div>
                    <label for="sumber_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Tahun ajaran sumber') }}
                    </label>
                    <select id="sumber_id" name="sumber_id" x-model="sumber" @change="terapkanUsulan()" required
                        class="{{ $inputClass }}">
                        @foreach ($tahunAjarans as $tahun)
                            <option value="{{ $tahun->id }}" @selected($tahun->id === (int) $awal['sumber'])>
                                {{ $tahun->label() }}{{ $tahun->is_active ? ' — '.__('aktif') : '' }}
                                ({{ __(':kelas kelas, :siswa siswa', ['kelas' => $tahun->kelas_count, 'siswa' => $tahun->siswas_count]) }})
                            </option>
                        @endforeach
                    </select>
                    @error('sumber_id')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <fieldset>
                    <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Tahun ajaran tujuan') }}</legend>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 text-sm transition hover:border-blue-300 dark:border-gray-700"
                            :class="pilihan === 'baru' ? 'border-blue-500 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-900/20' : ''">
                            <input type="radio" name="tujuan_pilihan" value="baru" x-model="pilihan" @checked($awal['pilihan'] === 'baru')
                                class="mt-0.5 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>
                                <span class="block font-semibold text-gray-900 dark:text-gray-100">{{ __('Buat tahun ajaran baru') }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Isian diusulkan otomatis dari tahun ajaran sumber.') }}</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 text-sm transition hover:border-blue-300 dark:border-gray-700"
                            :class="pilihan === 'ada' ? 'border-blue-500 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-900/20' : ''">
                            <input type="radio" name="tujuan_pilihan" value="ada" x-model="pilihan" @checked($awal['pilihan'] === 'ada')
                                class="mt-0.5 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>
                                <span class="block font-semibold text-gray-900 dark:text-gray-100">{{ __('Pakai tahun ajaran yang sudah ada') }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Hanya tahun ajaran yang masih kosong yang bisa dipilih.') }}</span>
                            </span>
                        </label>
                    </div>
                    @error('tujuan_pilihan')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div x-show="pilihan === 'ada'">
                    <label for="tujuan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Tahun ajaran tujuan') }}
                    </label>
                    <select id="tujuan_id" name="tujuan_id" x-model="tujuanId" :disabled="pilihan !== 'ada'" class="{{ $inputClass }}">
                        <option value="">{{ __('-- Pilih tahun ajaran --') }}</option>
                        @foreach ($tahunAjarans as $tahun)
                            <option value="{{ $tahun->id }}" @selected((string) $tahun->id === $awal['tujuanId'])
                                @disabled($berisiData[$tahun->id] ?? false)
                                :disabled="sumber === '{{ $tahun->id }}' || {{ ($berisiData[$tahun->id] ?? false) ? 'true' : 'false' }}">
                                {{ $tahun->label() }}{{ ($berisiData[$tahun->id] ?? false) ? ' — '.__('sudah berisi data') : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('tujuan_id')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="pilihan === 'baru'" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tujuan_nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Nama (contoh: 2026/2027)') }}
                        </label>
                        <input id="tujuan_nama" name="tujuan_nama" type="text" maxlength="50" x-model="nama"
                            value="{{ $awal['nama'] }}" :disabled="pilihan !== 'baru'" class="{{ $inputClass }}">
                        @error('tujuan_nama')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tujuan_semester" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Semester') }}
                        </label>
                        <select id="tujuan_semester" name="tujuan_semester" x-model="semester" :disabled="pilihan !== 'baru'"
                            class="{{ $inputClass }}">
                            <option value="Ganjil" @selected($awal['semester'] === 'Ganjil')>{{ __('Ganjil') }}</option>
                            <option value="Genap" @selected($awal['semester'] === 'Genap')>{{ __('Genap') }}</option>
                        </select>
                        @error('tujuan_semester')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tujuan_tahun_mulai" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Tahun Mulai') }}
                        </label>
                        <input id="tujuan_tahun_mulai" name="tujuan_tahun_mulai" type="number" x-model="mulai"
                            value="{{ $awal['mulai'] }}" :disabled="pilihan !== 'baru'" class="{{ $inputClass }}">
                        @error('tujuan_tahun_mulai')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tujuan_tahun_selesai" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Tahun Selesai') }}
                        </label>
                        <input id="tujuan_tahun_selesai" name="tujuan_tahun_selesai" type="number" x-model="selesai"
                            value="{{ $awal['selesai'] }}" :disabled="pilihan !== 'baru'" class="{{ $inputClass }}">
                        @error('tujuan_tahun_selesai')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @error('tujuan')
                    <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/40 dark:text-red-200">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-700">
                <button type="submit" @disabled($tahunAjarans->isEmpty())
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-blue-300">
                    {{ __('Lanjut ke Pratinjau') }} <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Dua cara yang didukung') }}</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-gray-900 dark:text-gray-100">
                            <i class="fas fa-forward mr-1 text-blue-600"></i> {{ __('Lanjut semester') }}
                        </dt>
                        <dd class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ __('Tahun ajaran yang sama, Ganjil ke Genap (mis. 2026/2027 Ganjil → 2026/2027 Genap). Semua kelas beserta wali kelas, siswa aktif, dan jadwal mengajar disalin apa adanya.') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900 dark:text-gray-100">
                            <i class="fas fa-level-up-alt mr-1 text-emerald-600"></i> {{ __('Kenaikan kelas') }}
                        </dt>
                        <dd class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ __('Genap ke Ganjil tahun ajaran berikutnya (mis. 2025/2026 Genap → 2026/2027 Ganjil). Di pratinjau Anda menentukan kelas tujuan dan siswa yang naik, tinggal kelas, atau lulus.') }}
                        </dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-100">
                <i class="fas fa-info-circle mr-1"></i>
                {{ __('Wizard tidak mengubah apa pun sebelum Anda menekan Jalankan di halaman pratinjau. Siswa nonaktif tidak ikut disalin.') }}
            </div>
        </aside>
    </div>
</x-layouts.app>
