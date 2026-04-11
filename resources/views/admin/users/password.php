<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Změna hesla</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            max-width: 700px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .actions {
            display: flex;
            gap: 12px;
            align-items: center;
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

        .btn.primary {
            background: #222;
            color: #fff;
            border-color: #222;
        }

        .form-box {
            border: 1px solid #ccc;
            padding: 20px;
            background: #fafafa;
        }

        .form-row {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            padding: 10px;
            width: 100%;
            max-width: 360px;
            box-sizing: border-box;
        }

        .meta {
            color: #666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <h1>Změna hesla</h1>
        <div class="meta">
            Uživatel:
            <strong><?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>

    <div class="actions">
        <a class="btn" href="/admin/users">Zpět na uživatele</a>
    </div>
</div>

<div class="form-box">
    <form method="post" action="/admin/users/<?= (int) $user['id'] ?>/password">
        <div class="form-row">
            <label for="password">Nové heslo</label>
            <input id="password" name="password" type="password" required>
        </div>

        <button class="btn primary" type="submit">Uložit nové heslo</button>
    </form>
</div>

</body>
</html>