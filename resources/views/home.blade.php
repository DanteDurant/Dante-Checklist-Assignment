<x-layouts.base title="Home">
    <div class="mx-auto max-w-3xl">
        <x-ui.card title="Compliance Checklist System">
            @auth
                <p class="text-sm leading-relaxed text-ui-fg-muted">
                    You are logged in as <span class="font-semibold text-ui-fg">{{ auth()->user()->email }}</span>.
                </p>
            @else
                <p class="text-sm leading-relaxed text-ui-fg-muted">
                    Sign in to open the admin or auditor area.
                </p>
                <div class="mt-4">
                    <x-ui.button :href="route('login')">Sign in</x-ui.button>
                </div>
            @endauth
        </x-ui.card>
    </div>
</x-layouts.base>
