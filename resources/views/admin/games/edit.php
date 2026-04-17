<?php
/** @var array $game */

$pageTitle = 'Upravit hru';
$pageSubtitle = 'Úprava základních parametrů a textů hry';
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/games/<?= (int) $game['id'] ?>">← Zpět na detail hry</a>
</div>

<div class="card">
    <h2>Upravit hru</h2>

    <form method="post" action="/admin/games/<?= (int) $game['id'] ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Název hry</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars((string)$game['name'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" required
                       value="<?= htmlspecialchars((string)$game['slug'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="status">Stav</label>
                <select id="status" name="status" required>
                    <?php
                    $statuses = ['draft', 'registration_open', 'active', 'finished', 'archived'];
                    foreach ($statuses as $status):
                    ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($game['status'] ?? '') === $status ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="operation_mode">Režim hry</label>
                <select id="operation_mode" name="operation_mode" required>
                    <?php
                    $modes = ['self_service' => 'Samostatná hra', 'moderated' => 'Moderovaná hra'];
                    foreach ($modes as $value => $label):
                    ?>
                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($game['operation_mode'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="starts_at">Začátek</label>
                <input type="datetime-local" id="starts_at" name="starts_at"
                       value="<?= !empty($game['starts_at']) ? date('Y-m-d\\TH:i', strtotime((string)$game['starts_at'])) : '' ?>">
            </div>

            <div class="form-group">
                <label for="ends_at">Konec</label>
                <input type="datetime-local" id="ends_at" name="ends_at"
                       value="<?= !empty($game['ends_at']) ? date('Y-m-d\\TH:i', strtotime((string)$game['ends_at'])) : '' ?>">
            </div>

            <div class="form-group">
                <label for="registration_enabled">Registrace</label>
                <select id="registration_enabled" name="registration_enabled" required>
                    <option value="1" <?= ((int)($game['registration_enabled'] ?? 0) === 1) ? 'selected' : '' ?>>ano</option>
                    <option value="0" <?= ((int)($game['registration_enabled'] ?? 0) === 0) ? 'selected' : '' ?>>ne</option>
                </select>
            </div>

            <div class="form-group">
                <label for="session_cookie_days">Cookie (dny)</label>
                <input type="number" id="session_cookie_days" name="session_cookie_days" min="1" max="3650"
                       value="<?= (int)($game['session_cookie_days'] ?? 365) ?>">
            </div>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label for="description">Popis</label>
            <textarea id="description" name="description" rows="4"><?= htmlspecialchars((string)($game['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group">
            <label for="intro_text">Intro</label>
            <textarea id="intro_text" name="intro_text" rows="5"><?= htmlspecialchars((string)($game['intro_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group">
            <label for="objective_text">Cíl hry</label>
            <textarea id="objective_text" name="objective_text" rows="4"><?= htmlspecialchars((string)($game['objective_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group">
            <label for="player_guide_text">Návod pro hráče</label>
            <textarea id="player_guide_text" name="player_guide_text" rows="8"><?= htmlspecialchars((string)($game['player_guide_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-actions" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Uložit změny</button>
            <a href="/admin/games/<?= (int) $game['id'] ?>" class="btn btn-secondary">Zrušit</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>