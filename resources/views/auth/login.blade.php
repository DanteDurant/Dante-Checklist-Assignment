<x-layouts.base title="Login">
    <div class="mx-auto max-w-md">
        <x-ui.card title="Sign in" description="Use your seeded admin/auditor credentials.">
            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"/>
                    @error('email')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"/>
                    @error('password')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <x-ui.button type="submit" class="w-full">Login</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.base>

