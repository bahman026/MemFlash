<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaticDeck;
use App\Models\StaticCard;

class StaticCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the static deck for Lesson 1
        $staticDeck = StaticDeck::where('lesson_number', 1)->first();
        
        if (!$staticDeck) {
            $this->command->error('Static deck for Lesson 1 not found. Please run StaticDeckSeeder first.');
            return;
        }

        // Use the static deck directly
        $lesson1Deck = $staticDeck;

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
                    'back' => $cardData['back']
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
    }
}
