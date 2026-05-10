<x-layouts.admin title="Templates" heading="Checklist Templates">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm leading-relaxed text-ui-fg-muted">Manage checklist templates and their questions.</p>
        </div>
        <div>
            <x-ui.button :href="route('admin.templates.create')">New template</x-ui.button>
        </div>
    </div>

    <div class="mt-6">
        <form method="GET" action="{{ route('admin.templates.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div class="min-w-0 flex-1 sm:max-w-md">
                <label for="template-search" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Search templates</label>
                <div class="flex gap-2">
                    <input id="template-search" name="search" type="search" value="{{ old('search', $search ?? '') }}" autocomplete="off"
                           placeholder="Title or description…"
                           class="block w-full rounded-lg border border-ui-fill-border bg-ui-canvas px-3 py-2 text-sm text-ui-fg shadow-ui-sm placeholder:text-ui-fg-subtle focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas dark:bg-ui-surface" />
                    @if(request()->filled('search'))
                        <x-ui.button variant="secondary" :href="route('admin.templates.index')">Clear</x-ui.button>
                    @endif
                </div>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" data-loading-text="Searching…">Search</x-ui.button>
            </div>
        </form>

        <div class="space-y-3 sm:hidden">
            @forelse ($templates as $t)
                <x-ui.card>
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <a class="block truncate text-sm font-semibold text-ui-fg underline decoration-ui-border underline-offset-2 transition hover:text-ui-fg-muted"
                               href="{{ route('admin.templates.show', $t) }}">
                                {{ $t->name }}
                            </a>
                            <p class="mt-1 text-sm text-ui-fg-muted">
                                <x-ui.status-badge :status="$t->status" /> · Questions: {{ $t->questions_count }}
                            </p>
                            <p class="mt-1 text-xs tabular-nums text-ui-fg-subtle">
                                Updated: {{ $t->updated_at?->toDateTimeString() }}
                            </p>
                        </div>
                        <div class="shrink-0">
                            <x-ui.button :href="route('admin.templates.edit', $t)" variant="secondary">Edit</x-ui.button>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-ui.button :href="route('admin.templates.show', $t)" variant="secondary">View</x-ui.button>
                        <form method="POST" action="{{ route('admin.templates.destroy', $t) }}"
                              data-confirm="Archive this template? It will be hidden from lists. Completed checklists and audit history stay in the system."
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger" data-loading-text="Archiving…">Archive</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty-state
                    title="{{ request()->filled('search') ? 'No matching templates' : 'No templates yet' }}"
                    message="{{ request()->filled('search') ? 'Try a different search or clear filters.' : 'Create your first checklist template to get started.' }}"
                >
                    @unless(request()->filled('search'))
                        <x-ui.button :href="route('admin.templates.create')">New template</x-ui.button>
                    @endunless
                </x-ui.empty-state>
            @endforelse
        </div>

        <div class="hidden sm:block">
            <x-ui.table :headers="['Title', 'Status', 'Questions', 'Updated', 'Actions']">
                @forelse ($templates as $t)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-ui-fg">
                            <a class="underline decoration-ui-border underline-offset-2 hover:text-ui-fg-muted"
                               href="{{ route('admin.templates.show', $t) }}">{{ $t->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm"><x-ui.status-badge :status="$t->status" /></td>
                        <td class="px-4 py-3 text-sm tabular-nums text-ui-fg-muted">{{ $t->questions_count }}</td>
                        <td class="px-4 py-3 text-sm tabular-nums text-ui-fg-muted">{{ $t->updated_at?->toDateTimeString() }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button :href="route('admin.templates.edit', $t)" variant="secondary">Edit</x-ui.button>
                                <form method="POST" action="{{ route('admin.templates.destroy', $t) }}"
                                      data-confirm="Archive this template? It will be hidden from lists. Completed checklists and audit history stay in the system."
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" data-loading-text="Archiving…">Archive</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6">
                            <x-ui.empty-state
                                title="{{ request()->filled('search') ? 'No matching templates' : 'No templates yet' }}"
                                message="{{ request()->filled('search') ? 'Try a different search or clear filters.' : 'Create your first checklist template to get started.' }}"
                            >
                                @unless(request()->filled('search'))
                                    <x-ui.button :href="route('admin.templates.create')">New template</x-ui.button>
                                @endunless
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>

    <div class="mt-6">
        {{ $templates->links() }}
    </div>
</x-layouts.admin>
