@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <img
                src="{{ asset('images/yamban-logo.jpg') }}"
                alt="{{ config('app.name') }} logo"
                class="size-8 object-contain"
            >
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <img
                src="{{ asset('images/yamban-logo.jpg') }}"
                alt="{{ config('app.name') }} logo"
                class="size-8 object-contain"
            >
        </x-slot>
    </flux:brand>
@endif
