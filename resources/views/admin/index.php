<?php
$pageTitle = 'Dashboard';
$pageSubtitle = 'Vítejte v administraci systému VentureOut';
$activeNav = '';

require __DIR__ . '/partials/header.php';

$pdo = \App\Support\Database::connection();
$stats = $pdo->query('SELECT 
    (SELECT COUNT(*) FROM games) as games_count,
    (SELECT COUNT(*) FROM players) as players_count,
    (SELECT COUNT(*) FROM users) as users_count
')->fetch();
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--accent);
        margin: 10px 0;
    }
</style>

<section class="dashboard-grid">
    <article class="card">
        <h2>Hry</h2>
        <p>Zakládej nové hry, upravuj jejich parametry, nastavuj čas, popis, režim a sdílení.</p>
        <div class="stat-number"><?= (int)$stats['games_count'] ?></div>
        <div class="help">Celkový počet her v systému</div>
        <div style="margin-top: 18px;">
            <a class="btn btn-primary" href="/admin/games">Otevřít hry</a>
        </div>
    </article>

    <article class="card">
        <h2>Hráči</h2>
        <p>Přehled všech registrovaných hráčů napříč všemi aktivními hrami.</p>
        <div class="stat-number"><?= (int)$stats['players_count'] ?></div>
        <div class="help">Registrovaných hráčů celkem</div>
        <div style="margin-top: 18px;">
            <a class="btn btn-primary" href="/admin/games">Přejít na hry</a>
        </div>
    </article>

    <article class="card">
        <h2>Uživatelé</h2>
        <p>Přehled administrátorů a správců systému, jejich role, přístupy a změny hesel.</p>
        <div class="stat-number"><?= (int)$stats['users_count'] ?></div>
        <div class="help">Administrátorů a správců</div>
        <div style="margin-top: 18px;">
            <a class="btn btn-primary" href="/admin/users">Otevřít uživatele</a>
        </div>
    </article>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
