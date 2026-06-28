<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * AiController - generisanje opisa aukcije pomoću Groq AI (besplatno).
 *  - describe()          : opis na osnovu naziva i kategorije (tekstualni model)
 *  - describeFromImage() : opis na osnovu SLIKE + naziva (vision model)
 *
 * Persona: iskusan aukcijski copywriter - samouveren, konkretan, iskren,
 * kratak (1-2 pasusa), gramatički ispravan srpski.
 */
class AiController extends Controller
{
    private function systemPrompt(): string
    {
        return implode(' ', [
            'Ti si profesionalni copywriter za aukcijske oglase sa dugogodišnjim iskustvom.',
            'Pišeš jasne, samouverene i iskrene opise predmeta, kao stručan aukcijski specijalista.',
            'JEZIK I GRAMATIKA (najvažnije):',
            'Piši isključivo na srpskom jeziku (latinica), gramatički savršeno, sa ispravnim padežima, rodom i brojem (npr. "belo kućište", "od bele kože", "tamne boje").',
            'Rečenice neka teku prirodno i povezano.',
            'SAMOUVERENOST:',
            'Ako na osnovu slike i/ili naziva sa sigurnošću prepoznaš model ili brend, navedi ga direktno (npr. "iPhone X"), BEZ ograda tipa "deluje kao", "izgleda kao" ili "ili sličan model".',
            'Ako si siguran, dodaj jednu opštepoznatu činjenicu o tom modelu iz svog znanja - na primer godinu izlaska ili poznatu karakteristiku (npr. "iPhone X je predstavljen 2017. godine, sa OLED ekranom i Face ID-em").',
            'Oštećenja i stanje opiši jasno i direktno, navodeći gde se vide na slici (npr. "sa pukotinom na ekranu koja je vidljiva na slici").',
            'IZMIŠLJANJE (zabranjeno):',
            'Ne izmišljaj specifikacije, dodatke ni stanje kojih nema u nazivu, na slici, niti u opštepoznatim činjenicama o tom modelu.',
            'Ne tvrdi da uređaj radi ispravno - to se ne vidi sa slike. Ako želiš da istakneš pozitivnu stranu, koristi opšte tačne činjenice (npr. da se ekran može zameniti u ovlašćenom servisu).',
            'OSTALO:',
            '1) Dužina: 1 do 2 kratka pasusa (oko 50 do 90 reči). Bez nabrajanja (bullet points).',
            '2) Spoji specifikacije iz naziva i izgled sa slike u jedan prirodan, povezan opis.',
            '3) Izbegavaj clickbait i prazne klišee ("savršen dodatak", "dašak luksuza", "uživajte u komforu i stilu", "jedinstvena prilika", "ne propustite"). Bez uzvičnika i preterivanja.',
            '4) Ne pominji cenu (cena je posebno polje). Završi jednom učtivom rečenicom koja prirodno poziva na licitiranje.',
            'Vrati samo gotov, doteran opis, bez naslova i navodnika.',
        ]);
    }

    public function describe(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = config('services.groq.key');
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Groq API ključ nije podešen (GROQ_API_KEY u .env).'], 422);
        }

        $prompt = "Napiši opis za aukcijski oglas za predmet: \"{$data['title']}\"";
        if (!empty($data['category'])) {
            $prompt .= " (kategorija: {$data['category']})";
        }
        $prompt .= '. Iskoristi sve specifikacije iz naziva. Ako prepoznaš model, navedi ga samouvereno i dodaj jednu opštepoznatu činjenicu (npr. godinu izlaska).';

        try {
            $response = Http::withToken($apiKey)
                ->timeout(40)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model', 'openai/gpt-oss-120b'),
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.6,
                    'max_tokens' => 500,
                ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'AI servis je vratio grešku (' . $response->status() . ').'], 422);
            }

            return response()->json(['success' => true, 'description' => trim((string) $response->json('choices.0.message.content'))]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Greška pri pozivu AI servisa: ' . $e->getMessage()], 422);
        }
    }

    public function describeFromImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'title' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = config('services.groq.key');
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Groq API ključ nije podešen (GROQ_API_KEY u .env).'], 422);
        }

        $file = $request->file('image');

        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($file->getRealPath());
            $img->scaleDown(width: 1024);
            $dataUri = $img->toJpeg(75)->toDataUri();
        } catch (\Throwable $e) {
            if ($file->getSize() > 3 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slika je prevelika za AI analizu. Uključi GD ekstenziju u PHP-u ili koristi manju sliku.',
                ], 422);
            }
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $dataUri = "data:{$mime};base64," . base64_encode(file_get_contents($file->getRealPath()));
        }

        $title = $request->input('title');
        $category = $request->input('category');

        $instruction = 'Pogledaj sliku predmeta i napiši kratak, samouveren opis za aukcijski oglas (1 do 2 pasusa). ';
        if ($title) {
            $instruction .= "Naziv predmeta: \"{$title}\". ";
        }
        if ($category) {
            $instruction .= "Kategorija: {$category}. ";
        }
        $instruction .= 'Ako sa sigurnošću prepoznaš model/brend sa slike (npr. logo ili prepoznatljiv izgled), navedi ga direktno bez ograda i dodaj jednu opštepoznatu činjenicu (npr. godinu izlaska). ';
        $instruction .= 'Spoji specifikacije iz naziva sa izgledom i stanjem sa slike. Oštećenja navedi jasno (gde se vide). Posebno pazi na ispravne srpske padeže i rod.';

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct'),
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $instruction],
                                ['type' => 'image_url', 'image_url' => ['url' => $dataUri]],
                            ],
                        ],
                    ],
                    'temperature' => 0.6,
                    'max_tokens' => 400,
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI servis je vratio grešku (' . $response->status() . '). Proveri vision model (GROQ_VISION_MODEL).',
                ], 422);
            }

            return response()->json(['success' => true, 'description' => trim((string) $response->json('choices.0.message.content'))]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Greška pri pozivu AI servisa: ' . $e->getMessage()], 422);
        }
    }
}