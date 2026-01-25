<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
    class="bg-sidebar text-sidebar-foreground border-r border-slate-200 dark:border-slate-700 sidebar-transition overflow-hidden">
    <div class="h-full flex flex-col">
        <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
            <ul class="space-y-1 px-2">
                @php
                    $role = auth()->user()?->role;
                @endphp

                <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon='fas-house'
                    :active="request()->routeIs('dashboard*')">Dashboard</x-layouts.sidebar-link>

                @if ($role === 'admin')
                    <x-layouts.sidebar-link href="{{ route('school-profile.index') }}" icon='fas-school'
                        :active="request()->routeIs('school-profile*')">Profil Sekolah</x-layouts.sidebar-link>
                    <li class="px-2 pt-4 pb-2">
                        <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
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
                            :active="request()->routeIs('tahun-ajaran*')">Tahun Ajaran</x-layouts.sidebar-two-level-link>
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
                        <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Rapor
                        </h2>
                    </li>
                    <x-layouts.sidebar-link href="{{ route('rapor.print-settings.edit') }}" icon='fas-gear'
                        :active="request()->routeIs('rapor.print-settings.*')">Pengaturan Cetak</x-layouts.sidebar-link>
                    <x-layouts.sidebar-link href="{{ route('rapor.index') }}" icon='fas-file-lines'
                        :active="request()->routeIs('rapor.index')">Cetak Rapor</x-layouts.sidebar-link>
                    <x-layouts.sidebar-link href="{{ route('tahfidz.index') }}" icon='fas-book-quran'
                        :active="request()->routeIs('tahfidz.*')">Raport Tahfidz</x-layouts.sidebar-link>

                    <li class="px-2 pt-4 pb-2">
                        <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Sistem
                        </h2>
                    </li>
                    <x-layouts.sidebar-link href="{{ route('backup.index') }}" icon='fas-database'
                        :active="request()->routeIs('backup.*')">Backup & Restore</x-layouts.sidebar-link>
                    <x-layouts.sidebar-link href="{{ route('settings.appearance.edit') }}" icon='fas-palette'
                        :active="request()->routeIs('settings.appearance.*')">Setting Tampilan</x-layouts.sidebar-link>
                @else
                    <li class="px-2 pt-4 pb-2">
                        <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Guru
                        </h2>
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
                        <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
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
                                class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">
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
                                            class="block rounded-md px-3 py-2 text-sm {{ request()->routeIs('guru.penilaian.show') && optional(request()->route('mengajar'))->id === $assignment->id ? 'bg-blue-600 text-white' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800' }}">
                                            <span>{{ $assignment->kelas->nama ?? '-' }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @empty
                        <p class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Mapel Belum diinput oleh admin') }}</p>
                    @endforelse

                    <li class="px-2 pt-4 pb-2">
                        <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Ekskul</h2>
                    </li>
                    @forelse(($sidebarEkskul ?? collect()) as $ex)
                        <x-layouts.sidebar-link href="{{ route('guru.ekskul.show', $ex) }}" icon='fas-star'
                            :active="request()->routeIs('guru.ekskul.show') &&
                                optional(request()->route('ekskul'))?->id === $ex->id">
                            {{ $ex->nama }}
                        </x-layouts.sidebar-link>
                    @empty
                        <p class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">Belum ada ekskul</p>
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
    </div>
</aside>
