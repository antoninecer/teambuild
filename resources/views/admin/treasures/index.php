<?php
/** @var array $game */
/** @var array $treasures */

$pageTitle = 'Poklady';
$pageSubtitle = 'Hra: ' . $game['name'];
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>">Zpět na detail hry</a>
    <a class="btn btn-primary" href="/admin/games/<?= (int) $game['id'] ?>/treasures/create">Vytvořit poklad</a>
</div>

<?php if (empty($treasures)): ?>
    <div class="card" style="margin-top: 20px;">
        <p style="margin:0; color: var(--ink-soft);">Pro tuto hru zatím nejsou definované žádné poklady.</p>
    </div>
<?php else: ?>
    <div class="table-wrap" style="margin-top: 20px;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Název</th>
                    <th>Typ</th>
                    <th>POI</th>
                    <th>Souřadnice</th>
                    <th>Radius</th>
                    <th>Body</th>
                    <th>Mapa</th>
                    <th>Aktivní</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($treasures as $treasure): ?>
                    <tr>
                        <td><?= (int) $treasure['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($treasure['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($treasure['description'])): ?>
                                <div style="margin-top:4px; font-size: 13px; color: var(--ink-soft);">
                                    <?= nl2br(htmlspecialchars((string) $treasure['description'], ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge"><?= htmlspecialchars($treasure['treasure_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars((string) ($treasure['poi_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span style="font-size: 12px; font-family: monospace;">
                                <?= htmlspecialchars((string) $treasure['lat'], ENT_QUOTES, 'UTF-8') ?>,<br>
                                <?= htmlspecialchars((string) $treasure['lon'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td><?= (int) $treasure['radius_m'] ?> m</td>
                        <td><?= (int) $treasure['points'] ?></td>
                        <td>
                            <span class="badge <?= (int) $treasure['is_visible_on_map'] === 1 ? 'badge-self' : '' ?>">
                                <?= (int) $treasure['is_visible_on_map'] === 1 ? 'ano' : 'ne' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= (int) $treasure['is_enabled'] === 1 ? 'badge-self' : '' ?>">
                                <?= (int) $treasure['is_enabled'] === 1 ? 'ano' : 'ne' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a class="btn btn-secondary" style="padding: 5px 10px; font-size: 13px;" href="/admin/treasures/<?= (int) $treasure['id'] ?>/edit">Upravit</a>
                                <form action="/admin/treasures/<?= (int) $treasure['id'] ?>/delete" method="POST" onsubmit="return confirm('Opravdu smazat tento poklad?')">
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
