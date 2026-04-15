<?php
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Admin login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 420px; }
        label, input, button { display: block; width: 100%; margin-bottom: 12px; }
        input { padding: 10px; }
        button { padding: 12px; cursor: pointer; }
        .error { color: #b00020; margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>Admin login</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/login">
        <label for="username">Uživatel</label>
        <input id="username" name="username" type="text" required>

        <label for="password">Heslo</label>
        <input id="password" name="password" type="password" required>

        <button type="submit">Přihlásit</button>
    </form>
</body>
</html>
