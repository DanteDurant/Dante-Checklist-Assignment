<x-layouts.admin title="Templates" heading="Checklist Templates">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-500">Manage checklist templates and their questions.</p>
        </div>
        <div>
            <x-ui.button :href="route('admin.templates.create')">New template</x-ui.button>
        </div>
    </div>

    <div class="mt-6">
        <x-ui.table :headers="['Title', 'Status', 'Questions', 'Updated', 'Actions']">
            @forelse ($templates as $t)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-slate-900">
                        <a class="hover:underline" href="{{ route('admin.templates.show', $t) }}">{{ $t->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $t->status->value }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $t->questions_count }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $t->updated_at?->toDateTimeString() }}</td>
                    <td class="px-4 py-3 text-sm">
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button :href="route('admin.templates.edit', $t)" variant="secondary">Edit</x-ui.button>
                            <form method="POST" action="{{ route('admin.templates.destroy', $t) }}"
                                  onsubmit="return confirm('Delete this template? This will cascade-delete its questions.');">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="danger">Delete</x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-sm text-slate-500">No templates yet.</td>
                </tr>
            @endforelse
        </x-ui.table>
    </div>

    <div class="mt-6">
        {{ $templates->links() }}
    </div>
</x-layouts.admin>

