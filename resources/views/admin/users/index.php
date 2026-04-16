<?php
$pageTitle = 'Správa uživatelů';
$pageSubtitle = 'Administrátoři a správci portálu a her';
$activeNav = 'users';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions" style="margin-bottom: 24px;">
    <a class="btn btn-secondary" href="/admin">← Zpět na dashboard</a>
</div>

<div class="card" style="margin-bottom: 30px;">
    <h3>Vytvořit nového uživatele</h3>

    <form method="post" action="/admin/users/create">
        <div class="form-grid">
            <div class="form-group">
                <label>Uživatelské jméno</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>

            <div class="form-group">
                <label>Heslo</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Globální role</label>
                <select name="global_role">
                    <option value="none">žádná</option>
                    <option value="superadmin">superadmin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Role (lokální)</label>
                <select name="role">
                    <option value="admin">admin</option>
                    <option value="editor">editor</option>
                    <option value="viewer">viewer</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button class="btn btn-primary" type="submit">Vytvořit uživatele</button>
        </div>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Uživatel</th>
            <th>Email</th>
            <th>Globální role</th>
            <th>Role</th>
            <th>Aktivní</th>
            <th>Poslední login</th>
            <th>Vytvořen</th>
            <th style="text-align: right;">Akce</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= (int)$user['id'] ?></td>

                <td>
                    <strong><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                </td>

                <td><?= htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8') ?></td>

                <td><?= htmlspecialchars((string)$user['global_role'], ENT_QUOTES, 'UTF-8') ?></td>

                <td><span class="badge"><?= htmlspecialchars((string)$user['role'], ENT_QUOTES, 'UTF-8') ?></span></td>

                <td>
                    <?php if ((int)$user['is_active'] === 1): ?>
                        <span class="badge badge-self">ano</span>
                    <?php else: ?>
                        <span class="badge">ne</span>
                    <?php endif; ?>
                </td>

                <td><small><?= htmlspecialchars((string)$user['last_login_at'], ENT_QUOTES, 'UTF-8') ?></small></td>

                <td><small><?= htmlspecialchars((string)$user['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>

                <td style="text-align: right;">
                    <div class="page-actions" style="justify-content: flex-end; margin-top: 0;">
                        <a class="btn btn-secondary" href="/admin/users/<?= (int)$user['id'] ?>/password" title="Změnit heslo">
                            Heslo
                        </a>

                        <form method="post" action="/admin/users/<?= (int)$user['id'] ?>/toggle" style="display:inline;">
                            <button type="submit" class="btn btn-secondary">
                                <?= (int)$user['is_active'] === 1 ? 'Deaktivovat' : 'Aktivovat' ?>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 24px;" class="help">
    Další krok: přiřazení uživatelů ke konkrétní hře (game_admin / editor).
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
