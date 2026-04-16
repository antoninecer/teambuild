<?php
/** @var array $game */
/** @var array $teams */
/** @var array|null $errors */
/** @var array|null $old */

$pageTitle = 'Nová pozvánka';
$pageSubtitle = 'Hra: ' . $game['name'];
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>/invites">← Zpět na seznam pozvánek</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="errors">
        <strong>Při ukládání došlo k chybám:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <form action="/admin/games/<?= (int) $game['id'] ?>/invites" method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label for="code">Kód pozvánky</label>
                <input type="text" id="code" name="code" value="<?= htmlspecialchars($old['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Např. TEAM2024">
                <p class="help">Pokud necháte prázdné, bude vygenerován náhodný 8místný kód.</p>
            </div>

            <div class="form-group">
                <label for="label">Popisek (interní)</label>
                <input type="text" id="label" name="label" value="<?= htmlspecialchars($old['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Např. Pozvánka pro sponzory">
            </div>

            <div class="form-group">
                <label for="team_id">Přiřadit k týmu</label>
                <select id="team_id" name="team_id">
                    <option value="">-- Libovolný tým / Vytvořit nový --</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= (int) $team['id'] ?>" <?= ($old['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="help">Pokud je vybrán tým, hráč bude po zadání kódu automaticky přiřazen k tomuto týmu.</p>
            </div>

            <div class="form-group">
                <label for="max_uses">Maximální počet použití</label>
                <input type="number" id="max_uses" name="max_uses" value="<?= htmlspecialchars($old['max_uses'] ?? '', ENT_QUOTES, 'UTF-8') ?>" min="1">
                <p class="help">Nechte prázdné pro neomezený počet použití.</p>
            </div>
        </div>

        <div style="margin-top: 24px; border-top: 1px solid var(--line); padding-top: 20px;">
            <button type="submit" class="btn btn-primary">Vytvořit pozvánku</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
