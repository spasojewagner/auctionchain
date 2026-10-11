# AuctionChain

[![Tests](https://github.com/spasojewagner/auctionchain/actions/workflows/tests.yml/badge.svg)](https://github.com/spasojewagner/auctionchain/actions/workflows/tests.yml)

Onlajn aukciona platforma sa licitiranjem u realnom vremenu i **escrow** zaštitom za kupce i prodavce. Seminarski rad iz predmeta *Internet programiranje*, FTN Čačak.

Izrada: Marko Spasojević, Pavle Tasić, Marko Mićović.

---

## Tehnologije
- **Laravel 11** (PHP), MVC arhitektura
- **MySQL** + Eloquent ORM
- **Blade** (SSR) + AJAX za licitiranje u realnom vremenu (polling)
- **Bootstrap 5**, FontAwesome, AOS (preko CDN-a — nije potreban Node/npm)
- **Stripe** (uplata karticom, test režim) i **MetaMask** (povezivanje Web3 novčanika uz nalog)
- **Groq AI** (generisanje opisa aukcije iz teksta/slike + chatbot pomoćnik)
- **PHPUnit** testovi escrow logike i GitHub Actions

## Glavne funkcije
- Registracija, prijava, korisnički profil sa virtuelnim balansom (RSD)
- Kreiranje aukcija sa slikama (1–5), opcionom „Kupi odmah" cenom i trajanjem
- Uređivanje slika postojeće aukcije (dodavanje, brisanje, izbor glavne slike)
- Licitiranje uživo (AJAX, cena i ponude se osvežavaju automatski)
- Escrow logika: sredstva se zaključavaju pri licitiranju, vraćaju pri nadlicitaciji, isplaćuju prodavcu nakon potvrde prijema (detalji ispod)
- „Kupi odmah" — momentalna kupovina po fiksnoj ceni
- **Ocenjivanje prodavaca i kupaca** (dodatni task): nakon završene transakcije kupac i prodavac ocenjuju jedan drugog (1–5 zvezdica + komentar); jedna ocena po aukciji uz mogućnost izmene; prosečna ocena prodavca prikazuje se na njegovim aukcijama
- Sistem sporova (kupac ili prodavac otvara, administrator rešava)
- Uplata sredstava karticom preko Stripe-a; povezivanje MetaMask novčanika na profilu
- Email i in-app notifikacije
- AI generisanje opisa aukcije (iz naziva ili analizom slike — vision model)
- AI chatbot pomoćnik: vođeni meni sa temama (licitiranje, plaćanje, escrow, ocenjivanje...) + slobodna pitanja preko Groq AI
- Newsletter prijava (footer)
- Admin panel (kategorije, korisnici, aukcije, sporovi)

---

## Pokretanje projekta lokalno

### Preduslovi
- PHP **8.2+**
- Composer
- MySQL (npr. preko XAMPP/Laragon)

> Node/npm **nije potreban** — frontend biblioteke se učitavaju preko CDN-a.

### Koraci

**1. Kloniraj repozitorijum**
```bash
git clone https://github.com/spasojewagner/auctionchain
cd auctionchain
```

**2. Instaliraj PHP zavisnosti**
```bash
composer install
```

**3. Napravi `.env` fajl**
```bash
cp .env.example .env
```
(na Windows-u: `copy .env.example .env`)

**4. Generiši aplikacioni ključ**
```bash
php artisan key:generate
```

**5. Napravi bazu i podesi `.env`**

Napravi praznu MySQL bazu pod imenom `auctionchain`:
```sql
CREATE DATABASE auctionchain CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Pa u `.env` podesi pristup bazi (`DB_USERNAME`, `DB_PASSWORD`).

**6. Pokreni migracije i napuni demo podatke**
```bash
php artisan migrate --seed
```

**7. Pokreni server**
```bash
php artisan serve
```
Otvori **http://localhost:8000**

---

## Demo nalozi (nakon `--seed`)
| Uloga | Email | Lozinka |
|-------|-------|---------|
| Administrator | admin@auctionchain.test | admin123 |
| Korisnik | marko@test.com | password |
| Korisnik | pavle@test.com | password |
| Korisnik | micovic@test.com | password |

> Novi nalozi počinju sa balansom 0 — sredstva se uplaćuju karticom preko Stripe-a.
> Demo nalozi imaju početni balans radi testiranja.

---

## Opciona podešavanja (eksterni servisi)

Aplikacija radi i bez ovih ključeva — samo te konkretne funkcije neće biti aktivne.

### Stripe (uplata karticom)
1. Napravi nalog na https://dashboard.stripe.com i prebaci na **Test mode**.
2. Kopiraj ključeve sa https://dashboard.stripe.com/test/apikeys u `.env`:
```
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
```
3. Test kartica: `4242 4242 4242 4242`, bilo koji budući datum, bilo koji CVC.

### Groq AI (opisi + chatbot)
1. Besplatan ključ sa https://console.groq.com/keys
2. U `.env`:
```
   GROQ_API_KEY=gsk_...
```

### Email (Mailtrap)
1. Besplatan nalog na https://mailtrap.io → Email Testing → Inbox → Integrations → Laravel.
2. Prekopiraj SMTP podatke u `.env` i postavi `MAIL_MAILER=smtp`.
> Bez ovoga, mejlovi se upisuju u `storage/logs/laravel.log`.

Nakon promene `.env` uvek pokreni:
```bash
php artisan config:clear
```

---

## Kako radi escrow

Sav novac prolazi kroz `app/Services/EscrowService.php`. Svaka operacija je jedna transakcija u bazi sa
zaključavanjem redova (`lockForUpdate`), a svaka promena balansa se upisuje u tabelu `transactions`.

| Događaj | Šta se dešava sa novcem |
|---|---|
| Licitiranje | iznos prelazi sa balansa u zaključani balans ponuđača |
| Nadlicitiranje | prethodnom najvišem ponuđaču se zaključani iznos vraća na balans |
| „Kupi odmah" | prethodni najviši ponuđač dobija novac nazad, kupcu se zaključava cena, aukcija se završava |
| Kraj aukcije | novac pobednika ostaje zaključan dok ne potvrdi prijem |
| Potvrda prijema | zaključani iznos ide prodavcu, aukcija je *completed* |
| Spor | može se otvoriti samo dok je novac u escrow-u (aukcija *ended*); admin ga rešava isplatom prodavcu ili povraćajem kupcu |
| Uklanjanje aukcije (admin) | ko god ima zaključan novac u toj aukciji dobija ga nazad |
| Uplata preko Stripe-a | iznos koji je Stripe zaista naplatio, knjiži se tačno jednom i kad se stranica osveži |

Pravila (minimalna ponuda, sopstvena aukcija, dovoljno sredstava...) vraćaju poruku korisniku; ostale greške se ne prikazuju.

## Testovi

```bash
composer install
php vendor/bin/phpunit
```

Testovi koriste SQLite bazu u memoriji, pa MySQL nije potreban. Pokrivaju licitiranje i nadlicitiranje,
„Kupi odmah", zatvaranje isteklih aukcija, potvrdu prijema, oba ishoda spora, uklanjanje aukcije sa
zaključanim novcem i registraciju. Posle svakog scenarija proverava se da ukupna količina novca u sistemu
nije promenjena. GitHub Actions ih pokreće na svaki push.

## Ocenjivanje (dodatni task)
Kada kupac potvrdi prijem robe, aukcija prelazi u status *completed* i na njenoj stranici se
i kupcu i prodavcu otvara forma za ocenjivanje druge strane (1–5 zvezdica + komentar do 500
karaktera). U bazi je `UNIQUE(auction_id, rater_id)` pa je moguća tačno jedna ocena po aukciji
po korisniku (ponovno slanje menja postojeću). Prosečna ocena i broj ocena prodavca
prikazuju se pored njegovog imena na stranici svake njegove aukcije.

## Napomene
- Slike aukcija se čuvaju u `public/uploads/auctions` — folder mora biti upisiv.
- Istekle aukcije se automatski finalizuju pri svakom učitavanju stranice (middleware), bez potrebe za cron-om.