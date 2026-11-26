<x-layouts.auth :title="__('Login')">
    @php
        $tahunAktif = $tahunAjaranOptions->firstWhere('is_active', true);
        $defaultTahunAjaran = old('tahun_ajaran_id') ?? ($tahunAktif->id ?? ($tahunAjaranOptions->first()->id ?? null));
        $school = \App\Models\SchoolProfile::first();
        $namaSekolah = $school?->name ?? config('app.name', 'e-Raport');
        $logoUrl = $school?->logo ? asset('storage/' . $school->logo) : asset('images/eraport-icon.png');

        $press = 'transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97] motion-reduce:transition-none';
        $field = 'flex h-12 items-center rounded-xl bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus-within:bg-white focus-within:ring-2 focus-within:ring-accent dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus-within:bg-zinc-900';
        $input = 'min-w-0 flex-1 bg-transparent text-[15px] text-zinc-900 placeholder-zinc-500 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-400';
        $label = 'text-[13px] font-medium text-zinc-900 dark:text-zinc-200';
        $error = 'text-sm text-red-600 dark:text-red-400';
    @endphp

    <x-slot:header>
        <header class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-3 px-6 py-3.5">
            <div class="flex items-center gap-3">
                <img src="{{ $logoUrl }}" alt="" class="h-10 w-10 rounded-xl bg-white object-contain">
                <div class="flex flex-col leading-tight">
                    <span class="text-[15px] font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ $namaSekolah }}</span>
                    @if ($school?->city)
                        <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $school->city }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 rounded-[10px] px-3 py-2.5 text-sm font-medium text-zinc-700 transition-colors duration-200 hover:bg-zinc-200 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M11 6l-6 6 6 6" />
                </svg>
                {{ __('Kembali ke beranda') }}
            </a>
        </header>
    </x-slot:header>

    <div class="mx-auto flex w-full max-w-[440px] flex-col gap-5">
        <form method="POST" action="{{ route('login') }}" id="loginForm"
            class="animate-rise flex flex-col gap-[22px] rounded-[28px] bg-white p-7 shadow-[0_1px_2px_rgba(0,0,0,.04),0_16px_40px_rgba(0,0,0,.06)] motion-reduce:animate-none sm:p-9 dark:bg-zinc-800">
            @csrf

            <div class="flex flex-col gap-4">
                @if ($tahunAktif)
                    <span
                        class="inline-flex h-[30px] items-center gap-2 self-start rounded-full bg-zinc-100 px-3 text-[13px] font-medium text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        {{ __('Tahun ajaran') }} {{ $tahunAktif->nama }}
                    </span>
                @endif
                <div class="flex flex-col gap-1.5">
                    <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.03em] text-zinc-900 dark:text-zinc-50">
                        {{ __('Masuk ke e-Raport') }}</h1>
                    <p class="text-[15px] text-zinc-700 dark:text-zinc-400">
                        {{ __('Silahkan login menggunakan akun Anda') }}.</p>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <label for="login" class="{{ $label }}">{{ __('Nomor Akun') }}</label>
                    <div class="{{ $field }} px-3.5">
                        <input id="login" type="text" name="login" value="{{ old('login') }}" required
                            autocomplete="username" class="{{ $input }}" placeholder="Masukan email anda">
                    </div>
                    @error('login')
                        <p class="{{ $error }}">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex items-baseline justify-between gap-3">
                        <label for="password" class="{{ $label }}">{{ __('Kata Sandi') }}</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-[13px] font-medium text-accent hover:underline dark:text-blue-400">{{ __('Lupa Password Admin?') }}</a>
                        @endif
                    </div>
                    <div class="{{ $field }} gap-1 pl-3.5 pr-0.5">
                        <input id="password" name="password" type="password" required
                            autocomplete="current-password" class="{{ $input }}" placeholder="••••••••">
                        <button type="button" id="togglePassword" aria-pressed="false"
                            aria-label="{{ __('Tampilkan kata sandi') }}"
                            class="{{ $press }} group flex h-11 w-11 flex-none items-center justify-center rounded-xl text-zinc-600 dark:text-zinc-400">
                            <svg aria-hidden="true" class="h-5 w-5 group-aria-pressed:hidden" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg aria-hidden="true" class="hidden h-5 w-5 group-aria-pressed:block" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M3 3l18 18" />
                                <path d="M10.6 5.1A10.6 10.6 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4" />
                                <path d="M6.5 6.6C3.6 8.5 2 12 2 12s3.5 7 10 7c1.7 0 3.2-.5 4.5-1.2" />
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="{{ $error }}">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="tahun_ajaran_id" class="{{ $label }}">{{ __('Tahun Ajaran & Semester') }}</label>
                    <div class="{{ $field }} gap-2 px-3.5">
                        <select id="tahun_ajaran_id" name="tahun_ajaran_id"
                            class="{{ $input }} h-full cursor-pointer appearance-none">
                            <option value="" disabled {{ $defaultTahunAjaran ? '' : 'selected' }}>
                                {{ __('Pilih tahun ajaran') }}</option>
                            @foreach ($tahunAjaranOptions as $tahun)
                                <option value="{{ $tahun->id }}" @selected($defaultTahunAjaran == $tahun->id)>
                                    {{ $tahun->nama }} - Semester {{ $tahun->semester }}
                                </option>
                            @endforeach
                        </select>
                        <svg aria-hidden="true" class="h-[18px] w-[18px] flex-none text-zinc-600 dark:text-zinc-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                    @error('tahun_ajaran_id')
                        <p class="{{ $error }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 self-start text-sm text-zinc-900 dark:text-zinc-200">
                <input id="remember" name="remember" type="checkbox" value="1" class="h-[18px] w-[18px] accent-accent"
                    @checked(old('remember'))>
                {{ __('Ingat saya') }}
            </label>

            <button type="submit" id="loginSubmit"
                class="{{ $press }} group flex h-[52px] items-center justify-center gap-2.5 rounded-2xl bg-accent text-base font-semibold text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-accent/30 disabled:cursor-wait">
                <svg aria-hidden="true" class="hidden h-[18px] w-[18px] animate-spin group-aria-busy:block"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                    <path d="M12 3a9 9 0 1 0 9 9" />
                </svg>
                <span class="group-aria-busy:hidden">{{ __('Masuk') }}</span>
                <span class="hidden group-aria-busy:inline">{{ __('Memeriksa akun…') }}</span>
                <svg aria-hidden="true" class="h-[18px] w-[18px] group-aria-busy:hidden" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </button>
        </form>

        <div class="text-center text-[13px] leading-relaxed text-zinc-600 dark:text-zinc-400">
            <p>{{ __('Akun dibuat oleh admin atau TU madrasah. Lupa kata sandi? Hubungi admin atau TU.') }}</p>
            <p class="pt-1 text-xs">E-Raport Versi 1.0 ({{ now()->format('YmdHis') }})</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const submitBtn = document.getElementById('loginSubmit');

            toggleBtn.addEventListener('click', () => {
                const show = passwordInput.type === 'password';
                passwordInput.type = show ? 'text' : 'password';
                toggleBtn.setAttribute('aria-pressed', String(show));
            });

            const setBusy = (busy) => {
                submitBtn.disabled = busy;
                submitBtn.setAttribute('aria-busy', String(busy));
            };
            document.getElementById('loginForm').addEventListener('submit', () => setBusy(true));
            // tombol kembali di browser memulihkan halaman dari cache dengan tombol masih terkunci
            window.addEventListener('pageshow', () => setBusy(false));
        });
    </script>
</x-layouts.auth>
