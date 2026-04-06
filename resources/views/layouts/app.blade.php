<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma Flexible</title>
    <link rel="icon" type="image/png" href="{{ asset('images/flexiweek-Iso.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="bg-[#1e1e1e] text-[#d4d4d4] font-sans antialiased min-h-screen flex flex-col">
    <style>[x-cloak] { display: none !important; }</style>
    
    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-[#252526] border-t border-[#333] p-4 text-center text-sm text-[#7b7b7b]">
        <p>FlexiWeek v1.4.1 - Developed by JaJo EkiZ</p>
    </footer>

</body>
</html>