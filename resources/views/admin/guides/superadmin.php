<?php
$pageTitle = 'Návod pro superadmina';
$pageSubtitle = 'Systémová a provozní správa celé aplikace';
$activeNav = 'guides';

require __DIR__ . '/../partials/header.php';
?>

<div class="page-actions" style="margin-bottom: 24px;">
    <a href="/admin" class="btn btn-secondary">← Zpět na dashboard</a>
</div>

<style>
    .guide-card {
        background: #fff;
        border: 1px solid #e5ded4;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 10px 24px rgba(0,0,0,0.04);
    }

    .guide-card h2 {
        margin-top: 0;
        margin-bottom: 16px;
    }

    .guide-card h3 {
        margin-top: 28px;
        margin-bottom: 10px;
        color: #5a3d24;
    }

    .guide-card p,
    .guide-card li {
        line-height: 1.65;
        font-size: 15px;
        color: #2f2a24;
    }

    .guide-card ul,
    .guide-card ol {
        margin: 10px 0 0 22px;
    }

    .guide-note {
        background: #eef6ff;
        border: 1px solid #90c2ff;
        padding: 14px 16px;
        border-radius: 12px;
        margin: 16px 0 20px;
    }
</style>

<div class="guide-card">
    <h2>Návod pro superadmina</h2>

    <div class="guide-note">
        Tento návod je určený pro správce celého systému. Týká se technické správy, provozního dohledu, dat a budoucího rozvoje.
    </div>

    <h3>1. Role superadmina</h3>
    <p>Superadmin spravuje celý systém, ne jen jednu konkrétní hru.</p>
    <p>Typicky řeší:</p>
    <ul>
        <li>technickou správu systému</li>
        <li>uživatele s admin oprávněním</li>
        <li>celkovou konfiguraci</li>
        <li>dohled nad dostupností</li>
        <li>údržbu databáze</li>
        <li>zálohy</li>
        <li>exporty a budoucí importy</li>
        <li>řešení incidentů</li>
    </ul>

    <h3>2. Co je hlavní cíl superadmina</h3>
    <p>Cílem superadmina je zajistit, aby:</p>
    <ul>
        <li>systém běžel stabilně</li>
        <li>admini mohli připravovat hry</li>
        <li>hráči mohli bezpečně hrát</li>
        <li>data byla dohledatelná</li>
        <li>při problému bylo možné rychle zjistit stav systému</li>
    </ul>

    <h3>3. Základní oblasti správy</h3>
    <p>Superadmin spravuje zejména:</p>
    <ul>
        <li>administrační přístupy</li>
        <li>hry a jejich technické prostředí</li>
        <li>databázi</li>
        <li>routy a API</li>
        <li>assety a média</li>
        <li>logy</li>
        <li>zálohy</li>
        <li>produkční nasazení</li>
    </ul>

    <h3>4. Co kontrolovat po nasazení změn</h3>
    <p>Po změnách v systému zkontroluj:</p>
    <ul>
        <li>registraci hráče</li>
        <li>vstup do hry</li>
        <li>načtení mapy</li>
        <li>odesílání polohy</li>
        <li>explore flow</li>
        <li>POI a poklady</li>
        <li>claim pokladu</li>
        <li>SOS</li>
        <li>admin header alerty</li>
        <li>detail hráče</li>
        <li>admin detail hry</li>
    </ul>

    <h3>5. Bezpečnostní a provozní doporučení</h3>
    <p>Doporučené minimum:</p>
    <ul>
        <li>chráněné admin přístupy</li>
        <li>bezpečnější cookie nastavení</li>
        <li>rozumné logování chyb</li>
        <li>záloha databáze</li>
        <li>oddělení konfigurace od repozitáře</li>
        <li>pravidelné ověření produkčního provozu po změnách</li>
    </ul>

    <h3>6. Data, která jsou důležitá při incidentu</h3>
    <p>Při řešení problému potřebuješ rychle ověřit:</p>
    <ul>
        <li>zda hráč existuje</li>
        <li>zda má session</li>
        <li>zda se ukládá poloha</li>
        <li>zda existuje SOS</li>
        <li>zda se zapisují eventy</li>
        <li>jaký je poslední kontakt hráče</li>
        <li>co se dělo těsně před problémem</li>
    </ul>

    <h3>7. Administrace adminů</h3>
    <p>Superadmin by měl udržovat přehled:</p>
    <ul>
        <li>kdo má admin přístup</li>
        <li>kdo má superadmin roli</li>
        <li>kdo smí zakládat hry</li>
        <li>kdo smí zasahovat do produkce</li>
    </ul>

    <h3>8. Média a assety</h3>
    <p>Superadmin by měl hlídat:</p>
    <ul>
        <li>správné uložení obrázků a videí</li>
        <li>přístupnost veřejných assetů</li>
        <li>velikost souborů</li>
        <li>dostupnost zvuků pro admin alerty</li>
        <li>strukturu veřejných cest</li>
    </ul>
    <p>Například:</p>
    <ul>
        <li>public/assets/sounds/</li>
        <li>další assety pro hráčské a admin rozhraní</li>
    </ul>

    <h3>9. Budoucí import Game Blueprint JSON</h3>
    <p>Do budoucna se počítá s importem scénářů hry přes JSON blueprint.</p>
    <p>Superadmin bude pravděpodobně spravovat:</p>
    <ul>
        <li>schéma blueprintu</li>
        <li>validátor</li>
        <li>bezpečné zpracování importu</li>
        <li>mapování na databázové entity</li>
        <li>nahrání obrázků a médií</li>
        <li>ochranu proti nekonzistentním datům</li>
    </ul>

    <h3>10. Doporučený směr pro blueprint</h3>
    <p>Správný provozní směr je:</p>
    <ol>
        <li>návrh scénáře</li>
        <li>validace struktury</li>
        <li>kontrola vazeb</li>
        <li>import</li>
        <li>následná ruční kontrola adminem hry</li>
    </ol>
    <p>Není vhodné pustit nevalidovaný JSON přímo do produkčních dat.</p>

    <h3>11. Co je třeba při registraci</h3>
    <p>Superadmin by měl hlídat, aby systém podporoval bezpečné minimum pro registraci hráče:</p>
    <ul>
        <li>přezdívka</li>
        <li>telefonní kontakt pro bezpečnost a provozní dohled</li>
    </ul>
    <p>Aby v návodu i ve hře bylo jasně uvedeno:</p>
    <ul>
        <li>doporučení mít nabitý telefon</li>
        <li>ideálně i powerbanku</li>
    </ul>

    <h3>12. Co je minimální provozní kostra</h3>
    <p>Za minimální provozní kostru se považuje:</p>
    <ul>
        <li>registrace</li>
        <li>mapa</li>
        <li>poloha</li>
        <li>explore flow</li>
        <li>POI</li>
        <li>treasure</li>
        <li>SOS</li>
        <li>admin dohled</li>
        <li>detail hráče</li>
        <li>základní alerting</li>
    </ul>
    <p>Když tyto prvky fungují stabilně, systém je použitelný pro piloty a první ostré akce.</p>

    <h3>13. Co řešit před škálováním</h3>
    <p>Před větším růstem je potřeba:</p>
    <ul>
        <li>zjednodušit tvorbu her</li>
        <li>zavést import scénářů</li>
        <li>validovat data</li>
        <li>oddělit konfigurační a provozní vrstvy</li>
        <li>zpřehlednit admin dashboard</li>
        <li>zavést lepší audit a monitoring</li>
    </ul>

    <h3>14. Co je důležitější než nové funkce</h3>
    <p>Při správě systému platí:</p>
    <ul>
        <li>stabilita je důležitější než množství funkcí</li>
        <li>dohledatelnost dat je důležitější než efekty</li>
        <li>bezpečnost hráčů je důležitější než herní kosmetika</li>
        <li>provozní čitelnost je důležitější než technická elegance</li>
    </ul>

    <h3>15. Krátký checklist superadmina</h3>
    <ul>
        <li>fungují admin loginy</li>
        <li>funguje registrace hráče</li>
        <li>funguje mapa</li>
        <li>funguje explore</li>
        <li>funguje claim pokladu</li>
        <li>funguje SOS</li>
        <li>funguje admin header</li>
        <li>funguje detail hráče</li>
        <li>hráči mohou uvést telefon</li>
        <li>v komunikaci je doporučená powerbanka</li>
        <li>existuje aktuální záloha</li>
    </ul>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>