<x-layouts.base title="Login">
    <div class="mx-auto max-w-md">
        <x-ui.card title="Sign in" description="Use your seeded admin/auditor credentials.">
            <div class="mb-4 rounded-lg border border-ui-border bg-ui-muted/60 p-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-ui-fg-muted">Quick credentials</p>
                <div class="mt-2 space-y-3">
                    <div class="rounded-md border border-ui-border bg-ui-surface p-3">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-ui-fg">Admin</div>
                                <div class="mt-1 text-sm text-ui-fg-muted">
                                    <span class="block break-all font-mono text-ui-fg-subtle">admin@example.com</span>
                                </div>
                                <div class="mt-1 text-sm text-ui-fg-muted">
                                    Password: <span class="font-mono text-ui-fg">password</span>
                                </div>
                            </div>
                            <div class="sm:justify-items-end">
                                <button type="button"
                                        class="w-full rounded-md bg-ui-surface px-3 py-2 text-sm font-semibold text-ui-fg ring-1 ring-inset ring-ui-border transition hover:bg-ui-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-surface sm:w-auto"
                                        data-fill-credentials
                                        data-fill-email="admin@example.com"
                                        data-fill-password="password">
                                    Autofill
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-md border border-ui-border bg-ui-surface p-3">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-ui-fg">Auditor</div>
                                <div class="mt-1 text-sm text-ui-fg-muted">
                                    <span class="block break-all font-mono text-ui-fg-subtle">auditor@example.com</span>
                                </div>
                                <div class="mt-1 text-sm text-ui-fg-muted">
                                    Password: <span class="font-mono text-ui-fg">password</span>
                                </div>
                            </div>
                            <div class="sm:justify-items-end">
                                <button type="button"
                                        class="w-full rounded-md bg-ui-surface px-3 py-2 text-sm font-semibold text-ui-fg ring-1 ring-inset ring-ui-border transition hover:bg-ui-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-surface sm:w-auto"
                                        data-fill-credentials
                                        data-fill-email="auditor@example.com"
                                        data-fill-password="password">
                                    Autofill
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs leading-relaxed text-ui-fg-subtle">Autofill will populate the form below.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <x-ui.field label="Email" name="email">
                    <x-ui.input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" />
                </x-ui.field>

                <x-ui.field label="Password" name="password">
                    <div class="relative">
                        <x-ui.input id="password" name="password" type="password" required class="pr-10" autocomplete="current-password" />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-3 text-ui-fg-subtle transition hover:text-ui-fg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-ui-ring"
                            data-toggle-password="password"
                            aria-label="Show password"
                        >
                            <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 4c-5 0-8.5 4.5-8.5 6s3.5 6 8.5 6 8.5-4.5 8.5-6-3.5-6-8.5-6Zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z" />
                                <path d="M10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                            </svg>
                            <svg data-password-icon="hide" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l1.57 1.57C2.5 6.2 1.5 8.02 1.5 10c0 1.5 3.5 6 8.5 6 1.57 0 3.02-.45 4.28-1.16l1.69 1.69a.75.75 0 1 0 1.06-1.06l-14.5-14.5Zm6.47 12.03a4 4 0 0 1-4-4c0-.52.1-1.02.29-1.47l1.2 1.2a3 3 0 0 0 3.98 3.98l1.2 1.2c-.45.19-.95.29-1.47.29Zm6.47-1.97-2.03-2.03A4 4 0 0 0 6.5 6.56L4.69 4.75C6.2 3.66 8.02 3 10 3c5 0 8.5 4.5 8.5 6 0 1.21-2.27 4.25-5.03 5.53Z" />
                            </svg>
                        </button>
                    </div>
                </x-ui.field>

                <div class="pt-2">
                    <x-ui.button type="submit" class="w-full">Login</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.base>
