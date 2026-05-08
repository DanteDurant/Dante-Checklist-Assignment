<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-900">
                {{ config('app.name', 'Checklist') }}
            </a>

            @auth
                <nav class="hidden items-center gap-3 sm:flex">
                    @if (auth()->user()->hasRole('admin'))
                        <x-app.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
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
        </div>
    </div>
</header>

