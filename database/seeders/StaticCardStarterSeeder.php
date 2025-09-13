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
            ['front' => 'ten', 'back' => 'ده', 'pronunciation' => '/ten /'],
            ['front' => 'spell', 'back' => 'هجی کردن', 'pronunciation' => '/spel /'],
            ['front' => 'Portugal', 'back' => 'پرتغال (کشور)', 'pronunciation' => '/ˈpɔrʧəgəl /'],
            ['front' => 'repeat', 'back' => 'تکرار کردن', 'pronunciation' => '/rəˈpiːt /'],
            ['front' => 'Saudi Arabia', 'back' => 'عربستان سعودی', 'pronunciation' => '/ˈsɔdi əˈreɪbiə /'],
            ['front' => 'one', 'back' => 'یک', 'pronunciation' => '/wʌn /'],
            ['front' => 'Spain', 'back' => 'اسپانیا', 'pronunciation' => '/speɪn /'],
            ['front' => 'English', 'back' => 'زبان انگلیسی', 'pronunciation' => '/ˈɪŋglɪʃ /'],
            ['front' => 'hello', 'back' => 'سلام', 'pronunciation' => '/heˈloʊ /'],
            ['front' => 'coffee', 'back' => 'قهوه', 'pronunciation' => '/ˈkɑːfi /'],
            ['front' => 'Vietnam', 'back' => 'ویتنام', 'pronunciation' => '/ˌvjetˈnæm /'],
            ['front' => 'country', 'back' => 'کشور', 'pronunciation' => '/ˈkʌntri /'],
            ['front' => 'paper', 'back' => 'کاغذ', 'pronunciation' => '/ˈpeɪ.pər /'],
            ['front' => 'hi', 'back' => 'سلام', 'pronunciation' => '/hɑɪ /'],
            ['front' => 'understand', 'back' => 'فهمیدن', 'pronunciation' => '/ˌʌndərˈstænd /'],
            ['front' => 'two', 'back' => 'دو', 'pronunciation' => '/tuː /'],
            ['front' => 'United States of America', 'back' => 'ایالات متحده امریکا', 'pronunciation' => '/juˈnaɪtəd steɪts ʌv əˈmɛrəkə /'],
            ['front' => 'pen', 'back' => 'خودکار', 'pronunciation' => '/pen /'],
            ['front' => 'three', 'back' => 'سه', 'pronunciation' => '/θri /'],
            ['front' => 'four', 'back' => 'چهار', 'pronunciation' => '/fɔːr /'],
            ['front' => 'Nice to meet you.', 'back' => 'از آشنایی با تو خوشوقتم.', 'pronunciation' => '/naɪs tu mit ju. /'],
            ['front' => 'five', 'back' => 'پنج', 'pronunciation' => '/fɑɪv /'],
            ['front' => 'where', 'back' => 'کجا', 'pronunciation' => '/wer /'],
            ['front' => 'he', 'back' => '[ضمیر سوم شخص مفرد مذکر]', 'pronunciation' => '/hiː /'],
            ['front' => 'please', 'back' => 'لطفاً', 'pronunciation' => '/pliːz /'],
            ['front' => 'goodbye', 'back' => 'خداحافظ', 'pronunciation' => '/ˌgʊdˈbɑɪ /'],
            ['front' => 'she', 'back' => '[ضمیر سوم شخص مفرد مونث]', 'pronunciation' => '/ʃiː /'],
            ['front' => 'stand up', 'back' => 'بلند شدن', 'pronunciation' => '/stænd ʌp /'],
            ['front' => 'six', 'back' => 'شش', 'pronunciation' => '/sɪks /'],
            ['front' => 'seven', 'back' => 'هفت', 'pronunciation' => '/ˈsevən /'],
            ['front' => 'it', 'back' => '[ضمیر سوم شخص مفرد اجسام]', 'pronunciation' => '/ɪt /'],
            ['front' => 'excuse me', 'back' => 'ببخشید', 'pronunciation' => '/ɪkˈskjuːz miː /'],
            ['front' => 'sit down', 'back' => 'نشستن', 'pronunciation' => '/sɪt daʊn /'],
            ['front' => 'Monday', 'back' => 'دوشنبه', 'pronunciation' => '/ˈmʌndeɪ /'],
            ['front' => 'see you soon', 'back' => 'به زودی می‌بینمت', 'pronunciation' => '/si ju sun /'],
            ['front' => 'be', 'back' => '[فعل کمکی]', 'pronunciation' => '/biː /'],
            ['front' => 'eight', 'back' => 'هشت', 'pronunciation' => '/eɪt /'],
            ['front' => 'book', 'back' => 'کتاب', 'pronunciation' => '/bʊk /'],
            ['front' => 'nine', 'back' => 'نه', 'pronunciation' => '/nɑɪn /'],
            ['front' => 'zero', 'back' => 'صفر', 'pronunciation' => '/ˈzɪroʊ /'],
            ['front' => 'from', 'back' => 'اهل', 'pronunciation' => '/frʌm /'],
            ['front' => 'sorry', 'back' => 'متاسف', 'pronunciation' => '/ˈsɑr.i /'],
            ['front' => 'Tuesday', 'back' => 'سه‌شنبه', 'pronunciation' => '/ˈtjuːzdeɪ /'],
            ['front' => 'ten', 'back' => 'ده', 'pronunciation' => '/ten /'],
            ['front' => 'week', 'back' => 'هفته', 'pronunciation' => '/wiːk /'],
            ['front' => 'Wednesday', 'back' => 'چهارشنبه', 'pronunciation' => '/ˈwenzdeɪ /'],
            ['front' => 'window', 'back' => 'پنجره', 'pronunciation' => '/ˈwɪndoʊ /'],
            ['front' => 'one', 'back' => 'یک', 'pronunciation' => '/wʌn /'],
            ['front' => 'Brazil', 'back' => 'برزیل', 'pronunciation' => '/brəˈzɪl /'],
            ['front' => 'I', 'back' => 'من', 'pronunciation' => '/ɑɪ /'],
            ['front' => 'two', 'back' => 'دو', 'pronunciation' => '/tuː /'],
            ['front' => 'look at', 'back' => 'نگاه کردن به', 'pronunciation' => '/lʊk æt /'],
            ['front' => 'Thursday', 'back' => 'پنجشنبه', 'pronunciation' => '/ˈθɜrzdeɪ /'],
            ['front' => 'number', 'back' => 'عدد', 'pronunciation' => '/ˈnʌm.bər /'],
            ['front' => 'you', 'back' => '[ضمیر دوم شخص فاعلی مفرد و جمع]', 'pronunciation' => '/juː /'],
            ['front' => 'three', 'back' => 'سه', 'pronunciation' => '/θri /'],
            ['front' => 'Friday', 'back' => 'جمعه', 'pronunciation' => '/ˈfrɑɪdeɪ /'],
            ['front' => 'Canada', 'back' => 'کانادا', 'pronunciation' => '/ˈkænədə /'],
            ['front' => 'chair', 'back' => 'صندلی', 'pronunciation' => '/tʃer /'],
            ['front' => 'open', 'back' => 'باز کردن', 'pronunciation' => '/ˈoʊ.pən /'],
            ['front' => 'Chile', 'back' => 'شیلی', 'pronunciation' => '/ˈtʃɪli /'],
            ['front' => 'coat', 'back' => 'پالتو', 'pronunciation' => '/koʊt /'],
            ['front' => 'Saturday', 'back' => 'شنبه', 'pronunciation' => '/ˈsætərdeɪ /'],
            ['front' => 'classroom', 'back' => 'کلاس', 'pronunciation' => '/ˈklæs.ruːm /'],
            ['front' => 'page', 'back' => 'صفحه', 'pronunciation' => '/peɪdʒ /'],
            ['front' => 'what', 'back' => 'چی', 'pronunciation' => '/wɒt /'],
            ['front' => 'four', 'back' => 'چهار', 'pronunciation' => '/fɔːr /'],
            ['front' => 'Sunday', 'back' => 'یکشنبه', 'pronunciation' => '/ˈsʌndeɪ /'],
            ['front' => 'China', 'back' => 'چین', 'pronunciation' => '/ˈʧaɪnə /'],
            ['front' => 'England', 'back' => 'انگلستان', 'pronunciation' => '/ˈɪŋglənd /'],
            ['front' => 'five', 'back' => 'پنج', 'pronunciation' => '/fɑɪv /'],
            ['front' => 'table', 'back' => 'میز', 'pronunciation' => '/ˈteɪbəl /'],
            ['front' => 'board', 'back' => 'تخته', 'pronunciation' => '/bɔrd /'],
            ['front' => 'Japan', 'back' => 'ژاپن', 'pronunciation' => '/ʤəˈpæn /'],
            ['front' => 'six', 'back' => 'شش', 'pronunciation' => '/sɪks /'],
            ['front' => 'go', 'back' => 'رفتن', 'pronunciation' => '/goʊ /'],
            ['front' => 'UK', 'back' => 'پادشاهی متحد بریتانیا', 'pronunciation' => '/jukeɪ /'],
            ['front' => 'laptop', 'back' => 'لپ‌تاپ', 'pronunciation' => '/ˈlæptɑp /'],
            ['front' => 'seven', 'back' => 'هفت', 'pronunciation' => '/ˈsevən /'],
            ['front' => 'Korea', 'back' => 'کشور کره', 'pronunciation' => '/kɔˈriə /'],
            ['front' => 'close', 'back' => 'بستن', 'pronunciation' => '/kloʊz /'],
            ['front' => 'eight', 'back' => 'هشت', 'pronunciation' => '/eɪt /'],
            ['front' => 'zero', 'back' => 'صفر', 'pronunciation' => '/ˈzɪroʊ /'],
            ['front' => 'Mexico', 'back' => 'مکزیک', 'pronunciation' => '/ˈmɛksəˌkoʊ /'],
            ['front' => 'door', 'back' => 'در', 'pronunciation' => '/dɔːr /'],
            ['front' => 'bye', 'back' => 'خداحافظ', 'pronunciation' => '/bɑɪ /'],
            ['front' => 'US', 'back' => 'ایالات متحده (آمریکا)', 'pronunciation' => '/ˌjuː ˈes /'],
            ['front' => 'nine', 'back' => 'نه', 'pronunciation' => '/nɑɪn /'],
            ['front' => 'Peru', 'back' => 'پرو', 'pronunciation' => '/pəˈruː /'],
            ['front' => 'dictionary', 'back' => 'واژه‌نامه', 'pronunciation' => '/ˈdɪkʃəneri /'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 1');
    }

    protected function seedLesson2($deck)
    {
        $vocabulary = [
            ['front' => 'Brazilian', 'back' => 'برزیلی', 'pronunciation' => '/brəˈzɪljən /'],
            ['front' => 'seventeen', 'back' => 'هفده', 'pronunciation' => '/ˌsevənˈtiːn /'],
            ['front' => 'eleven', 'back' => 'یازده', 'pronunciation' => '/ɪˈlevən /'],
            ['front' => 'Canadian', 'back' => 'کانادایی', 'pronunciation' => '/kəˈneɪdiən /'],
            ['front' => 'eighteen', 'back' => 'هجده', 'pronunciation' => '/ˌeɪˈtiːn /'],
            ['front' => 'forty', 'back' => 'چهل', 'pronunciation' => '/ˈfɔːrti /'],
            ['front' => 'twelve', 'back' => 'دوازده', 'pronunciation' => '/twelv /'],
            ['front' => 'nineteen', 'back' => 'نوزده', 'pronunciation' => '/nɑɪnˈtiːn /'],
            ['front' => 'fifty', 'back' => 'پنجاه', 'pronunciation' => '/ˈfɪfti /'],
            ['front' => 'how', 'back' => 'چطور', 'pronunciation' => '/hɑʊ /'],
            ['front' => 'thirteen', 'back' => 'سیزده', 'pronunciation' => '/θɜrtˈtiːn /'],
            ['front' => 'sixty', 'back' => 'شصت', 'pronunciation' => '/ˈsɪksti /'],
            ['front' => 'twenty', 'back' => 'بیست', 'pronunciation' => '/ˈtwenti /'],
            ['front' => 'seventy', 'back' => 'هفتاد', 'pronunciation' => '/ˈsevənti /'],
            ['front' => 'fourteen', 'back' => 'چهارده', 'pronunciation' => '/ˌfɔːrtˈtiːn /'],
            ['front' => 'eighty', 'back' => 'هشتاد', 'pronunciation' => '/ˈeɪti /'],
            ['front' => 'twenty-nine', 'back' => 'بیست و نه', 'pronunciation' => '/ˈtwɛnti-naɪn /'],
            ['front' => 'fifteen', 'back' => 'پانزده', 'pronunciation' => '/ˌfɪfˈtiːn /'],
            ['front' => 'ninety', 'back' => 'نود', 'pronunciation' => '/ˈnɑɪnti /'],
            ['front' => 'twenty-two', 'back' => 'بیست و دو', 'pronunciation' => '/ˈtwɛnti-tu /'],
            ['front' => 'seat', 'back' => 'جای نشستن', 'pronunciation' => '/siːt /'],
            ['front' => 'sixteen', 'back' => 'شانزده', 'pronunciation' => '/sɪksˈtiːn /'],
            ['front' => 'Spanish', 'back' => 'زبان اسپانیایی', 'pronunciation' => '/ˈspænɪʃ /'],
            ['front' => 'thirty', 'back' => 'سی', 'pronunciation' => '/ˈθɜːrti /'],
            ['front' => 'hundred', 'back' => 'صد', 'pronunciation' => '/ˈhʌn.drəd /'],
            ['front' => 'seventeen', 'back' => 'هفده', 'pronunciation' => '/ˌsevənˈtiːn /'],
            ['front' => 'Chinese', 'back' => 'چینی', 'pronunciation' => '/ʧaɪˈniz /'],
            ['front' => 'where', 'back' => 'کجا', 'pronunciation' => '/wer /'],
            ['front' => 'portuguese', 'back' => 'زبان پرتغالی', 'pronunciation' => '/ˈpɔrʧəˌgiz /'],
            ['front' => 'eighteen', 'back' => 'هجده', 'pronunciation' => '/ˌeɪˈtiːn /'],
            ['front' => 'what', 'back' => 'چی', 'pronunciation' => '/wɒt /'],
            ['front' => 'Japanese', 'back' => 'ژاپنی', 'pronunciation' => '/ˌʤæpəˈniz /'],
            ['front' => 'nineteen', 'back' => 'نوزده', 'pronunciation' => '/nɑɪnˈtiːn /'],
            ['front' => 'Arabic', 'back' => 'زبان عربی', 'pronunciation' => '/ˈærəbɪk /'],
            ['front' => 'who', 'back' => 'چه کسی', 'pronunciation' => '/huː /'],
            ['front' => 'twenty', 'back' => 'بیست', 'pronunciation' => '/ˈtwenti /'],
            ['front' => 'korean', 'back' => 'کره‌ای', 'pronunciation' => '/kɔˈriən /'],
            ['front' => 'language', 'back' => 'زبان', 'pronunciation' => '/ˈlæŋ.gwɪdʒ /'],
            ['front' => 'Mexican', 'back' => 'مکزیکی', 'pronunciation' => '/mɛksɪkən /'],
            ['front' => 'when', 'back' => 'چه زمانی', 'pronunciation' => '/wen /'],
            ['front' => 'thirty-three', 'back' => 'سی و سه', 'pronunciation' => '/ˈθɜrdi-θri /'],
            ['front' => 'Peruvian', 'back' => 'پرویی', 'pronunciation' => '/pəˈruːviən /'],
            ['front' => 'twenty-two', 'back' => 'بیست و دو', 'pronunciation' => '/ˈtwɛnti-tu /'],
            ['front' => 'nationality', 'back' => 'ملیت', 'pronunciation' => '/ˌnæʃəˈnælət̬i /'],
            ['front' => 'we', 'back' => '[ضمیر اول شخص جمع]', 'pronunciation' => '/wiː /'],
            ['front' => 'forty', 'back' => 'چهل', 'pronunciation' => '/ˈfɔːrti /'],
            ['front' => 'fifty', 'back' => 'پنجاه', 'pronunciation' => '/ˈfɪfti /'],
            ['front' => 'portuguese', 'back' => 'پرتغالی', 'pronunciation' => '/ˈpɔrʧəˌgiz /'],
            ['front' => 'sixty', 'back' => 'شصت', 'pronunciation' => '/ˈsɪksti /'],
            ['front' => 'eleven', 'back' => 'یازده', 'pronunciation' => '/ɪˈlevən /'],
            ['front' => 'Saudi', 'back' => 'سعودی', 'pronunciation' => '/ˈsaʊdi /'],
            ['front' => 'seventy', 'back' => 'هفتاد', 'pronunciation' => '/ˈsevənti /'],
            ['front' => 'Spanish', 'back' => 'اسپانیایی', 'pronunciation' => '/ˈspænɪʃ /'],
            ['front' => 'you', 'back' => '[ضمیر دوم شخص فاعلی مفرد و جمع]', 'pronunciation' => '/juː /'],
            ['front' => 'eighty', 'back' => 'هشتاد', 'pronunciation' => '/ˈeɪti /'],
            ['front' => 'British', 'back' => 'بریتانیایی', 'pronunciation' => '/ˈbrɪtɪʃ /'],
            ['front' => 'ninety', 'back' => 'نود', 'pronunciation' => '/ˈnɑɪnti /'],
            ['front' => 'twelve', 'back' => 'دوازده', 'pronunciation' => '/twelv /'],
            ['front' => 'Brazilian', 'back' => 'برزیلی', 'pronunciation' => '/brəˈzɪljən /'],
            ['front' => 'American', 'back' => 'آمریکایی', 'pronunciation' => '/əˈmɛrəkən /'],
            ['front' => 'thirteen', 'back' => 'سیزده', 'pronunciation' => '/θɜrtˈtiːn /'],
            ['front' => 'Vietnamese', 'back' => 'ویتنامی', 'pronunciation' => '/ˌvjetnəˈmiːz /'],
            ['front' => 'hundred', 'back' => 'صد', 'pronunciation' => '/ˈhʌn.drəd /'],
            ['front' => 'fourteen', 'back' => 'چهارده', 'pronunciation' => '/ˌfɔːrtˈtiːn /'],
            ['front' => 'twenty-nine', 'back' => 'بیست و نه', 'pronunciation' => '/ˈtwɛnti-naɪn /'],
            ['front' => 'fifteen', 'back' => 'پانزده', 'pronunciation' => '/ˌfɪfˈtiːn /'],
            ['front' => 'thirty', 'back' => 'سی', 'pronunciation' => '/ˈθɜːrti /'],
            ['front' => 'number', 'back' => 'عدد', 'pronunciation' => '/ˈnʌm.bər /'],
            ['front' => 'sixteen', 'back' => 'شانزده', 'pronunciation' => '/sɪksˈtiːn /'],
            ['front' => 'what', 'back' => 'چه', 'pronunciation' => '/wɒt /'],
            ['front' => 'chilean', 'back' => 'اهل شیلی', 'pronunciation' => '/ˈtʃɪliːən /'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 2');
    }

    protected function seedLesson3($deck)
    {
        $vocabulary = [
            ['front' => 'keychain', 'back' => 'جاکلیدی', 'pronunciation' => '/ki ʧeɪn /'],
            ['front' => 'bus', 'back' => 'اتوبوس', 'pronunciation' => '/bʌs /'],
            ['front' => 'toy', 'back' => 'اسباب‌بازی', 'pronunciation' => '/tɔɪ /'],
            ['front' => 'plane', 'back' => 'هواپیما', 'pronunciation' => '/pleɪn /'],
            ['front' => 'credit card', 'back' => 'کارت اعتباری', 'pronunciation' => '/ˈkred.ɪtˌkɑrd /'],
            ['front' => 'sunglasses', 'back' => 'عینک آفتابی', 'pronunciation' => '/ˈsʌnglæsɪz /'],
            ['front' => 'watch', 'back' => 'ساعت مچی', 'pronunciation' => '/wɒtʃ /'],
            ['front' => 'glasses', 'back' => 'عینک', 'pronunciation' => '/ˈglæsɪz /'],
            ['front' => 'ID card', 'back' => 'کارت شناسایی', 'pronunciation' => '/ɑɪˈdiːˌkɑːrd /'],
            ['front' => 'penny', 'back' => 'پنی', 'pronunciation' => '/ˈpen.i /'],
            ['front' => 'hat', 'back' => 'کلاه', 'pronunciation' => '/hæt /'],
            ['front' => 'wallet', 'back' => 'کیف پول', 'pronunciation' => '/ˈwɑl.ɪt /'],
            ['front' => 'T-shirt', 'back' => 'تی‌شرت', 'pronunciation' => '/ˈtiːʃɜːt /'],
            ['front' => 'photo', 'back' => 'عکس', 'pronunciation' => '/ˈfoʊt̬.oʊ /'],
            ['front' => 'map', 'back' => 'نقشه', 'pronunciation' => '/mæp /'],
            ['front' => 'postcard', 'back' => 'کارت پستال', 'pronunciation' => '/ˈpoʊst.kɑrd /'],
            ['front' => 'camera', 'back' => 'دوربین', 'pronunciation' => '/ˈkæm.rə /'],
            ['front' => 'coffee', 'back' => 'قهوه', 'pronunciation' => '/ˈkɑːfi /'],
            ['front' => 'this', 'back' => '[حرف اشاره مفرد نزدیک]', 'pronunciation' => '/ðɪs /'],
            ['front' => 'that', 'back' => 'آن', 'pronunciation' => '/ðæt /'],
            ['front' => 'glove', 'back' => 'دستکش', 'pronunciation' => '/glʌv /'],
            ['front' => 'these', 'back' => '[ضمیر اشاره جمع نزدیک]', 'pronunciation' => '/ðiːz /'],
            ['front' => 'those', 'back' => '[حرف اشاره جمع دور]', 'pronunciation' => '/ðəʊz /'],
            ['front' => 'bag', 'back' => 'کیف', 'pronunciation' => '/bæg /'],
            ['front' => 'how much', 'back' => 'چه قیمتی', 'pronunciation' => '/haʊ mʌʧ /'],
            ['front' => 'a', 'back' => 'یک', 'pronunciation' => '/eɪ /'],
            ['front' => 'small', 'back' => 'کوچک', 'pronunciation' => '/smɔːl /'],
            ['front' => 'thing', 'back' => 'چیز', 'pronunciation' => '/θɪŋ /'],
            ['front' => 'euro', 'back' => 'یورو', 'pronunciation' => '/ˈjʊr.oʊ /'],
            ['front' => 'an', 'back' => 'یک', 'pronunciation' => '/æn /'],
            ['front' => 'dollar', 'back' => 'دلار', 'pronunciation' => '/ˈdɑl.ər /'],
            ['front' => 'cell phone', 'back' => 'تلفن همراه', 'pronunciation' => '/ˈsel foʊn /'],
            ['front' => 'pound', 'back' => 'پوند (واحد پول با نماد £)', 'pronunciation' => '/pɑʊnd /'],
            ['front' => 'cent', 'back' => 'سنت (یک صدم دلار)', 'pronunciation' => '/sent /'],
            ['front' => 'umbrella', 'back' => 'چتر', 'pronunciation' => '/ʌmˈbrelə /'],
            ['front' => 'that', 'back' => 'آن', 'pronunciation' => '/ðæt /'],
            ['front' => 'this', 'back' => '[حرف اشاره مفرد نزدیک]', 'pronunciation' => '/ðɪs /'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 3');
    }

    private function seedLesson4($deck)
    {
        $vocabulary = [
            ['front' => 'boy', 'back' => 'پسر', 'pronunciation' => '/bɔɪ/'],
            ['front' => 'big', 'back' => 'بزرگ', 'pronunciation' => '/bɪg/'],
            ['front' => 'girl', 'back' => 'دختر', 'pronunciation' => '/ɡɜːrl/'],
            ['front' => 'my', 'back' => '[صفت ملکی اول شخص مفرد]', 'pronunciation' => '/maɪ/'],
            ['front' => 'small', 'back' => 'کوچک', 'pronunciation' => '/smɔːl/'],
            ['front' => 'very', 'back' => 'خیلی', 'pronunciation' => '/ˈveri/'],
            ['front' => 'man', 'back' => 'مرد', 'pronunciation' => '/mæn/'],
            ['front' => 'woman', 'back' => 'زن', 'pronunciation' => '/ˈwʊmən/'],
            ['front' => 'old', 'back' => 'قدیمی', 'pronunciation' => '/oʊld/'],
            ['front' => 'new', 'back' => 'جدید', 'pronunciation' => '/nuː/'],
            ['front' => 'children', 'back' => 'کودکان', 'pronunciation' => '/ˈʧɪldrən/'],
            ['front' => 'fast', 'back' => 'سریع', 'pronunciation' => '/fæst/'],
            ['front' => 'person', 'back' => 'فرد', 'pronunciation' => '/ˈpɜr.sən/'],
            ['front' => 'slow', 'back' => 'آهسته', 'pronunciation' => '/sloʊ/'],
            ['front' => 'luxurious', 'back' => 'مجلل', 'pronunciation' => '/lʌɡˈʒʊriəs/'],
            ['front' => 'good', 'back' => 'خوب', 'pronunciation' => '/gʊd/'],
            ['front' => 'car', 'back' => 'خودرو', 'pronunciation' => '/kɑːr/'],
            ['front' => 'common', 'back' => 'متداول', 'pronunciation' => '/ˈkɑmən/'],
            ['front' => 'husband', 'back' => 'شوهر', 'pronunciation' => '/ˈhʌz.bənd/'],
            ['front' => 'their', 'back' => '[صفت ملکی سوم شخص جمع]', 'pronunciation' => '/ðer/'],
            ['front' => 'cool', 'back' => 'باحال', 'pronunciation' => '/kuːl/'],
            ['front' => 'convertible', 'back' => 'خودروی کروکی', 'pronunciation' => '/kənˈvɜrtəbəl/'],
            ['front' => 'bad', 'back' => 'بد', 'pronunciation' => '/bæd/'],
            ['front' => 'wife', 'back' => 'همسر', 'pronunciation' => '/waɪf/'],
            ['front' => 'park', 'back' => 'پارک', 'pronunciation' => '/pɑrk/'],
            ['front' => 'mother', 'back' => 'مادر', 'pronunciation' => '/ˈmʌðər/'],
            ['front' => 'father', 'back' => 'پدر', 'pronunciation' => '/ˈfɑðər/'],
            ['front' => 'safe', 'back' => 'امن', 'pronunciation' => '/seɪf/'],
            ['front' => 'son', 'back' => '(فرزند) پسر', 'pronunciation' => '/sʌn/'],
            ['front' => 'important', 'back' => 'مهم', 'pronunciation' => '/ɪmˈpɔːrtnt/'],
            ['front' => 'color', 'back' => 'رنگ', 'pronunciation' => '/ˈkʌlər/'],
            ['front' => 'cheap', 'back' => 'ارزان', 'pronunciation' => '/tʃiːp/'],
            ['front' => 'popular', 'back' => 'پرطرفدار', 'pronunciation' => '/ˈpɑːpjələr/'],
            ['front' => 'red', 'back' => 'قرمز', 'pronunciation' => '/rɛd/'],
            ['front' => 'our', 'back' => '[صفت ملکی اول شخص جمع]', 'pronunciation' => '/ɑʊr/'],
            ['front' => 'green', 'back' => 'سبز', 'pronunciation' => '/griːn/'],
            ['front' => 'family', 'back' => 'خانواده', 'pronunciation' => '/ˈfæm.ə.li/'],
            ['front' => 'expensive', 'back' => 'گران', 'pronunciation' => '/ɪkˈspensɪv/'],
            ['front' => 'yellow', 'back' => 'زرد', 'pronunciation' => '/ˈjeloʊ/'],
            ['front' => 'her', 'back' => '[صفت ملکی سوم شخص مفرد مونث]', 'pronunciation' => '/hɜːr/'],
            ['front' => 'long', 'back' => 'بلند', 'pronunciation' => '/lɔːŋ/'],
            ['front' => 'friend', 'back' => 'دوست', 'pronunciation' => '/frend/'],
            ['front' => 'blue', 'back' => 'آبی', 'pronunciation' => '/bluː/'],
            ['front' => 'short', 'back' => 'کوتاه', 'pronunciation' => '/ʃɔːrt/'],
            ['front' => 'his', 'back' => '[صفت ملکی سوم شخص مفرد مذکر]', 'pronunciation' => '/hɪz/'],
            ['front' => 'orange', 'back' => 'نارنجی', 'pronunciation' => '/ˈɑrɪndʒ/'],
            ['front' => 'daughter', 'back' => '(فرزند) دختر', 'pronunciation' => '/ˈdɔːtər/'],
            ['front' => 'tall', 'back' => 'بلندقد', 'pronunciation' => '/tɔl/'],
            ['front' => 'brown', 'back' => 'قهوه‌ای', 'pronunciation' => '/braʊn/'],
            ['front' => 'brother', 'back' => 'برادر', 'pronunciation' => '/ˈbrʌðər/'],
            ['front' => 'its', 'back' => '[صفت ملکی سوم شخص مفرد خنثی]', 'pronunciation' => '/ɪts/'],
            ['front' => 'black', 'back' => 'سیاه', 'pronunciation' => '/blæk/'],
            ['front' => 'sister', 'back' => 'خواهر', 'pronunciation' => '/ˈsɪstər/'],
            ['front' => 'white', 'back' => 'سفید', 'pronunciation' => '/wɑɪt/'],
            ['front' => 'boyfriend', 'back' => 'دوست‌پسر', 'pronunciation' => '/ˈbɔɪ.frend/'],
            ['front' => 'your', 'back' => '[صفت ملکی دوم شخص مفرد و جمع]', 'pronunciation' => '/jʊr/'],
            ['front' => 'people', 'back' => 'مردم', 'pronunciation' => '/ˈpiː.pəl/'],
            ['front' => 'girlfriend', 'back' => 'دوست‌دختر', 'pronunciation' => '/ˈgɜrl.frend/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 4');
    }

    protected function seedLesson5($deck)
    {
        $vocabulary = [
            ['front' => 'chocolate', 'back' => 'شکلات', 'pronunciation' => '/ˈtʃɑk.lət /'],
            ['front' => 'hair', 'back' => 'مو', 'pronunciation' => '/her /'],
            ['front' => 'croissant', 'back' => 'کروسان', 'pronunciation' => '/krəˈsɑːnt /'],
            ['front' => 'day', 'back' => 'روز', 'pronunciation' => '/deɪ /'],
            ['front' => 'coffee', 'back' => 'قهوه', 'pronunciation' => '/ˈkɑːfi /'],
            ['front' => 'drink', 'back' => 'نوشیدن', 'pronunciation' => '/drɪŋk /'],
            ['front' => 'o\'clock', 'back' => 'ساعت', 'pronunciation' => '/əˈklɑk /'],
            ['front' => 'speak', 'back' => 'صحبت کردن', 'pronunciation' => '/spiːk /'],
            ['front' => 'tea', 'back' => 'چای', 'pronunciation' => '/tiː /'],
            ['front' => 'want', 'back' => 'خواستن', 'pronunciation' => '/wɒnt /'],
            ['front' => 'milk', 'back' => 'شیر', 'pronunciation' => '/mɪlk /'],
            ['front' => 'egg', 'back' => 'تخم مرغ', 'pronunciation' => '/eg /'],
            ['front' => 'like', 'back' => 'دوست داشتن', 'pronunciation' => '/lɑɪk /'],
            ['front' => 'water', 'back' => 'آب', 'pronunciation' => '/ˈwɔːtər /'],
            ['front' => 'after', 'back' => 'بعد از', 'pronunciation' => '/ˈæftər /'],
            ['front' => 'salad', 'back' => 'سالاد', 'pronunciation' => '/ˈsæləd /'],
            ['front' => 'juice', 'back' => 'آبمیوه', 'pronunciation' => '/dʒuːs /'],
            ['front' => 'work', 'back' => 'کار کردن', 'pronunciation' => '/wɜːrk /'],
            ['front' => 'quarter', 'back' => 'ربع (ساعت)', 'pronunciation' => '/ˈkwɔːrtər /'],
            ['front' => 'soda', 'back' => 'سودا', 'pronunciation' => '/ˈsoʊdə /'],
            ['front' => 'study', 'back' => 'درس خواندن', 'pronunciation' => '/ˈstʌdi /'],
            ['front' => 'near', 'back' => 'نزدیک', 'pronunciation' => '/nɪr /'],
            ['front' => 'to', 'back' => 'مانده به (ساعت)', 'pronunciation' => '/tu /'],
            ['front' => 'here', 'back' => 'اینجا', 'pronunciation' => '/hɪr /'],
            ['front' => 'go', 'back' => 'رفتن', 'pronunciation' => '/goʊ /'],
            ['front' => 'vegetable', 'back' => 'سبزیجات', 'pronunciation' => '/ˈvedʒtəbl /'],
            ['front' => 'lunch', 'back' => 'ناهار', 'pronunciation' => '/lʌntʃ /'],
            ['front' => 'nice', 'back' => 'خوب', 'pronunciation' => '/nɑɪs /'],
            ['front' => 'dinner', 'back' => 'شام', 'pronunciation' => '/ˈdɪnər /'],
            ['front' => 'haircut', 'back' => 'مدل مو', 'pronunciation' => '/ˈherkʌt /'],
            ['front' => 'morning', 'back' => 'صبح', 'pronunciation' => '/ˈmɔːr.nɪŋ /'],
            ['front' => 'live', 'back' => 'زندگی کردن', 'pronunciation' => '/lɪv /'],
            ['front' => 'potato', 'back' => 'سیب‌زمینی', 'pronunciation' => '/pəˈteɪtoʊ /'],
            ['front' => 'afternoon', 'back' => 'بعدازظهر', 'pronunciation' => '/ˌæf.tərˈnuːn /'],
            ['front' => 'have', 'back' => 'داشتن', 'pronunciation' => '/hæv /'],
            ['front' => 'evening', 'back' => 'عصر', 'pronunciation' => '/ˈiːv.nɪŋ /'],
            ['front' => 'fruit', 'back' => 'میوه', 'pronunciation' => '/fruːt /'],
            ['front' => 'watch', 'back' => 'تماشا کردن', 'pronunciation' => '/wɒtʃ /'],
            ['front' => 'bread', 'back' => 'نان', 'pronunciation' => '/bred /'],
            ['front' => 'butter', 'back' => 'کره', 'pronunciation' => '/ˈbʌtər /'],
            ['front' => 'listen', 'back' => 'گوش دادن', 'pronunciation' => '/ˈlɪs.ən /'],
            ['front' => 'breakfast', 'back' => 'صبحانه', 'pronunciation' => '/ˈbrekfəst /'],
            ['front' => 'time', 'back' => 'ساعت', 'pronunciation' => '/tɑɪm /'],
            ['front' => 'cheese', 'back' => 'پنیر', 'pronunciation' => '/tʃiːz /'],
            ['front' => 'read', 'back' => 'خواندن', 'pronunciation' => '/riːd /'],
            ['front' => 'meal', 'back' => 'وعده غذایی', 'pronunciation' => '/miːl /'],
            ['front' => 'sugar', 'back' => 'شکر', 'pronunciation' => '/ˈʃʊgər /'],
            ['front' => 'eat', 'back' => 'خوردن', 'pronunciation' => '/iːt /'],
            ['front' => 'sandwich', 'back' => 'ساندویچ', 'pronunciation' => '/ˈsæn.dwɪtʃ /'],
            ['front' => 'fish', 'back' => '(گوشت) ماهی', 'pronunciation' => '/fɪʃ /'],
            ['front' => 'meat', 'back' => 'گوشت', 'pronunciation' => '/miːt /'],
            ['front' => 'cereal', 'back' => 'غله (گندم و ...)', 'pronunciation' => '/ˈsɪr.iː.əl /'],
            ['front' => 'sausage', 'back' => 'سوسیس', 'pronunciation' => '/ˈsɔː.sɪdʒ /'],
            ['front' => 'pasta', 'back' => 'پاستا', 'pronunciation' => '/ˈpɑːstə /'],
            ['front' => 'toast', 'back' => 'نان برشته', 'pronunciation' => '/toʊst /'],
            ['front' => 'rice', 'back' => 'برنج', 'pronunciation' => '/rɑɪs /'],
            ['front' => 'soup', 'back' => 'سوپ', 'pronunciation' => '/suːp /'],
            ['front' => 'make', 'back' => 'درست کردن', 'pronunciation' => '/meɪk /'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 5');
    }

    private function seedLesson6($deck)
    {
        $vocabulary = [
            ['front' => 'afternoon', 'back' => 'بعدازظهر', 'pronunciation' => '/ˌæf.tərˈnuːn/'],
            ['front' => 'sometimes', 'back' => 'گاهی', 'pronunciation' => '/ˈsʌm.tɑɪmz/'],
            ['front' => 'doctor', 'back' => 'پزشک', 'pronunciation' => '/ˈdɑk.tər/'],
            ['front' => 'world', 'back' => 'جهان', 'pronunciation' => '/wɜːld/'],
            ['front' => 'never', 'back' => 'هرگز', 'pronunciation' => '/ˈnev.ər/'],
            ['front' => 'nurse', 'back' => 'پرستار', 'pronunciation' => '/nɜrs/'],
            ['front' => 'restaurant', 'back' => 'رستوران', 'pronunciation' => '/ˈrestərɑnt/'],
            ['front' => 'evening', 'back' => 'عصر', 'pronunciation' => '/ˈiːv.nɪŋ/'],
            ['front' => 'office', 'back' => 'اداره', 'pronunciation' => '/ˈɔːfɪs/'],
            ['front' => 'lunch', 'back' => 'ناهار', 'pronunciation' => '/lʌntʃ/'],
            ['front' => 'salesperson', 'back' => 'فروشنده', 'pronunciation' => '/ˈseɪlzˌpɜr.sən/'],
            ['front' => 'dinner', 'back' => 'شام', 'pronunciation' => '/ˈdɪnər/'],
            ['front' => 'typical', 'back' => 'کلیشه‌ای', 'pronunciation' => '/ˈtɪpəkəl/'],
            ['front' => 'waiter', 'back' => 'پیشخدمت (آقا)', 'pronunciation' => '/ˈweɪtər/'],
            ['front' => 'headquarters', 'back' => 'اداره کل', 'pronunciation' => '/ˈhɛdˌkwɔrtərz/'],
            ['front' => 'waitress', 'back' => 'پیشخدمت (خانم)', 'pronunciation' => '/ˈweɪtrəs/'],
            ['front' => 'when', 'back' => 'زمانی که', 'pronunciation' => '/wen/'],
            ['front' => 'manager', 'back' => 'مدیر', 'pronunciation' => '/ˈmæn.ɪdʒ.ər/'],
            ['front' => 'finish', 'back' => 'تمام کردن', 'pronunciation' => '/ˈfɪn.ɪʃ/'],
            ['front' => 'meeting', 'back' => 'جلسه', 'pronunciation' => '/ˈmiːt̬.ɪŋ/'],
            ['front' => 'school', 'back' => 'مدرسه', 'pronunciation' => '/skuːl/'],
            ['front' => 'get up', 'back' => 'بیدار شدن', 'pronunciation' => '/gɛt ʌp/'],
            ['front' => 'go shopping', 'back' => 'به خرید رفتن', 'pronunciation' => '/goʊ ˈʃɑpɪŋ/'],
            ['front' => 'then', 'back' => 'سپس', 'pronunciation' => '/ðen/'],
            ['front' => 'home', 'back' => 'خانه', 'pronunciation' => '/hoʊm/'],
            ['front' => 'document', 'back' => 'سند', 'pronunciation' => '/ˈdɑkjəmɛnt/'],
            ['front' => 'assistant', 'back' => 'دستیار', 'pronunciation' => '/əˈsɪs.tənt/'],
            ['front' => 'go home', 'back' => 'به خانه رفتن', 'pronunciation' => '/goʊ hoʊm/'],
            ['front' => 'street', 'back' => 'خیابان', 'pronunciation' => '/striːt/'],
            ['front' => 'lawyer', 'back' => 'وکیل', 'pronunciation' => '/ˈlɔɪ.ər/'],
            ['front' => 'gym', 'back' => 'باشگاه', 'pronunciation' => '/dʒɪm/'],
            ['front' => 'policeman', 'back' => 'پلیس', 'pronunciation' => '/pəˈliːsmən/'],
            ['front' => 'about', 'back' => 'حدود', 'pronunciation' => '/əˈbɑʊt/'],
            ['front' => 'breakfast', 'back' => 'صبحانه', 'pronunciation' => '/ˈbrekfəst/'],
            ['front' => 'make dinner', 'back' => 'شام درست کردن', 'pronunciation' => '/meɪk ˈdɪnər/'],
            ['front' => 'before', 'back' => 'قبل از', 'pronunciation' => '/bɪˈfɔːr/'],
            ['front' => 'policewoman', 'back' => 'مامور پلیس (زن)', 'pronunciation' => '/pəˈliːs.wʊm.ən/'],
            ['front' => 'housework', 'back' => 'کار خانه (نظافت و آشپزی و...)', 'pronunciation' => '/ˈhɑʊs.wɜrk/'],
            ['front' => 'take a shower', 'back' => 'دوش گرفتن', 'pronunciation' => '/teɪk ə ˈʃaʊər/'],
            ['front' => 'watch TV', 'back' => 'تلویزیون تماشا کردن', 'pronunciation' => '/wɑʧ tivi/'],
            ['front' => 'take a bath', 'back' => 'حمام کردن', 'pronunciation' => '/teɪk ə bæθ/'],
            ['front' => 'train', 'back' => 'قطار', 'pronunciation' => '/treɪn/'],
            ['front' => 'multinational', 'back' => 'چندملیتی', 'pronunciation' => '/ˌmʌlˌtaɪˈnæʃənəl/'],
            ['front' => 'do', 'back' => 'انجام دادن', 'pronunciation' => '/du/'],
            ['front' => 'factory', 'back' => 'کارخانه', 'pronunciation' => '/ˈfæk.tə.ri/'],
            ['front' => 'bed', 'back' => 'تختخواب', 'pronunciation' => '/bed/'],
            ['front' => 'sandwich', 'back' => 'ساندویچ', 'pronunciation' => '/ˈsæn.dwɪtʃ/'],
            ['front' => 'very much', 'back' => 'خیلی', 'pronunciation' => '/ˈvɛri mʌʧ/'],
            ['front' => 'worker', 'back' => 'کارگر', 'pronunciation' => '/ˈwɜr.kər/'],
            ['front' => 'tour guide', 'back' => 'راهنمای گردش', 'pronunciation' => '/ˈtʊrˌgɑɪd/'],
            ['front' => 'student', 'back' => 'دانش‌آموز', 'pronunciation' => '/ˈstuːdənt/'],
            ['front' => 'job', 'back' => 'شغل', 'pronunciation' => '/dʒɑːb/'],
            ['front' => 'place', 'back' => 'مکان', 'pronunciation' => '/pleɪs/'],
            ['front' => 'morning', 'back' => 'صبح', 'pronunciation' => '/ˈmɔːr.nɪŋ/'],
            ['front' => 'hospital', 'back' => 'بیمارستان', 'pronunciation' => '/ˈhɑːspɪtl/'],
            ['front' => 'work', 'back' => 'شغل', 'pronunciation' => '/wɜːrk/'],
            ['front' => 'always', 'back' => 'همیشه', 'pronunciation' => '/ˈɔːl.weɪz/'],
            ['front' => 'usually', 'back' => 'معمولاً', 'pronunciation' => '/ˈjuː.ʒu.ə.li/'],
            ['front' => 'teacher', 'back' => 'معلم', 'pronunciation' => '/ˈtiːtʃər/'],
            ['front' => 'all over', 'back' => 'همه‌جا', 'pronunciation' => '/ɔl ˈoʊvər/'],
            ['front' => 'store', 'back' => 'مغازه', 'pronunciation' => '/stɔːr/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 6');
    }

    protected function seedLesson7($deck)
    {
        $vocabulary = [
            ['front' => 'twenty-first', 'back' => 'بیست و یکم', 'pronunciation' => '/ˈtwɛnti-fɜrst /'],
            ['front' => 'tourist', 'back' => 'گردشگر', 'pronunciation' => '/ˈtʊr.ɪst /'],
            ['front' => 'February', 'back' => 'فوریه', 'pronunciation' => '/ˈfebrueri /'],
            ['front' => 'inside', 'back' => '(به) داخل (مکان)', 'pronunciation' => '/ɪnˈsɑɪd /'],
            ['front' => 'March', 'back' => 'مارس', 'pronunciation' => '/mɑrtʃ /'],
            ['front' => 'park', 'back' => 'پارک کردن', 'pronunciation' => '/pɑrk /'],
            ['front' => 'April', 'back' => 'آوریل', 'pronunciation' => '/ˈeɪprəl /'],
            ['front' => 'eleventh', 'back' => 'یازدهم', 'pronunciation' => '/ɪˈlevnθ /'],
            ['front' => 'swim', 'back' => 'شنا کردن', 'pronunciation' => '/swɪm /'],
            ['front' => 'May', 'back' => 'مه', 'pronunciation' => '/meɪ /'],
            ['front' => 'twelfth', 'back' => 'دوازدهم', 'pronunciation' => '/twelfθ /'],
            ['front' => 'come', 'back' => 'آمدن', 'pronunciation' => '/kʌm /'],
            ['front' => 'twenty-forth', 'back' => 'بیست و چهارم', 'pronunciation' => '/ˈtwɛnti-fɔrθ /'],
            ['front' => 'June', 'back' => 'ژوئن', 'pronunciation' => '/dʒuːn /'],
            ['front' => 'twenty-seventh', 'back' => 'بیست و هفتم', 'pronunciation' => '/ˈtwɛnti-ˈsɛvənθ /'],
            ['front' => 'city', 'back' => 'شهر', 'pronunciation' => '/ˈsɪt̬.i /'],
            ['front' => 'July', 'back' => 'ژوئیه', 'pronunciation' => '/dʒʊˈlɑɪ /'],
            ['front' => 'see', 'back' => 'دیدن', 'pronunciation' => '/siː /'],
            ['front' => 'August', 'back' => 'اوت', 'pronunciation' => '/ˈɑgəst /'],
            ['front' => 'hear', 'back' => 'شنیدن', 'pronunciation' => '/hɪr /'],
            ['front' => 'walk', 'back' => 'راه رفتن', 'pronunciation' => '/wɔːk /'],
            ['front' => 'September', 'back' => 'سپتامبر', 'pronunciation' => '/sepˈtembər /'],
            ['front' => 'thirteenth', 'back' => 'سیزدهم', 'pronunciation' => '/ˌθɜːrˈtiːnθ /'],
            ['front' => 'summer', 'back' => 'تابستان', 'pronunciation' => '/ˈsʌmər /'],
            ['front' => 'can', 'back' => 'توانستن', 'pronunciation' => '/kæn /'],
            ['front' => 'October', 'back' => 'اکتبر', 'pronunciation' => '/ɑkˈtoʊ.bər /'],
            ['front' => 'dark', 'back' => 'تاریک', 'pronunciation' => '/dɑːrk /'],
            ['front' => 'twenty-ninth', 'back' => 'بیست و نهم', 'pronunciation' => '/ˈtwɛnti-naɪnθ /'],
            ['front' => 'fourteenth', 'back' => 'چهاردهم', 'pronunciation' => '/ˌfɔːrˈtiːnθ /'],
            ['front' => 'hot', 'back' => 'داغ', 'pronunciation' => '/hɑːt /'],
            ['front' => 'fifteenth', 'back' => 'پانزدهم', 'pronunciation' => '/ˌfɪfˈtiːnθ /'],
            ['front' => 'November', 'back' => 'نوامبر', 'pronunciation' => '/noʊˈvembər /'],
            ['front' => 'thirtieth', 'back' => 'سی‌ام', 'pronunciation' => '/ˈθɜːrtiəθ /'],
            ['front' => 'December', 'back' => 'دسامبر', 'pronunciation' => '/dɪˈsembər /'],
            ['front' => 'sixteenth', 'back' => 'شانزدهم', 'pronunciation' => '/ˌsɪkˈtiːnθ /'],
            ['front' => 'ice skating', 'back' => 'اسکیت روی یخ', 'pronunciation' => '/aɪs ˈskeɪtɪŋ /'],
            ['front' => 'take a break', 'back' => 'استراحت کردن', 'pronunciation' => '/teɪk ə breɪk /'],
            ['front' => 'parking lot', 'back' => 'پارکینگ', 'pronunciation' => '/ˈpɑr.kɪŋˌlɑt /'],
            ['front' => 'outside', 'back' => 'بیرون', 'pronunciation' => '/ɑʊtˈsɑɪd /'],
            ['front' => 'seventeenth', 'back' => 'هفدهم', 'pronunciation' => '/ˌsevənˈtiːnθ /'],
            ['front' => 'no parking', 'back' => 'پارک ممنوع', 'pronunciation' => '/noʊ ˈpɑrkɪŋ /'],
            ['front' => 'twenty-second', 'back' => 'بیست و دوم', 'pronunciation' => '/ˈtwɛnti-ˈsɛkənd /'],
            ['front' => 'eighteenth', 'back' => 'هجدهم', 'pronunciation' => '/ˌeɪˈtiːnθ /'],
            ['front' => 'change', 'back' => 'پول خرد کردن', 'pronunciation' => '/tʃeɪndʒ /'],
            ['front' => 'play', 'back' => 'بازی کردن', 'pronunciation' => '/pleɪ /'],
            ['front' => 'same', 'back' => 'همان', 'pronunciation' => '/seɪm /'],
            ['front' => 'money', 'back' => 'پول', 'pronunciation' => '/ˈmʌn.i /'],
            ['front' => 'high', 'back' => 'زیاد', 'pronunciation' => '/hɑɪ /'],
            ['front' => 'nineteenth', 'back' => 'نوزدهم', 'pronunciation' => '/ˌnaɪnˈtiːnθ /'],
            ['front' => 'golf', 'back' => 'گلف', 'pronunciation' => '/ɡɑːlf /'],
            ['front' => 'winter', 'back' => 'زمستان', 'pronunciation' => '/ˈwɪntər /'],
            ['front' => 'twenty-fifth', 'back' => 'بیست و پنجم', 'pronunciation' => '/ˈtwɛnti-fɪfθ /'],
            ['front' => 'twentieth', 'back' => 'بیستم', 'pronunciation' => '/ˈtwentiəθ /'],
            ['front' => 'use', 'back' => 'استفاده کردن', 'pronunciation' => '/juːz /'],
            ['front' => 'first', 'back' => 'اولین', 'pronunciation' => '/fɜːrst /'],
            ['front' => 'sport', 'back' => 'ورزش', 'pronunciation' => '/spɔːrt /'],
            ['front' => 'cold', 'back' => 'سرد', 'pronunciation' => '/koʊld /'],
            ['front' => 'life', 'back' => 'زندگی', 'pronunciation' => '/lɑɪf /'],
            ['front' => 'second', 'back' => 'دومین', 'pronunciation' => '/ˈsekənd /'],
            ['front' => 'Internet', 'back' => 'اینترنت', 'pronunciation' => '/ˈɪntərnet /'],
            ['front' => 'piano', 'back' => 'پیانو', 'pronunciation' => '/piːˈæn.oʊ /'],
            ['front' => 'different', 'back' => 'متفاوت', 'pronunciation' => '/ˈdɪf.rənt /'],
            ['front' => 'low', 'back' => 'کم', 'pronunciation' => '/loʊ /'],
            ['front' => 'third', 'back' => 'سوم', 'pronunciation' => '/θɜrd /'],
            ['front' => 'twenty-third', 'back' => 'بیست و سوم', 'pronunciation' => '/ˈtwɛnti-θɜrd /'],
            ['front' => 'ski', 'back' => 'اسکی کردن', 'pronunciation' => '/ski /'],
            ['front' => 'fourth', 'back' => 'چهارم', 'pronunciation' => '/fɔːrθ /'],
            ['front' => 'end', 'back' => 'پایان', 'pronunciation' => '/end /'],
            ['front' => 'close to', 'back' => 'نزدیک به', 'pronunciation' => '/kloʊs tu /'],
            ['front' => 'fifth', 'back' => 'پنجم', 'pronunciation' => '/fɪfθ /'],
            ['front' => 'snowboard', 'back' => 'اسنوبرد سواری کردن', 'pronunciation' => '/ˈsnoʊ.bɔːrd /'],
            ['front' => 'take photos', 'back' => 'عکس گرفتن', 'pronunciation' => '/teɪk ˈfoʊˌtoʊz /'],
            ['front' => 'thirty-first', 'back' => 'سی و یکم', 'pronunciation' => '/ˈθɜrdi-fɜrst /'],
            ['front' => 'sixth', 'back' => 'ششم', 'pronunciation' => '/sɪksθ /'],
            ['front' => 'stay', 'back' => 'ماندن', 'pronunciation' => '/steɪ /'],
            ['front' => 'far', 'back' => 'دور', 'pronunciation' => '/fɑːr /'],
            ['front' => 'seventh', 'back' => 'هفتم', 'pronunciation' => '/ˈsevənθ /'],
            ['front' => 'light', 'back' => 'روشن (هوا و ...)', 'pronunciation' => '/lɑɪt /'],
            ['front' => 'twenty-sixth', 'back' => 'بیست و ششم', 'pronunciation' => '/ˈtwɛnti-sɪksθ /'],
            ['front' => 'eighth', 'back' => 'هشتم', 'pronunciation' => '/eɪtθ /'],
            ['front' => 'month', 'back' => 'ماه (30 روز)', 'pronunciation' => '/mʌnθ /'],
            ['front' => 'drive', 'back' => 'رانندگی کردن', 'pronunciation' => '/drɑɪv /'],
            ['front' => 'ninth', 'back' => 'نهم', 'pronunciation' => '/nɑɪnθ /'],
            ['front' => 'ordinal number', 'back' => 'عدد ترتیبی (ریاضی)', 'pronunciation' => '/ˈɔːrdənəlˈnʌmbr̩ /'],
            ['front' => 'long', 'back' => 'طولانی (زمان)', 'pronunciation' => '/lɔːŋ /'],
            ['front' => 'pay', 'back' => 'پرداخت کردن', 'pronunciation' => '/peɪ /'],
            ['front' => 'tenth', 'back' => 'دهم', 'pronunciation' => '/tenθ /'],
            ['front' => 'January', 'back' => 'ژانویه', 'pronunciation' => '/ˈdʒænjʊeri /'],
            ['front' => 'date', 'back' => 'تاریخ', 'pronunciation' => '/deɪt /'],
            ['front' => 'second', 'back' => 'دومین', 'pronunciation' => '/ˈsekənd /'],
            ['front' => 'first', 'back' => 'اولین', 'pronunciation' => '/fɜːrst /'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 7');
    }

    private function seedLesson8($deck)
    {
        $vocabulary = [
            ['front' => 'indoor', 'back' => 'سرپوشیده', 'pronunciation' => '/ˈɪn.dɔːr/'],
            ['front' => 'comfortable', 'back' => 'راحت', 'pronunciation' => '/ˈkʌmf.tə.bəl/'],
            ['front' => 'hotel', 'back' => 'هتل', 'pronunciation' => '/hoʊˈtel/'],
            ['front' => 'weather', 'back' => 'آب‌وهوا', 'pronunciation' => '/ˈweðər/'],
            ['front' => 'jet lag', 'back' => 'پرواززدگی', 'pronunciation' => '/ʤɛt læg/'],
            ['front' => 'today', 'back' => 'امروز', 'pronunciation' => '/təˈdeɪ/'],
            ['front' => 'carry', 'back' => 'حمل کردن', 'pronunciation' => '/ˈkær.i/'],
            ['front' => 'wear', 'back' => 'پوشیدن', 'pronunciation' => '/wer/'],
            ['front' => 'bed and breakfast', 'back' => 'مهمانخانه کوچک', 'pronunciation' => '/bɛd ænd ˈbrɛkfəst/'],
            ['front' => 'suit', 'back' => 'کت‌شلوار', 'pronunciation' => '/suːt/'],
            ['front' => 'curious', 'back' => 'کنجکاو', 'pronunciation' => '/ˈkjʊr.iː.əs/'],
            ['front' => 'owner', 'back' => 'مالک', 'pronunciation' => '/ˈoʊ.nər/'],
            ['front' => 'career', 'back' => 'شغل', 'pronunciation' => '/kəˈrɪr/'],
            ['front' => 'take', 'back' => 'رفتن (با وسایل نقلیه)', 'pronunciation' => '/teɪk/'],
            ['front' => 'follow', 'back' => 'پیگیری کردن', 'pronunciation' => '/ˈfɑl.oʊ/'],
            ['front' => 'it', 'back' => '[ضمیر مصنوعی]', 'pronunciation' => '/ɪt/'],
            ['front' => 'meet', 'back' => 'ملاقات کردن', 'pronunciation' => '/miːt/'],
            ['front' => 'enjoy', 'back' => 'لذت بردن', 'pronunciation' => '/enˈdʒɔɪ/'],
            ['front' => 'sunny', 'back' => 'آفتابی', 'pronunciation' => '/ˈsʌni/'],
            ['front' => 'cold', 'back' => 'سرد', 'pronunciation' => '/koʊld/'],
            ['front' => 'pay', 'back' => 'پرداخت کردن', 'pronunciation' => '/peɪ/'],
            ['front' => 'hot', 'back' => 'داغ', 'pronunciation' => '/hɑːt/'],
            ['front' => 'bill', 'back' => 'صورت‌حساب', 'pronunciation' => '/bɪl/'],
            ['front' => 'windy', 'back' => 'طوفانی', 'pronunciation' => '/ˈwɪndi/'],
            ['front' => 'have fun', 'back' => 'خوش گذراندن', 'pronunciation' => '/hæv fʌn/'],
            ['front' => 'cloudy', 'back' => 'ابری', 'pronunciation' => '/ˈklɑʊdi/'],
            ['front' => 'guide', 'back' => 'راهنما', 'pronunciation' => '/gaɪd/'],
            ['front' => 'dream', 'back' => 'هدف', 'pronunciation' => '/driːm/'],
            ['front' => 'rainy', 'back' => 'بارانی', 'pronunciation' => '/ˈreɪn.i/'],
            ['front' => 'view', 'back' => 'منظره', 'pronunciation' => '/vjuː/'],
            ['front' => 'snowy', 'back' => 'برفی', 'pronunciation' => '/ˈsnoʊi/'],
            ['front' => 'guest', 'back' => 'مهمان', 'pronunciation' => '/gest/'],
            ['front' => 'rain', 'back' => 'باران آمدن', 'pronunciation' => '/reɪn/'],
            ['front' => 'snow', 'back' => 'برف آمدن', 'pronunciation' => '/snoʊ/'],
            ['front' => 'star', 'back' => 'ستاره', 'pronunciation' => '/stɑr/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 8');
    }

    private function seedLesson9($deck)
    {
        $vocabulary = [
            ['front' => 'ahead', 'back' => 'جلو', 'pronunciation' => '/əˈhed/'],
            ['front' => 'gift shop', 'back' => 'کادوسرا', 'pronunciation' => '/gɪft ʃɑp/'],
            ['front' => 'bookstore', 'back' => 'کتاب‌فروشی', 'pronunciation' => '/ˈbʊkstɔːr/'],
            ['front' => 'floor', 'back' => 'کف', 'pronunciation' => '/flɔːr/'],
            ['front' => 'across from', 'back' => 'آن طرف', 'pronunciation' => '/əˈkrɔs frʌm/'],
            ['front' => 'school', 'back' => 'مدرسه', 'pronunciation' => '/skuːl/'],
            ['front' => 'bathroom', 'back' => 'حمام - دستشویی', 'pronunciation' => '/ˈbæθruːm/'],
            ['front' => 'reception', 'back' => 'پذیرش (هتل و ...)', 'pronunciation' => '/rɪˈsepʃn/'],
            ['front' => 'next to', 'back' => 'کنار', 'pronunciation' => '/nɛkst tʊ/'],
            ['front' => 'yard', 'back' => 'حیاط', 'pronunciation' => '/jɑːrd/'],
            ['front' => 'bathtub', 'back' => 'وان حمام', 'pronunciation' => '/ˈbæθtʌb/'],
            ['front' => 'hospital', 'back' => 'بیمارستان', 'pronunciation' => '/ˈhɑːspɪtl/'],
            ['front' => 'between', 'back' => 'بین', 'pronunciation' => '/bɪˈtwiːn/'],
            ['front' => 'parking lot', 'back' => 'پارکینگ', 'pronunciation' => '/ˈpɑr.kɪŋˌlɑt/'],
            ['front' => 'city', 'back' => 'شهر', 'pronunciation' => '/ˈsɪt̬.i/'],
            ['front' => 'store', 'back' => 'مغازه', 'pronunciation' => '/stɔːr/'],
            ['front' => 'shower', 'back' => 'دوش', 'pronunciation' => '/ˈʃɑʊər/'],
            ['front' => 'town', 'back' => 'شهر (کوچک)', 'pronunciation' => '/tɑʊn/'],
            ['front' => 'towel', 'back' => 'حوله', 'pronunciation' => '/ˈtɑʊ.əl/'],
            ['front' => 'supermarket', 'back' => 'سوپرمارکت', 'pronunciation' => '/ˈsuːpərˌmɑrkɪt/'],
            ['front' => 'village', 'back' => 'روستا', 'pronunciation' => '/ˈvɪləʤ/'],
            ['front' => 'museum', 'back' => 'موزه', 'pronunciation' => '/mjʊˈziːəm/'],
            ['front' => 'bank', 'back' => 'بانک', 'pronunciation' => '/bæŋk/'],
            ['front' => 'toilet', 'back' => 'توالت', 'pronunciation' => '/ˈtɔɪlət/'],
            ['front' => 'on the corner of', 'back' => '(در) نبش', 'pronunciation' => '/ɑn ðə ˈkɔrnər ʌv/'],
            ['front' => 'jungle', 'back' => 'جنگل', 'pronunciation' => '/ˈdʒʌŋ.gəl/'],
            ['front' => 'post office', 'back' => 'اداره پست', 'pronunciation' => '/ˈpoʊstˌɔː.fəs/'],
            ['front' => 'pharmacy', 'back' => 'داروخانه', 'pronunciation' => '/ˈfɑrməsi/'],
            ['front' => 'park', 'back' => 'پارک', 'pronunciation' => '/pɑrk/'],
            ['front' => 'beach', 'back' => 'ساحل', 'pronunciation' => '/biːtʃ/'],
            ['front' => 'hotel', 'back' => 'هتل', 'pronunciation' => '/hoʊˈtel/'],
            ['front' => 'be', 'back' => 'بودن', 'pronunciation' => '/biː/'],
            ['front' => 'ocean', 'back' => 'اقیانوس', 'pronunciation' => '/ˈoʊ.ʃən/'],
            ['front' => 'room', 'back' => 'اتاق', 'pronunciation' => '/ruːm/'],
            ['front' => 'swimming pool', 'back' => 'استخر شنا', 'pronunciation' => '/ˈswɪmɪŋ puːl/'],
            ['front' => 'bed', 'back' => 'تختخواب', 'pronunciation' => '/bed/'],
            ['front' => 'spa', 'back' => 'اسپا (تفریحگاهی دارای سونا، استخر، جکوزی و ...)', 'pronunciation' => '/spɑː/'],
            ['front' => 'river', 'back' => 'رود', 'pronunciation' => '/ˈrɪv.ər/'],
            ['front' => 'there', 'back' => '[وجود داشتن]', 'pronunciation' => '/ðer/'],
            ['front' => 'pillow', 'back' => 'بالش', 'pronunciation' => '/ˈpɪl.oʊ/'],
            ['front' => 'hot tub', 'back' => 'وان جکوزی', 'pronunciation' => '/ˈhɑːt tʌb/'],
            ['front' => 'star', 'back' => 'ستاره', 'pronunciation' => '/stɑr/'],
            ['front' => 'road', 'back' => 'جاده', 'pronunciation' => '/roʊd/'],
            ['front' => 'movie theater', 'back' => 'سینما', 'pronunciation' => '/ˈmuːviˌθiːətər/'],
            ['front' => 'turn', 'back' => 'پیچیدن', 'pronunciation' => '/tɜrn/'],
            ['front' => 'gas station', 'back' => 'جایگاه سوخت', 'pronunciation' => '/ˈgæsˌsteɪ.ʃən/'],
            ['front' => 'left', 'back' => '(به) چپ', 'pronunciation' => '/left/'],
            ['front' => 'in', 'back' => 'در', 'pronunciation' => '/ɪn/'],
            ['front' => 'train station', 'back' => 'ایستگاه راه‌آهن', 'pronunciation' => '/ˈtreɪn ˌsteɪʃən/'],
            ['front' => 'kitchen', 'back' => 'آشپزخانه', 'pronunciation' => '/ˈkɪtʃən/'],
            ['front' => 'airport', 'back' => 'فرودگاه', 'pronunciation' => '/ˈerpɔːrt/'],
            ['front' => 'lamp', 'back' => 'چراغ', 'pronunciation' => '/læmp/'],
            ['front' => 'at', 'back' => 'در (مکان)', 'pronunciation' => '/æt/'],
            ['front' => 'right', 'back' => '(سمت) راست', 'pronunciation' => '/rɑɪt/'],
            ['front' => 'gym', 'back' => 'باشگاه', 'pronunciation' => '/dʒɪm/'],
            ['front' => 'remote control', 'back' => 'کنترل (تلویزیون و...)', 'pronunciation' => '/rɪˌmoʊt.kənˈtroʊl/'],
            ['front' => 'straight', 'back' => 'مستقیم', 'pronunciation' => '/streɪt/'],
            ['front' => 'on', 'back' => 'در (مکان)', 'pronunciation' => '/ɔːn/'],
            ['front' => 'elevator', 'back' => 'آسانسور', 'pronunciation' => '/ˈelɪveɪtər/'],
            ['front' => 'restaurant', 'back' => 'رستوران', 'pronunciation' => '/ˈrestərɑnt/'],
            ['front' => 'suitcase', 'back' => 'چمدان', 'pronunciation' => '/ˈsuːt.keɪs/'],
            ['front' => 'there', 'back' => '[وجود داشتن]', 'pronunciation' => '/ðer/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 9');
    }

    private function seedLesson10($deck)
    {
        $vocabulary = [
            ['front' => 'house', 'back' => 'خانه', 'pronunciation' => '/hɑʊs/'],
            ['front' => 'shave', 'back' => 'اصلاح کردن (صورت، ریش و...)', 'pronunciation' => '/ʃeɪv/'],
            ['front' => 'try', 'back' => 'امتحان کردن', 'pronunciation' => '/traɪ/'],
            ['front' => 'bike', 'back' => 'دوچرخه', 'pronunciation' => '/baɪk/'],
            ['front' => 'get', 'back' => 'شدن', 'pronunciation' => '/get/'],
            ['front' => 'skydiving', 'back' => 'چتربازی سقوط آزاد', 'pronunciation' => '/ˈskaɪdaɪvɪŋ/'],
            ['front' => 'terrible', 'back' => 'افتضاح', 'pronunciation' => '/ˈtɛrəbəl/'],
            ['front' => 'terrifying', 'back' => 'وحشتناک', 'pronunciation' => '/ˈtɛrəˌfaɪɪŋ/'],
            ['front' => 'daily', 'back' => 'روزانه', 'pronunciation' => '/ˈdeɪ.li/'],
            ['front' => 'friendly', 'back' => 'دوستانه', 'pronunciation' => '/ˈfrendli/'],
            ['front' => 'cook', 'back' => 'درست کردن (غذا)', 'pronunciation' => '/kʊk/'],
            ['front' => 'routine', 'back' => 'کار همیشگی', 'pronunciation' => '/ruˈtin/'],
            ['front' => 'change', 'back' => 'تغییر دادن', 'pronunciation' => '/tʃeɪndʒ/'],
            ['front' => 'help', 'back' => 'کمک کردن', 'pronunciation' => '/help/'],
            ['front' => 'life', 'back' => 'زندگی', 'pronunciation' => '/lɑɪf/'],
            ['front' => 'laugh', 'back' => 'خندیدن', 'pronunciation' => '/læf/'],
            ['front' => 'look', 'back' => 'نگاه کردن', 'pronunciation' => '/lʊk/'],
            ['front' => 'miss', 'back' => 'دلتنگ شدن', 'pronunciation' => '/mɪs/'],
            ['front' => 'move', 'back' => 'اسباب‌کشی کردن', 'pronunciation' => '/muːv/'],
            ['front' => 'travel', 'back' => 'سفر کردن', 'pronunciation' => '/ˈtrævl/'],
            ['front' => 'trip', 'back' => 'سفر', 'pronunciation' => '/trɪp/'],
            ['front' => 'get married', 'back' => 'ازدواج کردن', 'pronunciation' => '/gɛt ˈmærɪd/'],
            ['front' => 'mirror', 'back' => 'آینه', 'pronunciation' => '/ˈmɪrər/'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 10');
    }

    protected function seedLesson11($deck)
    {
        $vocabulary = [
            ['front' => 'leave', 'back' => 'ترک کردن', 'pronunciation' => '/liːv /'],
            ['front' => 'lose', 'back' => 'گم کردن', 'pronunciation' => '/luːz /'],
            ['front' => 'send', 'back' => 'فرستادن', 'pronunciation' => '/sɛnd /'],
            ['front' => 'party', 'back' => 'مهمانی', 'pronunciation' => '/ˈpɑrti /'],
            ['front' => 'gift', 'back' => 'کادو', 'pronunciation' => '/gɪft /'],
            ['front' => 'you', 'back' => '[ضمیر دوم شخص مفعولی مفرد و جمع]', 'pronunciation' => '/juː /'],
            ['front' => 'arrive', 'back' => 'رسیدن', 'pronunciation' => '/əˈrɑɪv /'],
            ['front' => 'buy', 'back' => 'خریدن', 'pronunciation' => '/baɪ /'],
            ['front' => 'learn', 'back' => 'یاد گرفتن', 'pronunciation' => '/lɜːrn /'],
            ['front' => 'take', 'back' => 'برداشتن', 'pronunciation' => '/teɪk /'],
            ['front' => 'stay', 'back' => 'اقامت داشتن', 'pronunciation' => '/steɪ /'],
            ['front' => 'it', 'back' => '[ضمیر سوم شخص مفرد اجسام]', 'pronunciation' => '/ɪt /'],
            ['front' => 'would you like ...?', 'back' => 'آیا ... میل دارید؟', 'pronunciation' => '/wəd jʊ laɪk /'],
            ['front' => 'say', 'back' => 'گفتن', 'pronunciation' => '/seɪ /'],
            ['front' => 'rock', 'back' => 'موسیقی راک', 'pronunciation' => '/rɑk /'],
            ['front' => 'stranger', 'back' => 'غریبه', 'pronunciation' => '/ˈstreɪn.dʒər /'],
            ['front' => 'pop', 'back' => 'پاپ (ژانر موسیقی)', 'pronunciation' => '/pɑːp /'],
            ['front' => 'think', 'back' => 'فکر کردن', 'pronunciation' => '/θɪŋk /'],
            ['front' => 'love', 'back' => 'عاشق بودن', 'pronunciation' => '/lʌv /'],
            ['front' => 'hip hop', 'back' => 'موسیقی هیپ هاپ', 'pronunciation' => '/ˈhɪp hɑːp /'],
            ['front' => 'birthday', 'back' => '(روز) تولد', 'pronunciation' => '/ˈbɜːrθdeɪ /'],
            ['front' => 'hate', 'back' => 'متنفر بودن', 'pronunciation' => '/heɪt /'],
            ['front' => 'classical', 'back' => 'کلاسیک', 'pronunciation' => '/ˈklæs.ɪ.kəl /'],
            ['front' => 'letter', 'back' => 'نامه', 'pronunciation' => '/ˈletər /'],
            ['front' => 'awful', 'back' => 'افتضاح', 'pronunciation' => '/ˈɔːfəl /'],
            ['front' => 'tell', 'back' => 'گفتن', 'pronunciation' => '/tel /'],
            ['front' => 'fantastic', 'back' => 'خارق‌العاده', 'pronunciation' => '/fænˈtæs.tɪk /'],
            ['front' => 'present', 'back' => 'هدیه', 'pronunciation' => '/ˈprez.ənt /'],
            ['front' => 'stand', 'back' => 'تحمل کردن', 'pronunciation' => '/stænd /'],
            ['front' => 'turn on', 'back' => 'روشن کردن', 'pronunciation' => '/tɜrn ɑn /'],
            ['front' => 'email', 'back' => 'ایمیل', 'pronunciation' => '/ˈiː.meɪl /'],
            ['front' => 'turn off', 'back' => 'خاموش کردن', 'pronunciation' => '/tɜrn ɔf /'],
            ['front' => 'story', 'back' => 'داستان', 'pronunciation' => '/ˈstɔːr.i /'],
            ['front' => 'wait', 'back' => 'صبر کردن', 'pronunciation' => '/weɪt /'],
            ['front' => 'are', 'back' => 'هستیم', 'pronunciation' => '/ɑr /'],
            ['front' => 'pretty', 'back' => 'خیلی', 'pronunciation' => '/ˈprɪti /'],
            ['front' => 'light', 'back' => 'چراغ', 'pronunciation' => '/lɑɪt /'],
            ['front' => 'begin', 'back' => 'شروع شدن', 'pronunciation' => '/bɪˈgɪn /'],
            ['front' => 'call', 'back' => 'زنگ زدن', 'pronunciation' => '/kɔːl /'],
            ['front' => 'him', 'back' => '[ضمیر مفعولی سوم شخص مفرد مذکر]', 'pronunciation' => '/hɪm /'],
            ['front' => 'OK', 'back' => 'خوب', 'pronunciation' => '/oʊˈkeɪ /'],
            ['front' => 'find', 'back' => 'پیدا کردن', 'pronunciation' => '/fɑɪnd /'],
            ['front' => 'really', 'back' => 'واقعاً', 'pronunciation' => '/ˈriː.li /'],
            ['front' => 'her', 'back' => '[ضمیر مفعولی سوم شخص مفرد مونث]', 'pronunciation' => '/hɜːr /'],
            ['front' => 'get', 'back' => 'دریافت کردن', 'pronunciation' => '/get /'],
            ['front' => 'them', 'back' => '[ضمیر مفعولی سوم شخص جمع]', 'pronunciation' => '/ðem /'],
            ['front' => 'great', 'back' => 'عالی', 'pronunciation' => '/greɪt /'],
            ['front' => 'us', 'back' => '[ضمیر مفعولی اول شخص جمع]', 'pronunciation' => '/ʌs /'],
            ['front' => 'give', 'back' => 'دادن', 'pronunciation' => '/gɪv /'],
            ['front' => 'be', 'back' => 'بودن', 'pronunciation' => '/biː /'],
        ];

        $this->seedVocabulary($deck, $vocabulary, 'Lesson 11');
    }

    private function seedLesson12($deck)
    {
        $vocabulary = [
            ['front' => 'going to', 'back' => 'قصد (انجام کاری را) داشتن', 'pronunciation' => '/ˈgoʊɪŋ tu/'],
            ['front' => 'make friends', 'back' => 'دوست شدن', 'pronunciation' => '/meɪk frɛndz/'],
            ['front' => 'e-mail address', 'back' => 'آدرس ایمیل', 'pronunciation' => '/i-meɪl ˈæˌdrɛs/'],
            ['front' => 'homework', 'back' => 'تکلیف منزل', 'pronunciation' => '/ˈhoʊmwɜːrk/'],
            ['front' => 'group', 'back' => 'گروه', 'pronunciation' => '/gruːp/'],
            ['front' => 'tomorrow', 'back' => 'فردا', 'pronunciation' => '/təˈmɑˌroʊ/'],
            ['front' => 'question', 'back' => 'سوال', 'pronunciation' => '/ˈkwes.tʃən/'],
            ['front' => 'soccer', 'back' => 'فوتبال', 'pronunciation' => '/ˈsɑːkər/'],
            ['front' => 'next', 'back' => 'بعد', 'pronunciation' => '/nekst/'],
            ['front' => 'computer game', 'back' => 'بازی رایانه‌ای', 'pronunciation' => '/kəmˈpjutər geɪm/'],
            ['front' => 'year', 'back' => 'سال', 'pronunciation' => '/jɪr/'],
            ['front' => 'take', 'back' => 'طول کشیدن', 'pronunciation' => '/teɪk/'],
            ['front' => 'night', 'back' => 'شب', 'pronunciation' => '/nɑɪt/'],
            ['front' => 'now', 'back' => 'الان', 'pronunciation' => '/nɑʊ/'],
            ['front' => 'future', 'back' => 'آینده', 'pronunciation' => '/ˈfjuː.tʃər/'],
            ['front' => 'problem', 'back' => 'مشکل', 'pronunciation' => '/ˈprɑb.ləm/'],
            ['front' => 'weekend', 'back' => 'آخر هفته', 'pronunciation' => '/ˌwiːkˈend/'],
            ['front' => 'come back', 'back' => 'برگشتن', 'pronunciation' => '/kʌm bæk/'],
            ['front' => 'tonight', 'back' => 'امشب', 'pronunciation' => '/təˈnɑɪt/'],
            ['front' => 'start', 'back' => 'آغاز', 'pronunciation' => '/stɑrt/'],
            ['front' => 'finish', 'back' => 'پایان', 'pronunciation' => '/ˈfɪn.ɪʃ/'],
            ['front' => 'write', 'back' => 'نوشتن', 'pronunciation' => '/raɪt/'],
            ['front' => 'adventure', 'back' => 'ماجراجویی', 'pronunciation' => '/ədˈven.tʃər/'],
            ['front' => 'present', 'back' => 'زمان حال (دستور زبان)', 'pronunciation' => '/ˈprez.ənt/'],
            ['front' => 'lifetime', 'back' => 'عمر', 'pronunciation' => '/ˈlɑɪf.tɑɪm/'],
            ['front' => 'past', 'back' => 'گذشته (دستور زبان)', 'pronunciation' => '/pæst/'],
            ['front' => 'camp', 'back' => 'اردو زدن', 'pronunciation' => '/kæmp/'],
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
