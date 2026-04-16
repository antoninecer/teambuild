<?php
/** @var array $game */
/** @var array $invites */

$pageTitle = 'Pozvánky';
$pageSubtitle = $game['name'];
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>">Zpět na detail hry</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/invites/create">Vytvořit pozvánku</a>
</div>

<?php if (empty($invites)): ?>
    <div class="card" style="margin-top: 20px;">
        <p>Pro tuto hru zatím nebyly vytvořeny žádné pozvánky.</p>
    </div>
<?php else: ?>

<div class="table-wrap" style="margin-top: 20px;">
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
                    <div style="background: rgba(255,255,255,0.4); padding: 8px; border-radius: 8px; font-family: monospace; font-size: 13px; margin-bottom: 6px; word-break: break-all;" id="url-<?= $invite['id'] ?>">
                        <?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick="copyUrl('url-<?= $invite['id'] ?>')">Kopírovat</button>
                </td>
                <td>
                    <?= (int) $invite['used_count'] ?> /
                    <?= $invite['max_uses'] === null ? '∞' : (int) $invite['max_uses'] ?>
                </td>
                <td>
                    <?php if ((int)$invite['is_active'] === 1): ?>
                        <span class="status-badge mode-self">Ano</span>
                    <?php else: ?>
                        <span class="status-badge mode-moderated">Ne</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display: flex; gap: 5px;">
                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick="showQR('<?= $url ?>')">QR</button>
                        <form action="/admin/invites/<?= (int)$invite['id'] ?>/delete" method="POST" onsubmit="return confirm('Opravdu smazat?')">
                            <button type="submit" class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px; color: #7a1b1b;">Smazat</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;" onclick="closeQR()">
    <div style="background: var(--paper); padding: 30px; border-radius: 20px; text-align: center; max-width: 400px; border: 1px solid var(--line); box-shadow: 0 20px 40px var(--shadow);" onclick="event.stopPropagation()">
        <div id="qrcode" style="margin-bottom: 20px; display: flex; justify-content: center;"></div>
        <button class="btn btn-primary" onclick="closeQR()">Zavřít</button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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

<?php require __DIR__ . '/../partials/footer.php'; ?>
