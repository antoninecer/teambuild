<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa uživatelů</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            max-width: 1200px;
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

        .meta {
            color: #666;
        }

        .form-box {
            border: 1px solid #ccc;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 30px;
            background: #fafafa;
        }

        .form-box h2 {
            margin-top: 0;
        }

        .form-row {
            margin-bottom: 12px;
        }

        input, select {
            padding: 8px;
            width: 100%;
            max-width: 320px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .small {
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <h1>Správa uživatelů</h1>
        <div class="meta">
            Administrátoři a správci portálu a her.
        </div>
    </div>

    <div class="actions">
        <a class="btn" href="/admin">Zpět na admin</a>
    </div>
</div>

<!-- ========================= -->
<!-- CREATE USER FORM -->
<!-- ========================= -->
<div class="form-box">
    <h2>Vytvořit nového uživatele</h2>

    <form method="post" action="/admin/users/create">
        <div class="form-row">
            <label>Uživatelské jméno</label><br>
            <input type="text" name="username" required>
        </div>

        <div class="form-row">
            <label>Email</label><br>
            <input type="email" name="email">
        </div>

        <div class="form-row">
            <label>Heslo</label><br>
            <input type="password" name="password" required>
        </div>

        <div class="form-row">
            <label>Globální role</label><br>
            <select name="global_role">
                <option value="none">žádná</option>
                <option value="superadmin">superadmin</option>
            </select>
        </div>

        <div class="form-row">
            <label>Role (lokální)</label><br>
            <select name="role">
                <option value="admin">admin</option>
                <option value="editor">editor</option>
                <option value="viewer">viewer</option>
            </select>
        </div>

        <button class="btn primary" type="submit">Vytvořit uživatele</button>
    </form>
</div>

<!-- ========================= -->
<!-- USERS TABLE -->
<!-- ========================= -->
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Uživatel</th>
        <th>Email</th>
        <th>Globální role</th>
        <th>Role</th>
        <th>Aktivní</th>
        <th>Poslední login</th>
        <th>Vytvořen</th>
        <th>Akce</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= (int)$user['id'] ?></td>

            <td>
                <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                <div class="small">ID: <?= (int)$user['id'] ?></div>
            </td>

            <td><?= htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8') ?></td>

            <td><?= htmlspecialchars((string)$user['global_role'], ENT_QUOTES, 'UTF-8') ?></td>

            <td><?= htmlspecialchars((string)$user['role'], ENT_QUOTES, 'UTF-8') ?></td>

            <td><?= (int)$user['is_active'] === 1 ? 'ano' : 'ne' ?></td>

            <td><?= htmlspecialchars((string)$user['last_login_at'], ENT_QUOTES, 'UTF-8') ?></td>

            <td><?= htmlspecialchars((string)$user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>

            <td>
                <div class="actions">
                    <a class="btn" href="/admin/users/<?= (int)$user['id'] ?>/password">
                        Heslo
                    </a>

                    <form method="post" action="/admin/users/<?= (int)$user['id'] ?>/toggle" style="display:inline;">
                        <button type="submit">
                            <?= (int)$user['is_active'] === 1 ? 'Deaktivovat' : 'Aktivovat' ?>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top: 24px;" class="meta">
    Další krok: přiřazení uživatelů ke konkrétní hře (game_admin / editor).
</div>

</body>
</html>