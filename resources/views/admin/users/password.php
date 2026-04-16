<?php
$pageTitle = 'Změna hesla';
$pageSubtitle = 'Uživatel: ' . $user['username'];
$activeNav = 'users';
require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions">
    <a class="btn btn-secondary" href="/admin/users">Zpět na uživatele</a>
</div>

<div class="card">
    <form method="post" action="/admin/users/<?= (int) $user['id'] ?>/password">
        <div class="form-group">
            <label for="password">Nové heslo</label>
            <input id="password" name="password" type="password" required>
        </div>

        <button class="btn btn-primary" type="submit">Uložit nové heslo</button>
    </form>
</div>

<?php
require __DIR__ . '/../partials/footer.php';
?>
