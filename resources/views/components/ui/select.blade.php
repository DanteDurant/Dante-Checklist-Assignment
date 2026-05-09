<select {{ $attributes->merge(['class' => 'w-full rounded-md border border-ui-fill-border bg-ui-fill px-3 py-2 text-sm text-ui-fg shadow-sm focus:border-ui-ring focus:outline-none focus:ring-2 focus:ring-ui-ring/40 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-ui-muted disabled:text-ui-fg-subtle disabled:opacity-80']) }}>
    {{ $slot }}
</select>
