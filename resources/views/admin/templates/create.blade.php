<x-layouts.admin title="New Template" heading="New Template">
    <div class="max-w-3xl">
        <x-ui.card title="Template details">
            <form method="POST" action="{{ route('admin.templates.store') }}">
                @csrf
                <x-admin.templates.form :template="null" :statuses="$statuses" submit-label="Create template" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.admin>

