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
            max-width: 460px;
            margin: 0 auto;
            line-height: 1.45;
        }

        h1 {
            margin-bottom: 10px;
        }

        .info {
            background: #eef6ff;
            border: 1px solid #90c2ff;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .warning {
            background: #fff8e8;
            border: 1px solid #e3c675;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 8px;
        }

        .error {
            color: #8b0000;
            background: #fee;
            padding: 12px;
            border: 1px solid #d66;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="tel"] {
            width: 100%;
            padding: 11px;
            box-sizing: border-box;
            border: 1px solid #bbb;
            border-radius: 8px;
            font-size: 16px;
        }

        input[readonly] {
            background: #eee;
        }

        button {
            padding: 13px;
            background: #000;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        button:hover {
            background: #333;
        }

        .hint {
            font-size: 12px;
            color: #666;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<h1><?php echo htmlspecialchars($game['name']); ?></h1>
<p>Zadej svoje údaje pro vstup do hry.</p>

<div class="warning">
    <strong>Doporučení před startem:</strong><br>
    Měj nabitý telefon, zapnutou polohu a ideálně i připravenou powerbanku.
</div>

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

    <div class="form-group">
        <label for="phone">Telefonní kontakt pro bezpečnost:</label>
        <input type="tel"
               id="phone"
               name="phone"
               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
               required
               inputmode="tel"
               autocomplete="tel">
        <div class="hint">Použije se jen v případě, že bude potřeba rychle řešit pomoc nebo organizační problém.</div>
    </div>

    <?php if (!empty($inviteCode)): ?>
        <div class="form-group">
            <label>Invite kód:</label>
            <input type="text" value="<?php echo htmlspecialchars($inviteCode); ?>" readonly>
            <input type="hidden" name="invite_code" value="<?php echo htmlspecialchars($inviteCode); ?>">
            <div class="hint">Tento kód byl přidělen automaticky.</div>
        </div>
    <?php else: ?>
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