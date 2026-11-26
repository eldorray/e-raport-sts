{{-- Di HP navigasi menjadi kontrol segmen tiga tombol --}}
<div class="w-full md:w-64 shrink-0 border-r border-zinc-200 dark:border-zinc-700 pr-4 max-md:border-r-0 max-md:pr-0">
    <nav class="bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-hidden max-md:rounded-xl max-md:border max-md:border-zinc-200 max-md:dark:border-zinc-700">
        <ul class="divide-y divide-zinc-200 dark:divide-zinc-700 max-md:grid max-md:grid-cols-3 max-md:divide-x max-md:divide-y-0">
            <li>
                <a href="{{ route('settings.profile.edit') }}" @if (request()->routeIs('settings.profile.*')) aria-current="page" @endif @class([
                    'max-md:flex max-md:min-h-11 max-md:items-center max-md:justify-center max-md:px-2 max-md:text-center max-md:text-sm',
                    'bg-zinc-100 dark:bg-zinc-700 block px-4 py-3 text-zinc-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-600' => !request()->routeIs(
                        'settings.profile.*'),
                    'bg-white dark:bg-zinc-600 block px-4 py-3  text-zinc-900 dark:text-zinc-100 font-medium' => request()->routeIs(
                        'settings.profile.*'),
                ])>
                    {{ __('Profile') }}
                </a>
            </li>
            <li>
                <a href="{{ route('settings.password.edit') }}" @if (request()->routeIs('settings.password.*')) aria-current="page" @endif @class([
                    'max-md:flex max-md:min-h-11 max-md:items-center max-md:justify-center max-md:px-2 max-md:text-center max-md:text-sm',
                    'bg-zinc-100 dark:bg-zinc-700 block px-4 py-3 text-zinc-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-600' => !request()->routeIs(
                        'settings.password.*'),
                    'bg-white dark:bg-zinc-600  block px-4 py-3 text-zinc-900 dark:text-zinc-100 font-medium' => request()->routeIs(
                        'settings.password.*'),
                ])>
                    {{ __('Password') }}
                </a>
            </li>
            <li>
                <a href="{{ route('settings.appearance.edit') }}" @if (request()->routeIs('settings.appearance.*')) aria-current="page" @endif @class([
                    'max-md:flex max-md:min-h-11 max-md:items-center max-md:justify-center max-md:px-2 max-md:text-center max-md:text-sm',
                    'bg-zinc-100 dark:bg-zinc-700 block px-4 py-3 text-zinc-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-600' => !request()->routeIs(
                        'settings.appearance.*'),
                    'bg-white dark:bg-zinc-600 block px-4 py-3  text-zinc-900 dark:text-zinc-100 font-medium' => request()->routeIs(
                        'settings.appearance.*'),
                ])>
                    {{ __('Appearance') }}
                </a>
            </li>
        </ul>
    </nav>
</div>
