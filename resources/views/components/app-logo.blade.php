@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="FlexiWeek" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('images/flexiweek-Iso.png') }}" class="size-8 rounded-md object-contain" alt="FlexiWeek Logo">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="FlexiWeek" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('images/flexiweek-Iso.png') }}" class="size-8 rounded-md object-contain" alt="FlexiWeek Logo">
        </x-slot>
    </flux:brand>
@endif
