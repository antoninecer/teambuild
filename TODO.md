# TODO

## Hotovo
- [x] založit databázi
- [x] vytvořit základní tabulky
- [x] vložit admin uživatele
- [x] vložit testovací hru
- [x] vložit první týmy
- [x] vložit první POI
- [x] vytvořit strukturu projektu
- [x] připojení k DB v PHP
- [x] admin login
- [x] admin dashboard
- [x] seznam her
- [x] formulář založení hry
- [x] detail hry
- [x] editor POI
- [x] pozvánky + QR / link
- [x] registrace hráče (backend)
- [x] registrace hráče (frontend)
- [x] player session cookie
- [x] dashboard hráče
- [x] API pro polohu (backend)
- [x] API pro žádost o pomoc (backend)
- [x] základ správy uživatelů administrace
- [x] nasazení na play.rightdone.eu
- [x] HTTPS / certifikát
- [x] základní invite flow do hry

---

## Teď děláme
- [ ] přepínač režimu hry: samostatná / s organizátorem
- [ ] propsání režimu hry do adminu
- [ ] propsání režimu hry do player flow
- [ ] první verze pokladů
- [ ] první zobrazení POI v hráčské mapě

---

## Režim hry
- [ ] přidat do DB sloupec `operation_mode`
- [ ] default nastavit na `self_service`
- [ ] podporované hodnoty:
  - [ ] `self_service`
  - [ ] `moderated`
- [ ] upravit `GameRepository` pro čtení `operation_mode`
- [ ] upravit `GameRepository` pro zápis `operation_mode`
- [ ] upravit admin formulář založení hry
- [ ] upravit detail hry
- [ ] zobrazit režim hry v admin seznamu
- [ ] připravit podmínky pro budoucí moderovaný dashboard

---

## Self-service režim
- [ ] zobrazit POI v hráčské mapě
- [ ] načítat POI pro konkrétní hru
- [ ] vykreslit POI markery do Leaflet mapy
- [ ] proximity check pro POI
- [ ] odemykání POI podle vzdálenosti
- [ ] zobrazení příběhu po příchodu na bod
- [ ] progress hráče
- [ ] konec hry / dokončení trasy
- [ ] základní fallback bez organizátora
- [ ] definovat chování SOS v self-service režimu
  - [ ] vypnout
  - [ ] nebo jen informativní režim

---

## Poklady
- [ ] navrhnout DB tabulku `treasures`
- [ ] navrhnout DB tabulku `treasure_claims`
- [ ] připravit SQL skript pro lokál i server
- [ ] přidat `TreasureRepository`
- [ ] přidat admin správu pokladů
- [ ] přidat poklad do detailu hry
- [ ] možnost navázat poklad na POI
- [ ] možnost mít poklad i samostatně mimo POI
- [ ] definovat typ pokladu:
  - [ ] public
  - [ ] hidden
  - [ ] individual
  - [ ] team
- [ ] definovat viditelnost na mapě
- [ ] definovat limit sebrání
- [ ] vykreslit viditelné poklady do hráčské mapy
- [ ] přidat claim logiku
- [ ] přidat stav „už sebráno“
- [ ] přidat stav „prázdné místo“
- [ ] přidat první jednoduchou „inventární kartu“ hráče

---

## Moderovaný režim — později
- [ ] admin sledování hráčů na mapě
- [ ] help systém (zpracování v adminu)
- [ ] SOS panel pro organizátora
- [ ] broadcast zprávy od organizátora
- [ ] live dashboard hry
- [ ] výsledky (tabulky)
- [ ] chat room / týmový chat
- [ ] moderátorské zásahy do hry

---

## Správa uživatelů a práv
- [ ] vytvoření nového uživatele
- [ ] změna hesla uživatele
- [ ] aktivace / deaktivace uživatele
- [ ] přiřazení správce ke konkrétní hře
- [ ] role `game_admin`
- [ ] role `editor`
- [ ] seznam správců hry

---

## Stabilizace a úklid
- [ ] odstranit `.DS_Store` z projektu
- [ ] doladit `.gitignore`
- [ ] přesunout backupy mimo repo
- [ ] přesunout archivní `.tar.gz` mimo repo
- [ ] sjednotit názvy tabulek a repository
- [ ] projít PHP warningy / notices
- [ ] odstranit ruční hotfixe, které už nepatří do finální verze
- [ ] zapsat DB změny do verzovaných SQL souborů

---

## Workflow
- [ ] změny provádět na serveru přes VS Code Remote SSH
- [ ] po každé logické změně commit
- [ ] push do gitu
- [ ] lokál držet jako mirror přes pull
- [ ] každou DB změnu zapisovat do SQL souboru v repu

---

## Nejbližší konkrétní krok
- [ ] přidat `operation_mode` do DB a admin formuláře hry