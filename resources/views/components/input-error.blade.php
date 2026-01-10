@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'mt-2 text-sm text-red-600 dark:text-red-400']) }}>
        <ul class="list-disc list-inside">
            @foreach ((array) $messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
