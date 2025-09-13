<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use Illuminate\Database\Seeder;

class StaticCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the static deck for Lesson 1
        $lesson1Deck = StaticDeck::where('lesson_number', 1)->first();

        if (! $lesson1Deck) {
            $this->command->error('Static deck for Lesson 1 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 2
        $lesson2Deck = StaticDeck::where('lesson_number', 2)->first();

        if (! $lesson2Deck) {
            $this->command->error('Static deck for Lesson 2 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 3
        $lesson3Deck = StaticDeck::where('lesson_number', 3)->first();

        if (! $lesson3Deck) {
            $this->command->error('Static deck for Lesson 3 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 4
        $lesson4Deck = StaticDeck::where('lesson_number', 4)->first();

        if (! $lesson4Deck) {
            $this->command->error('Static deck for Lesson 4 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 5
        $lesson5Deck = StaticDeck::where('lesson_number', 5)->first();

        if (! $lesson5Deck) {
            $this->command->error('Static deck for Lesson 5 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 6
        $lesson6Deck = StaticDeck::where('lesson_number', 6)->first();

        if (! $lesson6Deck) {
            $this->command->error('Static deck for Lesson 6 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 7
        $lesson7Deck = StaticDeck::where('lesson_number', 7)->first();

        if (! $lesson7Deck) {
            $this->command->error('Static deck for Lesson 7 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 8
        $lesson8Deck = StaticDeck::where('lesson_number', 8)->first();

        if (! $lesson8Deck) {
            $this->command->error('Static deck for Lesson 8 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 9
        $lesson9Deck = StaticDeck::where('lesson_number', 9)->first();

        if (! $lesson9Deck) {
            $this->command->error('Static deck for Lesson 9 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 10
        $lesson10Deck = StaticDeck::where('lesson_number', 10)->first();

        if (! $lesson10Deck) {
            $this->command->error('Static deck for Lesson 10 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 11
        $lesson11Deck = StaticDeck::where('lesson_number', 11)->first();

        if (! $lesson11Deck) {
            $this->command->error('Static deck for Lesson 11 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for Lesson 12
        $lesson12Deck = StaticDeck::where('lesson_number', 12)->first();

        if (! $lesson12Deck) {
            $this->command->error('Static deck for Lesson 12 not found. Please run StaticDeckSeeder first.');

            return;
        }

        // Get the static deck for American English File 1 - Lesson 1
        $aef1Lesson1Deck = StaticDeck::where('lesson_number', 1)->where('level', 'elementary')->first();

        if (! $aef1Lesson1Deck) {
            $this->command->error('Static deck for American English File 1 - Lesson 1 not found. Please run StaticDeckSeeder first.');

            return;
        }

        $lesson1Vocabulary = [
            ['front' => 'ten', 'back' => 'ده', 'pronunciation' => '/ten/', 'category' => 'numbers'],
            ['front' => 'spell', 'back' => 'هجی کردن', 'pronunciation' => '/spel/', 'category' => 'verbs'],
            ['front' => 'Portugal', 'back' => 'پرتغال (کشور)', 'pronunciation' => '/ˈpɔrʧəgəl/', 'category' => 'countries'],
            ['front' => 'repeat', 'back' => 'تکرار کردن', 'pronunciation' => '/rəˈpiːt/', 'category' => 'verbs'],
            ['front' => 'Saudi Arabia', 'back' => 'عربستان سعودی', 'pronunciation' => '/ˈsɔdi əˈreɪbiə/', 'category' => 'countries'],
            ['front' => 'one', 'back' => 'یک', 'pronunciation' => '/wʌn/', 'category' => 'numbers'],
            ['front' => 'Spain', 'back' => 'اسپانیا', 'pronunciation' => '/speɪn/', 'category' => 'countries'],
            ['front' => 'English', 'back' => 'زبان انگلیسی', 'pronunciation' => '/ˈɪŋglɪʃ/', 'category' => 'languages'],
            ['front' => 'hello', 'back' => 'سلام', 'pronunciation' => '/heˈloʊ/', 'category' => 'greetings'],
            ['front' => 'coffee', 'back' => 'قهوه', 'pronunciation' => '/ˈkɑːfi/', 'category' => 'drinks'],
            ['front' => 'Vietnam', 'back' => 'ویتنام', 'pronunciation' => '/ˌvjetˈnæm/', 'category' => 'countries'],
            ['front' => 'country', 'back' => 'کشور', 'pronunciation' => '/ˈkʌntri/', 'category' => 'general'],
            ['front' => 'paper', 'back' => 'کاغذ', 'pronunciation' => '/ˈpeɪ.pər/', 'category' => 'classroom'],
            ['front' => 'hi', 'back' => 'سلام', 'pronunciation' => '/hɑɪ/', 'category' => 'greetings'],
            ['front' => 'understand', 'back' => 'فهمیدن', 'pronunciation' => '/ˌʌndərˈstænd/', 'category' => 'verbs'],
            ['front' => 'two', 'back' => 'دو', 'pronunciation' => '/tuː/', 'category' => 'numbers'],
            ['front' => 'United States of America', 'back' => 'ایالات متحده امریکا', 'pronunciation' => '/juˈnaɪtəd steɪts ʌv əˈmɛrəkə/', 'category' => 'countries'],
            ['front' => 'pen', 'back' => 'خودکار', 'pronunciation' => '/pen/', 'category' => 'classroom'],
            ['front' => 'three', 'back' => 'سه', 'pronunciation' => '/θri/', 'category' => 'numbers'],
            ['front' => 'four', 'back' => 'چهار', 'pronunciation' => '/fɔːr/', 'category' => 'numbers'],
            ['front' => 'Nice to meet you.', 'back' => 'از آشنایی با تو خوشوقتم.', 'pronunciation' => '/naɪs tu mit ju./', 'category' => 'phrases'],
            ['front' => 'five', 'back' => 'پنج', 'pronunciation' => '/fɑɪv/', 'category' => 'numbers'],
            ['front' => 'where', 'back' => 'کجا', 'pronunciation' => '/wer/', 'category' => 'question_words'],
            ['front' => 'he', 'back' => '[ضمیر سوم شخص مفرد مذکر]', 'pronunciation' => '/hiː/', 'category' => 'pronouns'],
            ['front' => 'please', 'back' => 'لطفاً', 'pronunciation' => '/pliːz/', 'category' => 'polite_expressions'],
            ['front' => 'goodbye', 'back' => 'خداحافظ', 'pronunciation' => '/ˌgʊdˈbɑɪ/', 'category' => 'greetings'],
            ['front' => 'she', 'back' => '[ضمیر سوم شخص مفرد مونث]', 'pronunciation' => '/ʃiː/', 'category' => 'pronouns'],
            ['front' => 'stand up', 'back' => 'بلند شدن', 'pronunciation' => '/stænd ʌp/', 'category' => 'phrases'],
            ['front' => 'six', 'back' => 'شش', 'pronunciation' => '/sɪks/', 'category' => 'numbers'],
            ['front' => 'seven', 'back' => 'هفت', 'pronunciation' => '/ˈsevən/', 'category' => 'numbers'],
            ['front' => 'it', 'back' => '[ضمیر سوم شخص مفرد اجسام]', 'pronunciation' => '/ɪt/', 'category' => 'pronouns'],
            ['front' => 'excuse me', 'back' => 'ببخشید', 'pronunciation' => '/ɪkˈskjuːz miː/', 'category' => 'polite_expressions'],
            ['front' => 'sit down', 'back' => 'نشستن', 'pronunciation' => '/sɪt daʊn/', 'category' => 'phrases'],
            ['front' => 'Monday', 'back' => 'دوشنبه', 'pronunciation' => '/ˈmʌndeɪ/', 'category' => 'days'],
            ['front' => 'see you soon', 'back' => 'به زودی می‌بینمت', 'pronunciation' => '/si ju sun/', 'category' => 'phrases'],
            ['front' => 'be', 'back' => '[فعل کمکی]', 'pronunciation' => '/biː/', 'category' => 'verbs'],
            ['front' => 'eight', 'back' => 'هشت', 'pronunciation' => '/eɪt/', 'category' => 'numbers'],
            ['front' => 'book', 'back' => 'کتاب', 'pronunciation' => '/bʊk/', 'category' => 'classroom'],
            ['front' => 'nine', 'back' => 'نه', 'pronunciation' => '/nɑɪn/', 'category' => 'numbers'],
            ['front' => 'zero', 'back' => 'صفر', 'pronunciation' => '/ˈzɪroʊ/', 'category' => 'numbers'],
            ['front' => 'from', 'back' => 'اهل', 'pronunciation' => '/frʌm/', 'category' => 'prepositions'],
            ['front' => 'sorry', 'back' => 'متاسف', 'pronunciation' => '/ˈsɑr.i/', 'category' => 'polite_expressions'],
            ['front' => 'Tuesday', 'back' => 'سه‌شنبه', 'pronunciation' => '/ˈtjuːzdeɪ/', 'category' => 'days'],
            ['front' => 'week', 'back' => 'هفته', 'pronunciation' => '/wiːk/', 'category' => 'time'],
            ['front' => 'Wednesday', 'back' => 'چهارشنبه', 'pronunciation' => '/ˈwenzdeɪ/', 'category' => 'days'],
            ['front' => 'window', 'back' => 'پنجره', 'pronunciation' => '/ˈwɪndoʊ/', 'category' => 'classroom'],
            ['front' => 'Brazil', 'back' => 'برزیل', 'pronunciation' => '/brəˈzɪl/', 'category' => 'countries'],
            ['front' => 'I', 'back' => 'من', 'pronunciation' => '/ɑɪ/', 'category' => 'pronouns'],
            ['front' => 'look at', 'back' => 'نگاه کردن به', 'pronunciation' => '/lʊk æt/', 'category' => 'phrases'],
            ['front' => 'Thursday', 'back' => 'پنجشنبه', 'pronunciation' => '/ˈθɜrzdeɪ/', 'category' => 'days'],
            ['front' => 'number', 'back' => 'عدد', 'pronunciation' => '/ˈnʌm.bər/', 'category' => 'general'],
            ['front' => 'you', 'back' => '[ضمیر دوم شخص فاعلی مفرد و جمع]', 'pronunciation' => '/juː/', 'category' => 'pronouns'],
            ['front' => 'Friday', 'back' => 'جمعه', 'pronunciation' => '/ˈfrɑɪdeɪ/', 'category' => 'days'],
            ['front' => 'Canada', 'back' => 'کانادا', 'pronunciation' => '/ˈkænədə/', 'category' => 'countries'],
            ['front' => 'chair', 'back' => 'صندلی', 'pronunciation' => '/tʃer/', 'category' => 'classroom'],
            ['front' => 'open', 'back' => 'باز کردن', 'pronunciation' => '/ˈoʊ.pən/', 'category' => 'verbs'],
            ['front' => 'Chile', 'back' => 'شیلی', 'pronunciation' => '/ˈtʃɪli/', 'category' => 'countries'],
            ['front' => 'coat', 'back' => 'پالتو', 'pronunciation' => '/koʊt/', 'category' => 'clothing'],
            ['front' => 'Saturday', 'back' => 'شنبه', 'pronunciation' => '/ˈsætərdeɪ/', 'category' => 'days'],
            ['front' => 'classroom', 'back' => 'کلاس', 'pronunciation' => '/ˈklæs.ruːm/', 'category' => 'classroom'],
            ['front' => 'page', 'back' => 'صفحه', 'pronunciation' => '/peɪdʒ/', 'category' => 'classroom'],
            ['front' => 'what', 'back' => 'چی', 'pronunciation' => '/wɒt/', 'category' => 'question_words'],
            ['front' => 'Sunday', 'back' => 'یکشنبه', 'pronunciation' => '/ˈsʌndeɪ/', 'category' => 'days'],
            ['front' => 'China', 'back' => 'چین', 'pronunciation' => '/ˈʧaɪnə/', 'category' => 'countries'],
            ['front' => 'England', 'back' => 'انگلستان', 'pronunciation' => '/ˈɪŋglənd/', 'category' => 'countries'],
            ['front' => 'table', 'back' => 'میز', 'pronunciation' => '/ˈteɪbəl/', 'category' => 'classroom'],
            ['front' => 'board', 'back' => 'تخته', 'pronunciation' => '/bɔrd/', 'category' => 'classroom'],
            ['front' => 'Japan', 'back' => 'ژاپن', 'pronunciation' => '/ʤəˈpæn/', 'category' => 'countries'],
            ['front' => 'go', 'back' => 'رفتن', 'pronunciation' => '/goʊ/', 'category' => 'verbs'],
            ['front' => 'UK', 'back' => 'پادشاهی متحد بریتانیا', 'pronunciation' => '/jukeɪ/', 'category' => 'countries'],
            ['front' => 'laptop', 'back' => 'لپ‌تاپ', 'pronunciation' => '/ˈlæptɑp/', 'category' => 'classroom'],
            ['front' => 'Korea', 'back' => 'کشور کره', 'pronunciation' => '/kɔˈriə/', 'category' => 'countries'],
            ['front' => 'close', 'back' => 'بستن', 'pronunciation' => '/kloʊz/', 'category' => 'verbs'],
            ['front' => 'Mexico', 'back' => 'مکزیک', 'pronunciation' => '/ˈmɛksəˌkoʊ/', 'category' => 'countries'],
            ['front' => 'door', 'back' => 'در', 'pronunciation' => '/dɔːr/', 'category' => 'classroom'],
            ['front' => 'bye', 'back' => 'خداحافظ', 'pronunciation' => '/bɑɪ/', 'category' => 'greetings'],
            ['front' => 'US', 'back' => 'ایالات متحده (آمریکا)', 'pronunciation' => '/ˌjuː ˈes/', 'category' => 'countries'],
            ['front' => 'Peru', 'back' => 'پرو', 'pronunciation' => '/pəˈruː/', 'category' => 'countries'],
            ['front' => 'dictionary', 'back' => 'واژه‌نامه', 'pronunciation' => '/ˈdɪkʃəneri/', 'category' => 'classroom'],
        ];

        foreach ($lesson1Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson1Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson1Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson1Vocabulary) . ' cards for Lesson 1');

        // Lesson 2 vocabulary
        $lesson2Vocabulary = [
            ['front' => 'Brazilian', 'back' => 'برزیلی', 'pronunciation' => '/brəˈzɪljən/', 'category' => 'nationalities'],
            ['front' => 'seventeen', 'back' => 'هفده', 'pronunciation' => '/ˌsevənˈtiːn/', 'category' => 'numbers'],
            ['front' => 'eleven', 'back' => 'یازده', 'pronunciation' => '/ɪˈlevən/', 'category' => 'numbers'],
            ['front' => 'Canadian', 'back' => 'کانادایی', 'pronunciation' => '/kəˈneɪdiən/', 'category' => 'nationalities'],
            ['front' => 'eighteen', 'back' => 'هجده', 'pronunciation' => '/ˌeɪˈtiːn/', 'category' => 'numbers'],
            ['front' => 'forty', 'back' => 'چهل', 'pronunciation' => '/ˈfɔːrti/', 'category' => 'numbers'],
            ['front' => 'twelve', 'back' => 'دوازده', 'pronunciation' => '/twelv/', 'category' => 'numbers'],
            ['front' => 'nineteen', 'back' => 'نوزده', 'pronunciation' => '/nɑɪnˈtiːn/', 'category' => 'numbers'],
            ['front' => 'fifty', 'back' => 'پنجاه', 'pronunciation' => '/ˈfɪfti/', 'category' => 'numbers'],
            ['front' => 'how', 'back' => 'چطور', 'pronunciation' => '/hɑʊ/', 'category' => 'question_words'],
            ['front' => 'thirteen', 'back' => 'سیزده', 'pronunciation' => '/θɜrtˈtiːn/', 'category' => 'numbers'],
            ['front' => 'sixty', 'back' => 'شصت', 'pronunciation' => '/ˈsɪksti/', 'category' => 'numbers'],
            ['front' => 'twenty', 'back' => 'بیست', 'pronunciation' => '/ˈtwenti/', 'category' => 'numbers'],
            ['front' => 'seventy', 'back' => 'هفتاد', 'pronunciation' => '/ˈsevənti/', 'category' => 'numbers'],
            ['front' => 'fourteen', 'back' => 'چهارده', 'pronunciation' => '/ˌfɔːrtˈtiːn/', 'category' => 'numbers'],
            ['front' => 'eighty', 'back' => 'هشتاد', 'pronunciation' => '/ˈeɪti/', 'category' => 'numbers'],
            ['front' => 'twenty-nine', 'back' => 'بیست و نه', 'pronunciation' => '/ˈtwɛnti-naɪn/', 'category' => 'numbers'],
            ['front' => 'fifteen', 'back' => 'پانزده', 'pronunciation' => '/ˌfɪfˈtiːn/', 'category' => 'numbers'],
            ['front' => 'ninety', 'back' => 'نود', 'pronunciation' => '/ˈnɑɪnti/', 'category' => 'numbers'],
            ['front' => 'twenty-two', 'back' => 'بیست و دو', 'pronunciation' => '/ˈtwɛnti-tu/', 'category' => 'numbers'],
            ['front' => 'seat', 'back' => 'جای نشستن', 'pronunciation' => '/siːt/', 'category' => 'classroom'],
            ['front' => 'sixteen', 'back' => 'شانزده', 'pronunciation' => '/sɪksˈtiːn/', 'category' => 'numbers'],
            ['front' => 'Spanish', 'back' => 'زبان اسپانیایی', 'pronunciation' => '/ˈspænɪʃ/', 'category' => 'languages'],
            ['front' => 'thirty', 'back' => 'سی', 'pronunciation' => '/ˈθɜːrti/', 'category' => 'numbers'],
            ['front' => 'hundred', 'back' => 'صد', 'pronunciation' => '/ˈhʌn.drəd/', 'category' => 'numbers'],
            ['front' => 'Chinese', 'back' => 'چینی', 'pronunciation' => '/ʧaɪˈniz/', 'category' => 'languages'],
            ['front' => 'where', 'back' => 'کجا', 'pronunciation' => '/wer/', 'category' => 'question_words'],
            ['front' => 'Portuguese', 'back' => 'زبان پرتغالی', 'pronunciation' => '/ˈpɔrʧəˌgiz/', 'category' => 'languages'],
            ['front' => 'what', 'back' => 'چی', 'pronunciation' => '/wɒt/', 'category' => 'question_words'],
            ['front' => 'Japanese', 'back' => 'ژاپنی', 'pronunciation' => '/ˌʤæpəˈniz/', 'category' => 'languages'],
            ['front' => 'Arabic', 'back' => 'زبان عربی', 'pronunciation' => '/ˈærəbɪk/', 'category' => 'languages'],
            ['front' => 'who', 'back' => 'چه کسی', 'pronunciation' => '/huː/', 'category' => 'question_words'],
            ['front' => 'Korean', 'back' => 'کره‌ای', 'pronunciation' => '/kɔˈriən/', 'category' => 'languages'],
            ['front' => 'language', 'back' => 'زبان', 'pronunciation' => '/ˈlæŋ.gwɪdʒ/', 'category' => 'general'],
            ['front' => 'Mexican', 'back' => 'مکزیکی', 'pronunciation' => '/mɛksɪkən/', 'category' => 'nationalities'],
            ['front' => 'when', 'back' => 'چه زمانی', 'pronunciation' => '/wen/', 'category' => 'question_words'],
            ['front' => 'thirty-three', 'back' => 'سی و سه', 'pronunciation' => '/ˈθɜrdi-θri/', 'category' => 'numbers'],
            ['front' => 'Peruvian', 'back' => 'پرویی', 'pronunciation' => '/pəˈruːviən/', 'category' => 'nationalities'],
            ['front' => 'nationality', 'back' => 'ملیت', 'pronunciation' => '/ˌnæʃəˈnælət̬i/', 'category' => 'general'],
            ['front' => 'we', 'back' => '[ضمیر اول شخص جمع]', 'pronunciation' => '/wiː/', 'category' => 'pronouns'],
            ['front' => 'Saudi', 'back' => 'سعودی', 'pronunciation' => '/ˈsaʊdi/', 'category' => 'nationalities'],
            ['front' => 'British', 'back' => 'بریتانیایی', 'pronunciation' => '/ˈbrɪtɪʃ/', 'category' => 'nationalities'],
            ['front' => 'American', 'back' => 'آمریکایی', 'pronunciation' => '/əˈmɛrəkən/', 'category' => 'nationalities'],
            ['front' => 'Vietnamese', 'back' => 'ویتنامی', 'pronunciation' => '/ˌvjetnəˈmiːz/', 'category' => 'nationalities'],
            ['front' => 'Chilean', 'back' => 'اهل شیلی', 'pronunciation' => '/ˈtʃɪliːən/', 'category' => 'nationalities'],
        ];

        foreach ($lesson2Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson2Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson2Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson2Vocabulary) . ' cards for Lesson 2');

        // Lesson 3 vocabulary
        $lesson3Vocabulary = [
            ['front' => 'key', 'back' => 'جاکلیدی', 'pronunciation' => '/kiː/', 'category' => 'objects'],
            ['front' => 'bus', 'back' => 'اتوبوس', 'pronunciation' => '/bʌs/', 'category' => 'transportation'],
            ['front' => 'toy', 'back' => 'اسباب‌بازی', 'pronunciation' => '/tɔɪ/', 'category' => 'objects'],
            ['front' => 'plane', 'back' => 'هواپیما', 'pronunciation' => '/pleɪn/', 'category' => 'transportation'],
            ['front' => 'credit card', 'back' => 'کارت اعتباری', 'pronunciation' => '/ˈkred.ɪtˌkɑrd/', 'category' => 'objects'],
            ['front' => 'sunglasses', 'back' => 'عینک آفتابی', 'pronunciation' => '/ˈsʌnglæsɪz/', 'category' => 'clothing'],
            ['front' => 'watch', 'back' => 'ساعت مچی', 'pronunciation' => '/wɒtʃ/', 'category' => 'objects'],
            ['front' => 'glasses', 'back' => 'عینک', 'pronunciation' => '/ˈglæsɪz/', 'category' => 'clothing'],
            ['front' => 'ID card', 'back' => 'کارت شناسایی', 'pronunciation' => '/ɑɪˈdiːˌkɑːrd/', 'category' => 'objects'],
            ['front' => 'penny', 'back' => 'پنی', 'pronunciation' => '/ˈpen.i/', 'category' => 'money'],
            ['front' => 'hat', 'back' => 'کلاه', 'pronunciation' => '/hæt/', 'category' => 'clothing'],
            ['front' => 'wallet', 'back' => 'کیف پول', 'pronunciation' => '/ˈwɑl.ɪt/', 'category' => 'objects'],
            ['front' => 'T-shirt', 'back' => 'تی‌شرت', 'pronunciation' => '/ˈtiːʃɜːt/', 'category' => 'clothing'],
            ['front' => 'photo', 'back' => 'عکس', 'pronunciation' => '/ˈfoʊt̬.oʊ/', 'category' => 'objects'],
            ['front' => 'map', 'back' => 'نقشه', 'pronunciation' => '/mæp/', 'category' => 'objects'],
            ['front' => 'postcard', 'back' => 'کارت پستال', 'pronunciation' => '/ˈpoʊst.kɑrd/', 'category' => 'objects'],
            ['front' => 'camera', 'back' => 'دوربین', 'pronunciation' => '/ˈkæm.rə/', 'category' => 'objects'],
            ['front' => 'coffee', 'back' => 'قهوه', 'pronunciation' => '/ˈkɑːfi/', 'category' => 'drinks'],
            ['front' => 'this', 'back' => '[حرف اشاره مفرد نزدیک]', 'pronunciation' => '/ðɪs/', 'category' => 'demonstratives'],
            ['front' => 'that', 'back' => 'آن', 'pronunciation' => '/ðæt/', 'category' => 'demonstratives'],
            ['front' => 'glove', 'back' => 'دستکش', 'pronunciation' => '/glʌv/', 'category' => 'clothing'],
            ['front' => 'these', 'back' => '[ضمیر اشاره جمع نزدیک]', 'pronunciation' => '/ðiːz/', 'category' => 'demonstratives'],
            ['front' => 'those', 'back' => '[حرف اشاره جمع دور]', 'pronunciation' => '/ðəʊz/', 'category' => 'demonstratives'],
            ['front' => 'bag', 'back' => 'کیف', 'pronunciation' => '/bæg/', 'category' => 'objects'],
            ['front' => 'how much', 'back' => 'چه قیمتی', 'pronunciation' => '/haʊ mʌʧ/', 'category' => 'question_phrases'],
            ['front' => 'a', 'back' => 'یک', 'pronunciation' => '/eɪ/', 'category' => 'articles'],
            ['front' => 'small', 'back' => 'کوچک', 'pronunciation' => '/smɔːl/', 'category' => 'adjectives'],
            ['front' => 'thing', 'back' => 'چیز', 'pronunciation' => '/θɪŋ/', 'category' => 'general'],
            ['front' => 'euro', 'back' => 'یورو', 'pronunciation' => '/ˈjʊr.oʊ/', 'category' => 'money'],
            ['front' => 'an', 'back' => 'یک', 'pronunciation' => '/æn/', 'category' => 'articles'],
            ['front' => 'dollar', 'back' => 'دلار', 'pronunciation' => '/ˈdɑl.ər/', 'category' => 'money'],
            ['front' => 'cell phone', 'back' => 'تلفن همراه', 'pronunciation' => '/ˈsel foʊn/', 'category' => 'objects'],
            ['front' => 'pound', 'back' => 'پوند (واحد پول با نماد £)', 'pronunciation' => '/pɑʊnd/', 'category' => 'money'],
            ['front' => 'cent', 'back' => 'سنت (یک صدم دلار)', 'pronunciation' => '/sent/', 'category' => 'money'],
            ['front' => 'umbrella', 'back' => 'چتر', 'pronunciation' => '/ʌmˈbrelə/', 'category' => 'objects'],
        ];

        foreach ($lesson3Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson3Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson3Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson3Vocabulary) . ' cards for Lesson 3');

        // Lesson 4 vocabulary
        $lesson4Vocabulary = [
            ['front' => 'boy', 'back' => 'پسر', 'pronunciation' => '/bɔɪ/', 'category' => 'people'],
            ['front' => 'big', 'back' => 'بزرگ', 'pronunciation' => '/bɪg/', 'category' => 'adjectives'],
            ['front' => 'girl', 'back' => 'دختر', 'pronunciation' => '/ɡɜːrl/', 'category' => 'people'],
            ['front' => 'my', 'back' => '[صفت ملکی اول شخص مفرد]', 'pronunciation' => '/maɪ/', 'category' => 'possessive_pronouns'],
            ['front' => 'small', 'back' => 'کوچک', 'pronunciation' => '/smɔːl/', 'category' => 'adjectives'],
            ['front' => 'very', 'back' => 'خیلی', 'pronunciation' => '/ˈveri/', 'category' => 'adverbs'],
            ['front' => 'man', 'back' => 'مرد', 'pronunciation' => '/mæn/', 'category' => 'people'],
            ['front' => 'woman', 'back' => 'زن', 'pronunciation' => '/ˈwʊmən/', 'category' => 'people'],
            ['front' => 'old', 'back' => 'قدیمی', 'pronunciation' => '/oʊld/', 'category' => 'adjectives'],
            ['front' => 'new', 'back' => 'جدید', 'pronunciation' => '/nuː/', 'category' => 'adjectives'],
            ['front' => 'children', 'back' => 'کودکان', 'pronunciation' => '/ˈʧɪldrən/', 'category' => 'people'],
            ['front' => 'fast', 'back' => 'سریع', 'pronunciation' => '/fæst/', 'category' => 'adjectives'],
            ['front' => 'person', 'back' => 'فرد', 'pronunciation' => '/ˈpɜr.sən/', 'category' => 'people'],
            ['front' => 'slow', 'back' => 'آهسته', 'pronunciation' => '/sloʊ/', 'category' => 'adjectives'],
            ['front' => 'luxurious', 'back' => 'مجلل', 'pronunciation' => '/lʌɡˈʒʊriəs/', 'category' => 'adjectives'],
            ['front' => 'good', 'back' => 'خوب', 'pronunciation' => '/gʊd/', 'category' => 'adjectives'],
            ['front' => 'car', 'back' => 'خودرو', 'pronunciation' => '/kɑːr/', 'category' => 'transportation'],
            ['front' => 'common', 'back' => 'متداول', 'pronunciation' => '/ˈkɑmən/', 'category' => 'adjectives'],
            ['front' => 'husband', 'back' => 'شوهر', 'pronunciation' => '/ˈhʌz.bənd/', 'category' => 'family'],
            ['front' => 'their', 'back' => '[صفت ملکی سوم شخص جمع]', 'pronunciation' => '/ðer/', 'category' => 'possessive_pronouns'],
            ['front' => 'cool', 'back' => 'باحال', 'pronunciation' => '/kuːl/', 'category' => 'adjectives'],
            ['front' => 'convertible', 'back' => 'خودروی کروکی', 'pronunciation' => '/kənˈvɜrtəbəl/', 'category' => 'transportation'],
            ['front' => 'bad', 'back' => 'بد', 'pronunciation' => '/bæd/', 'category' => 'adjectives'],
            ['front' => 'wife', 'back' => 'همسر', 'pronunciation' => '/waɪf/', 'category' => 'family'],
            ['front' => 'park', 'back' => 'پارک', 'pronunciation' => '/pɑrk/', 'category' => 'places'],
            ['front' => 'mother', 'back' => 'مادر', 'pronunciation' => '/ˈmʌðər/', 'category' => 'family'],
            ['front' => 'father', 'back' => 'پدر', 'pronunciation' => '/ˈfɑðər/', 'category' => 'family'],
            ['front' => 'safe', 'back' => 'امن', 'pronunciation' => '/seɪf/', 'category' => 'adjectives'],
            ['front' => 'son', 'back' => '(فرزند) پسر', 'pronunciation' => '/sʌn/', 'category' => 'family'],
            ['front' => 'important', 'back' => 'مهم', 'pronunciation' => '/ɪmˈpɔːrtnt/', 'category' => 'adjectives'],
            ['front' => 'color', 'back' => 'رنگ', 'pronunciation' => '/ˈkʌlər/', 'category' => 'general'],
            ['front' => 'cheap', 'back' => 'ارزان', 'pronunciation' => '/tʃiːp/', 'category' => 'adjectives'],
            ['front' => 'popular', 'back' => 'پرطرفدار', 'pronunciation' => '/ˈpɑːpjələr/', 'category' => 'adjectives'],
            ['front' => 'red', 'back' => 'قرمز', 'pronunciation' => '/rɛd/', 'category' => 'colors'],
            ['front' => 'our', 'back' => '[صفت ملکی اول شخص جمع]', 'pronunciation' => '/ɑʊr/', 'category' => 'possessive_pronouns'],
            ['front' => 'green', 'back' => 'سبز', 'pronunciation' => '/griːn/', 'category' => 'colors'],
            ['front' => 'family', 'back' => 'خانواده', 'pronunciation' => '/ˈfæm.ə.li/', 'category' => 'family'],
            ['front' => 'expensive', 'back' => 'گران', 'pronunciation' => '/ɪkˈspensɪv/', 'category' => 'adjectives'],
            ['front' => 'yellow', 'back' => 'زرد', 'pronunciation' => '/ˈjeloʊ/', 'category' => 'colors'],
            ['front' => 'her', 'back' => '[صفت ملکی سوم شخص مفرد مونث]', 'pronunciation' => '/hɜːr/', 'category' => 'possessive_pronouns'],
            ['front' => 'long', 'back' => 'بلند', 'pronunciation' => '/lɔːŋ/', 'category' => 'adjectives'],
            ['front' => 'friend', 'back' => 'دوست', 'pronunciation' => '/frend/', 'category' => 'people'],
            ['front' => 'blue', 'back' => 'آبی', 'pronunciation' => '/bluː/', 'category' => 'colors'],
            ['front' => 'short', 'back' => 'کوتاه', 'pronunciation' => '/ʃɔːrt/', 'category' => 'adjectives'],
            ['front' => 'his', 'back' => '[صفت ملکی سوم شخص مفرد مذکر]', 'pronunciation' => '/hɪz/', 'category' => 'possessive_pronouns'],
            ['front' => 'orange', 'back' => 'نارنجی', 'pronunciation' => '/ˈɑrɪndʒ/', 'category' => 'colors'],
            ['front' => 'daughter', 'back' => '(فرزند) دختر', 'pronunciation' => '/ˈdɔːtər/', 'category' => 'family'],
            ['front' => 'tall', 'back' => 'بلندقد', 'pronunciation' => '/tɔl/', 'category' => 'adjectives'],
            ['front' => 'brown', 'back' => 'قهوه‌ای', 'pronunciation' => '/braʊn/', 'category' => 'colors'],
            ['front' => 'brother', 'back' => 'برادر', 'pronunciation' => '/ˈbrʌðər/', 'category' => 'family'],
            ['front' => 'its', 'back' => '[صفت ملکی سوم شخص مفرد خنثی]', 'pronunciation' => '/ɪts/', 'category' => 'possessive_pronouns'],
            ['front' => 'black', 'back' => 'سیاه', 'pronunciation' => '/blæk/', 'category' => 'colors'],
            ['front' => 'sister', 'back' => 'خواهر', 'pronunciation' => '/ˈsɪstər/', 'category' => 'family'],
            ['front' => 'white', 'back' => 'سفید', 'pronunciation' => '/wɑɪt/', 'category' => 'colors'],
            ['front' => 'boyfriend', 'back' => 'دوست‌پسر', 'pronunciation' => '/ˈbɔɪ.frend/', 'category' => 'family'],
            ['front' => 'your', 'back' => '[صفت ملکی دوم شخص مفرد و جمع]', 'pronunciation' => '/jʊr/', 'category' => 'possessive_pronouns'],
            ['front' => 'people', 'back' => 'مردم', 'pronunciation' => '/ˈpiː.pəl/', 'category' => 'people'],
            ['front' => 'girlfriend', 'back' => 'دوست‌دختر', 'pronunciation' => '/ˈgɜrl.frend/', 'category' => 'family'],
        ];

        foreach ($lesson4Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson4Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson4Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson4Vocabulary) . ' cards for Lesson 4');

        // Lesson 5 vocabulary
        $lesson5Vocabulary = [
            ['front' => 'chocolate', 'back' => 'شکلات', 'pronunciation' => '/ˈtʃɔːk.lət/', 'category' => 'food'],
            ['front' => 'hair', 'back' => 'مو', 'pronunciation' => '/her/', 'category' => 'body'],
            ['front' => 'croissant', 'back' => 'کروسان', 'pronunciation' => '/krəˈsɑːnt/', 'category' => 'food'],
            ['front' => 'day', 'back' => 'روز', 'pronunciation' => '/deɪ/', 'category' => 'time'],
            ['front' => 'coffee', 'back' => 'قهوه', 'pronunciation' => '/ˈkɑːfi/', 'category' => 'drinks'],
            ['front' => 'drink', 'back' => 'نوشیدن', 'pronunciation' => '/drɪŋk/', 'category' => 'verbs'],
            ['front' => 'o\'clock', 'back' => 'ساعت', 'pronunciation' => '/əˈklɑk/', 'category' => 'time'],
            ['front' => 'speak', 'back' => 'صحبت کردن', 'pronunciation' => '/spiːk/', 'category' => 'verbs'],
            ['front' => 'tea', 'back' => 'چای', 'pronunciation' => '/tiː/', 'category' => 'drinks'],
            ['front' => 'want', 'back' => 'خواستن', 'pronunciation' => '/wɒnt/', 'category' => 'verbs'],
            ['front' => 'milk', 'back' => 'شیر', 'pronunciation' => '/mɪlk/', 'category' => 'drinks'],
            ['front' => 'egg', 'back' => 'تخم مرغ', 'pronunciation' => '/eg/', 'category' => 'food'],
            ['front' => 'like', 'back' => 'دوست داشتن', 'pronunciation' => '/lɑɪk/', 'category' => 'verbs'],
            ['front' => 'water', 'back' => 'آب', 'pronunciation' => '/ˈwɔːtər/', 'category' => 'drinks'],
            ['front' => 'after', 'back' => 'بعد از', 'pronunciation' => '/ˈæftər/', 'category' => 'prepositions'],
            ['front' => 'salad', 'back' => 'سالاد', 'pronunciation' => '/ˈsæləd/', 'category' => 'food'],
            ['front' => 'juice', 'back' => 'آبمیوه', 'pronunciation' => '/dʒuːs/', 'category' => 'drinks'],
            ['front' => 'work', 'back' => 'کار کردن', 'pronunciation' => '/wɜːrk/', 'category' => 'verbs'],
            ['front' => 'quarter', 'back' => 'ربع (ساعت)', 'pronunciation' => '/ˈkwɔːrtər/', 'category' => 'time'],
            ['front' => 'soda', 'back' => 'سودا', 'pronunciation' => '/ˈsoʊdə/', 'category' => 'drinks'],
            ['front' => 'study', 'back' => 'درس خواندن', 'pronunciation' => '/ˈstʌdi/', 'category' => 'verbs'],
            ['front' => 'near', 'back' => 'نزدیک', 'pronunciation' => '/nɪr/', 'category' => 'prepositions'],
            ['front' => 'to', 'back' => 'مانده به (ساعت)', 'pronunciation' => '/tu/', 'category' => 'prepositions'],
            ['front' => 'here', 'back' => 'اینجا', 'pronunciation' => '/hɪr/', 'category' => 'adverbs'],
            ['front' => 'go', 'back' => 'رفتن', 'pronunciation' => '/goʊ/', 'category' => 'verbs'],
            ['front' => 'vegetable', 'back' => 'سبزیجات', 'pronunciation' => '/ˈvedʒtəbl/', 'category' => 'food'],
            ['front' => 'lunch', 'back' => 'ناهار', 'pronunciation' => '/lʌntʃ/', 'category' => 'meals'],
            ['front' => 'nice', 'back' => 'خوب', 'pronunciation' => '/nɑɪs/', 'category' => 'adjectives'],
            ['front' => 'dinner', 'back' => 'شام', 'pronunciation' => '/ˈdɪnər/', 'category' => 'meals'],
            ['front' => 'haircut', 'back' => 'مدل مو', 'pronunciation' => '/ˈherkʌt/', 'category' => 'body'],
            ['front' => 'morning', 'back' => 'صبح', 'pronunciation' => '/ˈmɔːr.nɪŋ/', 'category' => 'time'],
            ['front' => 'live', 'back' => 'زندگی کردن', 'pronunciation' => '/lɪv/', 'category' => 'verbs'],
            ['front' => 'potato', 'back' => 'سیب‌زمینی', 'pronunciation' => '/pəˈteɪtoʊ/', 'category' => 'food'],
            ['front' => 'afternoon', 'back' => 'بعدازظهر', 'pronunciation' => '/ˌæf.tərˈnuːn/', 'category' => 'time'],
            ['front' => 'have', 'back' => 'داشتن', 'pronunciation' => '/hæv/', 'category' => 'verbs'],
            ['front' => 'evening', 'back' => 'عصر', 'pronunciation' => '/ˈiːv.nɪŋ/', 'category' => 'time'],
            ['front' => 'fruit', 'back' => 'میوه', 'pronunciation' => '/fruːt/', 'category' => 'food'],
            ['front' => 'watch', 'back' => 'تماشا کردن', 'pronunciation' => '/wɒtʃ/', 'category' => 'verbs'],
            ['front' => 'bread', 'back' => 'نان', 'pronunciation' => '/bred/', 'category' => 'food'],
            ['front' => 'butter', 'back' => 'کره', 'pronunciation' => '/ˈbʌtər/', 'category' => 'food'],
            ['front' => 'listen', 'back' => 'گوش دادن', 'pronunciation' => '/ˈlɪs.ən/', 'category' => 'verbs'],
            ['front' => 'breakfast', 'back' => 'صبحانه', 'pronunciation' => '/ˈbrekfəst/', 'category' => 'meals'],
            ['front' => 'time', 'back' => 'ساعت', 'pronunciation' => '/tɑɪm/', 'category' => 'time'],
            ['front' => 'cheese', 'back' => 'پنیر', 'pronunciation' => '/tʃiːz/', 'category' => 'food'],
            ['front' => 'read', 'back' => 'خواندن', 'pronunciation' => '/riːd/', 'category' => 'verbs'],
            ['front' => 'meal', 'back' => 'وعده غذایی', 'pronunciation' => '/miːl/', 'category' => 'meals'],
            ['front' => 'sugar', 'back' => 'شکر', 'pronunciation' => '/ˈʃʊgər/', 'category' => 'food'],
            ['front' => 'eat', 'back' => 'خوردن', 'pronunciation' => '/iːt/', 'category' => 'verbs'],
            ['front' => 'sandwich', 'back' => 'ساندویچ', 'pronunciation' => '/ˈsæn.dwɪtʃ/', 'category' => 'food'],
            ['front' => 'fish', 'back' => '(گوشت) ماهی', 'pronunciation' => '/fɪʃ/', 'category' => 'food'],
            ['front' => 'meat', 'back' => 'گوشت', 'pronunciation' => '/miːt/', 'category' => 'food'],
            ['front' => 'cereal', 'back' => 'غله (گندم و ...)', 'pronunciation' => '/ˈsɪr.iː.əl/', 'category' => 'food'],
            ['front' => 'sausage', 'back' => 'سوسیس', 'pronunciation' => '/ˈsɔː.sɪdʒ/', 'category' => 'food'],
            ['front' => 'pasta', 'back' => 'پاستا', 'pronunciation' => '/ˈpɑːstə/', 'category' => 'food'],
            ['front' => 'toast', 'back' => 'نان برشته', 'pronunciation' => '/toʊst/', 'category' => 'food'],
            ['front' => 'rice', 'back' => 'برنج', 'pronunciation' => '/rɑɪs/', 'category' => 'food'],
            ['front' => 'soup', 'back' => 'سوپ', 'pronunciation' => '/suːp/', 'category' => 'food'],
            ['front' => 'make', 'back' => 'درست کردن', 'pronunciation' => '/meɪk/', 'category' => 'verbs'],
        ];

        foreach ($lesson5Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson5Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson5Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson5Vocabulary) . ' cards for Lesson 5');

        // Lesson 7 vocabulary
        $lesson7Vocabulary = [
            ['front' => 'twenty-first', 'back' => 'بیست و یکم', 'pronunciation' => '/ˈtwɛnti-fɜrst/', 'category' => 'ordinal_numbers'],
            ['front' => 'tourist', 'back' => 'گردشگر', 'pronunciation' => '/ˈtʊr.ɪst/', 'category' => 'people'],
            ['front' => 'February', 'back' => 'فوریه', 'pronunciation' => '/ˈfebrueri/', 'category' => 'months'],
            ['front' => 'inside', 'back' => '(به) داخل (مکان)', 'pronunciation' => '/ɪnˈsɑɪd/', 'category' => 'prepositions'],
            ['front' => 'March', 'back' => 'مارس', 'pronunciation' => '/mɑrtʃ/', 'category' => 'months'],
            ['front' => 'park', 'back' => 'پارک کردن', 'pronunciation' => '/pɑrk/', 'category' => 'verbs'],
            ['front' => 'April', 'back' => 'آوریل', 'pronunciation' => '/ˈeɪprəl/', 'category' => 'months'],
            ['front' => 'eleventh', 'back' => 'یازدهم', 'pronunciation' => '/ɪˈlevnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'swim', 'back' => 'شنا کردن', 'pronunciation' => '/swɪm/', 'category' => 'verbs'],
            ['front' => 'May', 'back' => 'مه', 'pronunciation' => '/meɪ/', 'category' => 'months'],
            ['front' => 'twelfth', 'back' => 'دوازدهم', 'pronunciation' => '/twelfθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'come', 'back' => 'آمدن', 'pronunciation' => '/kʌm/', 'category' => 'verbs'],
            ['front' => 'twenty-fourth', 'back' => 'بیست و چهارم', 'pronunciation' => '/ˈtwɛnti-fɔrθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'June', 'back' => 'ژوئن', 'pronunciation' => '/dʒuːn/', 'category' => 'months'],
            ['front' => 'twenty-seventh', 'back' => 'بیست و هفتم', 'pronunciation' => '/ˈtwɛnti-ˈsɛvənθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'city', 'back' => 'شهر', 'pronunciation' => '/ˈsɪt̬.i/', 'category' => 'places'],
            ['front' => 'July', 'back' => 'ژوئیه', 'pronunciation' => '/dʒʊˈlɑɪ/', 'category' => 'months'],
            ['front' => 'see', 'back' => 'دیدن', 'pronunciation' => '/siː/', 'category' => 'verbs'],
            ['front' => 'August', 'back' => 'اوت', 'pronunciation' => '/ˈɑgəst/', 'category' => 'months'],
            ['front' => 'hear', 'back' => 'شنیدن', 'pronunciation' => '/hɪr/', 'category' => 'verbs'],
            ['front' => 'walk', 'back' => 'راه رفتن', 'pronunciation' => '/wɔːk/', 'category' => 'verbs'],
            ['front' => 'September', 'back' => 'سپتامبر', 'pronunciation' => '/sepˈtembər/', 'category' => 'months'],
            ['front' => 'thirteenth', 'back' => 'سیزدهم', 'pronunciation' => '/ˌθɜːrˈtiːnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'summer', 'back' => 'تابستان', 'pronunciation' => '/ˈsʌmər/', 'category' => 'seasons'],
            ['front' => 'can', 'back' => 'توانستن', 'pronunciation' => '/kæn/', 'category' => 'modal_verbs'],
            ['front' => 'October', 'back' => 'اکتبر', 'pronunciation' => '/ɑkˈtoʊ.bər/', 'category' => 'months'],
            ['front' => 'dark', 'back' => 'تاریک', 'pronunciation' => '/dɑːrk/', 'category' => 'adjectives'],
            ['front' => 'twenty-ninth', 'back' => 'بیست و نهم', 'pronunciation' => '/ˈtwɛnti-naɪnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'fourteenth', 'back' => 'چهاردهم', 'pronunciation' => '/ˌfɔːrˈtiːnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'hot', 'back' => 'داغ', 'pronunciation' => '/hɑːt/', 'category' => 'adjectives'],
            ['front' => 'fifteenth', 'back' => 'پانزدهم', 'pronunciation' => '/ˌfɪfˈtiːnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'November', 'back' => 'نوامبر', 'pronunciation' => '/noʊˈvembər/', 'category' => 'months'],
            ['front' => 'thirtieth', 'back' => 'سی‌ام', 'pronunciation' => '/ˈθɜːrtiəθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'December', 'back' => 'دسامبر', 'pronunciation' => '/dɪˈsembər/', 'category' => 'months'],
            ['front' => 'sixteenth', 'back' => 'شانزدهم', 'pronunciation' => '/ˌsɪkˈstiːnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'ice skating', 'back' => 'اسکیت روی یخ', 'pronunciation' => '/aɪs ˈskeɪtɪŋ/', 'category' => 'sports'],
            ['front' => 'take a break', 'back' => 'استراحت کردن', 'pronunciation' => '/teɪk ə breɪk/', 'category' => 'phrases'],
            ['front' => 'parking lot', 'back' => 'پارکینگ', 'pronunciation' => '/ˈpɑr.kɪŋˌlɑt/', 'category' => 'places'],
            ['front' => 'outside', 'back' => 'بیرون', 'pronunciation' => '/ɑʊtˈsɑɪd/', 'category' => 'prepositions'],
            ['front' => 'seventeenth', 'back' => 'هفدهم', 'pronunciation' => '/ˌsevənˈtiːnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'no parking', 'back' => 'پارک ممنوع', 'pronunciation' => '/noʊ ˈpɑrkɪŋ/', 'category' => 'signs'],
            ['front' => 'twenty-second', 'back' => 'بیست و دوم', 'pronunciation' => '/ˈtwɛnti-ˈsɛkənd/', 'category' => 'ordinal_numbers'],
            ['front' => 'eighteenth', 'back' => 'هجدهم', 'pronunciation' => '/ˌeɪˈtiːnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'change', 'back' => 'پول خرد کردن', 'pronunciation' => '/tʃeɪndʒ/', 'category' => 'verbs'],
            ['front' => 'play', 'back' => 'بازی کردن', 'pronunciation' => '/pleɪ/', 'category' => 'verbs'],
            ['front' => 'same', 'back' => 'همان', 'pronunciation' => '/seɪm/', 'category' => 'adjectives'],
            ['front' => 'money', 'back' => 'پول', 'pronunciation' => '/ˈmʌn.i/', 'category' => 'general'],
            ['front' => 'high', 'back' => 'زیاد', 'pronunciation' => '/hɑɪ/', 'category' => 'adjectives'],
            ['front' => 'nineteenth', 'back' => 'نوزدهم', 'pronunciation' => '/ˌnaɪnˈtiːnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'golf', 'back' => 'گلف', 'pronunciation' => '/ɡɑːlf/', 'category' => 'sports'],
            ['front' => 'winter', 'back' => 'زمستان', 'pronunciation' => '/ˈwɪntər/', 'category' => 'seasons'],
            ['front' => 'twenty-fifth', 'back' => 'بیست و پنجم', 'pronunciation' => '/ˈtwɛnti-fɪfθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'twentieth', 'back' => 'بیستم', 'pronunciation' => '/ˈtwentiəθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'use', 'back' => 'استفاده کردن', 'pronunciation' => '/juːz/', 'category' => 'verbs'],
            ['front' => 'first', 'back' => 'اولین', 'pronunciation' => '/fɜːrst/', 'category' => 'ordinal_numbers'],
            ['front' => 'sport', 'back' => 'ورزش', 'pronunciation' => '/spɔːrt/', 'category' => 'general'],
            ['front' => 'cold', 'back' => 'سرد', 'pronunciation' => '/koʊld/', 'category' => 'adjectives'],
            ['front' => 'life', 'back' => 'زندگی', 'pronunciation' => '/lɑɪf/', 'category' => 'general'],
            ['front' => 'second', 'back' => 'دومین', 'pronunciation' => '/ˈsekənd/', 'category' => 'ordinal_numbers'],
            ['front' => 'Internet', 'back' => 'اینترنت', 'pronunciation' => '/ˈɪntərnet/', 'category' => 'technology'],
            ['front' => 'piano', 'back' => 'پیانو', 'pronunciation' => '/piːˈæn.oʊ/', 'category' => 'instruments'],
            ['front' => 'different', 'back' => 'متفاوت', 'pronunciation' => '/ˈdɪf.rənt/', 'category' => 'adjectives'],
            ['front' => 'low', 'back' => 'کم', 'pronunciation' => '/loʊ/', 'category' => 'adjectives'],
            ['front' => 'third', 'back' => 'سوم', 'pronunciation' => '/θɜrd/', 'category' => 'ordinal_numbers'],
            ['front' => 'twenty-third', 'back' => 'بیست و سوم', 'pronunciation' => '/ˈtwɛnti-θɜrd/', 'category' => 'ordinal_numbers'],
            ['front' => 'ski', 'back' => 'اسکی کردن', 'pronunciation' => '/ski/', 'category' => 'sports'],
            ['front' => 'fourth', 'back' => 'چهارم', 'pronunciation' => '/fɔːrθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'end', 'back' => 'پایان', 'pronunciation' => '/end/', 'category' => 'general'],
            ['front' => 'close to', 'back' => 'نزدیک به', 'pronunciation' => '/kloʊs tu/', 'category' => 'prepositions'],
            ['front' => 'fifth', 'back' => 'پنجم', 'pronunciation' => '/fɪfθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'snowboard', 'back' => 'اسنوبرد سواری کردن', 'pronunciation' => '/ˈsnoʊ.bɔːrd/', 'category' => 'sports'],
            ['front' => 'take photos', 'back' => 'عکس گرفتن', 'pronunciation' => '/teɪk ˈfoʊˌtoʊz/', 'category' => 'phrases'],
            ['front' => 'thirty-first', 'back' => 'سی و یکم', 'pronunciation' => '/ˈθɜrdi-fɜrst/', 'category' => 'ordinal_numbers'],
            ['front' => 'sixth', 'back' => 'ششم', 'pronunciation' => '/sɪksθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'stay', 'back' => 'ماندن', 'pronunciation' => '/steɪ/', 'category' => 'verbs'],
            ['front' => 'far', 'back' => 'دور', 'pronunciation' => '/fɑːr/', 'category' => 'adjectives'],
            ['front' => 'seventh', 'back' => 'هفتم', 'pronunciation' => '/ˈsevənθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'light', 'back' => 'روشن (هوا و ...)', 'pronunciation' => '/lɑɪt/', 'category' => 'adjectives'],
            ['front' => 'twenty-sixth', 'back' => 'بیست و ششم', 'pronunciation' => '/ˈtwɛnti-sɪksθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'eighth', 'back' => 'هشتم', 'pronunciation' => '/eɪtθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'month', 'back' => 'ماه (30 روز)', 'pronunciation' => '/mʌnθ/', 'category' => 'time'],
            ['front' => 'drive', 'back' => 'رانندگی کردن', 'pronunciation' => '/drɑɪv/', 'category' => 'verbs'],
            ['front' => 'ninth', 'back' => 'نهم', 'pronunciation' => '/nɑɪnθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'ordinal number', 'back' => 'عدد ترتیبی (ریاضی)', 'pronunciation' => '/ˈɔːrdənəlˈnʌmbr̩/', 'category' => 'mathematics'],
            ['front' => 'long', 'back' => 'طولانی (زمان)', 'pronunciation' => '/lɔːŋ/', 'category' => 'adjectives'],
            ['front' => 'pay', 'back' => 'پرداخت کردن', 'pronunciation' => '/peɪ/', 'category' => 'verbs'],
            ['front' => 'tenth', 'back' => 'دهم', 'pronunciation' => '/tenθ/', 'category' => 'ordinal_numbers'],
            ['front' => 'January', 'back' => 'ژانویه', 'pronunciation' => '/ˈdʒænjʊeri/', 'category' => 'months'],
            ['front' => 'date', 'back' => 'تاریخ', 'pronunciation' => '/deɪt/', 'category' => 'time'],
        ];

        foreach ($lesson7Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson7Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson7Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson7Vocabulary) . ' cards for Lesson 7');

        // Lesson 8 vocabulary
        $lesson8Vocabulary = [
            ['front' => 'covered', 'back' => 'سرپوشیده', 'pronunciation' => '/ˈkʌvərd/', 'category' => 'adjectives'],
            ['front' => 'comfortable', 'back' => 'راحت', 'pronunciation' => '/ˈkʌmf.tə.bəl/', 'category' => 'adjectives'],
            ['front' => 'hotel', 'back' => 'هتل', 'pronunciation' => '/hoʊˈtel/', 'category' => 'places'],
            ['front' => 'weather', 'back' => 'آب‌وهوا', 'pronunciation' => '/ˈweðər/', 'category' => 'general'],
            ['front' => 'jet lag', 'back' => 'پرواززدگی', 'pronunciation' => '/ʤɛt læg/', 'category' => 'travel'],
            ['front' => 'today', 'back' => 'امروز', 'pronunciation' => '/təˈdeɪ/', 'category' => 'time'],
            ['front' => 'carry', 'back' => 'حمل کردن', 'pronunciation' => '/ˈkær.i/', 'category' => 'verbs'],
            ['front' => 'wear', 'back' => 'پوشیدن', 'pronunciation' => '/wer/', 'category' => 'verbs'],
            ['front' => 'bed and breakfast', 'back' => 'مهمانخانه کوچک', 'pronunciation' => '/bɛd ænd ˈbrɛkfəst/', 'category' => 'places'],
            ['front' => 'suit', 'back' => 'کت‌شلوار', 'pronunciation' => '/suːt/', 'category' => 'clothing'],
            ['front' => 'curious', 'back' => 'کنجکاو', 'pronunciation' => '/ˈkjʊr.iː.əs/', 'category' => 'adjectives'],
            ['front' => 'owner', 'back' => 'مالک', 'pronunciation' => '/ˈoʊ.nər/', 'category' => 'people'],
            ['front' => 'career', 'back' => 'شغل', 'pronunciation' => '/kəˈrɪr/', 'category' => 'general'],
            ['front' => 'take', 'back' => 'رفتن (با وسایل نقلیه)', 'pronunciation' => '/teɪk/', 'category' => 'verbs'],
            ['front' => 'follow', 'back' => 'پیگیری کردن', 'pronunciation' => '/ˈfɑl.oʊ/', 'category' => 'verbs'],
            ['front' => 'it', 'back' => '[ضمیر مصنوعی]', 'pronunciation' => '/ɪt/', 'category' => 'pronouns'],
            ['front' => 'meet', 'back' => 'ملاقات کردن', 'pronunciation' => '/miːt/', 'category' => 'verbs'],
            ['front' => 'enjoy', 'back' => 'لذت بردن', 'pronunciation' => '/enˈdʒɔɪ/', 'category' => 'verbs'],
            ['front' => 'sunny', 'back' => 'آفتابی', 'pronunciation' => '/ˈsʌni/', 'category' => 'weather'],
            ['front' => 'cold', 'back' => 'سرد', 'pronunciation' => '/koʊld/', 'category' => 'weather'],
            ['front' => 'pay', 'back' => 'پرداخت کردن', 'pronunciation' => '/peɪ/', 'category' => 'verbs'],
            ['front' => 'hot', 'back' => 'داغ', 'pronunciation' => '/hɑːt/', 'category' => 'weather'],
            ['front' => 'bill', 'back' => 'صورت‌حساب', 'pronunciation' => '/bɪl/', 'category' => 'general'],
            ['front' => 'windy', 'back' => 'طوفانی', 'pronunciation' => '/ˈwɪndi/', 'category' => 'weather'],
            ['front' => 'have fun', 'back' => 'خوش گذراندن', 'pronunciation' => '/hæv fʌn/', 'category' => 'phrases'],
            ['front' => 'cloudy', 'back' => 'ابری', 'pronunciation' => '/ˈklɑʊdi/', 'category' => 'weather'],
            ['front' => 'guide', 'back' => 'راهنما', 'pronunciation' => '/gaɪd/', 'category' => 'people'],
            ['front' => 'dream', 'back' => 'هدف', 'pronunciation' => '/driːm/', 'category' => 'general'],
            ['front' => 'rainy', 'back' => 'بارانی', 'pronunciation' => '/ˈreɪn.i/', 'category' => 'weather'],
            ['front' => 'view', 'back' => 'منظره', 'pronunciation' => '/vjuː/', 'category' => 'general'],
            ['front' => 'snowy', 'back' => 'برفی', 'pronunciation' => '/ˈsnoʊi/', 'category' => 'weather'],
            ['front' => 'guest', 'back' => 'مهمان', 'pronunciation' => '/gest/', 'category' => 'people'],
            ['front' => 'rain', 'back' => 'باران آمدن', 'pronunciation' => '/reɪn/', 'category' => 'weather'],
            ['front' => 'snow', 'back' => 'برف آمدن', 'pronunciation' => '/snoʊ/', 'category' => 'weather'],
            ['front' => 'star', 'back' => 'ستاره', 'pronunciation' => '/stɑr/', 'category' => 'nature'],
        ];

        foreach ($lesson8Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson8Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson8Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson8Vocabulary) . ' cards for Lesson 8');

        // Lesson 9 vocabulary
        $lesson9Vocabulary = [
            ['front' => 'front', 'back' => 'جلو', 'pronunciation' => '/frʌnt/', 'category' => 'prepositions'],
            ['front' => 'gift shop', 'back' => 'کادوسرا', 'pronunciation' => '/gɪft ʃɑp/', 'category' => 'places'],
            ['front' => 'bookstore', 'back' => 'کتاب‌فروشی', 'pronunciation' => '/ˈbʊkstɔːr/', 'category' => 'places'],
            ['front' => 'floor', 'back' => 'کف', 'pronunciation' => '/flɔːr/', 'category' => 'general'],
            ['front' => 'across from', 'back' => 'آن طرف', 'pronunciation' => '/əˈkrɔs frʌm/', 'category' => 'prepositions'],
            ['front' => 'school', 'back' => 'مدرسه', 'pronunciation' => '/skuːl/', 'category' => 'places'],
            ['front' => 'bathroom', 'back' => 'حمام - دستشویی', 'pronunciation' => '/ˈbæθruːm/', 'category' => 'places'],
            ['front' => 'reception', 'back' => 'پذیرش (هتل و ...)', 'pronunciation' => '/rɪˈsepʃn/', 'category' => 'places'],
            ['front' => 'next to', 'back' => 'کنار', 'pronunciation' => '/nɛkst tʊ/', 'category' => 'prepositions'],
            ['front' => 'yard', 'back' => 'حیاط', 'pronunciation' => '/jɑːrd/', 'category' => 'places'],
            ['front' => 'bathtub', 'back' => 'وان حمام', 'pronunciation' => '/ˈbæθtʌb/', 'category' => 'objects'],
            ['front' => 'hospital', 'back' => 'بیمارستان', 'pronunciation' => '/ˈhɑːspɪtl/', 'category' => 'places'],
            ['front' => 'between', 'back' => 'بین', 'pronunciation' => '/bɪˈtwiːn/', 'category' => 'prepositions'],
            ['front' => 'parking lot', 'back' => 'پارکینگ', 'pronunciation' => '/ˈpɑr.kɪŋˌlɑt/', 'category' => 'places'],
            ['front' => 'city', 'back' => 'شهر', 'pronunciation' => '/ˈsɪt̬.i/', 'category' => 'places'],
            ['front' => 'store', 'back' => 'مغازه', 'pronunciation' => '/stɔːr/', 'category' => 'places'],
            ['front' => 'shower', 'back' => 'دوش', 'pronunciation' => '/ˈʃɑʊər/', 'category' => 'objects'],
            ['front' => 'town', 'back' => 'شهر (کوچک)', 'pronunciation' => '/tɑʊn/', 'category' => 'places'],
            ['front' => 'towel', 'back' => 'حوله', 'pronunciation' => '/ˈtɑʊ.əl/', 'category' => 'objects'],
            ['front' => 'supermarket', 'back' => 'سوپرمارکت', 'pronunciation' => '/ˈsuːpərˌmɑrkɪt/', 'category' => 'places'],
            ['front' => 'village', 'back' => 'روستا', 'pronunciation' => '/ˈvɪləʤ/', 'category' => 'places'],
            ['front' => 'museum', 'back' => 'موزه', 'pronunciation' => '/mjʊˈziːəm/', 'category' => 'places'],
            ['front' => 'bank', 'back' => 'بانک', 'pronunciation' => '/bæŋk/', 'category' => 'places'],
            ['front' => 'toilet', 'back' => 'توالت', 'pronunciation' => '/ˈtɔɪlət/', 'category' => 'places'],
            ['front' => 'on the corner of', 'back' => '(در) نبش', 'pronunciation' => '/ɑn ðə ˈkɔrnər ʌv/', 'category' => 'prepositions'],
            ['front' => 'jungle', 'back' => 'جنگل', 'pronunciation' => '/ˈdʒʌŋ.gəl/', 'category' => 'places'],
            ['front' => 'post office', 'back' => 'اداره پست', 'pronunciation' => '/ˈpoʊstˌɔː.fəs/', 'category' => 'places'],
            ['front' => 'pharmacy', 'back' => 'داروخانه', 'pronunciation' => '/ˈfɑrməsi/', 'category' => 'places'],
            ['front' => 'park', 'back' => 'پارک', 'pronunciation' => '/pɑrk/', 'category' => 'places'],
            ['front' => 'beach', 'back' => 'ساحل', 'pronunciation' => '/biːtʃ/', 'category' => 'places'],
            ['front' => 'hotel', 'back' => 'هتل', 'pronunciation' => '/hoʊˈtel/', 'category' => 'places'],
            ['front' => 'be', 'back' => 'بودن', 'pronunciation' => '/biː/', 'category' => 'verbs'],
            ['front' => 'ocean', 'back' => 'اقیانوس', 'pronunciation' => '/ˈoʊ.ʃən/', 'category' => 'places'],
            ['front' => 'room', 'back' => 'اتاق', 'pronunciation' => '/ruːm/', 'category' => 'places'],
            ['front' => 'swimming pool', 'back' => 'استخر شنا', 'pronunciation' => '/ˈswɪmɪŋ puːl/', 'category' => 'places'],
            ['front' => 'bed', 'back' => 'تختخواب', 'pronunciation' => '/bed/', 'category' => 'objects'],
            ['front' => 'spa', 'back' => 'اسپا (تفریحگاهی دارای سونا، استخر، جکوزی و ...)', 'pronunciation' => '/spɑː/', 'category' => 'places'],
            ['front' => 'river', 'back' => 'رود', 'pronunciation' => '/ˈrɪv.ər/', 'category' => 'places'],
            ['front' => 'there', 'back' => '[وجود داشتن]', 'pronunciation' => '/ðer/', 'category' => 'pronouns'],
            ['front' => 'pillow', 'back' => 'بالش', 'pronunciation' => '/ˈpɪl.oʊ/', 'category' => 'objects'],
            ['front' => 'hot tub', 'back' => 'وان جکوزی', 'pronunciation' => '/ˈhɑːt tʌb/', 'category' => 'objects'],
            ['front' => 'star', 'back' => 'ستاره', 'pronunciation' => '/stɑr/', 'category' => 'nature'],
            ['front' => 'road', 'back' => 'جاده', 'pronunciation' => '/roʊd/', 'category' => 'places'],
            ['front' => 'movie theater', 'back' => 'سینما', 'pronunciation' => '/ˈmuːviˌθiːətər/', 'category' => 'places'],
            ['front' => 'turn', 'back' => 'پیچیدن', 'pronunciation' => '/tɜrn/', 'category' => 'verbs'],
            ['front' => 'gas station', 'back' => 'جایگاه سوخت', 'pronunciation' => '/ˈgæsˌsteɪ.ʃən/', 'category' => 'places'],
            ['front' => 'left', 'back' => '(به) چپ', 'pronunciation' => '/left/', 'category' => 'directions'],
            ['front' => 'in', 'back' => 'در', 'pronunciation' => '/ɪn/', 'category' => 'prepositions'],
            ['front' => 'train station', 'back' => 'ایستگاه راه‌آهن', 'pronunciation' => '/ˈtreɪn ˌsteɪʃən/', 'category' => 'places'],
            ['front' => 'kitchen', 'back' => 'آشپزخانه', 'pronunciation' => '/ˈkɪtʃən/', 'category' => 'places'],
            ['front' => 'airport', 'back' => 'فرودگاه', 'pronunciation' => '/ˈerpɔːrt/', 'category' => 'places'],
            ['front' => 'lamp', 'back' => 'چراغ', 'pronunciation' => '/læmp/', 'category' => 'objects'],
            ['front' => 'at', 'back' => 'در (مکان)', 'pronunciation' => '/æt/', 'category' => 'prepositions'],
            ['front' => 'right', 'back' => '(سمت) راست', 'pronunciation' => '/rɑɪt/', 'category' => 'directions'],
            ['front' => 'gym', 'back' => 'باشگاه', 'pronunciation' => '/dʒɪm/', 'category' => 'places'],
            ['front' => 'remote control', 'back' => 'کنترل (تلویزیون و...)', 'pronunciation' => '/rɪˌmoʊt.kənˈtroʊl/', 'category' => 'objects'],
            ['front' => 'straight', 'back' => 'مستقیم', 'pronunciation' => '/streɪt/', 'category' => 'directions'],
            ['front' => 'on', 'back' => 'در (مکان)', 'pronunciation' => '/ɔːn/', 'category' => 'prepositions'],
            ['front' => 'elevator', 'back' => 'آسانسور', 'pronunciation' => '/ˈelɪveɪtər/', 'category' => 'objects'],
            ['front' => 'restaurant', 'back' => 'رستوران', 'pronunciation' => '/ˈrestərɑnt/', 'category' => 'places'],
            ['front' => 'suitcase', 'back' => 'چمدان', 'pronunciation' => '/ˈsuːt.keɪs/', 'category' => 'objects'],
        ];

        foreach ($lesson9Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson9Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson9Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson9Vocabulary) . ' cards for Lesson 9');

        // Lesson 10 vocabulary
        $lesson10Vocabulary = [
            ['front' => 'home', 'back' => 'خانه', 'pronunciation' => '/hoʊm/', 'category' => 'places'],
            ['front' => 'shave', 'back' => 'اصلاح کردن (صورت، ریش و...)', 'pronunciation' => '/ʃeɪv/', 'category' => 'verbs'],
            ['front' => 'try', 'back' => 'امتحان کردن', 'pronunciation' => '/traɪ/', 'category' => 'verbs'],
            ['front' => 'bike', 'back' => 'دوچرخه', 'pronunciation' => '/baɪk/', 'category' => 'transportation'],
            ['front' => 'get', 'back' => 'شدن', 'pronunciation' => '/get/', 'category' => 'verbs'],
            ['front' => 'skydiving', 'back' => 'چتربازی سقوط آزاد', 'pronunciation' => '/ˈskaɪdaɪvɪŋ/', 'category' => 'sports'],
            ['front' => 'terrible', 'back' => 'افتضاح', 'pronunciation' => '/ˈtɛrəbəl/', 'category' => 'adjectives'],
            ['front' => 'terrifying', 'back' => 'وحشتناک', 'pronunciation' => '/ˈtɛrəˌfaɪɪŋ/', 'category' => 'adjectives'],
            ['front' => 'daily', 'back' => 'روزانه', 'pronunciation' => '/ˈdeɪ.li/', 'category' => 'adjectives'],
            ['front' => 'friendly', 'back' => 'دوستانه', 'pronunciation' => '/ˈfrendli/', 'category' => 'adjectives'],
            ['front' => 'cook', 'back' => 'درست کردن (غذا)', 'pronunciation' => '/kʊk/', 'category' => 'verbs'],
            ['front' => 'routine', 'back' => 'کار همیشگی', 'pronunciation' => '/ruˈtin/', 'category' => 'general'],
            ['front' => 'change', 'back' => 'تغییر دادن', 'pronunciation' => '/tʃeɪndʒ/', 'category' => 'verbs'],
            ['front' => 'help', 'back' => 'کمک کردن', 'pronunciation' => '/help/', 'category' => 'verbs'],
            ['front' => 'life', 'back' => 'زندگی', 'pronunciation' => '/lɑɪf/', 'category' => 'general'],
            ['front' => 'laugh', 'back' => 'خندیدن', 'pronunciation' => '/læf/', 'category' => 'verbs'],
            ['front' => 'look', 'back' => 'نگاه کردن', 'pronunciation' => '/lʊk/', 'category' => 'verbs'],
            ['front' => 'miss', 'back' => 'دلتنگ شدن', 'pronunciation' => '/mɪs/', 'category' => 'verbs'],
            ['front' => 'move', 'back' => 'اسباب‌کشی کردن', 'pronunciation' => '/muːv/', 'category' => 'verbs'],
            ['front' => 'travel', 'back' => 'سفر کردن', 'pronunciation' => '/ˈtrævl/', 'category' => 'verbs'],
            ['front' => 'trip', 'back' => 'سفر', 'pronunciation' => '/trɪp/', 'category' => 'general'],
            ['front' => 'get married', 'back' => 'ازدواج کردن', 'pronunciation' => '/gɛt ˈmærɪd/', 'category' => 'phrases'],
            ['front' => 'mirror', 'back' => 'آینه', 'pronunciation' => '/ˈmɪrər/', 'category' => 'objects'],
        ];

        foreach ($lesson10Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson10Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson10Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson10Vocabulary) . ' cards for Lesson 10');

        // Lesson 11 vocabulary
        $lesson11Vocabulary = [
            ['front' => 'leave', 'back' => 'ترک کردن', 'pronunciation' => '/liːv/', 'category' => 'verbs'],
            ['front' => 'lose', 'back' => 'گم کردن', 'pronunciation' => '/luːz/', 'category' => 'verbs'],
            ['front' => 'send', 'back' => 'فرستادن', 'pronunciation' => '/sɛnd/', 'category' => 'verbs'],
            ['front' => 'party', 'back' => 'مهمانی', 'pronunciation' => '/ˈpɑrti/', 'category' => 'general'],
            ['front' => 'gift', 'back' => 'کادو', 'pronunciation' => '/gɪft/', 'category' => 'objects'],
            ['front' => 'you', 'back' => '[ضمیر دوم شخص مفعولی مفرد و جمع]', 'pronunciation' => '/juː/', 'category' => 'pronouns'],
            ['front' => 'arrive', 'back' => 'رسیدن', 'pronunciation' => '/əˈrɑɪv/', 'category' => 'verbs'],
            ['front' => 'buy', 'back' => 'خریدن', 'pronunciation' => '/baɪ/', 'category' => 'verbs'],
            ['front' => 'learn', 'back' => 'یاد گرفتن', 'pronunciation' => '/lɜːrn/', 'category' => 'verbs'],
            ['front' => 'take', 'back' => 'برداشتن', 'pronunciation' => '/teɪk/', 'category' => 'verbs'],
            ['front' => 'stay', 'back' => 'اقامت داشتن', 'pronunciation' => '/steɪ/', 'category' => 'verbs'],
            ['front' => 'it', 'back' => '[ضمیر سوم شخص مفرد اجسام]', 'pronunciation' => '/ɪt/', 'category' => 'pronouns'],
            ['front' => 'would you like ...?', 'back' => 'آیا ... میل دارید؟', 'pronunciation' => '/wəd jʊ laɪk/', 'category' => 'phrases'],
            ['front' => 'say', 'back' => 'گفتن', 'pronunciation' => '/seɪ/', 'category' => 'verbs'],
            ['front' => 'rock', 'back' => 'موسیقی راک', 'pronunciation' => '/rɑk/', 'category' => 'music'],
            ['front' => 'stranger', 'back' => 'غریبه', 'pronunciation' => '/ˈstreɪn.dʒər/', 'category' => 'people'],
            ['front' => 'pop', 'back' => 'پاپ (ژانر موسیقی)', 'pronunciation' => '/pɑːp/', 'category' => 'music'],
            ['front' => 'think', 'back' => 'فکر کردن', 'pronunciation' => '/θɪŋk/', 'category' => 'verbs'],
            ['front' => 'love', 'back' => 'عاشق بودن', 'pronunciation' => '/lʌv/', 'category' => 'verbs'],
            ['front' => 'hip hop', 'back' => 'موسیقی هیپ هاپ', 'pronunciation' => '/ˈhɪp hɑːp/', 'category' => 'music'],
            ['front' => 'birthday', 'back' => '(روز) تولد', 'pronunciation' => '/ˈbɜːrθdeɪ/', 'category' => 'general'],
            ['front' => 'hate', 'back' => 'متنفر بودن', 'pronunciation' => '/heɪt/', 'category' => 'verbs'],
            ['front' => 'classical', 'back' => 'کلاسیک', 'pronunciation' => '/ˈklæs.ɪ.kəl/', 'category' => 'music'],
            ['front' => 'letter', 'back' => 'نامه', 'pronunciation' => '/ˈletər/', 'category' => 'objects'],
            ['front' => 'awful', 'back' => 'افتضاح', 'pronunciation' => '/ˈɔːfəl/', 'category' => 'adjectives'],
            ['front' => 'tell', 'back' => 'گفتن', 'pronunciation' => '/tel/', 'category' => 'verbs'],
            ['front' => 'fantastic', 'back' => 'خارق‌العاده', 'pronunciation' => '/fænˈtæs.tɪk/', 'category' => 'adjectives'],
            ['front' => 'present', 'back' => 'هدیه', 'pronunciation' => '/ˈprez.ənt/', 'category' => 'objects'],
            ['front' => 'stand', 'back' => 'تحمل کردن', 'pronunciation' => '/stænd/', 'category' => 'verbs'],
            ['front' => 'turn on', 'back' => 'روشن کردن', 'pronunciation' => '/tɜrn ɑn/', 'category' => 'phrases'],
            ['front' => 'email', 'back' => 'ایمیل', 'pronunciation' => '/ˈiː.meɪl/', 'category' => 'technology'],
            ['front' => 'turn off', 'back' => 'خاموش کردن', 'pronunciation' => '/tɜrn ɔf/', 'category' => 'phrases'],
            ['front' => 'story', 'back' => 'داستان', 'pronunciation' => '/ˈstɔːr.i/', 'category' => 'general'],
            ['front' => 'wait', 'back' => 'صبر کردن', 'pronunciation' => '/weɪt/', 'category' => 'verbs'],
            ['front' => 'are', 'back' => 'هستیم', 'pronunciation' => '/ɑr/', 'category' => 'verbs'],
            ['front' => 'pretty', 'back' => 'خیلی', 'pronunciation' => '/ˈprɪti/', 'category' => 'adverbs'],
            ['front' => 'light', 'back' => 'چراغ', 'pronunciation' => '/lɑɪt/', 'category' => 'objects'],
            ['front' => 'begin', 'back' => 'شروع شدن', 'pronunciation' => '/bɪˈgɪn/', 'category' => 'verbs'],
            ['front' => 'call', 'back' => 'زنگ زدن', 'pronunciation' => '/kɔːl/', 'category' => 'verbs'],
            ['front' => 'him', 'back' => '[ضمیر مفعولی سوم شخص مفرد مذکر]', 'pronunciation' => '/hɪm/', 'category' => 'pronouns'],
            ['front' => 'OK', 'back' => 'خوب', 'pronunciation' => '/oʊˈkeɪ/', 'category' => 'adjectives'],
            ['front' => 'find', 'back' => 'پیدا کردن', 'pronunciation' => '/fɑɪnd/', 'category' => 'verbs'],
            ['front' => 'really', 'back' => 'واقعاً', 'pronunciation' => '/ˈriː.li/', 'category' => 'adverbs'],
            ['front' => 'her', 'back' => '[ضمیر مفعولی سوم شخص مفرد مونث]', 'pronunciation' => '/hɜːr/', 'category' => 'pronouns'],
            ['front' => 'get', 'back' => 'دریافت کردن', 'pronunciation' => '/get/', 'category' => 'verbs'],
            ['front' => 'them', 'back' => '[ضمیر مفعولی سوم شخص جمع]', 'pronunciation' => '/ðem/', 'category' => 'pronouns'],
            ['front' => 'great', 'back' => 'عالی', 'pronunciation' => '/greɪt/', 'category' => 'adjectives'],
            ['front' => 'us', 'back' => '[ضمیر مفعولی اول شخص جمع]', 'pronunciation' => '/ʌs/', 'category' => 'pronouns'],
            ['front' => 'give', 'back' => 'دادن', 'pronunciation' => '/gɪv/', 'category' => 'verbs'],
            ['front' => 'be', 'back' => 'بودن', 'pronunciation' => '/biː/', 'category' => 'verbs'],
        ];

        foreach ($lesson11Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson11Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson11Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson11Vocabulary) . ' cards for Lesson 11');

        // Lesson 12 vocabulary
        $lesson12Vocabulary = [
            ['front' => 'going to', 'back' => 'قصد (انجام کاری را) داشتن', 'pronunciation' => '/ˈgoʊɪŋ tu/', 'category' => 'grammar'],
            ['front' => 'make friends', 'back' => 'دوست شدن', 'pronunciation' => '/meɪk frɛndz/', 'category' => 'phrases'],
            ['front' => 'e-mail address', 'back' => 'آدرس ایمیل', 'pronunciation' => '/i-meɪl ˈæˌdrɛs/', 'category' => 'technology'],
            ['front' => 'homework', 'back' => 'تکلیف منزل', 'pronunciation' => '/ˈhoʊmwɜːrk/', 'category' => 'education'],
            ['front' => 'group', 'back' => 'گروه', 'pronunciation' => '/gruːp/', 'category' => 'general'],
            ['front' => 'tomorrow', 'back' => 'فردا', 'pronunciation' => '/təˈmɑˌroʊ/', 'category' => 'time'],
            ['front' => 'question', 'back' => 'سوال', 'pronunciation' => '/ˈkwes.tʃən/', 'category' => 'general'],
            ['front' => 'soccer', 'back' => 'فوتبال', 'pronunciation' => '/ˈsɑːkər/', 'category' => 'sports'],
            ['front' => 'next', 'back' => 'بعد', 'pronunciation' => '/nekst/', 'category' => 'time'],
            ['front' => 'computer game', 'back' => 'بازی رایانه‌ای', 'pronunciation' => '/kəmˈpjutər geɪm/', 'category' => 'technology'],
            ['front' => 'year', 'back' => 'سال', 'pronunciation' => '/jɪr/', 'category' => 'time'],
            ['front' => 'take', 'back' => 'طول کشیدن', 'pronunciation' => '/teɪk/', 'category' => 'verbs'],
            ['front' => 'night', 'back' => 'شب', 'pronunciation' => '/nɑɪt/', 'category' => 'time'],
            ['front' => 'now', 'back' => 'الان', 'pronunciation' => '/nɑʊ/', 'category' => 'time'],
            ['front' => 'future', 'back' => 'آینده', 'pronunciation' => '/ˈfjuː.tʃər/', 'category' => 'time'],
            ['front' => 'problem', 'back' => 'مشکل', 'pronunciation' => '/ˈprɑb.ləm/', 'category' => 'general'],
            ['front' => 'weekend', 'back' => 'آخر هفته', 'pronunciation' => '/ˌwiːkˈend/', 'category' => 'time'],
            ['front' => 'come back', 'back' => 'برگشتن', 'pronunciation' => '/kʌm bæk/', 'category' => 'phrases'],
            ['front' => 'tonight', 'back' => 'امشب', 'pronunciation' => '/təˈnɑɪt/', 'category' => 'time'],
            ['front' => 'start', 'back' => 'آغاز', 'pronunciation' => '/stɑrt/', 'category' => 'verbs'],
            ['front' => 'finish', 'back' => 'پایان', 'pronunciation' => '/ˈfɪn.ɪʃ/', 'category' => 'verbs'],
            ['front' => 'write', 'back' => 'نوشتن', 'pronunciation' => '/raɪt/', 'category' => 'verbs'],
            ['front' => 'adventure', 'back' => 'ماجراجویی', 'pronunciation' => '/ədˈven.tʃər/', 'category' => 'general'],
            ['front' => 'present', 'back' => 'زمان حال (دستور زبان)', 'pronunciation' => '/ˈprez.ənt/', 'category' => 'grammar'],
            ['front' => 'lifetime', 'back' => 'عمر', 'pronunciation' => '/ˈlɑɪf.tɑɪm/', 'category' => 'time'],
            ['front' => 'past', 'back' => 'گذشته (دستور زبان)', 'pronunciation' => '/pæst/', 'category' => 'grammar'],
            ['front' => 'camp', 'back' => 'اردو زدن', 'pronunciation' => '/kæmp/', 'category' => 'verbs'],
        ];

        foreach ($lesson12Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $lesson12Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $lesson12Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($lesson12Vocabulary) . ' cards for Lesson 12');

        // American English File 1 - Lesson 1 vocabulary (first 50 words)
        $aef1Lesson1Vocabulary = [
            ['front' => 'please', 'back' => 'لطفاً', 'pronunciation' => '/pliːz/', 'category' => 'phrases'],
            ['front' => 'listen', 'back' => 'گوش دادن', 'pronunciation' => '/ˈlɪs.ən/', 'category' => 'verbs'],
            ['front' => 'ten', 'back' => 'ده', 'pronunciation' => '/ten/', 'category' => 'numbers'],
            ['front' => 'Thailand', 'back' => 'تایلند', 'pronunciation' => '/ˈtaɪˌlænd/', 'category' => 'countries'],
            ['front' => 'Thursday', 'back' => 'پنجشنبه', 'pronunciation' => '/ˈθɜrzdeɪ/', 'category' => 'time'],
            ['front' => 'thirteen', 'back' => 'سیزده', 'pronunciation' => '/θɜrtˈtiːn/', 'category' => 'numbers'],
            ['front' => 'Mexican', 'back' => 'مکزیکی', 'pronunciation' => '/mɛksɪkən/', 'category' => 'nationalities'],
            ['front' => 'ninety', 'back' => 'نود', 'pronunciation' => '/ˈnɑɪnti/', 'category' => 'numbers'],
            ['front' => 'European', 'back' => 'اروپایی', 'pronunciation' => '/ˌjʊrəˈpiən/', 'category' => 'nationalities'],
            ['front' => 'double room', 'back' => 'اتاق دونفره', 'pronunciation' => '/ˌdʌbl ˈruːm/', 'category' => 'places'],
            ['front' => 'Canadian', 'back' => 'کانادایی', 'pronunciation' => '/kəˈneɪdiən/', 'category' => 'nationalities'],
            ['front' => 'Australia', 'back' => 'استرالیا', 'pronunciation' => '/ɔˈstreɪljə/', 'category' => 'countries'],
            ['front' => 'Friday', 'back' => 'جمعه', 'pronunciation' => '/ˈfrɑɪdeɪ/', 'category' => 'time'],
            ['front' => 'eleven', 'back' => 'یازده', 'pronunciation' => '/ɪˈlevən/', 'category' => 'numbers'],
            ['front' => 'page', 'back' => 'صفحه', 'pronunciation' => '/peɪdʒ/', 'category' => 'classroom'],
            ['front' => 'repeat', 'back' => 'تکرار کردن', 'pronunciation' => '/rəˈpiːt/', 'category' => 'verbs'],
            ['front' => 'Mexico', 'back' => 'مکزیک', 'pronunciation' => '/ˈmɛksəˌkoʊ/', 'category' => 'countries'],
            ['front' => 'can', 'back' => 'توانستن', 'pronunciation' => '/kæn/', 'category' => 'verbs'],
            ['front' => 'fifty-nine', 'back' => 'پنجاه و نه', 'pronunciation' => '/ˈfɪfti-naɪn/', 'category' => 'numbers'],
            ['front' => 'he', 'back' => '[ضمیر سوم شخص مفرد مذکر]', 'pronunciation' => '/hiː/', 'category' => 'pronouns'],
            ['front' => 'fourteen', 'back' => 'چهارده', 'pronunciation' => '/ˌfɔːrtˈtiːn/', 'category' => 'numbers'],
            ['front' => 'forty', 'back' => 'چهل', 'pronunciation' => '/ˈfɔːrti/', 'category' => 'numbers'],
            ['front' => 'Saturday', 'back' => 'شنبه', 'pronunciation' => '/ˈsætərdeɪ/', 'category' => 'time'],
            ['front' => 'Europe', 'back' => 'اروپا', 'pronunciation' => '/ˈjʊrəp/', 'category' => 'places'],
            ['front' => 'twelve', 'back' => 'دوازده', 'pronunciation' => '/twelv/', 'category' => 'numbers'],
            ['front' => 'stand up', 'back' => 'بلند شدن', 'pronunciation' => '/stænd ʌp/', 'category' => 'phrases'],
            ['front' => 'fifteen', 'back' => 'پانزده', 'pronunciation' => '/ˌfɪfˈtiːn/', 'category' => 'numbers'],
            ['front' => 'go', 'back' => 'رفتن', 'pronunciation' => '/goʊ/', 'category' => 'verbs'],
            ['front' => 'where', 'back' => 'کجا', 'pronunciation' => '/wer/', 'category' => 'question_words'],
            ['front' => 'fifty', 'back' => 'پنجاه', 'pronunciation' => '/ˈfɪfti/', 'category' => 'numbers'],
            ['front' => 'sixty', 'back' => 'شصت', 'pronunciation' => '/ˈsɪksti/', 'category' => 'numbers'],
            ['front' => 'Thai', 'back' => 'تایلندی', 'pronunciation' => '/taɪ/', 'category' => 'nationalities'],
            ['front' => 'sixteen', 'back' => 'شانزده', 'pronunciation' => '/sɪksˈtiːn/', 'category' => 'numbers'],
            ['front' => 'North America', 'back' => 'آمریکای شمالی', 'pronunciation' => '/nɔrθ əˈmɛrəkə/', 'category' => 'places'],
            ['front' => 'sit down', 'back' => 'نشستن', 'pronunciation' => '/sɪt daʊn/', 'category' => 'phrases'],
            ['front' => 'for example', 'back' => 'برای مثال', 'pronunciation' => '/fɔːr ɪgˈzæm.pəl/', 'category' => 'phrases'],
            ['front' => 'Sunday', 'back' => 'یکشنبه', 'pronunciation' => '/ˈsʌndeɪ/', 'category' => 'time'],
            ['front' => 'how', 'back' => 'چطور', 'pronunciation' => '/hɑʊ/', 'category' => 'question_words'],
            ['front' => 'do', 'back' => 'انجام دادن', 'pronunciation' => '/du/', 'category' => 'verbs'],
            ['front' => 'ninety-four', 'back' => 'نود و چهار', 'pronunciation' => '/ˈnaɪnti-fɔr/', 'category' => 'numbers'],
            ['front' => 'South Korea', 'back' => 'کره جنوبی', 'pronunciation' => '/saʊθ kɔˈriə/', 'category' => 'countries'],
            ['front' => 'one hundred', 'back' => 'یک صد', 'pronunciation' => '/wʌn ˈhʌndrəd/', 'category' => 'numbers'],
            ['front' => 'South America', 'back' => 'آمریکای جنوبی', 'pronunciation' => '/saʊθ əˈmɛrəkə/', 'category' => 'places'],
            ['front' => 'seventeen', 'back' => 'هفده', 'pronunciation' => '/ˌsevənˈtiːn/', 'category' => 'numbers'],
            ['front' => 'seventy', 'back' => 'هفتاد', 'pronunciation' => '/ˈsevənti/', 'category' => 'numbers'],
            ['front' => 'turn off', 'back' => 'خاموش کردن', 'pronunciation' => '/tɜrn ɔf/', 'category' => 'phrases'],
            ['front' => 'meaning', 'back' => 'معنی', 'pronunciation' => '/ˈmiː.nɪŋ/', 'category' => 'general'],
            ['front' => 'exercise', 'back' => 'ورزش', 'pronunciation' => '/ˈeksərsɑɪz/', 'category' => 'general'],
            ['front' => 'she', 'back' => '[ضمیر سوم شخص مفرد مونث]', 'pronunciation' => '/ʃiː/', 'category' => 'pronouns'],
            ['front' => 'what', 'back' => 'چی', 'pronunciation' => '/wɒt/', 'category' => 'question_words'],
            ['front' => 'eighty', 'back' => 'هشتاد', 'pronunciation' => '/ˈeɪti/', 'category' => 'numbers'],
            ['front' => 'Turkey', 'back' => 'ترکیه', 'pronunciation' => '/ˈtɜːrki/', 'category' => 'countries'],
            ['front' => 'African', 'back' => 'آفریقایی', 'pronunciation' => '/ˈæfrəkən/', 'category' => 'nationalities'],
            ['front' => 'eighteen', 'back' => 'هجده', 'pronunciation' => '/ˌeɪˈtiːn/', 'category' => 'numbers'],
            ['front' => 'cell phone', 'back' => 'تلفن همراه', 'pronunciation' => '/ˈsel foʊn/', 'category' => 'technology'],
            ['front' => 'hundred', 'back' => 'صد', 'pronunciation' => '/ˈhʌn.drəd/', 'category' => 'numbers'],
            ['front' => 'my', 'back' => '[صفت ملکی اول شخص مفرد]', 'pronunciation' => '/maɪ/', 'category' => 'pronouns'],
        ];

        foreach ($aef1Lesson1Vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $aef1Lesson1Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $aef1Lesson1Deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($aef1Lesson1Vocabulary) . ' cards for American English File 1 - Lesson 1 (first batch)');
    }
}
