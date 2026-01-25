<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/eraport-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/eraport-icon.png') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap"
        rel="stylesheet">
    <script>
        // Apply appearance (theme) settings
        window.setAppearance = function(appearance) {
            let setDark = () => document.documentElement.classList.add('dark')
            let setLight = () => document.documentElement.classList.remove('dark')
            let setButtons = (appearance) => {
                document.querySelectorAll('button[onclick^="setAppearance"]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(appearance === button.value))
                })
            }
            if (appearance === 'system') {
                let media = window.matchMedia('(prefers-color-scheme: dark)')
                window.localStorage.removeItem('appearance')
                media.matches ? setDark() : setLight()
            } else if (appearance === 'dark') {
                window.localStorage.setItem('appearance', 'dark')
                setDark()
            } else if (appearance === 'light') {
                window.localStorage.setItem('appearance', 'light')
                setLight()
            }
            if (document.readyState === 'complete') {
                setButtons(appearance)
            } else {
                document.addEventListener("DOMContentLoaded", () => setButtons(appearance))
            }
        }
        window.setAppearance(window.localStorage.getItem('appearance') || 'system')

        // Apply font size settings
        ;
        (function() {
            const fontSize = window.localStorage.getItem('fontSize') || 'normal';
            const sizes = {
                small: '14px',
                normal: '16px',
                large: '18px'
            };
            document.documentElement.style.fontSize = sizes[fontSize] || sizes.normal;
        })();

        // Apply font family settings
        ;
        (function() {
            const fontFamily = window.localStorage.getItem('fontFamily') || 'sfpro';
            const fonts = {
                sfpro: '"SF Pro Display", -apple-system, BlinkMacSystemFont, sans-serif',
                inter: '"Inter", sans-serif',
                roboto: '"Roboto", sans-serif',
                opensans: '"Open Sans", sans-serif',
                lato: '"Lato", sans-serif',
                montserrat: '"Montserrat", sans-serif'
            };
            document.documentElement.style.fontFamily = fonts[fontFamily] || fonts.sfpro;
        })();

        // Apply font color settings
        ;
        (function() {
            const fontColor = window.localStorage.getItem('fontColor') || 'default';
            const colors = {
                default: {
                    light: '#1f2937',
                    dark: '#e5e7eb'
                },
                slate: {
                    light: '#334155',
                    dark: '#cbd5e1'
                },
                zinc: {
                    light: '#3f3f46',
                    dark: '#d4d4d8'
                },
                neutral: {
                    light: '#404040',
                    dark: '#d4d4d4'
                },
                stone: {
                    light: '#44403c',
                    dark: '#d6d3d1'
                },
                warmgray: {
                    light: '#78350f',
                    dark: '#fcd34d'
                },
                coolgray: {
                    light: '#1e3a5f',
                    dark: '#93c5fd'
                }
            };
            const selectedColor = colors[fontColor] || colors.default;
            document.documentElement.style.setProperty('--font-color-light', selectedColor.light);
            document.documentElement.style.setProperty('--font-color-dark', selectedColor.dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased" x-data="{
    sidebarOpen: localStorage.getItem('sidebarOpen') === null ? window.innerWidth >= 1024 : localStorage.getItem('sidebarOpen') === 'true',
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('sidebarOpen', this.sidebarOpen);
    },
    temporarilyOpenSidebar() {
        if (!this.sidebarOpen) {
            this.sidebarOpen = true;
            localStorage.setItem('sidebarOpen', true);
        }
    },
    closeSidebarOnMobile() {
        if (window.innerWidth < 768) {
            this.sidebarOpen = false;
            localStorage.setItem('sidebarOpen', false);
        }
    },
    formSubmitted: false,
}">

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col">

        <x-layouts.app.header />

        <!-- Main Content Area -->
        <div class="flex flex-1 overflow-hidden">

            <x-layouts.app.sidebar />

            <!-- Main Content -->
            <main class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-900 content-transition">
                <div class="p-6">
                    <!-- Success Message -->
                    @session('status')
                        <div x-data="{ showStatusMessage: true }" x-show="showStatusMessage"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded-md">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500 dark:text-green-400"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700 dark:text-green-200">{{ session('status') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <div class="-mx-1.5 -my-1.5">
                                        <button @click="showStatusMessage = false"
                                            class="inline-flex rounded-md p-1.5 text-green-500 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <span class="sr-only">{{ __('Dismiss') }}</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endsession

                    @if ($errors->any())
                        <div x-data="{ showErrorMessage: true }" x-show="showErrorMessage"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="mb-6 bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 rounded-md">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-300"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.59C19.02 15.97 18.122 18 16.518 18H3.482c-1.604 0-2.502-2.03-1.743-3.31l6.518-11.59zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V8a1 1 0 112 0v3a1 1 0 01-1 1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1 text-sm text-red-700 dark:text-red-200 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                                <button @click="showErrorMessage = false"
                                    class="text-red-600 dark:text-red-300 hover:text-red-800 dark:hover:text-red-100 focus:outline-none">
                                    &times;
                                </button>
                            </div>
                        </div>
                    @endif

                    {{ $slot }}

                </div>
            </main>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const tableIds = ['#subjects-table', '#guru-table', '#siswa-table', '#kelas-table', '#rombel-table',
            '#guru-subjects-table', '#rapor-table', '#tahfidz-table'
        ];
        tableIds.forEach((id) => {
            const tableEl = document.querySelector(id);
            if (tableEl) {
                new DataTable(tableEl);
            }
        });
    });
</script>

</html>
