# TODO

## Hotovo
- [x] databáze + základní tabulky
- [x] admin uživatel
- [x] testovací hra
- [x] týmy
- [x] POI (body)
- [x] struktura projektu
- [x] DB připojení (PHP)
- [x] admin login + dashboard
- [x] seznam her + detail
- [x] vytvoření hry
- [x] editor POI (včetně mapy)
- [x] pozvánky + QR / link
- [x] registrace hráče (backend + frontend)
- [x] player session
- [x] mapa hráče (Leaflet)
- [x] GPS tracking
- [x] API: poloha
- [x] API: SOS
- [x] správa uživatelů (základ)
- [x] deploy + HTTPS
- [x] invite flow
- [x] režim hry (self_service / moderated)
- [x] poklady (DB + admin create/edit + mapa)
- [x] endpoint `/api/player/map-data`
- [x] endpoint `/api/player/claim`
- [x] claim logika pokladů
- [x] zápis do `treasure_claims`
- [x] zobrazení POI v hráčské mapě
- [x] zobrazení pokladů v hráčské mapě
- [x] detail POI / pokladu nad mapou
- [x] TTS přes browser (`speechSynthesis`)
- [x] media pro POI (`poi_media`)
- [x] zobrazení obrázků a YouTube v detailu POI
- [x] admin formuláře POI rozšířené o média

---

# AKTUÁLNÍ PRIORITA

## Cíl
👉 přestat vrstvit jen funkce a udělat z toho **čitelnou, hratelnou a hezky působící aplikaci**

Tj. teď je priorita:
1. UX / layout hráče
2. výsledovka hráče
3. výsledovka admina
4. XP / level / leaderboard
5. až potom další rozšiřování mechanik

---

# PRIORITA 1 — PLAYER UX / MAP SCREEN

## Hlavní problémy, které jsou teď vidět
- [ ] levý horní box je technický, ne herní (`GPS OK (67m)` nedává hráči smysl)
- [ ] pravý horní timer vizuálně poskakuje a ruší
- [ ] spodní tlačítka zbytečně zakrývají mapu
- [ ] `Obnovit` nemá pro hráče jasný význam
- [ ] SOS nemá být na hlavní mapové obrazovce
- [ ] poklad se teď chová příliš automaticky, místo hledání spíš „vyskočí“
- [ ] UI je spíš utilitní než herní

## Udělat
- [ ] předělat horní levý panel na **player card trigger**
- [ ] místo textu `GPS OK (xxm)` udělat jen jednoduchý stav přesnosti / ikonu
- [ ] klik na player card otevře panel s přehledem hráče
- [ ] horní pravý panel stabilizovat (žádné skákání šířky podle času)
- [ ] promyslet, zda má vpravo být čas nebo funkční akce (`nejbližší úkol`, `mapa / seznam`, apod.)
- [ ] odstranit nebo nahradit tlačítko `Obnovit`
- [ ] přesunout SOS do hráčské karty / menu
- [ ] udělat spodní UI menší, lehčí a méně invazivní
- [ ] přejít na průhledné / glass panely nad mapou
- [ ] místo tvrdých modálů zvážit bottom sheet / vysouvací panel odspodu

## Poklady vs. POI chování
- [ ] POI se může otevřít automaticky po vstupu do radiusu
- [ ] poklad se **nemá** otevírat automaticky jen proto, že je hráč poblíž
- [ ] u pokladu má být pocit hledání a aktivní akce hráče
- [ ] navrhnout stav typu `něco je poblíž` + akce `prozkoumat`

---

# PRIORITA 2 — VÝSLEDOVKA / PLAYER CARD

## Chybí
- [ ] hráčská výsledovka není hotová
- [ ] není přehled, co hráč získal a jak si vede
- [ ] není „pocit identity“ hráče

## Udělat
- [ ] player card / profil hráče
- [ ] zobrazit nickname
- [ ] zobrazit tým
- [ ] zobrazit dobu ve hře / čas od přihlášení
- [ ] zobrazit navštívená POI
- [ ] zobrazit počet sebraných pokladů
- [ ] zobrazit seznam nalezených pokladů
- [ ] zobrazit progress ve hře
- [ ] připravit místo pro level / XP / žebříček
- [ ] přesunout SOS sem

## Později
- [ ] achievementy / milníky
- [ ] úrovně / odznaky
- [ ] historie objevů

---

# PRIORITA 3 — ADMIN UX / VÝSLEDOVKA HRY

## Problém
- [ ] admin backend je funkční, ale vizuálně působí jako utilita
- [ ] výsledovka hry není hotová nebo není dobře vidět

## Udělat
- [ ] admin přehled hráčů ve hře
- [ ] tabulka / dashboard XP a pořadí
- [ ] přehled pokladů: kdo našel co
- [ ] poslední aktivita hráčů
- [ ] poslední poloha hráče
- [ ] SOS requesty na jednom místě
- [ ] detail hráče
- [ ] detail hry s přehledným dashboard layoutem

---

# PRIORITA 4 — XP / LEVEL / LEADERBOARD

## Směr
👉 hra nemá být jen jednorázová trasa, ale má dát hráči pocit růstu, postupu a srovnání s ostatními

## XP systém
- [ ] přidat XP hráči
- [ ] XP za návštěvu POI
- [ ] XP za nalezení pokladu
- [ ] XP za dokončení hry
- [ ] možnost bonusových XP za objev / speciální událost

## Level systém
- [ ] levely ne lineárně, ale exponenciálně / geometricky
- [ ] rychlý začátek (hráč musí brzy postoupit)
- [ ] vyšší levely musí být těžší a mít váhu
- [ ] připravit stupnici minimálně do levelu 15
- [ ] level počítat z celkových XP
- [ ] zobrazit level + název hodnosti
- [ ] zobrazit progress do dalšího levelu

## Žebříček
- [ ] leaderboard podle XP
- [ ] pořadí hráče ve hře
- [ ] pořadí týmů (později)
- [ ] „objev“ má umět hráče výrazně posunout v žebříčku

## Alternativní stupnice hodností
Při zakládání hry musí jít vybrat styl levelů / hodností.

### Udělat
- [ ] přidat do hry volbu `level_scheme`
- [ ] admin si při založení hry vybere styl stupnice
- [ ] mapovat level → název podle zvolené stupnice

### První varianty
- [ ] `military` — lehký vojenský feeling
- [ ] `adventure` — průzkumník / dobrodruh / lovec pokladů
- [ ] `mystic` — tajemný řád / zasvěcení / strážci

### Poznámka
Nejde o historickou přesnost, ale o historicky uvěřitelný a herně silný pocit.

---

# PRIORITA 5 — POKLADY

## Současný směr ponechat
👉 poklad zůstává samostatná entita, zatím nebudeme refaktorovat do POI

## Pravidla
- [x] kdo první najde, ten vybere
- [x] po sebrání zmizí / změní stav
- [ ] musí se propsat do hráčovy karty / výsledovky
- [ ] musí se propsat do admin výsledovky
- [ ] odlišit víc chování POI vs. treasure v UX

## Další kroky
- [ ] lépe odlišit marker pokladu od markeru POI
- [ ] vymyslet UX hledání pokladu bez automatického reveal modalu
- [ ] volitelně přidat pokladům média až po stabilizaci player UX

---

# MEDIA / OBSAH

## Hotovo
- [x] POI media přes `poi_media`
- [x] obrázky v detailu POI
- [x] YouTube v detailu POI
- [x] admin formuláře POI rozšířené o média

## Dále
- [ ] dokončit ukládání médií v adminu včetně uploadu obrázků
- [ ] ukládání uploadů do `/public/uploads/games/{game_id}/pois/{poi_id}/`
- [ ] validace uploadu (jpg/png/webp, velikost)
- [ ] ponechat možnost kombinace: externí historický obrázek + vlastní aktuální fotka + YouTube
- [ ] neomezovat počet příloh na 3, ale mít 0 až N
- [ ] stejný systém médií později pro poklady

---

# SELF-SERVICE REŽIM

- [ ] zobrazit progress hráče
- [ ] dokončení hry
- [ ] výsledovka bez organizátora
- [ ] jasné flow bez zásahu admina
- [ ] SOS v self-service buď vypnout, nebo jen logovat

---

# MODEROVANÝ REŽIM (POZDĚJI)

- [ ] admin mapa hráčů
- [ ] live dashboard
- [ ] SOS panel
- [ ] broadcast zprávy
- [ ] výsledky
- [ ] chat (team / global)
- [ ] zásahy admina

---

# STABILIZACE / ÚKLID

- [ ] odstranit `.DS_Store`
- [ ] opravit `.gitignore`
- [ ] přesunout backupy mimo repo
- [ ] sjednotit názvy repository
- [ ] odstranit warningy
- [ ] cleanup hotfixů
- [ ] verzované SQL migrace
- [ ] zkontrolovat, že TODO neobsahuje staré poznámky a slepé větve

---

# WORKFLOW

- [ ] práce přes VS Code SSH
- [ ] malé commity
- [ ] push → server pull
- [ ] DB změny zapisovat do SQL
- [ ] větší UX zásahy nejdřív zkusit na jedné obrazovce, ne plošně

---

# NEJBLIŽŠÍ KONKRÉTNÍ KROK

## Varianta A — nejvyšší dopad
- [ ] předělat `player/dashboard.php` do čitelnějšího mapového layoutu
- [ ] přesunout SOS do player card
- [ ] odstranit technický text `GPS OK (xxm)`
- [ ] stabilizovat timer / horní pravý panel
- [ ] oddělit chování POI a treasure v UX

## Varianta B — paralelně
- [ ] připravit DB a helpery pro XP / level / leaderboard
- [ ] přidat `level_scheme` do hry
- [ ] navrhnout první 3 stupnice hodností

