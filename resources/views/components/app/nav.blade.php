<header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 sm:gap-6">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                {{ config('app.name', 'Checklist') }}
            </a>

            @auth
                <nav class="hidden items-center gap-3 sm:flex">
                    @if (auth()->user()->hasRole('admin'))
                        <x-app.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            Dashboard
                        </x-app.nav-link>

                        <x-app.nav-link :href="route('admin.templates.index')" :active="request()->routeIs('admin.templates.*')">
                            Templates
                        </x-app.nav-link>

                        <x-app.nav-link :href="route('admin.reports.checklist_instances')" :active="request()->routeIs('admin.reports.*')">
                            Reports
                        </x-app.nav-link>
                    @endif

                    @if (auth()->user()->hasRole('auditor'))
                        <x-app.nav-link :href="route('auditor.dashboard')" :active="request()->routeIs('auditor.*')">
                            Dashboard
                        </x-app.nav-link>
                    @endif
                </nav>
            @endauth
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @auth
                <div class="hidden text-sm text-slate-600 dark:text-slate-400 sm:block">
                    {{ auth()->user()->name }} <span class="text-slate-400">({{ auth()->user()->email }})</span>
                </div>
            @endauth

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md p-2 text-slate-700 ring-1 ring-inset ring-slate-300 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-900 dark:focus:ring-slate-600 dark:focus:ring-offset-slate-950"
                data-theme-toggle
            >
                <span class="sr-only" data-theme-toggle-label>Toggle theme</span>
                <svg data-theme-icon="light" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 2a.75.75 0 0 1 .75.75V4a.75.75 0 0 1-1.5 0V2.75A.75.75 0 0 1 10 2Zm0 14a.75.75 0 0 1 .75.75V18a.75.75 0 0 1-1.5 0v-1.25A.75.75 0 0 1 10 16Zm8-6a.75.75 0 0 1-.75.75H16a.75.75 0 0 1 0-1.5h1.25A.75.75 0 0 1 18 10ZM4 10a.75.75 0 0 1-.75.75H2a.75.75 0 0 1 0-1.5h1.25A.75.75 0 0 1 4 10Zm11.657-5.657a.75.75 0 0 1 0 1.06l-.884.884a.75.75 0 1 1-1.06-1.06l.884-.884a.75.75 0 0 1 1.06 0ZM6.287 13.713a.75.75 0 0 1 0 1.06l-.884.884a.75.75 0 1 1-1.06-1.06l.884-.884a.75.75 0 0 1 1.06 0Zm9.37 1.944a.75.75 0 0 1-1.06 0l-.884-.884a.75.75 0 0 1 1.06-1.06l.884.884a.75.75 0 0 1 0 1.06ZM6.287 6.287a.75.75 0 0 1-1.06 0l-.884-.884a.75.75 0 1 1 1.06-1.06l.884.884a.75.75 0 0 1 0 1.06ZM10 6.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" />
                </svg>
                <svg data-theme-icon="dark" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a6.5 6.5 0 1 0 10.586 10.586Z" />
                </svg>
            </button>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary">Logout</x-ui.button>
                </form>
            @else
                <x-ui.button :href="route('login')" variant="primary">Login</x-ui.button>
            @endauth

            @auth
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-900 sm:hidden"
                    data-mobile-menu-button
                    aria-controls="mobileNav"
                    aria-expanded="false"
                >
                    <span class="sr-only">Open menu</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 5h14a1 1 0 0 1 0 2H3a1 1 0 1 1 0-2zm0 6h14a1 1 0 1 1 0 2H3a1 1 0 0 1 0-2zm0 6h14a1 1 0 1 1 0 2H3a1 1 0 0 1 0-2z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endauth
        </div>
    </div>

    @auth
        <div class="sm:hidden">
            <div class="hidden border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950" id="mobileNav" data-mobile-menu>
                <div class="space-y-1 px-4 py-3">
                    @if (auth()->user()->hasRole('admin'))
                        <x-app.nav-link class="block w-full" :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-app.nav-link>
                        <x-app.nav-link class="block w-full" :href="route('admin.templates.index')" :active="request()->routeIs('admin.templates.*')">Templates</x-app.nav-link>
                        <x-app.nav-link class="block w-full" :href="route('admin.reports.checklist_instances')" :active="request()->routeIs('admin.reports.*')">Reports</x-app.nav-link>
                    @endif

                    @if (auth()->user()->hasRole('auditor'))
                        <x-app.nav-link class="block w-full" :href="route('auditor.dashboard')" :active="request()->routeIs('auditor.*')">Dashboard</x-app.nav-link>
                    @endif
                </div>
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-700 dark:hover:bg-slate-900"
                        data-theme-toggle
                    >
                        <svg data-theme-icon="light" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a.75.75 0 0 1 .75.75V4a.75.75 0 0 1-1.5 0V2.75A.75.75 0 0 1 10 2Zm0 14a.75.75 0 0 1 .75.75V18a.75.75 0 0 1-1.5 0v-1.25A.75.75 0 0 1 10 16Zm8-6a.75.75 0 0 1-.75.75H16a.75.75 0 0 1 0-1.5h1.25A.75.75 0 0 1 18 10ZM4 10a.75.75 0 0 1-.75.75H2a.75.75 0 0 1 0-1.5h1.25A.75.75 0 0 1 4 10Zm10-3.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" />
                        </svg>
                        <svg data-theme-icon="dark" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a6.5 6.5 0 1 0 10.586 10.586Z" />
                        </svg>
                        <span data-theme-toggle-label>Toggle theme</span>
                    </button>
                </div>
                <div class="border-t border-slate-200 px-4 py-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="w-full">Logout</x-ui.button>
                    </form>
                </div>
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ auth()->user()->name }}</div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>
    @endauth
</header>

