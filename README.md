# teambuild
web based hra gps

# Webová mapová teambuildingová hra

## Účel

Webová aplikace pro mobilní telefony pro venkovní hru s mapou, GPS a příběhem.

Hráči se registrují přezdívkou a systém si jejich identitu pamatuje pomocí cookie.

---

## 🍪 Session a cookie (DŮLEŽITÉ)

Po registraci hráče:

- vytvoří se `session_token`
- uloží se do DB (hash)
- nastaví se cookie s expirací **až 1 rok**

### Proč dlouhá cookie:

- možnost poslat pozvánky dopředu
- před-akční testování
- hráč se nemusí znovu registrovat
- QR kód funguje opakovaně

### Důležité:

- cookie patří ke konkrétní hře
- platnost hry je řízena `starts_at` / `ends_at`
- cookie ≠ aktivní hra

---

## 🧭 Princip hry

- každá hra má URL (slug)
- hráč přijde přes QR / odkaz
- zadá přezdívku
- dostane cookie
- čeká na start
- pak hraje

---

## 🧱 Stack

- PHP 8.x
- MariaDB (preferováno)
- OpenStreetMap
- Leaflet
- čistý JS

---

## 🚀 Vývojový plán

1. admin login
2. seznam her
3. založení hry
4. mapa + POI editor
5. pozvánky + QR
6. registrace hráče
7. GPS tracking
8. odemykání bodů
9. help systém
10. chat
11. výsledky

---

## 🧠 Filosofie

- jednoduchost
- žádný monolit
- rychlé MVP
- iterace
- použitelnost venku

prvotni heslo pro admina: admin123ZMENIT
