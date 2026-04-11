<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrace do hry - <?php echo htmlspecialchars($game['name']); ?></title>

    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            max-width: 420px;
            margin: 0 auto;
        }

        h1 {
            margin-bottom: 10px;
        }

        .info {
            background: #eef6ff;
            border: 1px solid #90c2ff;
            padding: 10px;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            background: #fee;
            padding: 10px;
            border: 1px solid red;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        input[readonly] {
            background: #eee;
        }

        button {
            padding: 12px;
            background: #000;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #333;
        }

        .hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<h1><?php echo htmlspecialchars($game['name']); ?></h1>
<p>Zadej svoji přezdívku pro vstup do hry.</p>

<?php if (!empty($inviteCode)): ?>
    <div class="info">
        Byl jsi pozván do hry pomocí kódu:
        <strong><?php echo htmlspecialchars($inviteCode); ?></strong>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form action="/game/<?php echo htmlspecialchars($game['slug']); ?>/register" method="POST">

    <div class="form-group">
        <label for="nickname">Přezdívka:</label>
        <input type="text"
               id="nickname"
               name="nickname"
               value="<?php echo htmlspecialchars($_POST['nickname'] ?? ''); ?>"
               required
               autofocus>
    </div>

    <?php if (!empty($inviteCode)): ?>
        <!-- readonly + hidden (jistota) -->
        <div class="form-group">
            <label>Invite kód:</label>
            <input type="text" value="<?php echo htmlspecialchars($inviteCode); ?>" readonly>
            <input type="hidden" name="invite_code" value="<?php echo htmlspecialchars($inviteCode); ?>">
            <div class="hint">Tento kód byl přidělen automaticky.</div>
        </div>
    <?php else: ?>
        <!-- fallback -->
        <div class="form-group">
            <label for="invite_code">Invite kód (volitelný):</label>
            <input type="text"
                   id="invite_code"
                   name="invite_code"
                   value="<?php echo htmlspecialchars($_POST['invite_code'] ?? ''); ?>">
        </div>
    <?php endif; ?>

    <button type="submit">Vstoupit do hry</button>
</form>

</body>
</html>