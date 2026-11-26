@props(['active' => false, 'title' => '', 'icon' => 'fas-list'])

<li x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button type="button" :aria-expanded="open && sidebarOpen"
        @click="
            if (sidebarOpen) {
                open = !open;
            } else {
                temporarilyOpenSidebar();
                open = true;
            }
        "
        @class([
            'w-full flex h-[38px] items-center rounded-xl px-3 text-sm transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] active:scale-[0.97] motion-reduce:transition-none',
            'font-semibold text-zinc-900 dark:text-zinc-100' => $active,
            'font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => !$active,
        ]) :class="{ 'justify-center': !sidebarOpen, 'justify-between': sidebarOpen }">
        <span class="flex items-center" :class="{ 'justify-center': !sidebarOpen }">
            @svg($icon, 'h-[18px] w-[18px] shrink-0 text-zinc-500 dark:text-zinc-400')
            <span x-show="sidebarOpen" x-transition:enter="transition-all duration-300"
            x-transition:enter-start="opacity-0 transform -translate-x-2"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition-all duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform -translate-x-2"
            class="ml-3 whitespace-nowrap">{{ $title }}</span>
        </span>
        <svg x-show="sidebarOpen" aria-hidden="true" class="h-4 w-4 transition-transform duration-300 ease-[cubic-bezier(.32,.72,0,1)]"
            :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>

    <!-- Level 2 submenu -->
    <div x-show="open && sidebarOpen" x-transition:enter="transition duration-300 ease-[cubic-bezier(.32,.72,0,1)]"
        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        class="ml-[21px] mt-0.5 space-y-0.5 border-l border-zinc-200 pl-2 dark:border-zinc-700">
        {{ $slot }}
    </div>
</li>
