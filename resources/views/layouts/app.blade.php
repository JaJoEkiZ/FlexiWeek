<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlexiWeek</title>
    <link rel="icon" type="image/png" href="{{ asset('images/flexiweek-Iso.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="bg-[#1e1e1e] text-[#d4d4d4] font-sans antialiased min-h-screen flex flex-col">
    <style>[x-cloak] { display: none !important; }</style>

    {{-- Global: Enter = submit/blur, Shift+Enter = newline on ALL text fields --}}
    <style>
        /* Auto-grow textareas that replace inputs */
        textarea.auto-grow {
            resize: none;
            overflow: hidden;
            min-height: 36px;
            field-sizing: content; /* Modern browsers: auto-size */
        }
    </style>
    <script>
        document.addEventListener('keydown', function(e) {
            const tag = e.target.tagName;
            const isTextarea = tag === 'TEXTAREA';
            const isInput = tag === 'INPUT' && (e.target.type === 'text' || e.target.type === 'number' || e.target.type === 'search');
            if (!isTextarea && !isInput) return;
            if (e.key !== 'Enter') return;
            if (e.shiftKey && isTextarea) return; // Shift+Enter = newline in textareas

            e.preventDefault();

            // Try to find the closest form and submit it
            const form = e.target.closest('form');
            if (form) {
                form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                return;
            }

            // Try to find a save/accept button in the closest container
            const container = e.target.closest('[role="dialog"], .pz-panel, .modal, form');
            if (container) {
                // Look for common save/submit buttons
                const saveBtn = container.querySelector('.pz-save-btn, [type="submit"]')
                    || container.querySelector('button[wire\\:click="save"]')
                    || container.querySelector('button[wire\\:click="save()"]');
                if (saveBtn) { saveBtn.click(); return; }
            }

            // Fallback: blur (triggers @blur save handlers)
            e.target.blur();
        });
    </script>
    
    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-[#252526] border-t border-[#333] p-4 text-center text-sm text-[#7b7b7b]">
        <p>FlexiWeek v1.9.0 - Developed by JaJo EkiZ</p>
    </footer>

</body>
</html>