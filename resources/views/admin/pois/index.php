<?php
/** @var array $game */
/** @var array $pois */

$pageTitle = 'Body zájmu (POI)';
$pageSubtitle = 'Hra: ' . $game['name'];
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>">Zpět na detail hry</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/pois/create">Přidat nový bod</a>
</div>

<?php if (empty($pois)): ?>
    <div class="card" style="margin-top: 20px;">
        <p style="margin:0; color: var(--ink-soft);">Pro tuto hru zatím nebyly vytvořeny žádné body zájmu.</p>
    </div>
<?php else: ?>
    <div class="table-wrap" style="margin-top: 20px;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Název</th>
                    <th>Typ</th>
                    <th>Pořadí</th>
                    <th>Aktivní</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pois as $poi): ?>
                    <tr>
                        <td><?= (int) $poi['id'] ?></td>
                        <td><?= htmlspecialchars($poi['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><code><?= htmlspecialchars($poi['type'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= (int) $poi['sort_order'] ?></td>
                        <td>
                            <?php if ((int) $poi['is_enabled'] === 1): ?>
                                <span class="badge badge-self">Ano</span>
                            <?php else: ?>
                                <span class="badge badge-moderated">Ne</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a class="btn btn-secondary" style="padding: 5px 10px; font-size: 13px;" href="/admin/pois/<?= (int) $poi['id'] ?>/edit">Upravit</a>
                                <form action="/admin/pois/<?= (int) $poi['id'] ?>/delete" method="POST" onsubmit="return confirm('Opravdu smazat tento bod?')">
                                    <button type="submit" class="btn btn-secondary" style="padding: 5px 10px; font-size: 13px; color: #7a1b1b;">Smazat</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
