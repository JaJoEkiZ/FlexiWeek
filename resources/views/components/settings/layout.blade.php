@props(['heading' => '', 'subheading' => ''])

<div class="bg-[#252526] dark:bg-[#252526] bg-white/80 border border-[#333] dark:border-[#333] border-gray-200 rounded-lg p-6 mb-6 {{ $attributes->get('class', '') }}">
    @if($heading)
        <h2 class="text-white dark:text-white text-gray-900 text-lg font-semibold mb-1">{{ $heading }}</h2>
    @endif
    @if($subheading)
        <p class="text-[#7b7b7b] dark:text-[#7b7b7b] text-gray-500 text-sm mb-4">{{ $subheading }}</p>
    @endif

    <div class="w-full max-w-lg">
        {{ $slot }}
    </div>
</div>
