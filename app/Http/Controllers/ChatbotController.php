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
                    'temperature' => 0.4,
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
            'O PLATFORMI:',
            '- AuctionChain je onlajn aukciona platforma sa escrow (depozitnom) zaštitom za kupce i prodavce. Valuta je RSD (virtuelni balans).',
            '- Nalog: korisnik se registruje (ime, email, lozinka). Sredstva se NE dobijaju besplatno - moraju se uplatiti.',
            '- Uplata sredstava: preko kartice (Stripe, test režim) ili MetaMask novčanika, na stranici Profil → Uplata na balans.',
            '- Licitiranje: na stranici aukcije uneseš iznos veći od trenutne cene i klikneš "Licitiraj". Taj iznos se odmah zaključava u escrow-u.',
            '- Escrow: dok licitiraš, novac je zaključan. Ako te neko nadlicitira, novac ti se automatski vraća na dostupan balans. Ako pobediš, novac ostaje zaključan dok ne potvrdiš prijem robe, a tada ide prodavcu.',
            '- Kupi odmah: neke aukcije imaju fiksnu "Kupi odmah" cenu - klikom momentalno kupuješ predmet i preskačeš licitaciju.',
            '- Kraj aukcije: kad istekne vreme, najviši ponuđač je pobednik. Pobednik klikne "Potvrdi prijem" kad dobije robu i tada novac ide prodavcu. Ako ima problema, može otvoriti "Spor".',
            '- Spor: ako roba ne stigne ili nije kako je opisano, kupac otvara spor, a administrator ga rešava (vraćanje novca ili isplata prodavcu).',
            '- Prodaja: preko "Nova aukcija" - uneseš naziv, opis (može i AI da ga generiše), 1 do 5 slika, početnu cenu, opciono "Kupi odmah" cenu i trajanje aukcije.',
            '- Kategorije: dostupne u meniju (navbar) pod "Kategorije".',
            '- Notifikacije: zvonce u meniju i email obaveštenja (kad te nadlicitiraju, kad pobediš, itd).',
            '',
            'PRAVILA:',
            '- Odgovaraj SAMO o AuctionChain platformi i aukcijama. Ako te pitaju nešto nevezano, ljubazno reci da pomažeš samo oko platforme.',
            '- Ne izmišljaj funkcije kojih nema. Ako ne znaš tačan odgovor, predloži kontakt sa administratorom/podrškom.',
            '- Budi kratak i konkretan. Ne koristi markdown formatiranje, piši običan tekst.',
        ]);
    }
}
