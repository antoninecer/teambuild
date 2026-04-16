# TODO

## Hotovo
- [x] databáze + základní tabulky
- [x] admin uživatelé
- [x] testovací hry
- [x] týmy
- [x] POI (body)
- [x] struktura projektu
- [x] DB připojení (PHP)
- [x] admin login
- [x] admin dashboard základ
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
- [x] režim hry (`self_service` / `moderated`)
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
- [x] landing / rozcestník `/`
- [x] VentureOut vizuální směr
- [x] background image pro landing / admin
- [x] admin `header.php`
- [x] admin `footer.php`
- [x] `admin/games/index.php` převedený na shared header/footer

---

# AKTUÁLNÍ SMĚR

## Cíl
👉 přestat vrstvit jen CRUD a udělat z toho:

- použitelnou hru pro hráče
- přehledné operační rozhraní pro správce
- vizuálně jednotný VentureOut systém

---

# PRIORITA 1 — SJEDNOCENÍ ADMINU

## Problém
Teď je admin mix:
- část už je VentureOut
- část je starý syrový CRUD
- některé stránky mají vlastní hlavičky, styly a layout
- dashboard nesmí být druhé menu pod hlavním menu

## Udělat
- [ ] napojit na shared `header.php` + `footer.php`:
  - [ ] `resources/views/admin/games/create.php`
  - [ ] `resources/views/admin/games/show.php`
  - [ ] `resources/views/admin/users/index.php`
  - [ ] později i `pois`, `treasures`, `invites`
- [ ] odstranit duplicitní navigaci z admin dashboardu
- [ ] z `admin/index.php` udělat skutečný dashboard, ne druhé rozcestníkové menu
- [ ] sjednotit buttony, formuláře, tabulky a spacing
- [ ] sjednotit breadcrumb / lokální page actions

---

# PRIORITA 2 — DASHBOARD SPRÁVCE

## Klíčová myšlenka
Dashboard správce nemá být jen:
- Hry
- Uživatelé
- menu

Dashboard správce má být **operační přehled**.

## Musí obsahovat
- [ ] výsledovku
- [ ] otevřená volání o pomoc
- [ ] poslední známou polohu hráčů
- [ ] poslední aktivitu hráčů
- [ ] trekovatelnost / seznam průchodů
- [ ] přehled kdo stojí, kdo se hýbe, kdo dlouho neupdatuje polohu

## Role
### Supersprávce
- [ ] přehled nad všemi hrami
- [ ] globální help requesty
- [ ] globální výsledovky
- [ ] globální poslední polohy / aktivita

### Správce jedné hry
- [ ] jen jeho hra
- [ ] její hráči
- [ ] její help requesty
- [ ] její výsledovka
- [ ] její trekování

## Udělat nejdřív
- [ ] dashboard jedné hry
- [ ] až potom globální supersprávcovský dashboard

---

# PRIORITA 3 — TRACKING / TREKOVATELNOST

## Máme základ
- [x] `updateLocation()`
- [x] logování polohy

## Chybí vytáhnout na povrch
- [ ] poslední známá poloha hráče
- [ ] čas posledního updatu
- [ ] přesnost GPS
- [ ] seznam průchodů / historie poloh
- [ ] detail hráče s posledními body pohybu
- [ ] možnost ukázat trasu na mapě
- [ ] omezení počtu bodů / rozumné okno historie

## Budoucí využití
- [ ] kontrola průchodu hrou
- [ ] dohled správce
- [ ] reakce na SOS
- [ ] kontrola neaktivity nebo podezřelého chování

---

# PRIORITA 4 — HELP / SOS

## Stav
- [x] endpoint existuje
- [x] hráč může poslat pomoc

## Chybí
- [ ] admin panel pro help requesty
- [ ] seznam otevřených SOS
- [ ] poslední známá poloha hráče u SOS
- [ ] čas požadavku
- [ ] text zprávy
- [ ] stav řešení (`open`, `in_progress`, `closed`)
- [ ] možnost označit vyřešeno
- [ ] zobrazení SOS na dashboardu správce

---

# PRIORITA 5 — VÝSLEDOVKY

## Hráč
- [ ] player card / profil hráče
- [ ] body
- [ ] tým
- [ ] doba ve hře
- [ ] navštívená POI
- [ ] sebrané poklady
- [ ] progress
- [ ] výsledovka hráče
- [ ] přesunout SOS sem

## Admin
- [ ] výsledovka jedné hry
- [ ] přehled pořadí hráčů
- [ ] kdo našel co
- [ ] kdo je aktivní
- [ ] poslední poloha hráčů
- [ ] historie průchodů

## Globálně
- [ ] supersprávce výsledovka přes všechny hry

---

# PRIORITA 6 — PLAYER UX / MAP SCREEN

## Hlavní problémy
- [ ] levý horní box je technický, ne herní
- [ ] `GPS OK (67m)` nedává hráči smysl
- [ ] pravý horní panel nemá ještě finální význam
- [ ] spodní tlačítka zakrývají mapu
- [ ] `Obnovit` nemá jasný význam
- [ ] SOS nemá být na hlavní mapě
- [ ] poklad se chová příliš automaticky
- [ ] UI je stále víc utilita než hra

## Udělat
- [ ] player card trigger
- [ ] jednoduchý GPS stav místo technického textu
- [ ] přesunout SOS do player card
- [ ] stabilizovat horní pravý panel
- [ ] rozhodnout, zda tam bude:
  - [ ] konec hry
  - [ ] kontextový slot
  - [ ] aktuální úkol / fáze / časovka
- [ ] zmenšit invazivnost UI
- [ ] finalizovat průhledné glass panely
- [ ] promyslet bottom sheet místo tvrdých modalů

## POI vs. poklad
- [ ] POI může auto-open
- [ ] poklad se nemá auto-open
- [ ] u pokladu musí být pocit hledání
- [ ] stav `něco je poblíž`
- [ ] akce `prozkoumat`

---

# PRIORITA 7 — ONBOARDING / NÁVODY

## Chybí
- [ ] obecný návod, co je VentureOut a jak se to hraje
- [ ] návod / briefing pro konkrétní hru
- [ ] hráč musí pochopit:
  - [ ] co jsou POI
  - [ ] co jsou poklady
  - [ ] co je cílem hry
  - [ ] co znamená progress / body / výhra
  - [ ] jak funguje SOS
  - [ ] co dělat při problému

## Udělat
- [ ] obecná stránka „Jak to funguje“
- [ ] briefing konkrétní hry před vstupem do mapy
- [ ] napojit briefing na `intro_text`
- [ ] časem rozlišit:
  - [ ] intro
  - [ ] pravidla
  - [ ] speciální mechaniky hry

---

# PRIORITA 8 — XP / LEVEL / LEADERBOARD

## Směr
👉 hra nemá být jen jednorázová trasa, ale má dát hráči pocit růstu

## XP
- [ ] XP za návštěvu POI
- [ ] XP za nalezení pokladu
- [ ] XP za dokončení hry
- [ ] bonusové XP za objev / speciální událost

## Levely
- [ ] ne lineárně, ale exponenciálně / geometricky
- [ ] rychlý začátek
- [ ] vyšší levely těžší
- [ ] minimálně do levelu 15
- [ ] level z celkových XP
- [ ] progress do dalšího levelu

## Žebříček
- [ ] leaderboard podle XP
- [ ] pořadí hráče ve hře
- [ ] později pořadí týmů

## Stupnice hodností
- [ ] `level_scheme` v nastavení hry
- [ ] admin vybírá styl při založení hry
- [ ] varianty:
  - [ ] `military`
  - [ ] `adventure`
  - [ ] `mystic`

---

# PRIORITA 9 — MEDIA / OBSAH

## Hotovo
- [x] POI media přes `poi_media`
- [x] obrázky v detailu POI
- [x] YouTube v detailu POI

## Dále
- [ ] upload obrázků v adminu dotáhnout plně
- [ ] ukládání do `/public/uploads/games/{game_id}/pois/{poi_id}/`
- [ ] validace uploadu
- [ ] 0 až N příloh
- [ ] kombinace externí URL + vlastní foto + YouTube
- [ ] později stejný systém i pro poklady

---

# PRIORITA 10 — STABILIZACE / ÚKLID

- [ ] odstranit `.DS_Store`
- [ ] opravit `.gitignore`
- [ ] přesunout backupy mimo repo
- [ ] cleanup hotfixů
- [ ] odstranit warningy / notices
- [ ] verzované SQL migrace
- [ ] uklidit staré view, které už nemají žít vlastním layoutem

---

# NEJBLIŽŠÍ KONKRÉTNÍ KROK NA RÁNO

## Varianta A — nejpraktičtější
- [ ] napojit na shared header/footer:
  - [ ] `games/create.php`
  - [ ] `games/show.php`
  - [ ] `users/index.php`

## Varianta B — správný produktový krok
- [ ] navrhnout dashboard správce jedné hry:
  - [ ] výsledovka
  - [ ] help requesty
  - [ ] poslední známé polohy
  - [ ] seznam průchodů

## Varianta C — hráčský release krok
- [ ] briefing konkrétní hry
- [ ] player card
- [ ] výsledovka hráče