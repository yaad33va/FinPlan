# FinPlan

Asmeninių finansų planavimo sistema.

## 1. Sprendžiamo uždavinio aprašymas

### 1.1. Sistemos paskirtis

Projekto tikslas – sukurti sistemą, kuri padėtų žmonėms planuoti asmeninius finansus ir matyti visas savo išlaidas bei pajamas vienoje vietoje.

Naudotojas, norėdamas naudotis platforma, pirmiausia turi prie jos prisiregistruoti. Prisijungęs jis gali sukurti pajamų ir išlaidų kategorijas. Kiekvienai kategorijai galima nusistatyti vieno mėnesio biudžetą, o po biudžetu suvesti operacijas. Taip naudotojas mato, kiek biudžeto toje kategorijoje išnaudota ir kiek liko. Administratorius prižiūri platformos naudotojus.

### 1.2. Funkciniai reikalavimai

**Neregistruotas naudotojas gali:**

1. Peržiūrėti platformos pradinį langą;
2. Prisiregistruoti prie platformos;
3. Prisijungti prie sistemos.

**Registruotas naudotojas gali:**

1. Atsijungti nuo sistemos;
2. Sukurti kategoriją;
3. Peržiūrėti kategorijų sąrašą;
4. Redaguoti kategoriją;
5. Šalinti kategoriją;
6. Sukurti biudžetą pasirinktai kategorijai;
7. Peržiūrėti biudžetų sąrašą;
8. Redaguoti biudžetą;
9. Šalinti biudžetą;
10. Įvesti pajamų arba išlaidų operaciją;
11. Peržiūrėti operacijų sąrašą;
12. Redaguoti operaciją;
13. Šalinti operaciją;
14. Matyti kiekvieno biudžeto išnaudotą sumą ir likutį;
15. Peržiūrėti konkrečios kategorijos operacijas.

**Administratorius gali:**

1. Peržiūrėti visų naudotojų sąrašą;
2. Šalinti naudotojus;
3. Peržiūrėti ir šalinti bet kurio naudotojo netinkamas kategorijas, biudžetus ir operacijas;
4. Atlikti visus registruoto naudotojo veiksmus.

## 2. Sistemos architektūra

Sistemą sudaro:

- **Kliento pusė (Front-End)** – Blade šablonai, kuriuos serveryje sugeneruoja Laravel, o naršyklė atvaizduoja kaip HTML puslapius (Tailwind CSS, Vite);
- **Serverio pusė (Back-End)** – PHP Laravel;
- **Duomenų bazė** – MySQL.

## 3. Diegimas ir paleidimas

Reikalavimai: PHP ^8.3, Composer, Node.js ir npm, MySQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Sukurkite MySQL duomenų bazę `finplan` ir, jei reikia, atnaujinkite `DB_*` reikšmes faile `.env`. Tada:

```bash
php artisan migrate
npm run build      # arba `npm run dev` kūrimo metu
php artisan serve
```

Sistema pasiekiama adresu http://127.0.0.1:8000.

## 4. Testavimas

```bash
php artisan test
```

## Licencija

[MIT licenciją](https://opensource.org/licenses/MIT).
