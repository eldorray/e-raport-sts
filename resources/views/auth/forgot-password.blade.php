<x-layouts.auth :title="__('Forgot Password')">
    <!-- Forgot Password Card -->
    <div
        class="kartu overflow-hidden">
        <div class="p-6">
            <div class="text-center mb-6">
                <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Forgot Password') }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Enter your email to receive a password reset link') }}</p>
            </div>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <!-- Email Input -->
                <div class="mb-4">
                    <x-forms.input name="email" type="email" label="Email" placeholder="your@email.com" />
                </div>

                <!-- Send Reset Link Button -->
                <x-button type="primary" buttonType="submit" class="w-full">
                    {{ __('Send Password Reset Link') }}
                </x-button>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center mt-6">
                <a href="{{ route('login') }}"
                    class="text-accent-strong dark:text-blue-400 hover:underline font-medium">{{ __('Back to login') }}</a>
            </div>
        </div>
    </div>
</x-layouts.auth>
