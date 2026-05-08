<x-layouts.admin title="Edit Template" :heading="'Edit: '.$template->name">
    <div class="max-w-3xl">
        <x-ui.card title="Template details">
            <form method="POST" action="{{ route('admin.templates.update', $template) }}">
                @csrf
                @method('PUT')
                <x-admin.templates.form :template="$template" :statuses="$statuses" submit-label="Save changes" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.admin>

