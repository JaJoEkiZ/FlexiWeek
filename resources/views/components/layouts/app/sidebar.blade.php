<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        /* ===== FlexiWeek Settings — Dark VS Code Theme ===== */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            background-color: #1e1e1e !important;
            color: #d4d4d4 !important;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh;
            margin: 0;
        }

        /* CSS Variables */
        :root {
            --settings-bg:               #1e1e1e;
            --settings-surface:          #252526;
            --settings-surface-2:        #2a2d2e;
            --settings-card-border:      #3a3a3a;
            --settings-input-bg:         #3c3c3c;
            --settings-input-border:     #4a4a4a;
            --settings-input-focus:      #007fd4;
            --settings-heading-text:     #d4d4d4;
            --settings-subheading-text:  #8b949e;
            --settings-sidebar-item-hover: rgba(255,255,255,0.05);
            --settings-sidebar-item-text: #8b949e;
        }

        /* Card container */
        .settings-card {
            background-color: #252526 !important;
            border: 1px solid #333 !important;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Labels */
        .settings-label {
            display: block !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: #8b949e !important;
            margin-bottom: 0.375rem !important;
        }

        /* ── Inputs & Selects ── */
        .settings-input,
        input.settings-input,
        select.settings-input,
        textarea.settings-input {
            display: block !important;
            width: 100% !important;
            padding: 0.5rem 0.875rem !important;
            font-size: 0.875rem !important;
            background-color: #3c3c3c !important;
            color: #d4d4d4 !important;
            border: 1px solid #4a4a4a !important;
            border-radius: 0.375rem !important;
            outline: none !important;
            box-shadow: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .settings-input:focus,
        input.settings-input:focus,
        select.settings-input:focus {
            border-color: #007fd4 !important;
            box-shadow: 0 0 0 3px rgba(0,127,212,0.18) !important;
        }
        .settings-input::placeholder { color: #666 !important; }
        .settings-input option,
        .settings-input optgroup {
            background-color: #3c3c3c !important;
            color: #d4d4d4 !important;
        }

        /* ── Primary Button ── */
        .settings-btn-primary,
        button.settings-btn-primary {
            display: inline-flex !important;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1.25rem !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            color: #ffffff !important;
            background-color: #007fd4 !important;
            border: 1px solid #1a93e3 !important;
            border-radius: 0.375rem !important;
            cursor: pointer;
            transition: background-color 0.15s, box-shadow 0.15s, transform 0.1s;
            box-shadow: 0 2px 8px rgba(0,127,212,0.3) !important;
            text-decoration: none !important;
        }
        .settings-btn-primary:hover,
        button.settings-btn-primary:hover {
            background-color: #006bb3 !important;
            border-color: #007fd4 !important;
            box-shadow: 0 4px 14px rgba(0,127,212,0.4) !important;
        }
        .settings-btn-primary:active { transform: scale(0.97); }

        /* ── Danger Button ── */
        .settings-btn-danger,
        button.settings-btn-danger {
            display: inline-flex !important;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1.25rem !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            color: #ffffff !important;
            background-color: #3b1219 !important;
            border: 1px solid #da3633 !important;
            border-radius: 0.375rem !important;
            cursor: pointer;
            transition: background-color 0.15s, box-shadow 0.15s, transform 0.1s;
            text-decoration: none !important;
        }
        .settings-btn-danger:hover,
        button.settings-btn-danger:hover {
            background-color: #da3633 !important;
            box-shadow: 0 4px 12px rgba(218,54,51,0.35) !important;
        }
        .settings-btn-danger:active { transform: scale(0.97); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; background: #1e1e1e; }
        ::-webkit-scrollbar-thumb { background: #424242; border-radius: 4px; border: 2px solid #1e1e1e; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
