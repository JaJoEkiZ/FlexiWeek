@props(['heading' => '', 'subheading' => ''])

<div class="settings-card {{ $attributes->get('class', '') }}">
    @if($heading)
        <h2 class="text-lg font-semibold mb-1" style="color: var(--settings-heading-text)">{{ $heading }}</h2>
    @endif
    @if($subheading)
        <p class="text-sm mb-4" style="color: var(--settings-subheading-text)">{{ $subheading }}</p>
    @endif

    <div class="w-full max-w-lg">
        {{ $slot }}
    </div>
</div>
