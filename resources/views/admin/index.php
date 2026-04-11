<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Administrace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            max-width: 1100px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .card {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            background: #fafafa;
        }

        .card h2 {
            margin-top: 0;
        }

        .card p {
            color: #444;
            line-height: 1.5;
        }

        .btn, button {
            padding: 10px 14px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #999;
            background: #fff;
            color: #000;
            display: inline-block;
        }

        .meta {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div>
            <h1>Administrace</h1>
            <div class="meta">
                Přihlášen: <strong><?= htmlspecialchars($adminUser['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($adminUser['global_role'])): ?>
                    | globální role: <strong><?= htmlspecialchars($adminUser['global_role'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php endif; ?>
            </div>
        </div>

        <form method="post" action="/admin/logout">
            <button type="submit">Odhlásit</button>
        </form>
    </div>

    <div class="cards">
        <div class="card">
            <h2>Správa her</h2>
            <p>
                Přehled her, zakládání nových her, detail hry, body zájmu, pozvánky, týmy a další herní nastavení.
            </p>
            <a class="btn" href="/admin/games">Otevřít správu her</a>
        </div>

        <div class="card">
            <h2>Správa uživatelů</h2>
            <p>
                Uživatelé administrace, globální správci, herní správci, změna hesla a vytváření nových účtů.
            </p>
            <a class="btn" href="/admin/users">Otevřít správu uživatelů</a>
        </div>
    </div>
</body>
</html>
