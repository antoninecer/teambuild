# TODO.md — stav projektu po dnešku

Datum: průběžný pracovní souhrn po poslední sérii oprav

---

## 1. Co je hotové

### Bezpečnost a role
- Superadmin a herní admin jsou oddělení.
- Herní admin vidí jen své přidělené hry.
- Herní admin nevidí správu uživatelů.
- Zakládání uživatelů je jen pro superadmina.
- Zakládání her je jen pro superadmina.
- Přiřazování adminů ke hrám funguje.
- Odebrání admina ze hry funguje.
- Detail cizí hry přes URL je zamčený.
- Detail cizího hráče přes URL je zamčený.
- POI, poklady a invites jsou zamčené podle `game_id`.
- SOS a eventy v admin headeru jsou filtrované podle přidělených her.
- `acknowledge` a `resolve` nejdou použít na cizí SOS jen přes ruční URL.

### Hra a obsah
- Telefon při registraci hráče je přidaný.
- Telefon je vidět v detailu hráče.
- Obecný návod pro hráče je přidaný do hráčského rozhraní.
- Briefing konkrétní hry se čte z DB:
  - `intro_text`
  - `objective_text`
  - `player_guide_text`
- Tyto texty jdou vytvářet i editovat v adminu.
- Detail hry v adminu tyto texty ukazuje.

### SOS a alerty
- SOS pípá.
- Kliknutí na SOS umí převzít případ.
- Po převzetí se přechází na detail hráče.
- Pokud SOS zůstane `open`, alarm se znovu připomene přibližně po 30 sekundách.
- Po převzetí nebo uzavření se reminder zastaví.

### Detail hráče
- Kliknutí na historii pohybu posouvá mapu na zvolený bod.
- Mění se text k zobrazované poloze.
- Odkaz do map se mění podle právě zobrazeného bodu.
- Trasa je defaultně skrytá.
- Dá se zobrazit / skrýt na vyžádání.

### Poklady
- Opraven bug u typu „public / první bere“.
- Takový poklad jde sebrat opravdu jen jednou celkem.
- Po prvním sebrání už se znovu nenabízí ostatním hráčům.

### Hráčský dashboard
- Opraven bug s výsledovkou vnořenou do špatného modalu.
- Výsledovka už není skrytá uvnitř jiného okna po kliknutí.

---

## 2. Co je rozdělané nebo k dočištění

### A. Hráčský dashboard je stále moc velký
`resources/views/player/dashboard.php` je pořád přerostlý soubor.

Je potřeba ho rozsekat minimálně na:
- `resources/views/player/partials/map_shell.php`
- `resources/views/player/partials/player_card.php`
- `resources/views/player/partials/poi_modal.php`
- `resources/views/player/partials/treasure_modal.php`
- `resources/views/player/partials/results_modal.php`

A později rozdělit i JS logiku na menší soubory.

### B. Přehrávání textu v POI / treasure
Tohle je stále citlivé místo.
Potřebujeme stabilně udělat:
- Přehrát
- Pozastavit
- Pokračovat
- Zastavit
- Zavření modalu = stop
- Přepnutí na jiné POI / treasure = stop předchozího čtení

Pozor:
- předchozí pokusy rozbily logiku dashboardu
- další zásah dělat už jen opatrně a ideálně nad aktuálním posledním stavem

### C. Veřejná výsledovka bez loginu
Směr je schválený:
- bez loginu
- bez tokenu
- jen bezpečná data
- žádná poloha
- žádný telefon
- žádné SOS
- žádné interní admin informace

Veřejně zobrazovat:
- pořadí
- nickname
- body
- počet POI
- počet pokladů
- poslední bodovaný úsek / checkpoint
- čas posledního bodovaného postupu

Technicky:
- samostatná veřejná stránka
- samostatný JSON endpoint
- auto-refresh asi 1× za minutu
- nesmí se reloadovat celá stránka
- nesmí to odscrollovat nahoru
- nesmí se rozbít zoom

### D. Markery na mapě
- [x] odlišit marker hráče od POI a treasure
- [x] vizuálně odlišit sebraný poklad (zašednutí)
- [ ] ideálně i vizuálně odlišit aktivní cíl / checkpoint (zatím odloženo)

---

## 3. Otevřené návrhy / rozhodnutí

### SOS pravidla
Není finálně rozhodnuto.

Aktuální úvaha:
- SOS nemá být zadarmo
- mělo by být penalizované
- aby se nezneužívalo jako herní zkratka

Možné varianty:
- každé SOS = pevná bodová penalizace
- stupňovaná penalizace
- po více SOS vyřazení z pořadí

Je třeba rozhodnout přesné pravidlo a pak ho propsat do:
- UI textu
- backendu
- výsledovky

### Důležité quest itemy
Proběhla úvaha, že některé předměty nejsou obyčejné poklady, ale klíčové herní itemy.

Typický příklad:
- klíč od trezoru
- důležitý předmět nutný k dohrání

Budoucí směr:
- možná speciální typ itemu
- možnost předat jinému hráči
- možnost relokace na mapu
- viditelný / skrytý stav

Zatím neimplementovat.
Nejdřív držet stabilní současnou logiku.

---

## 4. Co udělat příště jako první

### Priorita 1
Stabilně opravit přehrávání textu v hráčském dashboardu:
- play / pause / resume / stop
- bez rozbití explore flow
- bez rozbití modalů

### Priorita 2
Rozsekat `dashboard.php` na partialy, aby další zásahy byly bezpečnější.

### Priorita 3
Udělat veřejnou výsledovku bez loginu:
- bezpečná data
- checkpoint / poslední bodovaný úsek
- auto-refresh bez reloadu celé stránky

### Priorita 4
Odlišit markery:
- hráč
- POI
- treasure

---

## 5. Důležité poznámky pro další pokračování

- Vždy pracovat nad posledním aktuálním tar stavem.
- Nevracet se ke starým verzím souborů.
- U `dashboard.php` nedělat malé ruční zásahy naslepo.
- Když se sahá do dashboardu, je lepší vracet celý opravený soubor.
- `public first claim` treasure bug je opravený a nevracet se k agresivním přepisům repository bez důvodu.
- Přístupová práva herních adminů jsou teď kritická část, která už funguje a nemá se rozbít.

---

## 6. Krátké shrnutí stavu
Projekt už je provozně mnohem dál než na začátku:
- role jsou oddělené
- admini vidí jen své hry
- SOS je použitelnější
- detail hráče je výrazně lepší
- briefing hry a návody jsou v systému
- jednorázové poklady se chovají správně

Největší technický dluh teď není backend bezpečnost, ale:
- přerostlý hráčský dashboard
- přehrávání textu
- veřejná výsledovka
- vizuální odlišení mapových prvků
