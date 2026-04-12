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

---

# 🔥 AKTUÁLNÍ PRIORITA (NEZTRATIT SMĚR)

## 🎯 Cíl:
👉 udělat první FUNKČNÍ HERU (ne jen mapu)

---

## 🟥 PRIORITA 1 — HERNÍ LOGIKA (KRITICKÉ)

- [ ] načítat POI do hráčské mapy
- [ ] načítat poklady do hráčské mapy
- [ ] proximity check (hráč v radiusu)
- [ ] endpoint: `/api/player/claim`
- [ ] logika sebrání pokladu
- [ ] zápis do `treasure_claims`
- [ ] respektování `limit sebrání`
- [ ] skrýt poklad po sebrání
- [ ] stav: „už sebráno“
- [ ] stav: „prázdné místo“

👉 výsledek:
hráč přijde → něco se stane → vidí výsledek

---

## 🟧 PRIORITA 2 — PLAYER DASHBOARD

- [ ] stránka `/player/dashboard`
- [ ] zobrazit:
  - [ ] nickname
  - [ ] hra
  - [ ] progress
- [ ] seznam:
  - [ ] sebraných pokladů
  - [ ] navštívených bodů
- [ ] tlačítko zpět na mapu
- [ ] jednoduchý „inventory“ view

👉 výsledek:
hráč chápe, co dělá

---

## 🟨 PRIORITA 3 — ADMIN PŘEHLED

- [ ] seznam hráčů ve hře
- [ ] poslední poloha hráče
- [ ] seznam SOS requestů
- [ ] detail hráče

👉 výsledek:
organizátor vidí, co se děje

---

## 🟩 PRIORITA 4 — NÁVODY A UX

- [ ] stránka „Návod pro hráče“
- [ ] stránka „Návod pro admina“
- [ ] link do menu
- [ ] krátká nápověda u formulářů:
  - [ ] radius
  - [ ] limit sebrání
  - [ ] režim hry

---

# 🧭 HERNÍ MECHANIKY (POKLADY)

- [ ] typy pokladů:
  - [ ] public
  - [ ] hidden
  - [ ] individual
  - [ ] team
- [ ] viditelnost na mapě
- [ ] navázání na POI
- [ ] samostatné poklady
- [ ] „tajný poklad“ (není na mapě)
- [ ] „vybráno → prázdné místo“

---

# 🧭 SELF-SERVICE REŽIM

- [ ] zobrazit POI na mapě
- [ ] odemykání POI podle vzdálenosti
- [ ] zobrazit příběh
- [ ] progress hráče
- [ ] dokončení hry
- [ ] fallback bez organizátora
- [ ] SOS chování:
  - [ ] vypnout
  - [ ] nebo jen logovat

---

# 🧭 MODEROVANÝ REŽIM (POZDĚJI)

- [ ] admin mapa hráčů
- [ ] live dashboard
- [ ] SOS panel
- [ ] broadcast zprávy
- [ ] výsledky
- [ ] chat (team / global)
- [ ] zásahy admina

---

# 🧹 STABILIZACE

- [ ] odstranit `.DS_Store`
- [ ] opravit `.gitignore`
- [ ] přesunout backupy mimo repo
- [ ] sjednotit názvy repository
- [ ] odstranit warningy
- [ ] cleanup hotfixů
- [ ] verzované SQL migrace

---

# ⚙️ WORKFLOW

- [ ] práce přes VS Code SSH
- [ ] malé commity
- [ ] push → server pull
- [ ] DB změny zapisovat do SQL

---

# 🚀 DALŠÍ KONKRÉTNÍ KROK

👉 IMPLEMENTOVAT:

- [ ] `/api/player/claim`
- [ ] TreasureRepository → claim()
- [ ] kontrola vzdálenosti
- [ ] zápis do DB
- [ ] návrat JSON (success / already claimed)

👉 tím vznikne PRVNÍ HERní SMYČKA