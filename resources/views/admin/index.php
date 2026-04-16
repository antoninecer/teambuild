<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VentureOut Admin</title>
    <style>
        :root {
            --bg: #f3eee5;
            --card: #fffaf2;
            --ink: #3b2818;
            --ink-soft: #6b5240;
            --border: rgba(92, 61, 35, 0.18);
            --accent: #6c4322;
            --accent-dark: #4f2f17;
            --shadow: rgba(22, 12, 6, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                linear-gradient(rgba(24, 14, 8, 0.20), rgba(24, 14, 8, 0.34)),
                url('/assets/bg/ventureout-map.jpg') center center / cover no-repeat fixed;
        }

        .page {
            min-height: 100vh;
            padding: 36px 20px;
        }

        .shell {
            max-width: 1180px;
            margin: 0 auto;
        }

        .hero {
            background: rgba(248, 239, 221, 0.92);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 20px 40px var(--shadow);
            padding: 28px 28px 24px;
            margin-bottom: 22px;
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand-title {
            margin: 0;
            font-size: clamp(34px, 5vw, 54px);
            line-height: 1;
        }

        .brand-subtitle {
            margin: 8px 0 0;
            color: var(--ink-soft);
            font-size: 18px;
        }

        .user-box {
            text-align: right;
            font-size: 15px;
            color: var(--ink-soft);
        }

        .user-box strong {
            color: var(--ink);
        }

        .logout-form {
            margin-top: 10px;
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.12s ease, opacity 0.12s ease;
        }

        .btn:hover {
            opacity: 0.96;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-primary {
            background: linear-gradient(180deg, #7a4d27, #5e381b);
            color: #fff4e6;
            box-shadow: 0 10px 18px rgba(85, 49, 22, 0.22);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.56);
            color: var(--ink);
            border: 1px solid var(--border);
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .card {
            background: rgba(248, 239, 221, 0.94);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 16px 30px var(--shadow);
            padding: 22px;
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 26px;
            line-height: 1.15;
        }

        .card p {
            margin: 0 0 18px;
            color: var(--ink-soft);
            line-height: 1.55;
            font-size: 16px;
        }

        .card .btn {
            width: 100%;
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .user-box {
                text-align: left;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="shell">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <h1 class="brand-title">VentureOut</h1>
                    <div class="brand-subtitle">Administrace her, bodů, pokladů a hráčů</div>
                </div>

                <div class="user-box">
                    Přihlášený uživatel:<br>
                    <strong><?= htmlspecialchars($adminUser['username'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?></strong><br>
                    Role: <?= htmlspecialchars($adminUser['role'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?>

                    <form class="logout-form" method="post" action="/admin/logout">
                        <button class="btn btn-secondary" type="submit">Odhlásit se</button>
                    </form>
                </div>
            </div>

            <div class="actions">
                <a class="btn btn-primary" href="/admin/games">Správa her</a>
                <a class="btn btn-secondary" href="/admin/users">Uživatelé</a>
            </div>
        </section>

        <section class="grid">
            <article class="card">
                <h2>Hry</h2>
                <p>
                    Zakládej nové hry, upravuj jejich parametry, nastavuj čas, popis, režim a sdílení.
                </p>
                <a class="btn btn-primary" href="/admin/games">Otevřít hry</a>
            </article>

            <article class="card">
                <h2>Body a poklady</h2>
                <p>
                    Přes detail konkrétní hry spravuj POI, příběhy, média, poklady, invite kódy a výsledky.
                </p>
                <a class="btn btn-primary" href="/admin/games">Přejít na detail hry</a>
            </article>

            <article class="card">
                <h2>Uživatelé</h2>
                <p>
                    Přehled administrátorů a správců systému, jejich role, přístupy a změny hesel.
                </p>
                <a class="btn btn-primary" href="/admin/users">Otevřít uživatele</a>
            </article>
        </section>
    </div>
</div>
</body>
</html>