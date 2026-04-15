<?php
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VentureOut Admin</title>
    <style>
        :root {
            --paper: rgba(246, 236, 214, 0.90);
            --ink: #3b2818;
            --ink-soft: #6b5240;
            --accent: #6c4322;
            --accent-dark: #4f2f17;
            --shadow: rgba(22, 12, 6, 0.34);
            --danger: #a51f1f;
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
                url('/assets/images/bg/ventureout-map.jpg') center center / cover no-repeat;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 18px;
        }

        .card {
            width: 100%;
            max-width: 470px;
            background: var(--paper);
            border: 1px solid rgba(92, 61, 35, 0.24);
            border-radius: 18px;
            box-shadow: 0 20px 40px var(--shadow);
            padding: 30px 28px;
        }

        .brand {
            text-align: center;
            margin-bottom: 20px;
        }

        .brand-title {
            margin: 0;
            font-size: 42px;
            line-height: 1;
            letter-spacing: 0.01em;
        }

        .brand-subtitle {
            margin: 10px 0 0;
            color: var(--ink-soft);
            font-size: 17px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 30px;
            line-height: 1.15;
        }

        .lead {
            margin: 0 0 22px;
            color: var(--ink-soft);
            font-size: 16px;
            line-height: 1.5;
        }

        .error {
            color: #fff;
            background: rgba(165, 31, 31, 0.92);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 15px;
        }

        form {
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
            background: rgba(255,255,255,0.56);
            font-size: 15px;
            color: var(--ink);
        }

        input:focus {
            outline: none;
            border-color: rgba(108, 67, 34, 0.60);
            box-shadow: 0 0 0 3px rgba(108, 67, 34, 0.12);
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 12px;
            padding: 13px 16px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(180deg, #7a4d27, #5e381b);
            color: #fff4e6;
            box-shadow: 0 10px 18px rgba(85, 49, 22, 0.28);
            transition: transform 0.12s ease, opacity 0.12s ease;
            margin-top: 4px;
        }

        .btn:hover {
            opacity: 0.96;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .links {
            margin-top: 18px;
            text-align: center;
        }

        .links a {
            color: var(--accent-dark);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="brand">
            <h2 class="brand-title">VentureOut</h2>
            <div class="brand-subtitle">Správa her, bodů, pokladů a hráčů</div>
        </div>

        <h1>Admin rozhraní</h1>
        <p class="lead">
            Přihlaš se do administrace a vstup do organizační části hry.
        </p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/login">
            <div>
                <label for="username">Uživatelské jméno</label>
                <input id="username" name="username" type="text" required>
            </div>

            <div>
                <label for="password">Heslo</label>
                <input id="password" name="password" type="password" required>
            </div>

            <button class="btn" type="submit">Přihlásit se</button>
        </form>

        <div class="links">
            <a href="/">← Zpět na rozcestník</a>
        </div>
    </div>
</div>
</body>
</html>