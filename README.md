# AuctionChain – Aukciona platforma sa real-time licitiranjem

Seminarski rad iz predmeta **Internet programiranje** (FTN Čačak, RSI/IT 2026).

Veb aplikacija u **Laravel 11** frameworku: server-side rendering (Blade), AJAX za real-time licitiranje, virtuelni escrow sistem za zaštitu kupca i prodavca.

---

## Sadržaj
1. [Šta aplikacija radi](#šta-aplikacija-radi)
2. [Tehnologije](#tehnologije)
3. [Preduslovi (šta instalirati)](#preduslovi)
4. [Pokretanje korak po korak](#pokretanje)
5. [Demo nalozi](#demo-nalozi)
6. [Struktura projekta](#struktura-projekta)
7. [Česti problemi](#česti-problemi)

---

## Šta aplikacija radi

Tri tipa korisnika:

- **Gost (neautentifikovan):** pregled aktivnih i završenih aukcija, pretraga, filtriranje po kategoriji/ceni, registracija.
- **Registrovani korisnik:** postavljanje aukcija sa slikama, licitiranje u realnom vremenu (AJAX), praćenje svojih aukcija i ponuda, escrow zaključavanje sredstava, potvrda prijema robe, otvaranje sporova, notifikacije.
- **Administrator:** CRUD kategorija, moderacija aukcija, upravljanje korisnicima (suspenzija, balans), rešavanje sporova, statistika platforme.

**Escrow logika (srce aplikacije):** kada korisnik licitira, njegova sredstva se prebacuju iz `balance` u `locked_balance` (zaključano). Ako ga neko nadlicitira, sredstva mu se vraćaju. Kada aukcija završi, pobednik potvrđuje prijem i tek tada novac ide prodavcu. Ako ima problema, otvara se spor koji admin rešava.

---

## Tehnologije

| Sloj | Tehnologija |
|------|-------------|
| Backend framework | Laravel 11 (PHP 8.2+) |
| Baza | MySQL (preko Eloquent ORM) |
| Frontend | Blade templates (SSR) + Bootstrap 5 |
| Real-time | AJAX polling (vanilla JavaScript, fetch API) |
| Autentifikacija | Laravel session-based auth |

---

## Preduslovi

Pre pokretanja, instaliraj na svoj računar:

### 1. XAMPP (sadrži PHP + MySQL + Apache)
- Skini sa: https://www.apachefriends.org/
- Tokom instalacije dovoljno je čekirati **Apache** i **MySQL** (PHP ide automatski).
- Posle instalacije proveri verziju PHP-a — treba **8.2 ili novija**. (XAMPP 8.2.x ima PHP 8.2, što je dovoljno.)

> Alternativa XAMPP-u: možeš instalirati PHP i MySQL zasebno, ali XAMPP je najlakši za početak jer dobiješ i phpMyAdmin za bazu.

### 2. Composer (menadžer PHP paketa)
- Skini sa: https://getcomposer.org/download/
- Instaliraj Composer-Setup.exe (Windows). On će sam pronaći PHP iz XAMPP-a.
- Provera u terminalu: `composer --version`

### 3. VS Code
- https://code.visualstudio.com/
- Preporučene ekstenzije (Extensions panel, `Ctrl+Shift+X`):
  - **PHP Intelephense** (autocomplete za PHP)
  - **Laravel Blade Snippets** (podrška za .blade.php)
  - **Laravel Extra Intellisense**

### 4. Node.js — NIJE potreban
Ovaj projekat NE koristi Vite/npm. CSS i JavaScript su statički fajlovi u `public/css/` i `public/js/`, pa **ne moraš da instaliraš Node ni da pokrećeš `npm install`**.

---

## Pokretanje

### Korak 1 — Pokreni XAMPP
Otvori **XAMPP Control Panel** i klikni **Start** pored:
- **Apache**
- **MySQL**

### Korak 2 — Napravi bazu podataka
1. U browseru otvori: `http://localhost/phpmyadmin`
2. Levo klikni **New** (Nova baza).
3. Ime baze: `auctionchain`
4. Collation (kodna šema): `utf8mb4_unicode_ci`
5. Klikni **Create**.

(Tabele ne praviš ručno — to radi Laravel u Koraku 6.)

### Korak 3 — Otvori projekat u VS Code
1. Otpakuj `auctionchain.zip` negde gde ćeš lako naći (npr. `C:\auctionchain` ili `Desktop`).
2. U VS Code: **File → Open Folder** → izaberi `auctionchain` folder.
3. Otvori terminal u VS Code: **Terminal → New Terminal** (`Ctrl+ö` ili `Ctrl+~`).

> Sve sledeće komande kucaš u tom terminalu, dok si pozicioniran u `auctionchain` folderu.

### Korak 4 — Instaliraj Laravel zavisnosti
```bash
composer install
```
Ovo skida Laravel i sve potrebne pakete u `vendor/` folder. Traje par minuta prvi put.

### Korak 5 — Napravi `.env` fajl i ključ
```bash
copy .env.example .env
```
(na Mac/Linux: `cp .env.example .env`)

Zatim generiši aplikacioni ključ:
```bash
php artisan key:generate
```

Otvori `.env` fajl i proveri da DB podešavanja odgovaraju XAMPP-u (podrazumevano je već dobro):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auctionchain
DB_USERNAME=root
DB_PASSWORD=
```
> Napomena: u XAMPP-u MySQL `root` korisnik podrazumevano nema lozinku, zato je `DB_PASSWORD` prazno.

### Korak 6 — Napravi tabele i učitaj demo podatke
```bash
php artisan migrate --seed
```
Ovo:
- napravi sve tabele u bazi (`migrate`),
- ubaci demo korisnike, kategorije i aukcije (`--seed`).

Ako sve prođe, videćeš ispis sa demo nalozima.

### Korak 7 — Pokreni server
```bash
php artisan serve
```
Aplikacija je sada dostupna na: **http://localhost:8000**

Otvori taj link u browseru. 🎉

---

## Demo nalozi

Posle `--seed`, možeš se prijaviti sa:

| Uloga | Email | Lozinka |
|-------|-------|---------|
| Administrator | `admin@auctionchain.test` | `admin123` |
| Korisnik | `marko@test.com` | `password` |
| Korisnik | `pavle@test.com` | `password` |
| Korisnik | `micovic@test.com` | `password` |

Admin panel je na: **http://localhost:8000/admin** (vidljiv tek kad se prijaviš kao admin).

---

## Struktura projekta

```
auctionchain/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Kontroleri (C u MVC)
│   │   │   ├── Admin/          # Admin kontroleri
│   │   │   ├── AuctionController.php
│   │   │   ├── BidController.php     # AJAX licitiranje
│   │   │   └── ...
│   │   └── Middleware/         # AdminMiddleware, EndExpiredAuctions
│   ├── Models/                # Eloquent modeli (M u MVC)
│   │   ├── Auction.php
│   │   ├── Bid.php
│   │   ├── User.php
│   │   └── ...
│   └── Services/
│       └── EscrowService.php  # SVA logika sa novcem (escrow)
├── database/
│   ├── migrations/            # Definicije tabela
│   └── seeders/               # Demo podaci
├── resources/
│   └── views/                 # Blade šabloni (V u MVC)
│       ├── layouts/
│       ├── auctions/
│       ├── admin/
│       └── ...
├── routes/
│   └── web.php                # Sve rute aplikacije
├── public/
│   ├── css/app.css            # Stilizacija
│   ├── js/app.js              # AJAX logika (polling + licitiranje)
│   └── uploads/               # Slike aukcija
└── .env                       # Konfiguracija (kreiraš je u Koraku 5)
```

### Gde je šta (po MVC slojevima)

- **Model** → `app/Models/` — tabele baze kao PHP klase, relationships.
- **View** → `resources/views/` — Blade HTML šabloni koje korisnik vidi.
- **Controller** → `app/Http/Controllers/` — prima zahtev, poziva model/servis, vraća view.
- **Servis (biznis logika)** → `app/Services/EscrowService.php` — izdvojena logika za novac da se ne ponavlja po kontrolerima.

### Kako radi real-time licitiranje

1. Na stranici aukcije (`auctions/show.blade.php`) JavaScript klasa `AuctionLive` (`public/js/app.js`) na svake **3 sekunde** šalje AJAX zahtev na `/auctions/{id}/poll`.
2. `BidController@poll` vrati JSON sa trenutnom cenom i listom ponuda.
3. Kada korisnik pošalje ponudu, ide AJAX POST na `/auctions/{id}/bids` → `BidController@store` → `EscrowService@placeBid`.
4. Escrow zaključa sredstva, vrati novac prethodnom licitatoru, vrati JSON odgovor.

> Ovo je **polling** pristup (ne WebSocket) — usklađen sa zahtevom predmeta za server-side rendering + AJAX.

### Kako se aukcije završavaju

Umesto cron job-a (komplikovano za lokalno pokretanje), `EndExpiredAuctions` middleware proverava na svakom zahtevu da li je nekoj aukciji isteklo vreme i finalizuje je (postavi pobednika, pošalje notifikacije). Za seminarski je ovo sasvim dovoljno.

---

## Korisne komande

```bash
# Resetuj bazu i ponovo učitaj demo podatke (kad zezneš nešto)
php artisan migrate:fresh --seed

# Očisti keš ako se ponaša čudno
php artisan optimize:clear

# Lista svih ruta
php artisan route:list
```

---

## Česti problemi

**„could not find driver" pri migrate**
U XAMPP-u nije uključen `pdo_mysql`. Otvori `php.ini` (XAMPP → Apache → Config → php.ini), pronađi `;extension=pdo_mysql` i obriši `;` ispred. Restartuj Apache.

**„SQLSTATE[HY000] [1045] Access denied"**
Pogrešni DB podaci u `.env`. Proveri da je `DB_USERNAME=root` i `DB_PASSWORD=` (prazno) za XAMPP.

**„SQLSTATE[HY000] [2002] Connection refused"**
MySQL nije pokrenut u XAMPP Control Panel-u. Klikni Start pored MySQL.

**Slike se ne prikazuju**
Slike se čuvaju u `public/uploads/auctions/`. Proveri da taj folder postoji i da je upisiv. Demo aukcije koriste `public/uploads/placeholder.jpg`.

**Stranica je prazna / 500 greška**
Proveri da si uradio `php artisan key:generate` (Korak 5). Pogledaj `storage/logs/laravel.log` za detalje greške.

**„The stream or file ... could not be opened"**
Folder `storage/` nema dozvole za pisanje. Na Windows-u retko, na Mac/Linux: `chmod -R 775 storage bootstrap/cache`.

---

## Podela rada po MVC slojevima (predlog za tim)

| Član tima | Zadužen za |
|-----------|------------|
| Marko Spasojević | Modeli + EscrowService (poslovna logika, baza) |
| Pavle Tasić | Kontroleri + rute (obrada zahteva) |
| Marko Mićović | View-i + frontend (Blade, CSS, AJAX) |

Svako treba da razume i objasni svoj deo na odbrani.
