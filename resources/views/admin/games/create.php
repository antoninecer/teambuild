<?php
$pageTitle = 'Vytvořit novou hru';
$pageSubtitle = 'Založení nové herní kampaně v systému';
$activeNav = 'games';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions" style="margin-bottom: 24px;">
    <a href="/admin/games" class="btn btn-secondary">← Zpět na seznam</a>
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

<form action="/admin/games" method="POST" class="card">
    <div class="form-grid">
        <div class="form-group">
            <label for="name">Název hry*</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug (URL identifikátor)*</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($old['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="napr. letni-hra-2024" required>
        </div>

        <div class="form-group full-width">
            <label for="description">Popis hry (pro adminy)</label>
            <textarea id="description" name="description"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group full-width">
            <label for="intro_text">Úvodní text (pro hráče)</label>
            <textarea id="intro_text" name="intro_text"><?= htmlspecialchars($old['intro_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group">
            <label for="starts_at">Začátek hry*</label>
            <input type="datetime-local" id="starts_at" name="starts_at" value="<?= htmlspecialchars($old['starts_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="ends_at">Konec hry*</label>
            <input type="datetime-local" id="ends_at" name="ends_at" value="<?= htmlspecialchars($old['ends_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="status">Stav</label>
            <select id="status" name="status">
                <?php
                $statuses = [
                    'draft' => 'Koncept',
                    'registration_open' => 'Registrace otevřena',
                    'active' => 'Aktivní',
                    'finished' => 'Ukončena',
                    'archived' => 'Archivována'
                ];
                $selectedStatus = $old['status'] ?? 'draft';
                foreach ($statuses as $val => $label):
                ?>
                    <option value="<?= $val ?>" <?= $selectedStatus === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="operation_mode">Režim hry</label>
            <select id="operation_mode" name="operation_mode">
                <?php $selectedMode = $old['operation_mode'] ?? 'self_service'; ?>
                <option value="self_service" <?= $selectedMode === 'self_service' ? 'selected' : '' ?>>Samostatná hra</option>
                <option value="moderated" <?= $selectedMode === 'moderated' ? 'selected' : '' ?>>Hra s organizátorem</option>
            </select>
            <div class="help">
                Samostatná hra běží bez živého organizátora. Hra s organizátorem počítá s dohledem a zásahy správce.
            </div>
        </div>

        <div class="form-group checkbox-group" style="padding-top: 25px;">
            <input type="checkbox" id="registration_enabled" name="registration_enabled" value="1" <?= isset($old['registration_enabled']) ? 'checked' : '' ?>>
            <label for="registration_enabled" style="margin: 0;">Povolit registraci hráčů</label>
        </div>

        <div class="form-group">
            <label for="map_center_lat">Střed mapy - Latitude</label>
            <input type="text" id="map_center_lat" name="map_center_lat" value="<?= htmlspecialchars($old['map_center_lat'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="napr. 50.0755">
        </div>

        <div class="form-group">
            <label for="map_center_lon">Střed mapy - Longitude</label>
            <input type="text" id="map_center_lon" name="map_center_lon" value="<?= htmlspecialchars($old['map_center_lon'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="napr. 14.4378">
        </div>

        <div class="form-group">
            <label for="map_default_zoom">Výchozí zoom mapy</label>
            <input type="number" id="map_default_zoom" name="map_default_zoom" value="<?= htmlspecialchars($old['map_default_zoom'] ?? '14', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="session_cookie_days">Platnost session (dny)</label>
            <input type="number" id="session_cookie_days" name="session_cookie_days" value="<?= htmlspecialchars($old['session_cookie_days'] ?? '365', ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>

    <div style="margin-top: 30px; border-top: 1px solid var(--line); padding-top: 20px;">
        <button type="submit" class="btn btn-primary">Vytvořit hru</button>
    </div>
</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>
