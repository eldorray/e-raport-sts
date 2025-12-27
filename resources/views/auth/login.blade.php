<x-layouts.auth :title="__('Login')">
    @php
        $defaultTahunAjaran =
            old('tahun_ajaran_id') ??
            ($tahunAjaranOptions->firstWhere('is_active', true)->id ?? ($tahunAjaranOptions->first()->id ?? null));
        $defaultSemester = old('semester', 'Ganjil');
        $school = \App\Models\SchoolProfile::first();
    @endphp

    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50"></div>
        <div class="relative p-8 sm:p-10 lg:p-12">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold tracking-wide text-gray-700 uppercase">
                        {{ __('E-Raport Kurikulum Merdeka') }}</p>
                    <h1 class="text-3xl font-extrabold leading-tight text-gray-900 lg:text-4xl">
                        {{ $school?->name }}
                    </h1>
                    <p class="text-sm text-gray-600">{{ __('Silahkan login menggunakan akun Anda') }}</p>

                </div>

                <div class="w-full max-w-xl">
                    <div class="space-y-4 rounded-2xl bg-white/80 p-6 shadow-inner ring-1 ring-gray-200 backdrop-blur">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-gray-700">{{ __('Nomor Akun') }}</label>
                                <div
                                    class="flex items-center gap-2 rounded-xl border border-gray-300 bg-blue-50 px-3 py-2.5 ring-1 ring-transparent focus-within:border-blue-500 focus-within:ring-blue-200">
                                    <span class="text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                    <input type="text" name="login" value="{{ old('login') }}" required
                                        autocomplete="username"
                                        class="w-full bg-transparent text-base font-semibold text-gray-900 placeholder-gray-400 focus:outline-none"
                                        placeholder="Masukan email anda">
                                </div>
                                @error('login')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-gray-700">{{ __('Kata Sandi') }}</label>
                                <div
                                    class="flex items-center gap-2 rounded-xl border border-gray-300 bg-blue-50 px-3 py-2.5 ring-1 ring-transparent focus-within:border-blue-500 focus-within:ring-blue-200">
                                    <span class="text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M12 15v2m-6 2h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V7a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <input id="password" name="password" type="password" required
                                        autocomplete="current-password"
                                        class="w-full bg-transparent text-base font-semibold text-gray-900 placeholder-gray-400 focus:outline-none"
                                        placeholder="••••••••">
                                    <button type="button" id="togglePassword"
                                        class="text-gray-500 transition hover:text-gray-700">
                                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-gray-700">{{ __('Tahun Ajaran') }}</label>
                                <div
                                    class="flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-2.5 ring-1 ring-transparent focus-within:border-blue-500 focus-within:ring-blue-200">
                                    <span class="text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M8 7h12M8 12h12m-5 5h5M4 7h.01M4 12h.01M4 17h.01" />
                                        </svg>
                                    </span>
                                    <select name="tahun_ajaran_id"
                                        class="w-full bg-transparent text-base font-semibold text-gray-900 focus:outline-none">
                                        <option value="" disabled {{ $defaultTahunAjaran ? '' : 'selected' }}>
                                            {{ __('Pilih tahun ajaran') }}</option>
                                        @foreach ($tahunAjaranOptions as $tahun)
                                            <option value="{{ $tahun->id }}" @selected($defaultTahunAjaran == $tahun->id)>
                                                {{ $tahun->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('tahun_ajaran_id')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-gray-700">{{ __('Semester') }}</label>
                                <div
                                    class="flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-2.5 ring-1 ring-transparent focus-within:border-blue-500 focus-within:ring-blue-200">
                                    <span class="text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M8 7h12M8 12h12m-5 5h5M4 7h.01M4 12h.01M4 17h.01" />
                                        </svg>
                                    </span>
                                    <select name="semester"
                                        class="w-full bg-transparent text-base font-semibold text-gray-900 focus:outline-none">
                                        <option value="Ganjil" @selected($defaultSemester === 'Ganjil')>Ganjil</option>
                                        <option value="Genap" @selected($defaultSemester === 'Genap')>Genap</option>
                                    </select>
                                </div>
                                @error('semester')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                <div class="flex items-center gap-2">
                                    <input id="remember" name="remember" type="checkbox" value="1"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        @checked(old('remember'))>
                                    <label for="remember"
                                        class="text-sm font-medium text-gray-700">{{ __('Ingat saya') }}</label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                        class="text-sm font-semibold text-blue-600 hover:underline">{{ __('Lupa Password Admin?') }}</a>
                                @endif
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-lg font-semibold text-white shadow-lg transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                                    {{ __('Login') }}
                                </button>
                            </div>
                        </form>

                        <div class="pt-3 text-sm text-gray-600">
                            <p>{{ __('Aplikasi E-Raport Kurikulum Merdeka') }}</p>
                            <p>{{ __('Masih dalam pengembangan') }}
                            </p>
                            <p class="pt-1 text-gray-500">E-Raport Versi 1.0 ({{ now()->format('YmdHis') }})</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            toggleBtn?.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
            });
        });
    </script>
</x-layouts.auth>
