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
                              data-confirm="Delete this template? This will cascade-delete its questions."
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger" data-loading-text="Deleting...">Delete</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty-state title="No templates yet" message="Create your first checklist template to get started.">
                    <x-ui.button :href="route('admin.templates.create')">New template</x-ui.button>
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
                                      data-confirm="Delete this template? This will cascade-delete its questions."
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" data-loading-text="Deleting...">Delete</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6">
                            <x-ui.empty-state title="No templates yet" message="Create your first checklist template to get started.">
                                <x-ui.button :href="route('admin.templates.create')">New template</x-ui.button>
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
