<?php
$pageTitle = 'Seznam her';
$pageSubtitle = 'Přehled všech her, jejich stavů a základních parametrů.';
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions">
    <a class="btn btn-primary" href="/admin/games/create">Vytvořit novou hru</a>
</div>

<?php if (empty($games)): ?>
    <div class="card">
        <p style="margin:0; color: var(--ink-soft);">Zatím nebyly vytvořeny žádné hry.</p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Název</th>
                    <th>Stav</th>
                    <th>Režim hry</th>
                    <th>Začátek</th>
                    <th>Konec</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                    <tr>
                        <td><?= (int) $game['id'] ?></td>
                        <td><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="status-badge">
                                <?= htmlspecialchars($game['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <?php if (($game['operation_mode'] ?? 'self_service') === 'moderated'): ?>
                                <span class="mode-badge mode-moderated">Hra s organizátorem</span>
                            <?php else: ?>
                                <span class="mode-badge mode-self">Samostatná hra</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($game['starts_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($game['ends_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>