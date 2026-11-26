<x-layouts.app>
    <!-- Breadcrumbs (di HP diganti tombol kembali / menu) -->
    <div class="mb-6 flex items-center text-sm max-md:hidden">
        <a href="{{ route('dashboard') }}"
            class="text-accent-strong dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-zinc-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('settings.profile.edit') }}"
           class="text-accent-strong dark:text-blue-400 hover:underline">{{ __('Profile') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-zinc-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Password') }}</span>
    </div>

    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Update password') }}</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Ensure your account is using a long, random password to stay secure') }}
        </p>
    </div>

    <div class="p-6 max-md:p-0">
        <div class="flex flex-col md:flex-row gap-6 max-md:gap-4">
            <!-- Sidebar Navigation -->
            @include('settings.partials.navigation')

            <!-- Profile Content -->
            <div class="flex-1">
                <div
                    class="kartu overflow-hidden mb-6">
                    <div class="p-6 max-md:p-4">
                        <!-- Profile Form -->
                        <form
                            class="max-w-md mb-10 max-md:mb-0 max-md:[&_button]:min-h-11 max-md:[&_button]:w-full max-md:[&_input]:min-h-11 max-md:[&_input]:text-base"
                            action="{{ route('settings.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <x-forms.input label="Current Password" name="current_password" type="password" />
                            </div>

                            <div class="mb-6">
                                <x-forms.input label="New Password" name="password" type="password" />
                            </div>

                            <div class="mb-6">
                                <x-forms.input label="Confirm Password" name="password_confirmation" type="password" />
                            </div>

                            <div>
                                <x-button type="primary">{{ __('Update Password') }}</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
