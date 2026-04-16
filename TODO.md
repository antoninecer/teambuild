# TODO

## Co je skutečně hotové
- [x] databáze + základní tabulky
- [x] struktura projektu
- [x] DB připojení (PHP)
- [x] admin uživatelé
- [x] admin login
- [x] základ admin dashboardu
- [x] seznam her + detail hry
- [x] vytvoření hry
- [x] invite flow + QR / link
- [x] registrace hráče (backend + frontend)
- [x] player session
- [x] mapa hráče (Leaflet)
- [x] GPS tracking
- [x] API: poloha
- [x] API: SOS (základní endpoint)
- [x] režim hry (`self_service` / `moderated`)
- [x] editor POI (včetně mapy)
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
- [x] poslední známá poloha hráče
- [x] čas posledního updatu
- [x] přesnost GPS
- [x] detail hráče s historií pohybu
- [x] detail hráče s historií průchodů (základ)
- [x] výsledovka v detailu hry (základ)
- [x] evidence průchodu POI přes `events` (technický základ)

---

# Kritická pravda o stavu projektu

## Není hotové produktově, i když je to částečně v kódu
- [ ] player card je dotažený herní modul
- [ ] výsledovka hráče je dotažený UX modul
- [ ] SOS je dotažený provozní systém pro organizátora
- [ ] POI návštěva znamená skutečnou interakci hráče, ne jen vstup do radiusu
- [ ] poklad má skutečný pocit hledání a odměny
- [ ] onboarding vysvětluje hráči cíl a pravidla konkrétní hry
- [ ] admin detail hry funguje jako živý operační cockpit

---

# Hlavní produktový princip odteď

## Nové pravidlo hry
- [x] technická blízkost != herní objev
- [x] hráč musí mít možnost vědomé akce `Prozkoumat okolí`
- [x] POI / treasure se nemají počítat jako navštívené jen za průjezd kolem
- [x] teprve průzkum / interakce má vést k odhalení obsahu
- [x] teprve dokončení interakce má vést k zápisu postupu a bodů

## Herní stavový model
- [ ] `nearby` — místo je dost blízko na průzkum
- [ ] `explored` — hráč klikl `Prozkoumat okolí`
- [ ] `opened` / `engaged` — hráč otevřel obsah nebo začal interakci
- [ ] `completed` / `claimed` — hráč dokončil POI nebo získal poklad

---

# PRIORITA 1 — HERNÍ SMYČKA NA MAPĚ

## Cíl
👉 přestat mít jen mapu s body a udělat z toho jasnou hru:

1. hráč se pohybuje
2. hra oznámí, že je něco poblíž
3. hráč zvolí `Prozkoumat okolí`
4. hra odhalí relevantní POI / stopu / treasure
5. hráč obsah přečte, poslechne nebo splní interakci
6. teprve potom se zapíše průchod a odměna

## Udělat hned
- [ ] zrušit automatický význam `poi_visited` jen za vstup do radiusu
- [ ] přestat auto-otevírat POI bez akce hráče
- [ ] zavést stav „Místo je dost blízko na průzkum“
- [ ] přidat univerzální akci `Prozkoumat okolí`
- [ ] po průzkumu vracet hráči to, co je herně relevantní v okolí
- [ ] rozlišit technický proximity stav od skutečné návštěvy / objevu
- [ ] u průzkumu rozhodovat, zda:
  - [ ] otevřít rovnou jeden objekt
  - [ ] nebo nabídnout výběr více objektů v dosahu
- [ ] při více objektech vracet hráči lidský výběr, ne technický seznam záznamů

## Když je v dosahu více objektů
- [ ] pokud je 1 relevantní objekt → otevřít rovnou
- [ ] pokud je více relevantních objektů → nabídnout výběr
- [ ] později zavést prioritu podle:
  - [ ] vzdálenosti
  - [ ] typu objektu
  - [ ] ruční priority z adminu
  - [ ] stavu hry / fáze hry
  - [ ] toho, zda už byl objekt objeven

---

# PRIORITA 2 — POI A TREASURE JAKO DVA ODLIŠNÉ ZÁŽITKY

## Směr
👉 POI nemá být totéž co poklad.

- POI = informace / příběh / kontext / stopa / úkol
- treasure = odměna / nález / něco skrytého / něco, co se odemyká

## Udělat
- [ ] přestat mít stejné chování POI a treasure
- [ ] POI otevírat až po vědomém průzkumu
- [ ] treasure neukazovat příliš automaticky
- [ ] zavést stav `něco je poblíž`
- [ ] zavést pocit hledání u treasure

## Hlavní model treasure
- [x] treasure nemá být hlavní plošně automatická věc na mapě
- [x] splnění / dokončení POI může být trigger k odhalení treasure
- [ ] doplnit vazbu `POI -> unlocks treasure`
- [ ] po dokončení POI ukázat hráči, že odhalil další stopu nebo možnost nálezu
- [ ] umožnit i vedlejší typ treasure mimo hlavní trigger flow

## Doporučené typy treasure
- [ ] `primary` — hlavní treasure odemykaný přes POI
- [ ] `bonus` — volný bonusový treasure
- [ ] `hidden` — treasure pouze přes indicii / stopu
- [ ] `team` — treasure vázaný na tým nebo společnou akci

---

# PRIORITA 3 — ENDPOINTY A EVENT MODEL PRO NOVOU HRU

## Backend změny
- [ ] navrhnout nový endpoint `POST /api/player/explore`
- [ ] endpoint vezme aktuální polohu hráče
- [ ] endpoint najde relevantní objekty v dosahu
- [ ] endpoint rozhodne, zda vrátit jeden detail nebo seznam možností
- [ ] endpoint zapíše správný event podle typu akce

## Events
- [ ] přestat spoléhat jen na `poi_visited`
- [ ] zavést jemnější eventy, minimálně konceptuálně:
  - [ ] `poi_nearby`
  - [ ] `poi_discovered`
  - [ ] `poi_opened`
  - [ ] `poi_completed`
  - [ ] `treasure_nearby`
  - [ ] `treasure_discovered`
  - [ ] `treasure_claimed`
- [ ] rozhodnout, které eventy jsou jen interní analytika a které se počítají do výsledků

## Datový model
- [ ] doplnit DB / model tak, aby šlo rozlišit:
  - [ ] blízkost
  - [ ] objevení
  - [ ] otevření
  - [ ] dokončení
- [ ] doplnit vazbu mezi POI a treasure
- [ ] doplnit prioritu objektu
- [ ] doplnit možnost skrýt treasure do doby triggeru

---

# PRIORITA 4 — PLAYER UX / MAP SCREEN

## Hlavní problémy dnes
- [ ] levý horní box je technický, ne herní
- [ ] `GPS OK (67m)` nedává hráči hlavní smysl
- [ ] pravý horní panel nemá finální roli
- [ ] spodní tlačítka zakrývají mapu
- [ ] `Obnovit` nemá jasný význam
- [ ] SOS nemá být na hlavní mapě jako hlavní akce
- [ ] UI je stále víc utilita než hra

## Udělat
- [ ] udělat z mapy skutečný herní screen
- [ ] hlavní message má být herní, ne technická
- [ ] GPS stav přesunout do sekundární role
- [ ] zobrazovat kontextový panel:
  - [ ] něco je poblíž
  - [ ] můžeš prozkoumat okolí
  - [ ] odhalil jsi stopu
  - [ ] můžeš pokračovat
- [ ] přidat primární CTA podle situace:
  - [ ] `Prozkoumat okolí`
  - [ ] `Otevřít stopu`
  - [ ] `Pokračovat v průzkumu`
  - [ ] `Sebrat poklad`
- [ ] přesunout SOS do player card
- [ ] promyslet bottom sheet místo tvrdých modalů
- [ ] zachovat jednoduchost ovládání na mobilu

## Player card
- [ ] stabilizovat player card jako jedno hlavní místo pro:
  - [ ] profil hráče
  - [ ] body / XP
  - [ ] progress
  - [ ] tým
  - [ ] SOS
  - [ ] stav hry

---

# PRIORITA 5 — ONBOARDING / BRIEFING HRY

## Kritický problém
Hráč dnes nemá dost jasně vysvětlené:
- [ ] co je cílem konkrétní hry
- [ ] co jsou POI
- [ ] co jsou poklady
- [ ] co znamenají body a progress
- [ ] jak funguje SOS
- [ ] co má dělat, když něco nejde

## Udělat
- [ ] obecná stránka „Jak VentureOut funguje“
- [ ] briefing konkrétní hry před vstupem do mapy
- [ ] napojit briefing na `intro_text`
- [ ] oddělit do budoucna:
  - [ ] intro
  - [ ] pravidla
  - [ ] speciální mechaniky hry
- [ ] vysvětlit hráči princip:
  - [ ] jsi poblíž místa
  - [ ] prozkoumej okolí
  - [ ] objev obsah
  - [ ] dokonči interakci
  - [ ] získej odměnu / postup

---

# PRIORITA 6 — DASHBOARD SPRÁVCE / OPERAČNÍ COCKPIT

## Cíl
Dashboard správce nemá být jen menu, ale živý přehled jedné hry.

## Udělat
- [ ] otevřená volání o pomoc
- [ ] poslední aktivita hráčů
- [ ] poslední známá poloha hráčů
- [ ] přehled průchodů POI / treasure
- [ ] kdo je aktivní / neaktivní
- [ ] kdo naposledy něco objevil
- [ ] mapa jedné hry s hráči
- [ ] rychlý přístup k detailu hráče
- [ ] dashboard jedné hry jako operační obrazovka

---

# PRIORITA 7 — HELP / SOS JAKO OPRAVDOVÝ PROVOZNÍ MODUL

## Stav dnes
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

# PRIORITA 8 — VÝSLEDOVKY, BODY, XP, PROGRESS

## Důležitá poznámka
Nejdřív musí být jasná herní smyčka. Až potom má smysl body a levely rozšiřovat.

## Hráč
- [ ] player card / profil hráče
- [ ] body
- [ ] tým
- [ ] doba ve hře
- [ ] objevená POI
- [ ] dokončená POI
- [ ] sebrané poklady
- [ ] progress
- [ ] výsledovka hráče

## Admin
- [ ] výsledovka jedné hry
- [ ] přehled pořadí hráčů
- [ ] kdo našel co
- [ ] kdo je aktivní
- [ ] poslední poloha hráčů
- [ ] historie průchodů

## XP
- [ ] XP za dokončení POI
- [ ] XP za nalezení treasure
- [ ] XP za dokončení hry
- [ ] bonusové XP za speciální objev / událost

---

# PRIORITA 9 — LEVELY / HODNOSTI / STYL HRY

## Směr
👉 hra nemá být jen jednorázová trasa, ale má dát hráči pocit růstu.

## Levely
- [ ] level z celkových XP
- [ ] progress do dalšího levelu
- [ ] rychlý začátek, později těžší růst
- [ ] minimálně do levelu 15

## Hodnosti
- [ ] `level_scheme` v nastavení hry
- [ ] admin vybírá styl při založení hry
- [ ] varianty:
  - [ ] `military`
  - [ ] `adventure`
  - [ ] `mystic`
  - [ ] později případně kombinované schéma

## Poznámka
Hodnosti až po stabilizaci toho, za co hráč skutečně získává XP.

---

# PRIORITA 10 — MEDIA / OBSAH

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
- [ ] později stejný systém i pro treasure

---

# PRIORITA 11 — TRACKING / TREKOVATELNOST

## Máme základ
- [x] `updateLocation()`
- [x] logování polohy
- [x] poslední známá poloha hráče
- [x] čas posledního updatu
- [x] přesnost GPS
- [x] detail hráče s posledními body pohybu

## Udělat
- [ ] možnost ukázat trasu na mapě (v detailu)
- [ ] omezení počtu bodů / rozumné okno historie
- [ ] využít tracking pro operační dashboard
- [ ] využít tracking pro SOS a dohled

---

# PRIORITA 12 — STABILIZACE / ÚKLID

- [ ] odstranit `.DS_Store`
- [ ] opravit `.gitignore`
- [ ] přesunout backupy mimo repo
- [ ] cleanup hotfixů
- [ ] odstranit warningy / notices
- [ ] verzované SQL migrace
- [ ] uklidit staré view, které už nemají žít vlastním layoutem
- [ ] zpevnit session cookie (`httponly`, `secure`, `samesite`)
- [ ] respektovat `session_cookie_days`
- [ ] doplnit kontrolu `valid_from` / `valid_to` u invites
- [ ] lépe vynucovat časové okno hry (`starts_at`, `ends_at`)

---

# Co dává největší smysl udělat ještě dnes

## Varianta doporučená jako další implementační krok
- [ ] přepsat flow hráčské mapy z auto-open na `Prozkoumat okolí`
- [ ] navrhnout a zavést endpoint `POST /api/player/explore`
- [ ] přestat zapisovat návštěvu POI jen za proximity
- [ ] vracet 1 objekt nebo seznam objektů v dosahu
- [ ] připravit trigger `POI -> treasure`

## Co nebrat dnes jako první
- [ ] neřešit dnes nejdřív hodnosti
- [ ] neřešit dnes nejdřív další CRUD stránky
- [ ] neřešit dnes nejdřív kosmetické drobnosti adminu

## Pracovní pravidlo
👉 Nejprve zafixovat herní smyčku hráče. Teprve potom rozšiřovat výsledovky, levely a širší admin.

---

# PRIORITA 6 — GAME BLUEPRINT JSON + IMPORT / SYNC

## Směr
👉 hra nemá vznikat jen ručně přes pomalý admin formulář.

Potřebujeme tři způsoby tvorby obsahu:
- [ ] `Game Blueprint JSON` — návrh hry od stolu / po brainstormingu / přes GPT
- [ ] `Quick Capture` — rychlé zachycení místa v terénu
- [ ] admin formulář — ruční detailní zásah a dolaďování

## Game Blueprint JSON
- [ ] navrhnout první verzi `game_blueprint.schema.json`
- [ ] blueprint musí být kanonický návrh hry, ne jen export DB tabulek
- [ ] blueprint musí pokrýt minimálně:
  - [ ] metadata hry
  - [ ] intro / briefing / outro texty
  - [ ] POI
  - [ ] treasures
  - [ ] visibility pravidla
  - [ ] trigger `POI -> unlocks treasure`
  - [ ] scoring základy
- [ ] každý importovatelný objekt musí mít stabilní klíč, ne jen název:
  - [ ] `game_key`
  - [ ] `poi_key`
  - [ ] `treasure_key`
  - [ ] později `task_key`

## Import režimy
- [ ] `create new` — vytvořit novou hru z blueprintu
- [ ] `replace existing` — kompletně přepsat návrhovou strukturu existující hry
- [ ] `merge / sync existing` — aktualizovat existující hru podle stabilních klíčů objektů
- [ ] u `replace existing` vždy udělat preview a zálohu před importem
- [ ] u `merge / sync` nemazat provozní data hráčů bez explicitního reset režimu

## Validace a preview importu
- [ ] validace struktury blueprintu před importem
- [ ] validace logiky hry před importem
- [ ] kontrola duplicitních klíčů objektů
- [ ] kontrola referencí (`POI -> treasure`, `requires`, `unlocks`)
- [ ] preview importu musí ukázat:
  - [ ] kolik objektů vznikne
  - [ ] kolik se aktualizuje
  - [ ] kolik se smaže
  - [ ] zda zůstávají zachována runtime data hráčů

## Oddělení návrhových a provozních dat
- [ ] jasně oddělit návrhovou vrstvu:
  - [ ] hra
  - [ ] POI
  - [ ] treasures
  - [ ] tasks
  - [ ] texty
  - [ ] scoring pravidla
- [ ] od provozní vrstvy:
  - [ ] players
  - [ ] player_positions
  - [ ] events
  - [ ] treasure_claims
  - [ ] help_requests
  - [ ] leaderboard / progress
- [ ] importer nesmí bezmyšlenkovitě rozbíjet aktivní nebo odehranou hru

## První praktický cíl
- [ ] navrhnout první verzi blueprint formátu
- [ ] vytvořit jeden ukázkový blueprint
- [ ] až potom napsat importér
- [ ] první importér má umět bezpečně jen `create new`
- [ ] `merge / sync` dodělat až po odzkoušení formátu

---

# PRIORITA 7 — QUICK CAPTURE / TERÉNNÍ ZÁCHYT

## Směr
👉 admin formulář je pomalý pro situace typu: „vidím v terénu kapličku, tak ji tam dám“.

Potřebujeme rychlou cestu pro spontánní záchyt místa.

## Cíl
- [ ] mít velmi rychlé přidání bodu z terénu
- [ ] vzít aktuální GPS
- [ ] volitelně přidat fotku
- [ ] volitelně přidat krátkou poznámku
- [ ] uložit jako `draft`, ne nutně hned jako finální POI

## Datový model terénního záchytu
- [ ] rozhodnout, zda použít:
  - [ ] `pois.status = draft / active / hidden`
  - [ ] nebo samostatnou tabulku `poi_drafts`
- [ ] draft objekt musí umět minimálně:
  - [ ] GPS
  - [ ] pracovní název
  - [ ] typ návrhu (`poi`, `treasure`, `draft`)
  - [ ] fotku
  - [ ] poznámku
  - [ ] čas vytvoření
  - [ ] autora

## Workflow
- [ ] rychlá akce `Přidat místo z aktuální GPS`
- [ ] rychlá akce `Vyfotit a uložit jako draft`
- [ ] pozdější převod draftu na:
  - [ ] POI
  - [ ] treasure
  - [ ] součást blueprintu
- [ ] možnost draft skrýt / archivovat / smazat

## Produktový princip
- [ ] admin formulář není hlavní kreativní nástroj
- [ ] hlavní kreativní vstupy jsou:
  - [ ] blueprint návrh hry
  - [ ] quick capture v terénu
- [ ] admin editace je hlavně pro dolaďování, opravy a přesné ruční zásahy
