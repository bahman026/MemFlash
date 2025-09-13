<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use Illuminate\Database\Seeder;

class StaticCardStarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all Starter level static decks
        $starterDecks = StaticDeck::where('level', 'starter')->get();

        if ($starterDecks->isEmpty()) {
            $this->command->error('No Starter level static decks found. Please run StaticDeckSeeder first.');

            return;
        }

        // Lesson 1 vocabulary
        $lesson1Deck = $starterDecks->where('lesson_number', 1)->first();
        if ($lesson1Deck) {
            $this->seedLesson1($lesson1Deck);
        }

        // Lesson 2 vocabulary
        $lesson2Deck = $starterDecks->where('lesson_number', 2)->first();
        if ($lesson2Deck) {
            $this->seedLesson2($lesson2Deck);
        }

        // Lesson 3 vocabulary
        $lesson3Deck = $starterDecks->where('lesson_number', 3)->first();
        if ($lesson3Deck) {
            $this->seedLesson3($lesson3Deck);
        }

        // Lesson 4 vocabulary
        $lesson4Deck = $starterDecks->where('lesson_number', 4)->first();
        if ($lesson4Deck) {
            $this->seedLesson4($lesson4Deck);
        }

        // Lesson 5 vocabulary
        $lesson5Deck = $starterDecks->where('lesson_number', 5)->first();
        if ($lesson5Deck) {
            $this->seedLesson5($lesson5Deck);
        }

        // Lesson 6 vocabulary
        $lesson6Deck = $starterDecks->where('lesson_number', 6)->first();
        if ($lesson6Deck) {
            $this->seedLesson6($lesson6Deck);
        }

        // Lesson 7 vocabulary
        $lesson7Deck = $starterDecks->where('lesson_number', 7)->first();
        if ($lesson7Deck) {
            $this->seedLesson7($lesson7Deck);
        }

        // Lesson 8 vocabulary
        $lesson8Deck = $starterDecks->where('lesson_number', 8)->first();
        if ($lesson8Deck) {
            $this->seedLesson8($lesson8Deck);
        }

        // Lesson 9 vocabulary
        $lesson9Deck = $starterDecks->where('lesson_number', 9)->first();
        if ($lesson9Deck) {
            $this->seedLesson9($lesson9Deck);
        }

        // Lesson 10 vocabulary
        $lesson10Deck = $starterDecks->where('lesson_number', 10)->first();
        if ($lesson10Deck) {
            $this->seedLesson10($lesson10Deck);
        }

        // Lesson 11 vocabulary
        $lesson11Deck = $starterDecks->where('lesson_number', 11)->first();
        if ($lesson11Deck) {
            $this->seedLesson11($lesson11Deck);
        }

        // Lesson 12 vocabulary
        $lesson12Deck = $starterDecks->where('lesson_number', 12)->first();
        if ($lesson12Deck) {
            $this->seedLesson12($lesson12Deck);
        }
    }

    private function seedLesson1($deck)
    {
        $vocabulary = [
            ['front' => 'ten', 'back' => 'ده', 'pronunciation' => '/ten/'],
            ['front' => 'spell', 'back' => 'هجی کردن', 'pronunciation' => '/spel/'],
            ['front' => 'Portugal', 'back' => 'پرتغال (کشور)', 'pronunciation' => '/ˈpɔrʧəgəl/'],
            // ... (all Lesson 1 vocabulary)
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 1');
    }

    private function seedLesson2($deck)
    {
        $vocabulary = [
            ['front' => 'Brazilian', 'back' => 'برزیلی', 'pronunciation' => '/brəˈzɪl.i.ən/'],
            ['front' => 'seventeen', 'back' => 'هفده', 'pronunciation' => '/ˌsevənˈtiːn/'],
            // ... (all Lesson 2 vocabulary)
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 2');
    }

    private function seedLesson3($deck)
    {
        $vocabulary = [
            ['front' => 'key', 'back' => 'جاکلیدی', 'pronunciation' => '/kiː/'],
            ['front' => 'bus', 'back' => 'اتوبوس', 'pronunciation' => '/bʌs/'],
            ['front' => 'car', 'back' => 'ماشین', 'pronunciation' => '/kɑr/'],
            ['front' => 'bike', 'back' => 'دوچرخه', 'pronunciation' => '/baɪk/'],
            ['front' => 'train', 'back' => 'قطار', 'pronunciation' => '/treɪn/'],
            ['front' => 'plane', 'back' => 'هواپیما', 'pronunciation' => '/pleɪn/'],
            ['front' => 'boat', 'back' => 'قایق', 'pronunciation' => '/boʊt/'],
            ['front' => 'taxi', 'back' => 'تاکسی', 'pronunciation' => '/ˈtæksi/'],
            ['front' => 'walk', 'back' => 'پیاده روی', 'pronunciation' => '/wɔk/'],
            ['front' => 'run', 'back' => 'دویدن', 'pronunciation' => '/rʌn/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 3');
    }

    private function seedLesson4($deck)
    {
        $vocabulary = [
            ['front' => 'boy', 'back' => 'پسر', 'pronunciation' => '/bɔɪ/'],
            ['front' => 'big', 'back' => 'بزرگ', 'pronunciation' => '/bɪg/'],
            ['front' => 'small', 'back' => 'کوچک', 'pronunciation' => '/smɔl/'],
            ['front' => 'tall', 'back' => 'بلند', 'pronunciation' => '/tɔl/'],
            ['front' => 'short', 'back' => 'کوتاه', 'pronunciation' => '/ʃɔrt/'],
            ['front' => 'fat', 'back' => 'چاق', 'pronunciation' => '/fæt/'],
            ['front' => 'thin', 'back' => 'لاغر', 'pronunciation' => '/θɪn/'],
            ['front' => 'old', 'back' => 'پیر', 'pronunciation' => '/oʊld/'],
            ['front' => 'young', 'back' => 'جوان', 'pronunciation' => '/jʌŋ/'],
            ['front' => 'happy', 'back' => 'خوشحال', 'pronunciation' => '/ˈhæpi/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 4');
    }

    private function seedLesson5($deck)
    {
        $vocabulary = [
            ['front' => 'chocolate', 'back' => 'شکلات', 'pronunciation' => '/ˈtʃɔkələt/'],
            ['front' => 'hair', 'back' => 'مو', 'pronunciation' => '/her/'],
            ['front' => 'eyes', 'back' => 'چشم‌ها', 'pronunciation' => '/aɪz/'],
            ['front' => 'nose', 'back' => 'بینی', 'pronunciation' => '/noʊz/'],
            ['front' => 'mouth', 'back' => 'دهان', 'pronunciation' => '/maʊθ/'],
            ['front' => 'ears', 'back' => 'گوش‌ها', 'pronunciation' => '/ɪrz/'],
            ['front' => 'hands', 'back' => 'دست‌ها', 'pronunciation' => '/hændz/'],
            ['front' => 'feet', 'back' => 'پاها', 'pronunciation' => '/fit/'],
            ['front' => 'head', 'back' => 'سر', 'pronunciation' => '/hed/'],
            ['front' => 'face', 'back' => 'صورت', 'pronunciation' => '/feɪs/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 5');
    }

    private function seedLesson6($deck)
    {
        $vocabulary = [
            ['front' => 'afternoon', 'back' => 'بعدازظهر', 'pronunciation' => '/ˌæftərˈnun/'],
            ['front' => 'sometimes', 'back' => 'گاهی', 'pronunciation' => '/ˈsʌmˌtaɪmz/'],
            ['front' => 'always', 'back' => 'همیشه', 'pronunciation' => '/ˈɔlweɪz/'],
            ['front' => 'never', 'back' => 'هرگز', 'pronunciation' => '/ˈnevər/'],
            ['front' => 'usually', 'back' => 'معمولاً', 'pronunciation' => '/ˈjuʒuəli/'],
            ['front' => 'often', 'back' => 'اغلب', 'pronunciation' => '/ˈɔfən/'],
            ['front' => 'rarely', 'back' => 'به ندرت', 'pronunciation' => '/ˈrerli/'],
            ['front' => 'every day', 'back' => 'هر روز', 'pronunciation' => '/ˈevri deɪ/'],
            ['front' => 'weekend', 'back' => 'آخر هفته', 'pronunciation' => '/ˈwikend/'],
            ['front' => 'weekday', 'back' => 'روز کاری', 'pronunciation' => '/ˈwikdeɪ/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 6');
    }

    private function seedLesson7($deck)
    {
        $vocabulary = [
            ['front' => 'twenty-first', 'back' => 'بیست و یکم', 'pronunciation' => '/ˈtwenti fɜrst/'],
            ['front' => 'tourist', 'back' => 'گردشگر', 'pronunciation' => '/ˈtʊrɪst/'],
            ['front' => 'hotel', 'back' => 'هتل', 'pronunciation' => '/hoʊˈtel/'],
            ['front' => 'restaurant', 'back' => 'رستوران', 'pronunciation' => '/ˈrestərɑnt/'],
            ['front' => 'museum', 'back' => 'موزه', 'pronunciation' => '/mjuˈziəm/'],
            ['front' => 'park', 'back' => 'پارک', 'pronunciation' => '/pɑrk/'],
            ['front' => 'beach', 'back' => 'ساحل', 'pronunciation' => '/bitʃ/'],
            ['front' => 'mountain', 'back' => 'کوه', 'pronunciation' => '/ˈmaʊntən/'],
            ['front' => 'river', 'back' => 'رودخانه', 'pronunciation' => '/ˈrɪvər/'],
            ['front' => 'lake', 'back' => 'دریاچه', 'pronunciation' => '/leɪk/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 7');
    }

    private function seedLesson8($deck)
    {
        $vocabulary = [
            ['front' => 'covered', 'back' => 'سرپوشیده', 'pronunciation' => '/ˈkʌvərd/'],
            ['front' => 'comfortable', 'back' => 'راحت', 'pronunciation' => '/ˈkʌmfərtəbəl/'],
            ['front' => 'uncomfortable', 'back' => 'ناراحت', 'pronunciation' => '/ʌnˈkʌmfərtəbəl/'],
            ['front' => 'expensive', 'back' => 'گران', 'pronunciation' => '/ɪkˈspensɪv/'],
            ['front' => 'cheap', 'back' => 'ارزان', 'pronunciation' => '/tʃip/'],
            ['front' => 'modern', 'back' => 'مدرن', 'pronunciation' => '/ˈmɑdərn/'],
            ['front' => 'old-fashioned', 'back' => 'قدیمی', 'pronunciation' => '/oʊld ˈfæʃənd/'],
            ['front' => 'beautiful', 'back' => 'زیبا', 'pronunciation' => '/ˈbjutəfəl/'],
            ['front' => 'ugly', 'back' => 'زشت', 'pronunciation' => '/ˈʌgli/'],
            ['front' => 'clean', 'back' => 'تمیز', 'pronunciation' => '/klin/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 8');
    }

    private function seedLesson9($deck)
    {
        $vocabulary = [
            ['front' => 'front', 'back' => 'جلو', 'pronunciation' => '/frʌnt/'],
            ['front' => 'gift shop', 'back' => 'کادوسرا', 'pronunciation' => '/gɪft ʃɑp/'],
            ['front' => 'souvenir', 'back' => 'یادگاری', 'pronunciation' => '/ˌsuvəˈnɪr/'],
            ['front' => 'postcard', 'back' => 'کارت پستال', 'pronunciation' => '/ˈpoʊstkɑrd/'],
            ['front' => 'camera', 'back' => 'دوربین', 'pronunciation' => '/ˈkæmərə/'],
            ['front' => 'map', 'back' => 'نقشه', 'pronunciation' => '/mæp/'],
            ['front' => 'guidebook', 'back' => 'راهنمای سفر', 'pronunciation' => '/ˈgaɪdbʊk/'],
            ['front' => 'ticket', 'back' => 'بلیت', 'pronunciation' => '/ˈtɪkət/'],
            ['front' => 'passport', 'back' => 'پاسپورت', 'pronunciation' => '/ˈpæspɔrt/'],
            ['front' => 'luggage', 'back' => 'چمدان', 'pronunciation' => '/ˈlʌgɪdʒ/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 9');
    }

    private function seedLesson10($deck)
    {
        $vocabulary = [
            ['front' => 'house', 'back' => 'خانه', 'pronunciation' => '/haʊs/'],
            ['front' => 'shave', 'back' => 'اصلاح کردن (صورت، ریش و...)', 'pronunciation' => '/ʃeɪv/'],
            ['front' => 'shower', 'back' => 'دوش', 'pronunciation' => '/ˈʃaʊər/'],
            ['front' => 'bathroom', 'back' => 'حمام', 'pronunciation' => '/ˈbæθrum/'],
            ['front' => 'bedroom', 'back' => 'اتاق خواب', 'pronunciation' => '/ˈbedrum/'],
            ['front' => 'kitchen', 'back' => 'آشپزخانه', 'pronunciation' => '/ˈkɪtʃən/'],
            ['front' => 'living room', 'back' => 'اتاق نشیمن', 'pronunciation' => '/ˈlɪvɪŋ rum/'],
            ['front' => 'dining room', 'back' => 'اتاق غذاخوری', 'pronunciation' => '/ˈdaɪnɪŋ rum/'],
            ['front' => 'garage', 'back' => 'گاراژ', 'pronunciation' => '/gəˈrɑʒ/'],
            ['front' => 'garden', 'back' => 'باغ', 'pronunciation' => '/ˈgɑrdən/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 10');
    }

    private function seedLesson11($deck)
    {
        $vocabulary = [
            ['front' => 'leave', 'back' => 'ترک کردن', 'pronunciation' => '/liv/'],
            ['front' => 'lose', 'back' => 'گم کردن', 'pronunciation' => '/luz/'],
            ['front' => 'find', 'back' => 'پیدا کردن', 'pronunciation' => '/faɪnd/'],
            ['front' => 'forget', 'back' => 'فراموش کردن', 'pronunciation' => '/fərˈget/'],
            ['front' => 'remember', 'back' => 'یاد آوردن', 'pronunciation' => '/rɪˈmembər/'],
            ['front' => 'think', 'back' => 'فکر کردن', 'pronunciation' => '/θɪŋk/'],
            ['front' => 'know', 'back' => 'دانستن', 'pronunciation' => '/noʊ/'],
            ['front' => 'understand', 'back' => 'فهمیدن', 'pronunciation' => '/ˌʌndərˈstænd/'],
            ['front' => 'learn', 'back' => 'یادگیری', 'pronunciation' => '/lɜrn/'],
            ['front' => 'teach', 'back' => 'آموزش دادن', 'pronunciation' => '/titʃ/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 11');
    }

    private function seedLesson12($deck)
    {
        $vocabulary = [
            ['front' => 'going to', 'back' => 'قصد (انجام کاری را) داشتن', 'pronunciation' => '/ˈgoʊɪŋ tu/'],
            ['front' => 'will', 'back' => 'خواهد', 'pronunciation' => '/wɪl/'],
            ['front' => 'future', 'back' => 'آینده', 'pronunciation' => '/ˈfjutʃər/'],
            ['front' => 'plan', 'back' => 'برنامه', 'pronunciation' => '/plæn/'],
            ['front' => 'hope', 'back' => 'امید', 'pronunciation' => '/hoʊp/'],
            ['front' => 'dream', 'back' => 'رویا', 'pronunciation' => '/drim/'],
            ['front' => 'goal', 'back' => 'هدف', 'pronunciation' => '/goʊl/'],
            ['front' => 'wish', 'back' => 'آرزو', 'pronunciation' => '/wɪʃ/'],
            ['front' => 'expect', 'back' => 'انتظار', 'pronunciation' => '/ɪkˈspekt/'],
            ['front' => 'intend', 'back' => 'قصد', 'pronunciation' => '/ɪnˈtend/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 12');
    }

    private function seedVocabulary($deck, $vocabulary, $lessonName)
    {
        foreach ($vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($vocabulary) . ' cards for ' . $lessonName);
    }
}
