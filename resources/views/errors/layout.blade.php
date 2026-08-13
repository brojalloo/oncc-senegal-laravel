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
            --ground: #F3F4F8;
            --surface: #FFFFFF;
            --ink: #131A2E;
            --ink-soft: #6B7594;
            --rule: #D6DAE6;
            --accent: #3B2FA8;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --ground: #0D1222;
                --surface: #151C31;
                --ink: #E7EAF4;
                --ink-soft: #8089A6;
                --rule: #2A3352;
                --accent: #9A90F5;
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
            border-radius: 4px;
            padding: 2.5rem;
            position: relative;
            max-width: 32rem;
            width: 100%;
            text-align: center;
        }

        /* La bande de latitude du système, écrite en dur : ce gabarit ne
           charge délibérément aucune feuille externe. */
        .card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg,
                #E8DCC0 0 20%, #D4A855 20% 40%, #A8A03C 40% 60%,
                #5E8C3A 60% 80%, #2C6B4F 80% 100%);
        }

        .code {
            font-size: 3.5rem;
            font-weight: 500;
            font-family: "IBM Plex Mono", ui-monospace, Consolas, monospace;
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
            border-radius: 4px;
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
