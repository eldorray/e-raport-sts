@php
    $sidebarUser = auth()->user();
    $sidebarRole = $sidebarUser?->role === 'admin' ? 'Admin' : (($isWaliKelas ?? false) ? 'Guru · Wali Kelas' : 'Guru');
@endphp
<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-[68px] hidden md:block': !sidebarOpen }"
    class="sticky top-0 h-screen shrink-0 bg-sidebar text-sidebar-foreground border-r border-zinc-200 dark:border-zinc-800 sidebar-transition overflow-hidden">
    <div class="h-full flex flex-col">
        <div class="flex items-center gap-3 px-4 pb-3 pt-5" :class="{ 'justify-center px-0': !sidebarOpen }">
            <img src="{{ asset('images/eraport-icon.png') }}" alt="" class="h-9 w-9 shrink-0 rounded-[11px]">
            <div x-show="sidebarOpen" class="flex min-w-0 flex-1 flex-col leading-tight">
                <span class="text-[15px] font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">E-Raport</span>
                <span class="text-xs text-zinc-600 dark:text-zinc-400">Kurikulum Merdeka</span>
            </div>
            <button type="button" @click="toggleSidebar" aria-label="{{ __('Tutup menu') }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl text-zinc-600 transition hover:bg-zinc-100 active:scale-[0.97] md:hidden dark:text-zinc-300 dark:hover:bg-zinc-800">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.2" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto custom-scrollbar pb-4" aria-label="{{ __('Menu utama') }}">
            <ul class="space-y-0.5 px-3">
                @php
                    $role = auth()->user()?->role;
                @endphp

                <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon='fas-house'
                    :active="request()->routeIs('dashboard*')">Dashboard</x-layouts.sidebar-link>

                @if ($role === 'admin')
                    <li class="py-1">
                        <a href="{{ route('admin.pwa.beranda') }}" @click="closeSidebarOnMobile()"
                            class="flex h-[38px] items-center rounded-xl bg-zinc-100 px-3 text-sm font-semibold text-zinc-900 transition duration-200 hover:bg-zinc-200 active:scale-[0.97] dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700"
                            :class="{ 'justify-center': !sidebarOpen }">
                            @svg('fas-mobile-screen-button', 'h-[18px] w-[18px] shrink-0 text-zinc-600 dark:text-zinc-300')
                            <span x-show="sidebarOpen" x-transition:enter="transition-all duration-300"
                                x-transition:enter-start="opacity-0 transform -tranzinc-x-2"
                                x-transition:enter-end="opacity-100 transform tranzinc-x-0"
                                x-transition:leave="transition-all duration-300"
                                x-transition:leave-start="opacity-100 transform tranzinc-x-0"
                                x-transition:leave-end="opacity-0 transform -tranzinc-x-2"
                                class="ml-3 whitespace-nowrap">{{ __('Aplikasi Admin (HP)') }}</span>
                        </a>
                    </li>
                    <x-layouts.sidebar-link href="{{ route('school-profile.index') }}" icon='fas-school'
                        :active="request()->routeIs('school-profile*')">Profil Sekolah</x-layouts.sidebar-link>
                    <li class="px-2 pt-4 pb-2">
                        <h2 x-show="sidebarOpen" class="text-[11px] font-semibold uppercase tracking-[0.06em] text-zinc-600 dark:text-zinc-400">
                            Pengaturan
                        </h2>
                    </li>

                    @php
                        $lembagaOpen =
                            request()->routeIs('tahun-ajaran*') ||
                            request()->routeIs('mata-pelajaran*') ||
                            request()->routeIs('kelas*');
                        $guruOpen = request()->routeIs('guru*') || request()->routeIs('mengajar*');
                        $siswaOpen = request()->routeIs('siswa*') || request()->routeIs('rombel*');
                        $ekskulOpen = request()->routeIs('ekskul*');
                    @endphp
                    <x-layouts.sidebar-two-level-link-parent title="Lembaga" icon="fas-house" :active="$lembagaOpen">
                        <x-layouts.sidebar-two-level-link href="{{ route('tahun-ajaran.index') }}" icon='fas-calendar'
                            :active="request()->routeIs('tahun-ajaran.*')">Tahun Ajaran</x-layouts.sidebar-two-level-link>
                        <x-layouts.sidebar-two-level-link href="{{ route('tahun-ajaran-baru.create') }}" icon='fas-forward'
                            :active="request()->routeIs('tahun-ajaran-baru.*')">Tahun Ajaran Baru</x-layouts.sidebar-two-level-link>
                        <x-layouts.sidebar-two-level-link href="{{ route('mata-pelajaran.index') }}" icon='fas-book'
                            :active="request()->routeIs('mata-pelajaran*')">Mata Pelajaran</x-layouts.sidebar-two-level-link>
                        <x-layouts.sidebar-two-level-link href="{{ route('kelas.index') }}" icon='fas-layer-group'
                            :active="request()->routeIs('kelas*')">Kelas</x-layouts.sidebar-two-level-link>
                        <x-layouts.sidebar-two-level-link href="{{ route('ekskul.index') }}" icon='fas-star'
                            :active="request()->routeIs('ekskul*')">Ekskul</x-layouts.sidebar-two-level-link>
                    </x-layouts.sidebar-two-level-link-parent>

                    <x-layouts.sidebar-two-level-link-parent title="Guru" icon="fas-users" :active="$guruOpen">
                        <x-layouts.sidebar-two-level-link href="{{ route('guru.index') }}" icon='fas-user'
                            :active="request()->routeIs('guru.*')">Guru</x-layouts.sidebar-two-level-link>
                        <x-layouts.sidebar-two-level-link href="{{ route('mengajar.index') }}"
                            icon='fas-person-chalkboard' :active="request()->routeIs('mengajar.index')">Mengajar</x-layouts.sidebar-two-level-link>
                        <x-layouts.sidebar-two-level-link href="{{ route('mengajar-tahfidz.index') }}"
                            icon='fas-book-quran' :active="request()->routeIs('mengajar-tahfidz.*')">Mengajar Tahfidz</x-layouts.sidebar-two-level-link>
                    </x-layouts.sidebar-two-level-link-parent>

                    <x-layouts.sidebar-two-level-link-parent title="Siswa" icon="fas-users" :active="$siswaOpen">
                        <x-layouts.sidebar-two-level-link href="{{ route('siswa.index') }}" icon='fas-user-graduate'
                            :active="request()->routeIs('siswa*')">Siswa</x-layouts.sidebar-two-level-link>
                        <x-layouts.sidebar-two-level-link href="{{ route('rombel.index') }}" icon='fas-people-roof'
                            :active="request()->routeIs('rombel*')">Rombel Kelas</x-layouts.sidebar-two-level-link>
                    </x-layouts.sidebar-two-level-link-parent>

                    <x-layouts.sidebar-link href="{{ route('users.index') }}" icon='fas-user-gear'
                        :active="request()->routeIs('users.*')">Manajemen User</x-layouts.sidebar-link>

                    <li class="px-2 pt-4 pb-2">
                        <h2 x-show="sidebarOpen" class="text-[11px] font-semibold uppercase tracking-[0.06em] text-zinc-600 dark:text-zinc-400">
                            Rapor
                        </h2>
                    </li>
                    <x-layouts.sidebar-link href="{{ route('rapor.print-settings.edit') }}" icon='fas-gear'
                        :active="request()->routeIs('rapor.print-settings.*')">Pengaturan Cetak</x-layouts.sidebar-link>
                    <x-layouts.sidebar-link href="{{ route('rapor.index') }}" icon='fas-file-lines'
                        :active="request()->routeIs('rapor.index')">Cetak Rapor</x-layouts.sidebar-link>
                    <x-layouts.sidebar-link href="{{ route('koreksi-nilai.index') }}" icon='fas-pen-to-square'
                        :active="request()->routeIs('koreksi-nilai.*')">Koreksi Nilai</x-layouts.sidebar-link>
                    <x-layouts.sidebar-link href="{{ route('tahfidz.index') }}" icon='fas-book-quran'
                        :active="request()->routeIs('tahfidz.*')">Raport Tahfidz</x-layouts.sidebar-link>

                    <li class="px-2 pt-4 pb-2">
                        <h2 x-show="sidebarOpen" class="text-[11px] font-semibold uppercase tracking-[0.06em] text-zinc-600 dark:text-zinc-400">
                            Sistem
                        </h2>
                    </li>
                    <x-layouts.sidebar-link href="{{ route('backup.index') }}" icon='fas-database'
                        :active="request()->routeIs('backup.*')">Backup</x-layouts.sidebar-link>
                    <x-layouts.sidebar-link href="{{ route('settings.appearance.edit') }}" icon='fas-palette'
                        :active="request()->routeIs('settings.appearance.*')">Setting Tampilan</x-layouts.sidebar-link>
                @else
                    <li class="px-2 pt-4 pb-2">
                        <h2 x-show="sidebarOpen" class="text-[11px] font-semibold uppercase tracking-[0.06em] text-zinc-600 dark:text-zinc-400">
                            Guru
                        </h2>
                    </li>
                    <li class="py-1">
                        <a href="{{ route('guru.pwa.beranda') }}" @click="closeSidebarOnMobile()"
                            class="flex h-[38px] items-center rounded-xl bg-zinc-100 px-3 text-sm font-semibold text-zinc-900 transition duration-200 hover:bg-zinc-200 active:scale-[0.97] dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700"
                            :class="{ 'justify-center': !sidebarOpen }">
                            @svg('fas-mobile-screen-button', 'h-[18px] w-[18px] shrink-0 text-zinc-600 dark:text-zinc-300')
                            <span x-show="sidebarOpen" x-transition:enter="transition-all duration-300"
                                x-transition:enter-start="opacity-0 transform -tranzinc-x-2"
                                x-transition:enter-end="opacity-100 transform tranzinc-x-0"
                                x-transition:leave="transition-all duration-300"
                                x-transition:leave-start="opacity-100 transform tranzinc-x-0"
                                x-transition:leave-end="opacity-0 transform -tranzinc-x-2"
                                class="ml-3 whitespace-nowrap">{{ __('Aplikasi Guru (HP)') }}</span>
                        </a>
                    </li>

                    <x-layouts.sidebar-link href="{{ route('guru.pelajaran') }}" icon='fas-book-open'
                        :active="request()->routeIs('guru.pelajaran')">{{ __('Pelajaran Saya') }}</x-layouts.sidebar-link>

                    <x-layouts.sidebar-link href="{{ route('guru.ekskul.index') }}" icon='fas-star'
                        :active="request()->routeIs('guru.ekskul.*')">Ekskul Saya</x-layouts.sidebar-link>

                    @if ($hasTahfidzAssignment ?? false)
                        <x-layouts.sidebar-link href="{{ route('tahfidz.index') }}" icon='fas-book-quran'
                            :active="request()->routeIs('tahfidz.*')">Tahfidz Saya</x-layouts.sidebar-link>
                    @endif

                    @if ($isWaliKelas ?? false)
                        <x-layouts.sidebar-link href="{{ route('wali-kelas.siswa.index') }}" icon='fas-user-graduate'
                            :active="request()->routeIs('wali-kelas.siswa.*')">Siswa Kelas Saya</x-layouts.sidebar-link>
                        <x-layouts.sidebar-link href="{{ route('rapor.index') }}" icon='fas-file-lines'
                            :active="request()->routeIs('rapor.index')">Cetak Rapor</x-layouts.sidebar-link>
                        <x-layouts.sidebar-link href="{{ route('rapor.absen') }}" icon='fas-calendar-check'
                            :active="request()->routeIs('rapor.absen')">Data Absen</x-layouts.sidebar-link>
                        <x-layouts.sidebar-link href="{{ route('rapor.prestasi') }}" icon='fas-trophy'
                            :active="request()->routeIs('rapor.prestasi')">Prestasi Siswa</x-layouts.sidebar-link>
                        <x-layouts.sidebar-link href="{{ route('rapor.catatan') }}" icon='fas-note-sticky'
                            :active="request()->routeIs('rapor.catatan')">Catatan Wali</x-layouts.sidebar-link>
                    @endif

                    <li class="px-2 pt-4 pb-2">
                        <h2 x-show="sidebarOpen" class="text-[11px] font-semibold uppercase tracking-[0.06em] text-zinc-600 dark:text-zinc-400">
                            {{ __('Mapel Saya') }}
                        </h2>
                    </li>

                    @forelse(($sidebarAssignments ?? collect()) as $mapelId => $items)
                        @php
                            $mapel = $items->first()?->mataPelajaran;
                            $mapelDone = ($sidebarMapelStatus[$mapelId] ?? false) === true;
                            $mapelBadgeLabel = $mapelDone ? '✓' : 'X';
                            $mapelBadgeClass = $mapelDone
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-100'
                                : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-100';
                        @endphp
                        <details class="group"
                            {{ request()->routeIs('guru.penilaian.show') && optional($items->first())->id === optional(request()->route('mengajar'))->id ? 'open' : '' }}>
                            <summary
                                class="flex cursor-pointer items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-100 dark:text-zinc-100 dark:hover:bg-zinc-800">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-book text-xs"></i>
                                    {{ $mapel->kode ?? ($mapel->nama_mapel ?? '-') }}
                                    <span
                                        class="ml-2 inline-flex items-center rounded-full px-1.5 py-[1px] text-[9px] font-semibold {{ $mapelBadgeClass }}">{{ $mapelBadgeLabel }}</span>
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                            </summary>
                            <ul class="space-y-1 pb-2 pl-5 pr-2">
                                @foreach ($items as $assignment)
                                    <li>
                                        <a href="{{ route('guru.penilaian.show', $assignment) }}"
                                            @click="closeSidebarOnMobile()"
                                            class="block rounded-xl px-3 py-2 text-sm {{ request()->routeIs('guru.penilaian.show') && optional(request()->route('mengajar'))->id === $assignment->id ? 'bg-accent/10 font-semibold text-accent-strong dark:bg-accent/20 dark:text-blue-300' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800' }}">
                                            <span>{{ $assignment->kelas->nama ?? '-' }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @empty
                        <p class="px-3 py-2 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Mapel Belum diinput oleh admin') }}</p>
                    @endforelse

                    <li class="px-2 pt-4 pb-2">
                        <h2 x-show="sidebarOpen" class="text-[11px] font-semibold uppercase tracking-[0.06em] text-zinc-600 dark:text-zinc-400">
                            Ekskul</h2>
                    </li>
                    @forelse(($sidebarEkskul ?? collect()) as $ex)
                        <x-layouts.sidebar-link href="{{ route('guru.ekskul.show', $ex) }}" icon='fas-star'
                            :active="request()->routeIs('guru.ekskul.show') &&
                                optional(request()->route('ekskul'))?->id === $ex->id">
                            {{ $ex->nama }}
                        </x-layouts.sidebar-link>
                    @empty
                        <p class="px-3 py-2 text-xs text-zinc-500 dark:text-zinc-400">Belum ada ekskul</p>
                    @endforelse
                @endif

                {{-- @php
                    $exampleOpen = request()->routeIs('three-level*');
                @endphp
                <x-layouts.sidebar-two-level-link-parent title="Example three level" icon="fas-house" :active="$exampleOpen">
                    <x-layouts.sidebar-two-level-link href="#" icon='fas-house' :active="request()->routeIs('three-level*')">Single
                        Link</x-layouts.sidebar-two-level-link>
                    <x-layouts.sidebar-three-level-parent title="Third Level" icon="fas-house" :active="request()->routeIs('three-level*')">
                        <x-layouts.sidebar-three-level-link href="#" :active="request()->routeIs('three-level*')">
                            Third Level Link
                        </x-layouts.sidebar-three-level-link>
                    </x-layouts.sidebar-three-level-parent>
                </x-layouts.sidebar-two-level-link-parent> --}}
            </ul>
        </nav>
        <div class="p-3">
            <div class="flex items-center gap-3 rounded-2xl bg-zinc-100 p-2.5 dark:bg-zinc-800"
                :class="{ 'justify-center bg-transparent p-0 dark:bg-transparent': !sidebarOpen }">
                <a href="{{ route('settings.profile.edit') }}" title="{{ __('Pengaturan akun') }}"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-xs font-semibold text-white transition active:scale-[0.97] dark:bg-zinc-100 dark:text-zinc-900">
                    {{ \Illuminate\Support\Str::substr($sidebarUser->initials(), 0, 2) }}
                </a>
                <a href="{{ route('settings.profile.edit') }}" x-show="sidebarOpen"
                    class="flex min-w-0 flex-1 flex-col leading-tight">
                    <span class="truncate text-[13px] font-semibold text-zinc-900 dark:text-zinc-100">{{ $sidebarUser->name }}</span>
                    <span class="truncate text-xs text-zinc-600 dark:text-zinc-400">{{ $sidebarRole }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen">
                    @csrf
                    <button type="submit" aria-label="{{ __('Keluar') }}" title="{{ __('Keluar') }}"
                        class="flex h-9 w-9 items-center justify-center rounded-[10px] text-zinc-600 transition hover:bg-zinc-200 active:scale-[0.97] dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <svg aria-hidden="true" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3M15 16l4-4-4-4M19 12H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
