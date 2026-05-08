<x-layouts.base title="Welcome">
    <div class="mx-auto max-w-3xl">
        <x-ui.card title="Welcome" description="Blade-based UI shell for the compliance checklist system.">
            <p class="text-sm text-slate-600">
                Use the navigation above to access the Admin or Auditor areas based on your role.
            </p>

            @guest
                <div class="mt-4">
                    <x-ui.button :href="route('login')">Login</x-ui.button>
                </div>
            @endguest
        </x-ui.card>
            </div>
</x-layouts.base>

