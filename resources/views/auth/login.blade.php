<x-layouts.base title="Login">
    <div class="mx-auto max-w-md">
        <x-ui.card title="Sign in" description="Use your seeded admin/auditor credentials.">
            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <x-ui.field label="Email" name="email">
                    <x-ui.input id="email" name="email" type="email" value="{{ old('email') }}" required />
                </x-ui.field>

                <x-ui.field label="Password" name="password">
                    <x-ui.input id="password" name="password" type="password" required />
                </x-ui.field>

                <div class="pt-2">
                    <x-ui.button type="submit" class="w-full">Login</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.base>

