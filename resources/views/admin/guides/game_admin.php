<?php
$pageTitle = 'Návod pro admina hry';
$pageSubtitle = 'Provozní návod pro správu konkrétní hry';
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
        background: #fff8e8;
        border: 1px solid #e3c675;
        padding: 14px 16px;
        border-radius: 12px;
        margin: 16px 0 20px;
    }
</style>

<div class="guide-card">
    <h2>Návod pro admina hry</h2>

    <div class="guide-note">
        Tento návod je určený pro organizátora konkrétní hry. Zaměřuje se na přípravu hry, dohled nad hráči a práci se SOS.
    </div>

    <h3>1. Role admina hry</h3>
    <p>Admin hry připravuje a řídí konkrétní hru.</p>
    <p>Typicky zajišťuje:</p>
    <ul>
        <li>založení nebo úpravu hry</li>
        <li>přípravu POI a pokladů</li>
        <li>invite a registraci hráčů</li>
        <li>kontrolu průběhu hry</li>
        <li>dohled nad hráči</li>
        <li>reakci na SOS</li>
        <li>základní vyhodnocení</li>
    </ul>
    <p>Admin hry pracuje hlavně s konkrétní jednou hrou a jejím průběhem.</p>

    <h3>2. Co zkontrolovat před spuštěním hry</h3>
    <p>Před ostrým použitím zkontroluj:</p>
    <ul>
        <li>že hra existuje a má správný název</li>
        <li>že je nastavena mapa a výchozí střed</li>
        <li>že jsou POI a poklady na správných místech</li>
        <li>že mají smysluplný radius</li>
        <li>že jsou aktivní</li>
        <li>že máš připravené invite kódy nebo odkaz</li>
        <li>že briefing hry odpovídá realitě</li>
        <li>že hráči vědí, že mají zadat telefonní kontakt</li>
        <li>že je doporučené mít nabitou powerbanku</li>
    </ul>

    <h3>3. Doporučení k registraci hráčů</h3>
    <p>Při registraci má hráč uvést:</p>
    <ul>
        <li>přezdívku</li>
        <li>telefonní kontakt pro svou bezpečnost</li>
    </ul>
    <p>To je důležité pro:</p>
    <ul>
        <li>řešení SOS</li>
        <li>rychlé doptání při komplikaci</li>
        <li>dohled při ztrátě kontaktu</li>
    </ul>

    <h3>4. Jak připravovat POI</h3>
    <p>POI používej jako:</p>
    <ul>
        <li>příběhový bod</li>
        <li>informační bod</li>
        <li>stopu</li>
        <li>přechod mezi částmi hry</li>
        <li>odemykací bod pro poklad</li>
    </ul>
    <p>U POI zkontroluj:</p>
    <ul>
        <li>název</li>
        <li>text</li>
        <li>případná média</li>
        <li>souřadnice</li>
        <li>radius</li>
        <li>zda je aktivní</li>
        <li>zda navazuje na další krok</li>
    </ul>
    <p><strong>Pravidlo:</strong> POI nemá být jen „bod na mapě“. Má mít význam.</p>

    <h3>5. Jak připravovat poklady</h3>
    <p>Poklady používej jako:</p>
    <ul>
        <li>odměnu</li>
        <li>potvrzení postupu</li>
        <li>bonus</li>
        <li>zvláštní nález</li>
        <li>navázaný výsledek po POI</li>
    </ul>
    <p>U pokladu zkontroluj:</p>
    <ul>
        <li>název</li>
        <li>body</li>
        <li>souřadnice</li>
        <li>radius</li>
        <li>zda je aktivní</li>
        <li>zda je veřejný nebo skrytý</li>
        <li>zda je navázaný na POI</li>
    </ul>

    <h3>6. Doporučení k radiusům</h3>
    <p>Příliš malý radius dělá hru frustrující. Příliš velký radius dělá hru nepřesnou.</p>
    <p>Na pilot a testování je lepší mít radius spíš velkorysejší. Zejména:</p>
    <ul>
        <li>v městské zástavbě</li>
        <li>při slabé GPS</li>
        <li>při testování na telefonu</li>
    </ul>

    <h3>7. Co musí hráč vědět před hrou</h3>
    <p>Admin musí hráčům předat:</p>
    <ul>
        <li>cíl hry</li>
        <li>základní ovládání</li>
        <li>význam tlačítka „Prozkoumat okolí“</li>
        <li>jak funguje SOS</li>
        <li>že mají mít nabitý telefon</li>
        <li>že je doporučená powerbanka</li>
        <li>že při registraci zadávají telefonní kontakt kvůli bezpečnosti</li>
    </ul>

    <h3>8. Co sledovat během hry</h3>
    <p>Během hry sleduj:</p>
    <ul>
        <li>nová SOS v headeru</li>
        <li>události v admin feedu</li>
        <li>detail konkrétní hry</li>
        <li>detail hráče při problému</li>
        <li>poslední polohu hráče</li>
        <li>historii pohybu</li>
        <li>poslední události hráče</li>
    </ul>

    <h3>9. Jak pracovat se SOS</h3>
    <p>Když přijde SOS:</p>
    <ol>
        <li>otevři nebo převezmi případ</li>
        <li>přejdi na detail hráče</li>
        <li>zjisti:
            <ul>
                <li>poslední známou polohu</li>
                <li>trasu</li>
                <li>poslední aktivitu</li>
                <li>text zprávy</li>
            </ul>
        </li>
        <li>rozhodni, zda:
            <ul>
                <li>hráče navedeš</li>
                <li>zkontroluješ telefonicky</li>
                <li>pošleš pomoc</li>
                <li>ukončíš jeho hru</li>
            </ul>
        </li>
        <li>po vyřešení případ uzavři</li>
    </ol>

    <h3>10. Co vidíš v detailu hráče</h3>
    <p>Detail hráče slouží jako rychlý provozní přehled.</p>
    <p>Najdeš tam:</p>
    <ul>
        <li>základní údaje</li>
        <li>poslední aktivitu</li>
        <li>přesnost GPS</li>
        <li>poslední známou polohu</li>
        <li>historii pohybu</li>
        <li>sebrané poklady</li>
        <li>poslední události hráče</li>
        <li>aktivní SOS, pokud existuje</li>
    </ul>

    <h3>11. Jak rozumět stavu SOS</h3>
    <p>Doporučené významy:</p>
    <ul>
        <li><strong>open</strong> = nové, nepřevzaté</li>
        <li><strong>acknowledged</strong> = převzaté organizátorem</li>
        <li><strong>resolved</strong> = uzavřené</li>
    </ul>
    <p>Nové kritické alerty jsou ty, které jsou otevřené a dosud nepřevzaté.</p>

    <h3>12. Kdy hru nepouštět mezi lidi</h3>
    <p>Hru nepouštěj, pokud:</p>
    <ul>
        <li>nejsou odzkoušené POI</li>
        <li>poklady mají špatné souřadnice</li>
        <li>briefing neodpovídá realitě</li>
        <li>admin nemá přehled o trase a SOS</li>
        <li>nikdo z organizátorů nehlídá průběh hry</li>
        <li>hráči nedostali instrukci o telefonu, GPS a powerbance</li>
    </ul>

    <h3>13. Krátký checklist před akcí</h3>
    <ul>
        <li>hra je aktivní</li>
        <li>POI jsou zkontrolovaná</li>
        <li>poklady jsou zkontrolované</li>
        <li>admin header funguje</li>
        <li>SOS funguje</li>
        <li>detail hráče ukazuje polohu</li>
        <li>hráči dostali instrukce</li>
        <li>registrace sbírá telefonní kontakt</li>
        <li>doporučení powerbanky zaznělo</li>
    </ul>

    <h3>14. Co neřešit během pilotu</h3>
    <p>Při prvních akcích neřeš dokonalost. Důležitější je:</p>
    <ul>
        <li>plynulý průběh</li>
        <li>dohled nad lidmi</li>
        <li>čitelné instrukce</li>
        <li>bezpečnost</li>
        <li>základní funkčnost POI, treasure a SOS</li>
    </ul>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>