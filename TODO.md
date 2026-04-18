# TODO

Tento soubor je přepsaný podle skutečného stavu repozitáře, ne podle původního plánu.
Priorita 1

Oprávnění a oddělení herních adminů

superadmin zakládá hry
superadmin přiřazuje adminy ke hrám
admin bez přiřazené hry nesmí nic vytvářet ani spravovat
admin vidí jen své hry
admin vidí jen své hráče
admin vidí jen své SOS a své eventy
zamknout URL přístupy serverově, ne jen v menu
zamknout správu uživatelů jen pro superadmina
Priorita 2

UI pro přehrávání POI / treasure

při přehrávání musí jít obsah zastavit
ideálně stavové tlačítko:
Přehrát
Zastavit
při zavření modalu se čtení musí ukončit
přepnutí na jiné POI / treasure musí předchozí čtení ukončit
nesmí se stát, že hlas běží dál na pozadí bez kontroly
Priorita 3

Dočištění práv v celé admin části

POI jen pro správce dané hry
poklady jen pro správce dané hry
invites jen pro správce dané hry
detail hráče jen pro správce jeho hry
header alerty filtrovat podle přidělených her
Priorita 4

Admin návody

game admin
superadmin
případně prolink na hráčský návod
Priorita 5

UX hráče

itinerář hry
rozlišení markerů hráč / POI / treasure
další jemné doladění dashboardu
Priorita 6

Blueprint / JSON import

schema
validace
ukázkový blueprint
importer

Tohle je teď správné pořadí.

A ano: ten problém s přehráváním POI/treasure je reálný UX bug, ne kosmetika. Jen je pořád menší než bezpečnost a oprávnění adminů.
---

## Ověřeně hotové

### Základ projektu
- [x] ruční router v `public/index.php`
- [x] DB připojení přes `App\Support\Database`
- [x] repository vrstva pro users / games / invites / teams / players / pois / help / treasures
- [x] landing stránka `/`
- [x] základní admin layout a sdílený header/footer

### Admin autentizace a uživatelé
- [x] admin login / logout
- [x] kontrola session pro admin část
- [x] seznam admin uživatelů
- [x] vytvoření admin uživatele
- [x] aktivace / deaktivace uživatele
- [x] změna hesla admin uživatele

### Hry
- [x] seznam her
- [x] vytvoření hry
- [x] detail hry
- [x] stav hry (`draft`, `registration_open`, `active`, `finished`, `archived`)
- [x] režim hry (`self_service`, `moderated`)
- [x] `intro_text`, map center, zoom, `session_cookie_days`
- [x] veřejný odkaz na hru z detailu hry

### Invite flow
- [x] seznam invite kódů pro hru
- [x] vytvoření invite kódu
- [x] vazba invite -> team
- [x] max uses u invite
- [x] smazání invite
- [x] QR / pozvánkový flow je v UI i backendu napojený

### POI
- [x] seznam POI pro hru
- [x] vytvoření POI
- [x] editace POI
- [x] smazání POI
- [x] Leaflet mapa v admin formuláři POI
- [x] typy POI (`start_point`, `story_point`, `checkpoint`, `rescue_point`, `hint_point`, `finish_point`, `meetup_point`)
- [x] `radius_m`, `sort_order`, `active_from`, `active_to`
- [x] `auto_unlock_on_proximity`, `is_required`, `is_enabled`

### POI média
- [x] tabulka / práce s `poi_media`
- [x] admin formulář pro média u POI
- [x] obrázky v detailu POI na hráčské straně
- [x] YouTube / video URL v detailu POI
- [x] browser TTS přes `speechSynthesis`

### Treasures
- [x] seznam pokladů pro hru
- [x] vytvoření pokladu
- [x] editace pokladu
- [x] smazání pokladu
- [x] `treasure_type` (`public`, `hidden`, `individual`, `team`)
- [x] `is_visible_on_map`, `max_claims`, `points`, `is_enabled`
- [x] vazba treasure -> POI přes `poi_id` existuje v DB a formuláři
- [x] claim logika v backendu
- [x] zápis do `treasure_claims`
- [x] event `treasure_claimed`

### Hráčský vstup do hry
- [x] veřejná URL hry `/game/{slug}`
- [x] registrace hráče přezdívkou
- [x] kontrola unikátní přezdívky v rámci hry
- [x] obsluha invite kódu při registraci
- [x] vytvoření player session
- [x] návrat do hry přes cookie `player_session`
- [x] dashboard hráče

### Hráčská mapa a herní loop
- [x] Leaflet mapa pro hráče
- [x] GPS watchPosition
- [x] endpoint `POST /api/player/location`
- [x] endpoint `GET /api/player/map-data`
- [x] endpoint `POST /api/player/explore`
- [x] endpoint `POST /api/player/poi/complete`
- [x] endpoint `POST /api/player/claim`
- [x] endpoint `POST /api/player/help`
- [x] zobrazení POI v mapě hráče
- [x] zobrazení treasure v mapě hráče
- [x] panel `Prozkoumat okolí`
- [x] průzkum vrací 0 / 1 / více objektů v dosahu
- [x] modal pro výběr z více objektů v okolí
- [x] detail POI a detail treasure v modalu
- [x] potvrzení dokončení POI až vědomou akcí hráče
- [x] claim treasure až vědomou akcí hráče

### Výsledky a přehled hráče
- [x] výpočet bodů z treasure claimů
- [x] základní leaderboard pro hráče
- [x] player card modal
- [x] výsledovka v hráčském UI
- [x] progress procenta z POI + treasures

### Tracking a admin dohled
- [x] logování polohy do `location_log`
- [x] detail hráče v adminu
- [x] poslední známá poloha v detailu hráče
- [x] historie poloh v detailu hráče
- [x] poslední události hráče v detailu hráče
- [x] sebrané poklady v detailu hráče
- [x] výsledovka na detailu hry

### Help / SOS
- [x] hráč může poslat SOS / help request
- [x] ukládání do `help_requests`
- [x] admin header alerts pro SOS a důležité eventy
- [x] acknowledge / resolve endpointy pro help request
- [x] aktivní SOS je vidět v detailu hráče

---

## Hotové jen částečně nebo technicky, ale ne dotažené

- [~] herní smyčka `explore -> open -> complete/claim` existuje, ale bez jemnějších stavů typu `nearby / discovered / opened / completed`
- [~] POI už nejsou čistě auto-open flow, ale event model stále používá jen `poi_visited`
- [~] player card existuje, ale je spíš modal s pár statistikami než plnohodnotný herní hub
- [~] výsledovka existuje pro player i admin, ale pořadí se opírá hlavně o poklady a body
- [~] SOS funguje, ale není z něj samostatný provozní modul / dashboard jedné hry
- [~] treasure typy existují, ale herní význam `public/hidden/individual/team` zatím není dotažený do širší logiky hry
- [~] vazba `treasure.poi_id` existuje, ale dokončení POI zatím nic skutečně neodemyká (`unlocked_treasures` se vrací prázdné pole)
- [~] `session_cookie_days` je v administraci, ale hráčská registrace zatím vždy nastavuje cookie natvrdo na 365 dní

---

## Důležité mezery a dluhy

### Herní logika
- [ ] doplnit skutečný unlock flow `POI -> treasure`
- [ ] rozlišit eventy minimálně na `poi_discovered`, `poi_opened`, `poi_completed`, `treasure_discovered`, `treasure_claimed`
- [ ] rozhodnout, co se počítá do výsledků a co je jen analytika
- [ ] přestat mít `poi_visited` jako jediný stav POI
- [ ] doplnit prioritu objektů při více kandidátech v dosahu
- [ ] lépe využít `auto_unlock_on_proximity` nebo ho odstranit, pokud už nemá význam
- [ ] skrývání treasure do doby triggeru není dotažené; aktuálně se na mapě řídí hlavně `is_visible_on_map`

### Admin provoz a operativa
- [ ] samostatná stránka / obrazovka pro otevřené SOS
- [ ] dashboard jedné hry s živou mapou hráčů
- [ ] rychlý přehled aktivních / neaktivních hráčů podle posledního kontaktu
- [ ] přehled posledních objevů / posledních claimů po jednotlivých hrách
- [ ] lepší operační cockpit než současný globální dashboard se třemi počty

### Tracking
- [ ] `POST /api/player/location` momentálně loguje polohu jen do `location_log`
- [ ] vrátit nebo nahradit update `players.last_lat`, `players.last_lon`, `players.last_accuracy`, `players.last_seen_at`
- [ ] bez toho je část admin přehledů a leaderboardu založená na zastarávajících údajích v tabulce `players`
- [ ] vykreslení trasy hráče na mapě v adminu
- [ ] omezit nebo archivovat růst `location_log`

### Hráčský UX
- [ ] onboarding / briefing konkrétní hry před mapou
- [ ] lépe vysvětlit rozdíl mezi POI a treasure
- [ ] herní texty v mapovém UI místo utilitních hlášek
- [ ] lepší práce s GPS nejistotou a chybami geolokace
- [ ] méně alertů, více řízený mobile-first UX flow
- [ ] průběžná aktualizace player stats z backendu místo lokálního dopočtu jen v JS

### Obsah a média
- [ ] dotáhnout uploady do konzistentní struktury a validace
- [ ] ověřit a uklidit ukládání souborů do `public/uploads`
- [ ] stejné media flow doplnit i pro treasures
- [ ] audio soubory pro POI / treasure nejsou jako plnohodnotný upload modul dotažené

### Bezpečnost a pravidla přístupu
- [ ] zpevnit cookie pro hráče (`httponly`, `secure`, `samesite`)
- [ ] respektovat časové okno hry i při obnově session
- [ ] důsledně vynucovat `starts_at` / `ends_at` při vstupu a hraní
- [ ] doplnit kontrolu `valid_from` / `valid_to` u invites, pokud jsou v DB navržené
- [ ] projít oprávnění editor/viewer rolí; teď je admin guard v zásadě jen `isset($_SESSION['admin_user'])`

### Kód a údržba
- [ ] odstranit `.DS_Store`
- [ ] přesunout SQL backupy mimo repo
- [ ] doplnit skutečné verzované migrace (adresář `database/migrations` je prázdný)
- [ ] zkontrolovat a doplnit seedery
- [ ] uklidit dead code a starší experimenty v TODO / dokumentaci
- [ ] sjednotit README, `popis.txt` a reálný stav aplikace

### Funkce, které v zadání jsou, ale v repu zatím nejsou
- [ ] chat hráčů / týmů
- [ ] admin správa týmů
- [ ] checkpoint / rescue flow jako samostatná gameplay logika
- [ ] import / export blueprintů hry
- [ ] quick capture z terénu
- [ ] XP / levely / hodnosti
- [ ] plnohodnotný briefing, outro a širší narativní flow hry

---

## Doporučená nejbližší priorita

### 1. Opravit provozní pravdu o poloze hráče
- [ ] v `updateLocation()` znovu zapisovat i `players.last_*`
- [ ] ověřit, že admin detail hry a detail hráče ukazují aktuální data

### 2. Dotáhnout skutečný unlock flow
- [ ] po `completePoi()` opravdu odemykat navázané treasure
- [ ] vracet `unlocked_treasures` podle reality
- [ ] zapisovat odpovídající eventy

### 3. Stabilizovat event model
- [ ] rozdělit `poi_visited` na jemnější stavy
- [ ] upravit leaderboard a progress, aby stál na finálních stavech

### 4. Udělat z adminu použitelný operační nástroj
- [ ] přidat přehled otevřených SOS po hrách
- [ ] přidat aktivitu hráčů a poslední polohu do dashboardu jedné hry

---

## Co teď rozhodně netvrdit v dokumentaci jako hotové

- [ ] že treasure se odemyká po dokončení konkrétního POI
- [ ] že `session_cookie_days` skutečně řídí hráčskou cookie
- [ ] že admin dashboard je živý operační cockpit hry
- [ ] že existuje chat
- [ ] že jsou hotové migrace
- [ ] že tracking automaticky drží aktuální poslední polohu v tabulce `players`
