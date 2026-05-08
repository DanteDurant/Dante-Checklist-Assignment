<x-layouts.base title="Login">
    <div class="mx-auto max-w-md">
        <x-ui.card title="Sign in" description="Use your seeded admin/auditor credentials.">
            <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Quick credentials</p>
                <div class="mt-2 space-y-3">
                    <div class="rounded-md border border-slate-200 bg-white p-3">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-900">Admin</div>
                                <div class="mt-1 text-sm text-slate-700">
                                    <span class="block break-all font-mono">admin@example.com</span>
                                </div>
                                <div class="mt-1 text-sm text-slate-700">
                                    Password: <span class="font-mono">password</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-1 sm:justify-items-end">
                                <button type="button" class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto"
                                        data-copy="admin@example.com" data-copy-label="Admin email">
                                    Copy email
                                </button>
                                <button type="button" class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto"
                                        data-copy="password" data-copy-label="Admin password">
                                    Copy password
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white p-3">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-900">Auditor</div>
                                <div class="mt-1 text-sm text-slate-700">
                                    <span class="block break-all font-mono">auditor@example.com</span>
                                </div>
                                <div class="mt-1 text-sm text-slate-700">
                                    Password: <span class="font-mono">password</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-1 sm:justify-items-end">
                                <button type="button" class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto"
                                        data-copy="auditor@example.com" data-copy-label="Auditor email">
                                    Copy email
                                </button>
                                <button type="button" class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto"
                                        data-copy="password" data-copy-label="Auditor password">
                                    Copy password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-500">Copy uses your browser clipboard permissions.</p>
            </div>

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

