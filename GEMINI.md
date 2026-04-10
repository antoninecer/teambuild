# Local AI Development Policy

Tento projekt používá **lokální LLM modely**.

Cíl:
- šetřit tokeny
- minimalizovat cloud usage
- mít kontrolu nad vývojem

---

## 🧠 Používané modely

Lokální (Ollama):

- coder → deepseek-coder
- qwen → qwen2.5

Použití:

- coder → psaní kódu
- qwen → vysvětlení, debugging

---

## ⚠️ Zásady

- vždy preferuj lokální modely
- cloud jen pokud nutné
- neplýtvej tokeny
- nepoužívej velké modely na jednoduché úkoly

---

## 🔁 Iterativní vývoj

Každý úkol:

1. návrh
2. implementace
3. test
4. refaktor
5. pokračuj

---

## 📌 Persistentní progres (KRITICKÉ)

Projekt MUSÍ evidovat stav.

Použij:

- TODO.md
- nebo checklist v README

Formát:

- [ ] todo
- [x] done

Cíl:

- možnost přerušit práci
- pokračovat další den
- neztratit kontext

---

## 🧱 Architektura

- modulární struktura
- malé soubory
- oddělená logika
- žádné velké skripty

---

## 🐘 PHP pravidla

- controllers
- services
- repositories
- žádný mix HTML + logiky

---

## 🤖 Autonomní režim

Agent:

- neptej se zbytečně
- dělej rozumné předpoklady
- pokračuj ve vývoji

---

## 🎯 Priorita

Ne demo.

👉 Funkční systém pro reálnou akci

---

## 🧭 Co je důležité

- jednoduchost
- spolehlivost
- UX venku
- rychlost

---

## ✅ Shrnutí

- lokální modely
- šetřit tokeny
- ukládat progres
- iterovat
- dokončit funkční celek

