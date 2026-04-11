<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Pozvánky - <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 1100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background: #f8f8f8; }
        .actions { margin-bottom: 20px; }
        .btn {
            padding: 8px 12px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #999;
            background: #fff;
            color: #000;
            display: inline-block;
            font-size: 0.9em;
        }
        .btn-primary { background: #000; color: #fff; border: 1px solid #000; }
        .btn-danger { color: #d00; border-color: #d00; background: #fff; }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            background: #eee;
        }
        .status-active { background: #e6ffed; color: #22863a; }
        .status-inactive { background: #ffeef0; color: #cb2431; }

        .url-box {
            background: #f0f0f0;
            padding: 8px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            margin-top: 6px;
            font-size: 0.85em;
        }

        .qr-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .qr-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            max-width: 400px;
        }
    </style>
</head>
<body>

<h1>Pozvánky: <?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></h1>

<div class="actions">
    <a class="btn" href="/admin/games/<?= (int) $game['id'] ?>">Zpět na detail hry</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/invites/create">Vytvořit pozvánku</a>
</div>

<?php if (empty($invites)): ?>
    <p>Pro tuto hru zatím nebyly vytvořeny žádné pozvánky.</p>
<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Kód</th>
            <th>Popisek</th>
            <th>URL</th>
            <th>Použití</th>
            <th>Aktivní</th>
            <th>Akce</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        foreach ($invites as $invite):
            $url = $baseUrl . "/game/" . $game['slug'] . "?invite=" . $invite['code'];
    ?>
        <tr>
            <td><code><?= htmlspecialchars($invite['code'], ENT_QUOTES, 'UTF-8') ?></code></td>

            <td><?= htmlspecialchars($invite['label'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>

            <td>
                <div class="url-box" id="url-<?= $invite['id'] ?>">
                    <?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <button class="btn" onclick="copyUrl('url-<?= $invite['id'] ?>')">Kopírovat</button>
            </td>

            <td>
                <?= (int) $invite['used_count'] ?> /
                <?= $invite['max_uses'] === null ? '∞' : (int) $invite['max_uses'] ?>
            </td>

            <td>
                <?php if ((int)$invite['is_active'] === 1): ?>
                    <span class="status-badge status-active">Ano</span>
                <?php else: ?>
                    <span class="status-badge status-inactive">Ne</span>
                <?php endif; ?>
            </td>

            <td>
                <button class="btn" onclick="showQR('<?= $url ?>')">QR</button>

                <form action="/admin/invites/<?= (int)$invite['id'] ?>/delete" method="POST" style="display:inline;">
                    <button type="submit" class="btn btn-danger">Smazat</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<div id="qrModal" class="qr-modal" onclick="closeQR()">
    <div class="qr-content" onclick="event.stopPropagation()">
        <div id="qrcode"></div>
        <button class="btn" onclick="closeQR()">Zavřít</button>
    </div>
</div>

<script>
    function copyUrl(id) {
        const text = document.getElementById(id).innerText;
        navigator.clipboard.writeText(text);
        alert("Zkopírováno");
    }

    let qrcode = new QRCode(document.getElementById("qrcode"), {
        width: 256,
        height: 256
    });

    function showQR(url) {
        qrcode.clear();
        qrcode.makeCode(url);
        document.getElementById("qrModal").style.display = "flex";
    }

    function closeQR() {
        document.getElementById("qrModal").style.display = "none";
    }
</script>

</body>
</html>