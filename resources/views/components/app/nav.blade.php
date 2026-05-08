<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 sm:gap-6">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-900">
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

        <div class="flex items-center gap-3">
            @auth
                <div class="hidden text-sm text-slate-600 sm:block">
                    {{ auth()->user()->name }} <span class="text-slate-400">({{ auth()->user()->email }})</span>
                </div>

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
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:hidden"
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
            <div class="hidden border-t border-slate-200 bg-white" id="mobileNav" data-mobile-menu>
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
                <div class="border-t border-slate-200 px-4 py-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="w-full">Logout</x-ui.button>
                    </form>
                </div>
                <div class="border-t border-slate-200 px-4 py-3">
                    <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</div>
                    <div class="text-sm text-slate-600">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>
    @endauth
</header>

