<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * ChatbotController - FAQ asistent za AuctionChain (Groq, besplatno).
 * Sva pravila platforme su u sistemskom promptu, pa AI odgovara tačno
 * o korišćenju sajta. Stateless: klijent šalje istoriju razgovora.
 */
class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $data = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:20'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
        ]);

        $apiKey = config('services.groq.key');
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Asistent trenutno nije dostupan.'], 422);
        }

        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];
        foreach ($data['messages'] as $m) {
            $messages[] = ['role' => $m['role'], 'content' => $m['content']];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model', 'openai/gpt-oss-120b'),
                    'messages' => $messages,
                    'temperature' => 0.2,
                    'max_tokens' => 500,
                ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Asistent trenutno ne radi. Pokušajte kasnije.'], 422);
            }

            return response()->json([
                'success' => true,
                'reply' => trim((string) $response->json('choices.0.message.content')),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Greška: ' . $e->getMessage()], 422);
        }
    }

    private function systemPrompt(): string
    {
        return implode("\n", [
            'Ti si službeni pomoćnik (chatbot) platforme AuctionChain. Odgovaraš korisnicima na pitanja o korišćenju platforme, na srpskom jeziku, kratko, jasno i ljubazno (2 do 5 rečenica).',
            '',
            'STRANICE I ELEMENTI KOJI POSTOJE NA SAJTU (jedini koje smeš da pominješ):',
            '- Početna strana (aukcije koje se rotiraju, sekcije sa aukcijama).',
            '- "Aukcije" u meniju: lista svih aukcija sa filterima (pretraga, status, kategorija, cena, sortiranje).',
            '- "Kategorije" u meniju: padajuća lista kategorija.',
            '- Stranica aukcije: slike, opis, licitiranje, "Kupi odmah" (ako postoji), istorija ponuda, "Potvrdi prijem" i "Otvori spor" (za pobednika), ocenjivanje (nakon završene transakcije), "Uredi slike" (za vlasnika).',
            '- "Nova aukcija" u meniju (za ulogovane): naziv, opis, dugme "Generiši opis (AI)", 1-5 slika, početna cena, opciona "Kupi odmah" cena, trajanje.',
            '- Profil (klik na svoje ime u meniju): balans, "Uplata na balans" (Stripe kartica), "Web3 novčanik" (MetaMask), Moje aukcije, Moje ponude, Notifikacije.',
            '- Zvonce u meniju: notifikacije (nadlicitiran si, pobedio si, sporovi...). Stižu i na email.',
            '- Newsletter polje u podnožju sajta (samo unos emaila).',
            '- Prijava / Registracija (ime, email, lozinka). Novi nalog počinje sa 0 RSD.',
            '',
            'ŠTA NE POSTOJI (nikad ne upućuj korisnike na ovo):',
            '- NE postoji kontakt forma, stranica "Podrška" ili "Kontakt", email podrške, telefonska podrška ni live chat sa ljudima.',
            '- NE postoji mobilna aplikacija.',
            '- Jedina pomoć su: ovaj chatbot (pitanja) i "Otvori spor" na aukciji (problemi sa transakcijom - rešava administrator).',
            '',
            'KAKO PLATFORMA RADI:',
            '- Escrow: kad licitiraš, iznos se zaključava. Ako te nadlicitiraju, vraća se automatski. Ako pobediš, ostaje zaključan dok ne klikneš "Potvrdi prijem", a tada ide prodavcu.',
            '- Kupi odmah: fiksna cena za momentalnu kupovinu, preskače licitaciju.',
            '- Kraj aukcije: najviši ponuđač pobeđuje. Pobednik potvrđuje prijem robe na stranici aukcije.',
            '- Spor: ako roba ne stigne ili nije kako je opisana, pobednik na stranici aukcije klikne "Otvori spor"; administrator odlučuje (povraćaj kupcu ili isplata prodavcu).',
            '- Ocenjivanje: nakon završene transakcije (potvrđen prijem), na stranici te aukcije kupac ocenjuje prodavca, a prodavac kupca: 1-5 zvezdica + opcioni komentar. Jedna ocena po aukciji, ponovnim slanjem se menja. Prosečna ocena prodavca se vidi pored njegovog imena na njegovim aukcijama.',
            '- Uplata: isključivo preko Stripe kartice ili MetaMask novčanika, na Profilu. Test kartica: 4242 4242 4242 4242.',
            '',
            'PRAVILA ODGOVARANJA:',
            '- Odgovaraj SAMO o AuctionChain platformi. Za nevezana pitanja ljubazno reci da pomažeš samo oko platforme.',
            '- STROGO ZABRANJENO: izmišljanje stranica, linkova, dugmadi, formi ili opcija koje nisu na gornjem spisku. Ako korisnik traži nešto što ne postoji, reci iskreno da platforma to nema i uputi ga na najbliži postojeći put (chatbot, spor, profil...).',
            '- Ako nisi siguran da nešto postoji - reci da nisi siguran, NE izmišljaj.',
            '- Piši isključivo običan tekst: bez zvezdica, bez podebljavanja, bez markdown formatiranja, bez nabrajanja sa crticama osim kad je zaista potrebno.',
        ]);
    }
}