<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VentureOut</title>
    <style>
        :root {
            --paper: rgba(246, 236, 214, 0.88);
            --paper-strong: rgba(241, 229, 205, 0.94);
            --ink: #3b2818;
            --ink-soft: #6b5240;
            --accent: #6c4322;
            --accent-dark: #4f2f17;
            --shadow: rgba(22, 12, 6, 0.34);
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
                linear-gradient(rgba(24, 14, 8, 0.28), rgba(24, 14, 8, 0.46)),
                url('/assets/bg/ventureout-map.jpg') center center / cover no-repeat;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px 20px;
        }

        .shell {
            width: 100%;
            max-width: 1180px;
        }

        .brand {
            text-align: center;
            color: #f7ecda;
            text-shadow: 0 2px 18px rgba(0,0,0,0.45);
            margin-bottom: 28px;
        }

        .brand h1 {
            margin: 0;
            font-size: clamp(42px, 6vw, 78px);
            letter-spacing: 0.02em;
            font-weight: 700;
        }

        .brand p {
            margin: 8px 0 0;
            font-size: clamp(16px, 2vw, 24px);
            opacity: 0.94;
        }

        .cards {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 28px;
            align-items: start;
        }

        .card {
            background: var(--paper);
            border: 1px solid rgba(92, 61, 35, 0.24);
            border-radius: 18px;
            box-shadow: 0 20px 40px var(--shadow);
            padding: 28px;
            backdrop-filter: blur(2px);
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 30px;
            line-height: 1.15;
        }

        .card p.lead {
            margin: 0 0 22px;
            color: var(--ink-soft);
            font-size: 17px;
            line-height: 1.5;
        }

        .game-list {
            display: grid;
            gap: 14px;
        }

        .game-item {
            background: rgba(255,255,255,0.30);
            border: 1px solid rgba(92, 61, 35, 0.18);
            border-radius: 14px;
            padding: 16px 16px 14px;
        }

        .game-title {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 700;
        }

        .game-meta {
            margin: 0 0 12px;
            font-size: 14px;
            color: var(--ink-soft);
            line-height: 1.4;
        }

        .game-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
            opacity: 0.95;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-primary {
            background: linear-gradient(180deg, #7a4d27, #5e381b);
            color: #fff4e6;
            box-shadow: 0 10px 18px rgba(85, 49, 22, 0.28);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.46);
            color: var(--ink);
            border: 1px solid rgba(92, 61, 35, 0.18);
        }

        .empty-state {
            padding: 18px;
            border-radius: 14px;
            background: rgba(255,255,255,0.30);
            border: 1px dashed rgba(92, 61, 35, 0.24);
            color: var(--ink-soft);
            line-height: 1.5;
        }

        .admin-card form {
            display: grid;
            gap: 12px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 12px 13px;
            border-radius: 12px;
            border: 1px solid rgba(92, 61, 35, 0.24);
            background: rgba(255,255,255,0.55);
            font-size: 15px;
            color: var(--ink);
        }

        input:focus {
            outline: none;
            border-color: rgba(108, 67, 34, 0.60);
            box-shadow: 0 0 0 3px rgba(108, 67, 34, 0.12);
        }

        .helper {
            margin-top: 14px;
            font-size: 14px;
            color: var(--ink-soft);
            line-height: 1.5;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            color: rgba(250, 238, 217, 0.94);
            font-size: 15px;
            text-shadow: 0 1px 8px rgba(0,0,0,0.3);
        }

        @media (max-width: 900px) {
            .cards {
                grid-template-columns: 1fr;
            }

            .brand {
                margin-bottom: 22px;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="shell">
        <div class="brand">
            <h1>VentureOut</h1>
            <p>Mapa. Dobrodružství. Tým. Reálný svět.</p>
        </div>

        <div class="cards">
            <section class="card">
                <h2>Připojit se ke hře</h2>
                <p class="lead">
                    Vyber si běžící hru nebo hru s otevřenou registrací a vstup do jejího světa.
                </p>

                <?php if ($publicGames === []): ?>
                    <div class="empty-state">
                        Právě teď tu není žádná hra s otevřenou registrací.
                        Jakmile organizátor spustí novou výpravu, objeví se tady.
                    </div>
                <?php else: ?>
                    <div class="game-list">
                        <?php foreach ($publicGames as $game): ?>
                            <article class="game-item">
                                <h3 class="game-title"><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="game-meta">
                                    Stav: <?= htmlspecialchars($game['status'], ENT_QUOTES, 'UTF-8') ?><br>
                                    Začátek: <?= htmlspecialchars((string) $game['starts_at'], ENT_QUOTES, 'UTF-8') ?><br>
                                    Konec: <?= htmlspecialchars((string) $game['ends_at'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <div class="game-actions">
                                    <a class="btn btn-primary" href="/game/<?= htmlspecialchars($game['slug'], ENT_QUOTES, 'UTF-8') ?>">
                                        Vstoupit do hry
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="card admin-card">
                <h2>Admin rozhraní</h2>
                <p class="lead">
                    Přihlaš se do administrace a spravuj hry, body, poklady, pozvánky i hráče.
                </p>

                <form method="post" action="/admin/login">
                    <div>
                        <label for="username">Uživatelské jméno</label>
                        <input id="username" name="username" type="text" required>
                    </div>

                    <div>
                        <label for="password">Heslo</label>
                        <input id="password" name="password" type="password" required>
                    </div>

                    <button class="btn btn-primary" type="submit">Přihlásit se</button>
                </form>

                <div class="helper">
                    Vstup do administrace je oddělený od hráčské části.
                    Hráč se připojuje do konkrétní hry, správce vstupuje sem.
                </div>
            </section>
        </div>

        <div class="footer-note">
            Prozkoumejte. Objevujte. Dobývejte.
        </div>
    </div>
</div>
</body>
</html>