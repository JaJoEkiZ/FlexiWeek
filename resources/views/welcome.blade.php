<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlexiWeek — Tu semana. Tus reglas.</title>
    <meta name="description" content="FlexiWeek no te dice cómo usar tu tiempo. Te muestra cómo lo estás usando — y te deja decidir si eso es lo que querés.">
    <link rel="icon" type="image/png" href="{{ asset('images/flexiweek-Iso.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

        /* Grid de fondo */
        .bg-grid {
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 48px 48px;
            background-attachment: fixed;
        }

        /* Glow hero — centered on logo */
        .hero-glow {
            position: absolute;
            width: 800px;
            height: 800px;
            top: 50%;
            left: 50%;
            margin-top: -400px;
            margin-left: -400px;
            background: radial-gradient(circle, rgba(0,127,212,0.12) 0%, rgba(0,127,212,0.04) 35%, transparent 65%);
            pointer-events: none;
            z-index: 0;
        }

        /* Quote line */
        .quote-accent {
            border-left: 2px solid #007fd4;
        }

        /* Section cards */
        .section-card {
            background: rgba(37,37,38,0.6);
            border: 1px solid #333;
            border-radius: 12px;
            backdrop-filter: blur(12px);
        }

        /* CTA glow */
        .cta-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .cta-btn::before {
            content: '';
            position: absolute;
            inset: -1px;
            background: linear-gradient(135deg, #007fd4, #569cd6, #007fd4);
            border-radius: inherit;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .cta-btn:hover::before { opacity: 1; }
        .cta-btn:hover { box-shadow: 0 0 32px rgba(0,127,212,0.3), 0 0 64px rgba(0,127,212,0.1); }

        /* Divider */
        .divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #333 50%, transparent);
        }

        /* Subtle floating animation for logo */
        .logo-float {
            animation: floatLogo 6s ease-in-out infinite;
        }
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        /* Fade in on load */
        .fade-in {
            opacity: 0;
            transform: translateY(16px);
            animation: fadeUp 0.7s ease forwards;
        }
        .delay-1 { animation-delay: 0.15s; }
        .delay-2 { animation-delay: 0.3s; }
        .delay-3 { animation-delay: 0.45s; }
        .delay-4 { animation-delay: 0.6s; }
        .delay-5 { animation-delay: 0.75s; }
        .delay-6 { animation-delay: 0.9s; }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Mono label */
        .mono-label {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 10px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #555;
        }
    </style>
</head>
<body class="bg-[#1e1e1e] text-[#d4d4d4] antialiased min-h-screen flex flex-col bg-grid">

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="flex-1 flex flex-col items-center px-6 md:px-12 relative">

        <div class="max-w-3xl w-full relative z-10">

            {{-- ═══ HERO ═══ --}}
            <section class="pt-20 md:pt-36 pb-20 md:pb-32 text-center fade-in delay-1">
                {{-- Logo isotipo grande + glow centered here --}}
                <div class="flex flex-col items-center justify-center mb-14 relative" style="min-height: 220px;">
                    {{-- Contenedor animado para que el logo y el glow floten juntos --}}
                    <div class="relative flex items-center justify-center logo-float">
                        <div class="hero-glow"></div>
                        <img src="{{ asset('images/flexiweek-Iso.png') }}" alt="" class="h-44 md:h-80 w-auto relative z-10">
                    </div>

                    {{-- Botón debajo del logo --}}
                    @auth
                        <a href="{{ url('/planner') }}"
                           class="mt-8 relative z-10 text-sm text-[#007fd4] border border-[#007fd4]/40 rounded-lg px-6 py-2.5 hover:bg-[#007fd4]/10 hover:border-[#007fd4] transition-all">
                            Ir al planificador →
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="mt-8 relative z-10 text-sm text-[#007fd4] border border-[#007fd4]/40 rounded-lg px-6 py-2.5 hover:bg-[#007fd4]/10 hover:border-[#007fd4] transition-all">
                            Comenzar →
                        </a>
                    @endauth
                </div>

                {{-- Quote --}}
                <div class="mb-16">
                    <blockquote class="quote-accent text-left max-w-lg mx-auto pl-6 py-3">
                        <p class="text-[#7b7b7b] text-base md:text-lg italic leading-snug">
                            "Estamos estancados en un presente que no tiene pasado del cual aprender y que no tiene futuro hacia el cual avanzar."
                        </p>
                        <p class="text-[#555555] text-sm md:text-base italic leading-snug">
                            "We are stuck in a present that has no past to learn from and no future to move towards."
                        </p>
                        <footer class="mt-2 text-xs text-[#444] not-italic">
                            — Mark Fisher, <cite>Ghosts of My Life</cite> (2014)
                        </footer>
                    </blockquote>
                </div>

                {{-- Headline --}}
                <div class="space-y-1">
                    <h1 class="text-3xl md:text-[2.75rem] font-light leading-snug tracking-tight text-[#e8e8e8]">
                        El tiempo no es tuyo.<br>
                        Te lo prestaron con condiciones.
                    </h1>
                    <p class="text-2xl md:text-3xl font-medium text-[#007fd4]">
                        FlexiWeek te lo devuelve.
                    </p>
                </div>

                <p class="mt-10 text-[#7b7b7b] text-base md:text-lg leading-relaxed max-w-xl mx-auto">
                    Te enseñaron a medir tu vida en horas productivas. Nosotros te enseñamos a medirla en tuyas.
                </p>
            </section>

            <div class="divider fade-in delay-2"></div>

            {{-- ═══ CONCEPTO ═══ --}}
            <section class="py-20 md:py-8 fade-in delay-2">
                <span class="mono-label mb-6 block">Concepto</span>
                <div class="section-card p-7 md:p-10 space-y-5">
                    <p class="text-[#e8e8e8] text-lg md:text-xl leading-relaxed">
                        No es una app de productividad.
                    </p>
                    <p class="text-[#8b949e] text-base md:text-lg leading-relaxed">
                        Es una herramienta para ver, en tiempo real, cuánto de tu semana ya no te pertenece — y recuperar el resto.
                    </p>
                </div>
            </section>

            <div class="divider fade-in delay-3"></div>

            {{-- ═══ POR QUÉ EXISTE ═══ --}}
            <section class="py-20 md:py-8 fade-in delay-3">
                <span class="mono-label mb-6 block">Por qué existe</span>
                <div class="space-y-6">

                    <div class="section-card p-7 md:p-10 space-y-4 border-l-2 border-l-[#007fd4]/50">
                        <p class="text-[#e8e8e8] text-lg md:text-xl leading-relaxed">
                            FlexiWeek no te dice cómo usar tu tiempo.
                        </p>
                        <p class="text-[#8b949e] text-base md:text-lg leading-relaxed">
                            Te muestra cómo lo estás usando — y te deja decidir si eso es lo que querés.
                        </p>
                    </div>
                </div>
            </section>

            <div class="divider fade-in delay-4"></div>

            {{-- ═══ CÓMO FUNCIONA ═══ --}}
            <section class="py-20 md:py-8 fade-in delay-4">
                <span class="mono-label mb-6 block">Cómo funciona</span>
                <div class="grid gap-4">
                    <div class="section-card p-6 md:p-8">
                        <p class="text-[#e8e8e8] text-base md:text-lg leading-relaxed">
                            Organizás tu semana en bloques. Cortos, concretos, tuyos.
                        </p>
                    </div>
                    <div class="section-card p-6 md:p-8">
                        <p class="text-[#8b949e] text-base md:text-lg leading-relaxed">
                            Ves qué hiciste, qué dejaste, qué te comió el día.
                        </p>
                    </div>
                    <div class="section-card p-6 md:p-8">
                        <p class="text-[#8b949e] text-base md:text-lg leading-relaxed">
                            Sin notificaciones que te persigan. Sin streak que te culpe. Sin jefe digital.
                        </p>
                    </div>
                    <div class="section-card p-6 md:p-8 border-l-2 border-l-[#007fd4]/50">
                        <p class="text-[#e8e8e8] text-lg md:text-xl font-medium leading-relaxed text-center">
                            Solo vos y tu semana.
                        </p>
                    </div>
                </div>
            </section>

            <div class="divider fade-in delay-5"></div>

            {{-- ═══ CIERRE / CTA ═══ --}}
            <section class="py-28 md:py-8 text-center fade-in delay-5">
                <div class="space-y-4 mb-12">
                    <p class="text-2xl md:text-3xl font-light text-[#e8e8e8] leading-relaxed">
                        El tiempo es lo único que realmente tenés.
                    </p>
                    <p class="text-[#8b949e] text-lg md:text-xl leading-relaxed">
                        Usalo como si fuera tuyo.
                    </p>
                </div>

                <p class="mono-label mb-10">Tu semana · Tus reglas</p>

                @auth
                    <a href="{{ url('/planner') }}"
                       class="cta-btn inline-block text-base font-medium text-white bg-[#007fd4] border border-[#007fd4] rounded-lg px-12 py-4 hover:bg-[#006cb5] transition-all">
                        Entrá
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="cta-btn inline-block text-base font-medium text-white bg-[#007fd4] border border-[#007fd4] rounded-lg px-12 py-4 hover:bg-[#006cb5] transition-all">
                        Entrá
                    </a>
                @endauth
            </section>

        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="w-full px-6 md:px-12 py-6 border-t border-[#ffffff08] text-center">
        <p class="text-[11px] text-[#444] tracking-wider">FlexiWeek v1.8.1 — Developed by JaJo EkiZ</p>
    </footer>

</body>
</html>
