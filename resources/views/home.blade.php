<x-layouts.base title="Home">
    <div class="mx-auto max-w-3xl">
        <x-ui.card title="Compliance Checklist System" description="Blade + Tailwind UI shell with role-based navigation.">
            @auth
                <p class="text-sm text-slate-600">
                    You are logged in as <span class="font-medium">{{ auth()->user()->email }}</span>.
                </p>
            @else
                <p class="text-sm text-slate-600">
                    Please sign in to access the admin or auditor dashboards.
                </p>
                <div class="mt-4">
                    <x-ui.button :href="route('login')">Go to login</x-ui.button>
                </div>
            @endauth
        </x-ui.card>
    </div>
</x-layouts.base>

