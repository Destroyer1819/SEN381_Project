<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name', 'CivicConnect') }}</title>
    {{-- Self-contained on purpose: no Vite or build files, so the error page still works if the build is missing. --}}
    <style>
        :root { --bg: #ffffff; --fg: #171717; --muted: #6b7280; --accent: #171717; --accent-fg: #ffffff; --border: #e5e7eb; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #0a0a0a; --fg: #fafafa; --muted: #a3a3a3; --accent: #fafafa; --accent-fg: #171717; --border: #262626; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; background: var(--bg); color: var(--fg);
               font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        main { max-width: 28rem; text-align: center; }
        .code { margin: 0; font-size: 4rem; font-weight: 700; color: var(--muted); font-variant-numeric: tabular-nums; }
        h1 { margin: 0.5rem 0; font-size: 1.5rem; font-weight: 600; letter-spacing: -0.01em; }
        .message { margin: 0 0 1.5rem; font-size: 0.9rem; line-height: 1.5; color: var(--muted); }
        .actions { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center; }
        a.button { display: inline-block; padding: 0.55rem 1rem; border-radius: 0.375rem; font-size: 0.9rem; font-weight: 500; text-decoration: none;
                   background: var(--accent); color: var(--accent-fg); border: 1px solid var(--accent); }
        a.button.secondary { background: transparent; color: var(--fg); border-color: var(--border); }
        a.button:focus-visible { outline: 2px solid var(--fg); outline-offset: 2px; }
    </style>
</head>
<body>
    <main>
        <p class="code" aria-hidden="true">@yield('code')</p>
        <h1>@yield('heading')</h1>
        <p class="message">@yield('message')</p>
        <div class="actions">
            <a class="button" href="{{ url('/requests') }}">Back to requests</a>
            <a class="button secondary" href="javascript:history.back()">Go back</a>
        </div>
    </main>
</body>
</html>
