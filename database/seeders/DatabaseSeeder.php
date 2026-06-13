<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Bid;
use App\Models\Category;
use App\Models\User;
use App\Services\EscrowService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(EscrowService $escrow): void
    {
        // === ADMIN ===
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@auctionchain.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'balance' => 100000,
        ]);

        // === DEMO KORISNICI ===
        $marko = User::create([
            'name' => 'Marko Spasojević',
            'email' => 'marko@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'balance' => 50000,
        ]);

        $pavle = User::create([
            'name' => 'Pavle Tasić',
            'email' => 'pavle@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'balance' => 50000,
        ]);

        $micovic = User::create([
            'name' => 'Marko Mićović',
            'email' => 'micovic@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'balance' => 50000,
        ]);

        // Dodatnih nekoliko korisnika za realisticnost
        $jovan = User::create([
            'name' => 'Jovan Petrović',
            'email' => 'jovan@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'balance' => 30000,
        ]);

        $milica = User::create([
            'name' => 'Milica Jovanović',
            'email' => 'milica@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'balance' => 30000,
        ]);

        // === KATEGORIJE ===
        $kategorije = [
            ['name' => 'Elektronika', 'description' => 'Telefoni, laptopi, gaming oprema...'],
            ['name' => 'Vozila', 'description' => 'Automobili, motori, delovi'],
            ['name' => 'Moda i odeća', 'description' => 'Markirana odeća, obuća, satovi'],
            ['name' => 'Umetnost i kolekcionarstvo', 'description' => 'Slike, kovani novac, marke'],
            ['name' => 'Knjige i mediji', 'description' => 'Knjige, vinili, ploče'],
            ['name' => 'Sport i rekreacija', 'description' => 'Sportska oprema, bicikli'],
            ['name' => 'Kuća i bašta', 'description' => 'Nameštaj, alati, dekoracija'],
        ];

        foreach ($kategorije as $kat) {
            Category::create($kat);
        }

        // === AUKCIJE ===
        $aukcije = [
            [
                'seller' => $marko,
                'category' => 1,
                'title' => 'iPhone 14 Pro 256GB - Space Black',
                'description' => "Polovan iPhone 14 Pro u odličnom stanju, 256GB, boja Space Black.\n\nKupljen u zvaničnom Apple shop-u, ima fiskalni račun. Baterija na 92%. Nema ogrebotina, čuvan je u silikonskoj maski i sa staklom od prvog dana.\n\nU kompletu: kutija, kabl, dokumentacija.",
                'starting_price' => 80000,
                'ends_at' => now()->addDays(3),
            ],
            [
                'seller' => $pavle,
                'category' => 1,
                'title' => 'MacBook Air M2 2023 - 8GB/256GB',
                'description' => "MacBook Air sa M2 čipom, 8GB RAM, 256GB SSD, srebrna boja. Garancija još 6 meseci. Idealan za studente i posao.",
                'starting_price' => 120000,
                'ends_at' => now()->addDays(5),
            ],
            [
                'seller' => $micovic,
                'category' => 6,
                'title' => 'Trek Marlin 7 - MTB bicikl',
                'description' => "Trek Marlin 7, veličina M, 29 inča. Korišćen jedno sezonu, kao nov. Hidraulične disk kočnice, Shimano Deore.",
                'starting_price' => 65000,
                'ends_at' => now()->addHours(18),
            ],
            [
                'seller' => $jovan,
                'category' => 3,
                'title' => 'Tag Heuer Carrera - originalni sat',
                'description' => "Tag Heuer Carrera, automatski mehanizam. Sa originalnom kutijom i papirima. Kupljen 2020. godine.",
                'starting_price' => 180000,
                'ends_at' => now()->addDays(7),
            ],
            [
                'seller' => $milica,
                'category' => 4,
                'title' => 'Stara srebrna kovanica iz Kraljevine Jugoslavije',
                'description' => "Srebrna kovanica od 50 dinara iz 1938. godine, u veoma dobrom stanju. Idealno za kolekcionare.",
                'starting_price' => 8000,
                'ends_at' => now()->addDays(2),
            ],
            [
                'seller' => $marko,
                'category' => 5,
                'title' => 'Vinil ploče - Pink Floyd kolekcija',
                'description' => "Originalne vinil ploče: Dark Side of the Moon, Wish You Were Here, The Wall. Sve u odličnom stanju.",
                'starting_price' => 15000,
                'ends_at' => now()->addDays(4),
            ],
            [
                'seller' => $pavle,
                'category' => 1,
                'title' => 'PlayStation 5 + 2 kontrolera + igre',
                'description' => "PS5 disk verzija, kupljen prošle godine. U paketu 2 kontrolera (jedan beli, jedan crni) + igre: GTA V, FIFA 24, Spider-Man 2.",
                'starting_price' => 70000,
                'ends_at' => now()->addHours(36),
            ],
            [
                'seller' => $jovan,
                'category' => 7,
                'title' => 'Bosch GSB 18V akumulatorska bušilica',
                'description' => "Bosch GSB 18V Professional, sa dve baterije i punjačem. Komplet u koferu. Kao nova, korišćena par puta.",
                'starting_price' => 18000,
                'ends_at' => now()->addDays(1),
            ],
        ];

        foreach ($aukcije as $a) {
            $auction = Auction::create([
                'seller_id' => $a['seller']->id,
                'category_id' => $a['category'],
                'title' => $a['title'],
                'description' => $a['description'],
                'starting_price' => $a['starting_price'],
                'current_price' => $a['starting_price'],
                'status' => 'active',
                'starts_at' => now()->subHours(rand(1, 48)),
                'ends_at' => $a['ends_at'],
            ]);

            // Placeholder slika
            AuctionImage::create([
                'auction_id' => $auction->id,
                'path' => 'placeholder.jpg',
                'is_primary' => true,
            ]);
        }

        // Dodaj nekoliko ponuda na prve dve aukcije za demonstraciju
        $iphoneAuction = Auction::find(1);
        $macAuction = Auction::find(2);

        // Ponude za iPhone
        $escrow->placeBid($jovan, $iphoneAuction, 82000);
        $escrow->placeBid($milica, $iphoneAuction, 85000);
        $escrow->placeBid($pavle, $iphoneAuction, 88000);

        // Ponude za MacBook
        $escrow->placeBid($marko, $macAuction, 122000);
        $escrow->placeBid($jovan, $macAuction, 125000);

        $this->command->info('===================================');
        $this->command->info('Demo podaci uspešno učitani!');
        $this->command->info('===================================');
        $this->command->info('Admin: admin@auctionchain.test / admin123');
        $this->command->info('Korisnik: marko@test.com / password');
        $this->command->info('Korisnik: pavle@test.com / password');
        $this->command->info('Korisnik: micovic@test.com / password');
        $this->command->info('===================================');
    }
}
