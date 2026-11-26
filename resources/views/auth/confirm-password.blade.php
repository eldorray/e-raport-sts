<x-layouts.auth :title="__('Confirm Password')">
    <!-- Confirm Password Card -->
    <div
        class="kartu overflow-hidden">
        <div class="p-6">
            <div class="text-center mb-6">
                <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Confirm Password') }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Please confirm your password before continuing.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf
                <!-- Password Input -->
                <div class="mb-4">
                    <x-forms.input name="password" type="password" label="Password" placeholder="••••••••" />
                </div>

                <!-- Confirm Button -->
                <x-button type="primary" buttonType="submit" class="w-full">
                    {{ __('Confirm Password') }}
                </x-button>
            </form>

            <!-- Forgot Password Link -->
            <div class="text-center mt-6">
                <a href="{{ route('password.request') }}"
                    class="text-accent-strong dark:text-blue-400 hover:underline font-medium">{{ __('Forgot your password?') }}</a>
            </div>
        </div>
    </div>
</x-layouts.auth>
