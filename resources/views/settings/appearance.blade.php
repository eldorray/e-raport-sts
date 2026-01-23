<x-layouts.app>
    <!-- Breadcrumbs -->
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('settings.profile.edit') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Profile') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Appearance') }}</span>
    </div>

    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Appearance') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Update the appearance settings for your account') }}
        </p>
    </div>

    <div class="p-6">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar Navigation -->
            @include('settings.partials.navigation')

            <!-- Profile Content -->
            <div class="flex-1 space-y-6">
                <!-- Theme Section -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Theme') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Pilih tema tampilan yang nyaman untuk mata Anda') }}</p>
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <button onclick="setAppearance('light')" id="theme-light"
                                class="theme-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <i class="fas fa-sun mr-2"></i>{{ __('Light') }}
                            </button>
                            <button onclick="setAppearance('dark')" id="theme-dark"
                                class="theme-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <i class="fas fa-moon mr-2"></i>{{ __('Dark') }}
                            </button>
                            <button onclick="setAppearance('system')" id="theme-system"
                                class="theme-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <i class="fas fa-desktop mr-2"></i>{{ __('System') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Primary Color Section -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Warna Utama') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Pilih warna aksen untuk tampilan aplikasi') }}</p>
                        <div class="flex flex-wrap gap-3">
                            <button onclick="setPrimaryColor('blue')" data-color="blue"
                                class="color-btn w-10 h-10 rounded-full bg-blue-500 hover:ring-4 hover:ring-blue-200 dark:hover:ring-blue-900 transition-all flex items-center justify-center"
                                title="Biru">
                                <i class="fas fa-check text-white hidden"></i>
                            </button>
                            <button onclick="setPrimaryColor('green')" data-color="green"
                                class="color-btn w-10 h-10 rounded-full bg-green-500 hover:ring-4 hover:ring-green-200 dark:hover:ring-green-900 transition-all flex items-center justify-center"
                                title="Hijau">
                                <i class="fas fa-check text-white hidden"></i>
                            </button>
                            <button onclick="setPrimaryColor('red')" data-color="red"
                                class="color-btn w-10 h-10 rounded-full bg-red-500 hover:ring-4 hover:ring-red-200 dark:hover:ring-red-900 transition-all flex items-center justify-center"
                                title="Merah">
                                <i class="fas fa-check text-white hidden"></i>
                            </button>
                            <button onclick="setPrimaryColor('purple')" data-color="purple"
                                class="color-btn w-10 h-10 rounded-full bg-purple-500 hover:ring-4 hover:ring-purple-200 dark:hover:ring-purple-900 transition-all flex items-center justify-center"
                                title="Ungu">
                                <i class="fas fa-check text-white hidden"></i>
                            </button>
                            <button onclick="setPrimaryColor('orange')" data-color="orange"
                                class="color-btn w-10 h-10 rounded-full bg-orange-500 hover:ring-4 hover:ring-orange-200 dark:hover:ring-orange-900 transition-all flex items-center justify-center"
                                title="Oranye">
                                <i class="fas fa-check text-white hidden"></i>
                            </button>
                            <button onclick="setPrimaryColor('teal')" data-color="teal"
                                class="color-btn w-10 h-10 rounded-full bg-teal-500 hover:ring-4 hover:ring-teal-200 dark:hover:ring-teal-900 transition-all flex items-center justify-center"
                                title="Teal">
                                <i class="fas fa-check text-white hidden"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Font Size Section -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Ukuran Font') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Sesuaikan ukuran teks sesuai kenyamanan Anda') }}</p>
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <button onclick="setFontSize('small')" id="font-small"
                                class="font-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <span class="text-xs">A</span> {{ __('Kecil') }}
                            </button>
                            <button onclick="setFontSize('normal')" id="font-normal"
                                class="font-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <span class="text-base">A</span> {{ __('Sedang') }}
                            </button>
                            <button onclick="setFontSize('large')" id="font-large"
                                class="font-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <span class="text-lg">A</span> {{ __('Besar') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Font Style Section -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                            {{ __('Jenis Font') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Pilih jenis font yang diinginkan') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <button onclick="setFontFamily('inter')" id="font-family-inter"
                                class="font-family-btn px-4 py-3 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 text-left">
                                <span style="font-family: 'Inter', sans-serif;">Inter</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    style="font-family: 'Inter', sans-serif;">The quick brown fox</span>
                            </button>
                            <button onclick="setFontFamily('roboto')" id="font-family-roboto"
                                class="font-family-btn px-4 py-3 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 text-left">
                                <span style="font-family: 'Roboto', sans-serif;">Roboto</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    style="font-family: 'Roboto', sans-serif;">The quick brown fox</span>
                            </button>
                            <button onclick="setFontFamily('opensans')" id="font-family-opensans"
                                class="font-family-btn px-4 py-3 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 text-left">
                                <span style="font-family: 'Open Sans', sans-serif;">Open Sans</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    style="font-family: 'Open Sans', sans-serif;">The quick brown fox</span>
                            </button>
                            <button onclick="setFontFamily('lato')" id="font-family-lato"
                                class="font-family-btn px-4 py-3 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 text-left">
                                <span style="font-family: 'Lato', sans-serif;">Lato</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    style="font-family: 'Lato', sans-serif;">The quick brown fox</span>
                            </button>
                            <button onclick="setFontFamily('montserrat')" id="font-family-montserrat"
                                class="font-family-btn px-4 py-3 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 text-left">
                                <span style="font-family: 'Montserrat', sans-serif;">Montserrat</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    style="font-family: 'Montserrat', sans-serif;">The quick brown fox</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Mode Section -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                            {{ __('Mode Sidebar') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Pilih tampilan sidebar yang diinginkan') }}</p>
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <button onclick="setSidebarMode('expanded')" id="sidebar-expanded"
                                class="sidebar-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <i class="fas fa-arrows-alt-h mr-2"></i>{{ __('Expanded') }}
                            </button>
                            <button onclick="setSidebarMode('compact')" id="sidebar-compact"
                                class="sidebar-btn px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                <i class="fas fa-compress-alt mr-2"></i>{{ __('Compact') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reset Settings -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                            {{ __('Reset Pengaturan') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Kembalikan semua pengaturan tampilan ke default') }}</p>
                        <button onclick="resetAllSettings()"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                            <i class="fas fa-undo mr-2"></i>{{ __('Reset ke Default') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize settings from localStorage
            initializeSettings();
        });

        function initializeSettings() {
            // Theme
            const currentTheme = localStorage.getItem('appearance') || 'system';
            updateThemeButtons(currentTheme);

            // Primary Color
            const currentColor = localStorage.getItem('primaryColor') || 'blue';
            updateColorButtons(currentColor);
            applyPrimaryColor(currentColor);

            // Font Size
            const currentFontSize = localStorage.getItem('fontSize') || 'normal';
            updateFontButtons(currentFontSize);
            applyFontSize(currentFontSize);

            // Font Family
            const currentFontFamily = localStorage.getItem('fontFamily') || 'inter';
            updateFontFamilyButtons(currentFontFamily);
            applyFontFamily(currentFontFamily);

            // Sidebar Mode
            const sidebarOpen = localStorage.getItem('sidebarOpen');
            const currentSidebarMode = sidebarOpen === 'false' ? 'compact' : 'expanded';
            updateSidebarButtons(currentSidebarMode);
        }

        // Theme functions
        function updateThemeButtons(theme) {
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-blue-500');
            });
            const activeBtn = document.getElementById('theme-' + theme);
            if (activeBtn) {
                activeBtn.classList.add('ring-2', 'ring-blue-500');
            }
        }

        // Extend the existing setAppearance function
        const originalSetAppearance = window.setAppearance;
        window.setAppearance = function(appearance) {
            originalSetAppearance(appearance);
            updateThemeButtons(appearance);
        };

        // Primary Color functions
        function setPrimaryColor(color) {
            localStorage.setItem('primaryColor', color);
            applyPrimaryColor(color);
            updateColorButtons(color);
        }

        function applyPrimaryColor(color) {
            const root = document.documentElement;
            const colors = {
                blue: {
                    primary: '59 130 246',
                    hover: '37 99 235',
                    ring: '147 197 253'
                },
                green: {
                    primary: '34 197 94',
                    hover: '22 163 74',
                    ring: '134 239 172'
                },
                red: {
                    primary: '239 68 68',
                    hover: '220 38 38',
                    ring: '252 165 165'
                },
                purple: {
                    primary: '168 85 247',
                    hover: '147 51 234',
                    ring: '216 180 254'
                },
                orange: {
                    primary: '249 115 22',
                    hover: '234 88 12',
                    ring: '253 186 116'
                },
                teal: {
                    primary: '20 184 166',
                    hover: '13 148 136',
                    ring: '94 234 212'
                }
            };

            const selectedColor = colors[color] || colors.blue;
            root.style.setProperty('--color-primary', selectedColor.primary);
            root.style.setProperty('--color-primary-hover', selectedColor.hover);
            root.style.setProperty('--color-primary-ring', selectedColor.ring);
        }

        function updateColorButtons(color) {
            document.querySelectorAll('.color-btn').forEach(btn => {
                const icon = btn.querySelector('i');
                btn.classList.remove('ring-4', 'ring-offset-2');
                if (icon) icon.classList.add('hidden');
            });
            const activeBtn = document.querySelector('.color-btn[data-color="' + color + '"]');
            if (activeBtn) {
                activeBtn.classList.add('ring-4', 'ring-offset-2');
                const icon = activeBtn.querySelector('i');
                if (icon) icon.classList.remove('hidden');
            }
        }

        // Font Size functions
        function setFontSize(size) {
            localStorage.setItem('fontSize', size);
            applyFontSize(size);
            updateFontButtons(size);
        }

        function applyFontSize(size) {
            const root = document.documentElement;
            const sizes = {
                small: '14px',
                normal: '16px',
                large: '18px'
            };
            root.style.fontSize = sizes[size] || sizes.normal;
        }

        function updateFontButtons(size) {
            document.querySelectorAll('.font-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-blue-500');
            });
            const activeBtn = document.getElementById('font-' + size);
            if (activeBtn) {
                activeBtn.classList.add('ring-2', 'ring-blue-500');
            }
        }

        // Font Family functions
        function setFontFamily(family) {
            localStorage.setItem('fontFamily', family);
            applyFontFamily(family);
            updateFontFamilyButtons(family);
        }

        function applyFontFamily(family) {
            const fonts = {
                inter: '"Inter", sans-serif',
                roboto: '"Roboto", sans-serif',
                opensans: '"Open Sans", sans-serif',
                lato: '"Lato", sans-serif',
                montserrat: '"Montserrat", sans-serif'
            };
            document.documentElement.style.fontFamily = fonts[family] || fonts.inter;
        }

        function updateFontFamilyButtons(family) {
            document.querySelectorAll('.font-family-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-blue-500');
            });
            const activeBtn = document.getElementById('font-family-' + family);
            if (activeBtn) {
                activeBtn.classList.add('ring-2', 'ring-blue-500');
            }
        }

        // Sidebar Mode functions
        function setSidebarMode(mode) {
            const isExpanded = mode === 'expanded';
            localStorage.setItem('sidebarOpen', isExpanded);
            updateSidebarButtons(mode);

            // Trigger Alpine.js sidebar toggle if available
            const event = new CustomEvent('sidebar-mode-changed', {
                detail: {
                    expanded: isExpanded
                }
            });
            window.dispatchEvent(event);

            // Reload page to apply sidebar change
            location.reload();
        }

        function updateSidebarButtons(mode) {
            document.querySelectorAll('.sidebar-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-blue-500');
            });
            const activeBtn = document.getElementById('sidebar-' + mode);
            if (activeBtn) {
                activeBtn.classList.add('ring-2', 'ring-blue-500');
            }
        }

        // Reset all settings
        function resetAllSettings() {
            if (confirm('Apakah Anda yakin ingin mengembalikan semua pengaturan ke default?')) {
                localStorage.removeItem('appearance');
                localStorage.removeItem('primaryColor');
                localStorage.removeItem('fontSize');
                localStorage.removeItem('fontFamily');
                localStorage.setItem('sidebarOpen', 'true');

                // Apply defaults
                setAppearance('system');
                applyPrimaryColor('blue');
                applyFontSize('normal');
                applyFontFamily('inter');

                // Reload to apply all changes
                location.reload();
            }
        }
    </script>
</x-layouts.app>
