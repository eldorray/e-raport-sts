@props(['active' => false, 'href' => '#', 'icon' => 'fas-house'])

    <a href="{{ $href }}" @click="closeSidebarOnMobile()" @if ($active) aria-current="page" @endif
        @class([
            'flex h-[38px] items-center rounded-xl px-3 text-sm transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] active:scale-[0.97] motion-reduce:transition-none',
            'bg-accent/10 font-semibold text-accent-strong dark:bg-accent/20 dark:text-blue-300' => $active,
            'font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => !$active,
        ]) :class="{ 'justify-center': !sidebarOpen, 'justify-start': sidebarOpen }">
        @svg($icon, 'h-[18px] w-[18px] shrink-0' . ($active ? '' : ' text-zinc-500 dark:text-zinc-400'))
        <span x-show="sidebarOpen" x-transition:enter="transition-all duration-300"
            x-transition:enter-start="opacity-0 transform -translate-x-2"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition-all duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform -translate-x-2"
            class="ml-3 whitespace-nowrap">{{ $slot }}</span>
    </a>
