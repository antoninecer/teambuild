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

potrebuju pokud je popis jak mista tak pokladu aby bylo mozne precist objevi se nekde na mape nejake info modul a moznost hlasem precist umis to ?
Thought for 5s

Ano, tohle umím navrhnout a je to velmi dobrý krok.

A navíc bych to udělal ve dvou vrstvách, aby ses nezasekl na složitosti.

Co přesně bych udělal

Když hráč:

dorazí do bodu,
nebo najde poklad,

tak se otevře informační panel nad mapou:

název
popis
příběh
obrázek, pokud existuje
tlačítko:
Přečíst nahlas
případně Zastavit čtení
Nejrychlejší verze
1. Info panel v mapě

Na player dashboardu přidat skrytý panel, který se otevře:

po vstupu do radiusu POI
po claimnutí pokladu
nebo kliknutím na marker

Obsah:

name
story_text nebo description
u treasure name + description
2. Čtení hlasem přes prohlížeč

Použil bych hned:

window.speechSynthesis

To je nejrychlejší, protože:

nic negeneruješ na serveru
nemusíš ukládat audio
funguje to hned
na telefonu to často funguje překvapivě dobře

Například:

vezmeš text
vytvoříš SpeechSynthesisUtterance
přehraješ

To je ideální MVP.

Lepší verze později

Až budeš chtít hezčí zážitek:

předgenerované audio u POI
předgenerované audio u pokladů
možnost:
přehrát originální audio
nebo fallback na browser TTS

To znamená:

ručně nahraný hlas
nebo AI voice
a když chybí audio, použije se speech synthesis
Co bych doporučil teď
Pro POI

Číst:

story_text
a když není, tak description
Pro poklad

Číst:

description
a když není, tak name
UX návrh

Po příchodu k bodu:

vyskočí panel
tlačítka:
Přehrát hlasem
Zavřít
případně Pokračovat

To je přesně ten moment, kdy hra začne působit živě.

Technicky je to snadné

Ano, umím to, a šel bych tímto pořadím:

krok 1

Info modul v player mapě

krok 2

Speech synthesis tlačítko

krok 3

Napojení na:

POI unlock
treasure claim
Důležitá poznámka

Na mobilech může být potřeba, aby se hlas spustil až po interakci uživatele:

kliknutí na tlačítko
ne automaticky bez dotyku

Takže pro první verzi bych nedělal:

automatické čtení samo od sebe

Ale:

otevře se panel
hráč klikne Přečíst nahlas

To je spolehlivější.

Můj verdikt

Ano.
A je to velmi dobrý nápad, protože:

POI dostanou atmosféru
poklady budou působit živě
sekretariát to pochopí
hráč dostane jasnou akci

Teď bych to udělal v pořadí:
info panel → čtení hlasem → claim/unlock napojení

Pošli mi pak:

resources/views/player/dashboard.php
app/Controllers/Player/PlayerController.php

a uděláme první verzi panelu i s tlačítkem Přečíst nahlas.