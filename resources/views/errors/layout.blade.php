{{--
    Gabarit commun aux pages d'erreur.

    Volontairement autonome : il ne dépend ni de layouts.app, ni de la session,
    ni d'une requête en base. Une page d'erreur doit pouvoir s'afficher quand
    justement quelque chose est cassé — y compris la base de données.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — ONCC Sénégal</title>
    <style>
        :root {
            --ground: #f5f8f9;
            --surface: #ffffff;
            --ink: #101920;
            --ink-soft: #61757f;
            --rule: #d7e1e5;
            --accent: #0b6f79;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --ground: #0d1317;
                --surface: #151f25;
                --ink: #e5edf0;
                --ink-soft: #8397a1;
                --rule: #26343b;
                --accent: #3fbeca;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: var(--ground);
            color: var(--ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.6;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--rule);
            border-radius: 8px;
            padding: 2.5rem;
            max-width: 32rem;
            width: 100%;
            text-align: center;
        }

        .code {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1;
            color: var(--accent);
            margin: 0 0 0.75rem;
            font-variant-numeric: tabular-nums;
        }

        h1 {
            font-size: 1.35rem;
            margin: 0 0 0.75rem;
            text-wrap: balance;
        }

        p {
            margin: 0 0 1.75rem;
            color: var(--ink-soft);
            text-wrap: pretty;
        }

        a.action {
            display: inline-block;
            background: var(--accent);
            color: var(--surface);
            text-decoration: none;
            padding: 0.65rem 1.4rem;
            border-radius: 5px;
            font-weight: 600;
        }

        a.action:hover,
        a.action:focus-visible { opacity: 0.9; }

        a.action:focus-visible { outline: 3px solid var(--accent); outline-offset: 3px; }
    </style>
</head>
<body>
    <main class="card">
        <p class="code">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a class="action" href="{{ url('/') }}">Retour à l'accueil</a>
    </main>
</body>
</html>
