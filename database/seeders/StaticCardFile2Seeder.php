<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use Illuminate\Database\Seeder;

class StaticCardFile2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all Pre-intermediate level static decks
        $preIntermediateDecks = StaticDeck::where('level', 'pre_intermediate')->get();

        if ($preIntermediateDecks->isEmpty()) {
            $this->command->error('No Pre-intermediate level static decks found. Please run StaticDeckSeeder first.');
            return;
        }

        // Lesson 1 vocabulary
        $lesson1Deck = $preIntermediateDecks->where('lesson_number', 1)->first();
        if ($lesson1Deck) {
            $this->seedLesson1($lesson1Deck);
        }

        // Lesson 2 vocabulary
        $lesson2Deck = $preIntermediateDecks->where('lesson_number', 2)->first();
        if ($lesson2Deck) {
            $this->seedLesson2($lesson2Deck);
        }

        // Lesson 3 vocabulary
        $lesson3Deck = $preIntermediateDecks->where('lesson_number', 3)->first();
        if ($lesson3Deck) {
            $this->seedLesson3($lesson3Deck);
        }

        // Lesson 4 vocabulary
        $lesson4Deck = $preIntermediateDecks->where('lesson_number', 4)->first();
        if ($lesson4Deck) {
            $this->seedLesson4($lesson4Deck);
        }

        // Lesson 5 vocabulary
        $lesson5Deck = $preIntermediateDecks->where('lesson_number', 5)->first();
        if ($lesson5Deck) {
            $this->seedLesson5($lesson5Deck);
        }

        // Lesson 6 vocabulary
        $lesson6Deck = $preIntermediateDecks->where('lesson_number', 6)->first();
        if ($lesson6Deck) {
            $this->seedLesson6($lesson6Deck);
        }

        // Lesson 7 vocabulary
        $lesson7Deck = $preIntermediateDecks->where('lesson_number', 7)->first();
        if ($lesson7Deck) {
            $this->seedLesson7($lesson7Deck);
        }

        // Lesson 8 vocabulary
        $lesson8Deck = $preIntermediateDecks->where('lesson_number', 8)->first();
        if ($lesson8Deck) {
            $this->seedLesson8($lesson8Deck);
        }

        // Lesson 9 vocabulary
        $lesson9Deck = $preIntermediateDecks->where('lesson_number', 9)->first();
        if ($lesson9Deck) {
            $this->seedLesson9($lesson9Deck);
        }

        // Lesson 10 vocabulary
        $lesson10Deck = $preIntermediateDecks->where('lesson_number', 10)->first();
        if ($lesson10Deck) {
            $this->seedLesson10($lesson10Deck);
        }

        // Lesson 11 vocabulary
        $lesson11Deck = $preIntermediateDecks->where('lesson_number', 11)->first();
        if ($lesson11Deck) {
            $this->seedLesson11($lesson11Deck);
        }

        // Lesson 12 vocabulary
        $lesson12Deck = $preIntermediateDecks->where('lesson_number', 12)->first();
        if ($lesson12Deck) {
            $this->seedLesson12($lesson12Deck);
        }

        $this->command->info('Vocabulary added for American English File 2 (Pre-intermediate level)');
    }

    protected function seedLesson1($deck)
    {
        $vocabulary = [
            ['front' => 'portrait', 'back' => 'پرتره', 'pronunciation' => '/ˈpɔːrtrət/'],
            ['front' => 'in the middle', 'back' => 'در وسط', 'pronunciation' => '/ɪn ðə ˈmɪdəl/'],
            ['front' => 'straight', 'back' => 'مستقیم', 'pronunciation' => '/streɪt/'],
            ['front' => 'best friend', 'back' => 'بهترین دوست', 'pronunciation' => '/bɛst frɛnd/'],
            ['front' => 'poster', 'back' => 'پوستر', 'pronunciation' => '/ˈpoʊ.stər/'],
            ['front' => 'appearance', 'back' => 'ظاهر', 'pronunciation' => '/əˈpɪrəns/'],
            ['front' => 'fun', 'back' => 'سرگرم‌کننده', 'pronunciation' => '/fʌn/'],
            ['front' => 'serious', 'back' => 'جدی', 'pronunciation' => '/ˈsɪriəs/'],
            ['front' => 'weekly', 'back' => 'هفتگی', 'pronunciation' => '/ˈwiː.kli/'],
            ['front' => 'pregnant', 'back' => 'باردار', 'pronunciation' => '/ˈpreg.nənt/'],
            ['front' => 'in', 'back' => 'در', 'pronunciation' => '/ɪn/'],
            ['front' => 'cheap', 'back' => 'خسیس', 'pronunciation' => '/tʃiːp/'],
            ['front' => 'personality', 'back' => 'شخصیت', 'pronunciation' => '/ˌpɜrsəˈnælɪti/'],
            ['front' => 'get in touch', 'back' => 'در تماس بودن', 'pronunciation' => '/gɛt ɪn tʌʧ/'],
            ['front' => 'relationship', 'back' => 'رابطه', 'pronunciation' => '/rəˈleɪ.ʃənˌʃɪp/'],
            ['front' => 'belt', 'back' => 'کمربند', 'pronunciation' => '/belt/'],
            ['front' => 'dark', 'back' => 'تیره', 'pronunciation' => '/dɑːrk/'],
            ['front' => 'interesting', 'back' => 'جالب', 'pronunciation' => '/ˈɪntrəstɪŋ/'],
            ['front' => 'unusual', 'back' => 'غیرعادی', 'pronunciation' => '/ʌnˈjuː.ʒu.əl/'],
            ['front' => 'top', 'back' => 'تاپ', 'pronunciation' => '/tɑːp/'],
            ['front' => 'on', 'back' => 'روی', 'pronunciation' => '/ɔːn/'],
            ['front' => 'friendly', 'back' => 'دوستانه', 'pronunciation' => '/ˈfrendli/'],
            ['front' => 'boot', 'back' => 'چکمه', 'pronunciation' => '/buːt/'],
            ['front' => 'cap', 'back' => 'کلاه (لبه‌دار)', 'pronunciation' => '/kæp/'],
            ['front' => 'close', 'back' => 'صمیمی', 'pronunciation' => '/kloʊz/'],
            ['front' => 'smile', 'back' => 'لبخند', 'pronunciation' => '/smaɪl/'],
            ['front' => 'glove', 'back' => 'دستکش', 'pronunciation' => '/glʌv/'],
            ['front' => 'wavy', 'back' => 'موج‌دار', 'pronunciation' => '/ˈweɪvi/'],
            ['front' => 'funny', 'back' => 'بامزه', 'pronunciation' => '/ˈfʌni/'],
            ['front' => 'stupid', 'back' => 'احمق', 'pronunciation' => '/ˈstuː.pɪd/'],
            ['front' => 'hat', 'back' => 'کلاه', 'pronunciation' => '/hæt/'],
            ['front' => 'beard', 'back' => 'ریش', 'pronunciation' => '/bɪrd/'],
            ['front' => 'warm-up suit', 'back' => 'ست گرم‌کن ورزشی', 'pronunciation' => '/wɔrm-ʌp sut/'],
            ['front' => 'leggings', 'back' => 'شلوار جذب', 'pronunciation' => '/ˈleɡɪŋz/'],
            ['front' => 'mustache', 'back' => 'سبیل', 'pronunciation' => '/ˈmʌstæʃ/'],
            ['front' => 'laugh', 'back' => 'خندیدن', 'pronunciation' => '/læf/'],
            ['front' => 'generous', 'back' => 'بخشنده', 'pronunciation' => '/ˈdʒenərəs/'],
            ['front' => 'jewelry', 'back' => 'جواهر', 'pronunciation' => '/ˈdʒuː.əl.ri/'],
            ['front' => 'cardigan', 'back' => 'ژاکت (پشمی)', 'pronunciation' => '/ˈkɑːrdɪɡən/'],
            ['front' => 'footwear', 'back' => 'پاپوش', 'pronunciation' => '/ˈfʊtwer/'],
            ['front' => 'pants', 'back' => 'شلوار', 'pronunciation' => '/pænts/'],
            ['front' => 'under', 'back' => 'زیر', 'pronunciation' => '/ˈʌndər/'],
            ['front' => 'intelligent', 'back' => 'باهوش', 'pronunciation' => '/ɪnˈtel.ə.dʒənt/'],
            ['front' => 'feel like', 'back' => 'خواستن', 'pronunciation' => '/fil laɪk/'],
            ['front' => 'kind', 'back' => 'مهربان', 'pronunciation' => '/kɑɪnd/'],
            ['front' => 'bracelet', 'back' => 'دست‌بند', 'pronunciation' => '/ˈbreɪ.slət/'],
            ['front' => 'clothes', 'back' => 'لباس', 'pronunciation' => '/kloʊðz/'],
            ['front' => 'bald', 'back' => 'کچل', 'pronunciation' => '/bɔːld/'],
            ['front' => 'What do you do?', 'back' => 'چه کاره هستید؟', 'pronunciation' => '/wʌt du ju du?/'],
            ['front' => 'in front of', 'back' => 'در مقابل', 'pronunciation' => '/ɪn frʌnt ʌv/'],
            ['front' => 'be into', 'back' => 'علاقه داشتن', 'pronunciation' => '/bi ˈɪntu/'],
            ['front' => 'shirt', 'back' => 'پیراهن', 'pronunciation' => '/ʃɜrt/'],
            ['front' => 'coat', 'back' => 'پالتو', 'pronunciation' => '/koʊt/'],
            ['front' => 'extrovert', 'back' => 'برون‌گرا', 'pronunciation' => '/ˈekstrəvɜːrt/'],
            ['front' => 'tall', 'back' => 'بلندقد', 'pronunciation' => '/tɔl/'],
            ['front' => 'earring', 'back' => 'گوشواره', 'pronunciation' => '/ˈɪr.rɪŋ/'],
            ['front' => 'behind', 'back' => 'پشت', 'pronunciation' => '/bəˈhaɪnd/'],
            ['front' => 'have something in common', 'back' => 'در چیزی تفاهم داشتن', 'pronunciation' => '/hæv ˈsʌmθɪŋ ɪn ˈkɑmən/'],
            ['front' => 'flip-flops', 'back' => 'دمپایی لا انگشتی', 'pronunciation' => '/ˈflɪp flɑːps/'],
            ['front' => 'choose', 'back' => 'انتخاب کردن', 'pronunciation' => '/tʃuːz/'],
            ['front' => 'shoe', 'back' => 'کفش', 'pronunciation' => '/ʃu/'],
            ['front' => 'lazy', 'back' => 'تنبل', 'pronunciation' => '/ˈleɪ.zi/'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 1 vocabulary added.');
    }


    protected function seedLesson2($deck)
    {
        $vocabulary = [
            ['front' => 'special', 'back' => 'خاص', 'pronunciation' => '/ˈspeʃ.əl/'],
            ['front' => 'screen', 'back' => 'صفحه نمایش', 'pronunciation' => '/skrin/'],
            ['front' => 'bicycle', 'back' => 'دوچرخه', 'pronunciation' => '/ˈbɑɪsɪkəl/'],
            ['front' => 'about', 'back' => 'درباره', 'pronunciation' => '/əˈbɑʊt/'],
            ['front' => 'go biking', 'back' => 'دوچرخه‌سواری کردن', 'pronunciation' => '/goʊ ˈbaɪkɪŋ/'],
            ['front' => 'upload', 'back' => 'بارگذاری کردن', 'pronunciation' => '/ˈʌp.loʊd/'],
            ['front' => 'ski', 'back' => 'اسکی', 'pronunciation' => '/ski/'],
            ['front' => 'disgusting', 'back' => 'تنفرآمیز', 'pronunciation' => '/dɪsˈgʌs.tɪŋ/'],
            ['front' => 'at', 'back' => 'در (مکان)', 'pronunciation' => '/æt/'],
            ['front' => 'go swimming', 'back' => 'شنا کردن', 'pronunciation' => '/goʊ ˈswɪmɪŋ/'],
            ['front' => 'screen saver', 'back' => 'محافظ صفحه نمایش (کامپیوتر)', 'pronunciation' => '/ˈskriːn seɪvər/'],
            ['front' => 'for', 'back' => 'برای', 'pronunciation' => '/fɔːr/'],
            ['front' => 'go sailing', 'back' => 'به قایقرانی رفتن', 'pronunciation' => '/goʊ ˈseɪlɪŋ/'],
            ['front' => 'warm', 'back' => 'گرم', 'pronunciation' => '/wɔrm/'],
            ['front' => 'alright', 'back' => 'بسیارخوب', 'pronunciation' => '/ɔːlˈraɪt/'],
            ['front' => 'beach', 'back' => 'ساحل', 'pronunciation' => '/biːtʃ/'],
            ['front' => 'go camping', 'back' => 'به اردوی تفریحی رفتن', 'pronunciation' => '/goʊ ˈkæmpɪŋ/'],
            ['front' => 'anniversary', 'back' => 'سالروز', 'pronunciation' => '/ˌænəˈvɜrsəri/'],
            ['front' => 'in', 'back' => 'در (زمان)', 'pronunciation' => '/ɪn/'],
            ['front' => 'vacation', 'back' => 'مرخصی', 'pronunciation' => '/veɪˈkeɪʃən/'],
            ['front' => 'comfortable', 'back' => 'راحت', 'pronunciation' => '/ˈkʌmf.tə.bəl/'],
            ['front' => 'sunny', 'back' => 'آفتابی', 'pronunciation' => '/ˈsʌni/'],
            ['front' => 'awful', 'back' => 'افتضاح', 'pronunciation' => '/ˈɔːfəl/'],
            ['front' => 'brake', 'back' => 'ترمز', 'pronunciation' => '/breɪk/'],
            ['front' => 'go surfing', 'back' => 'موج‌سواری کردن', 'pronunciation' => '/goʊ ˈsɜrfɪŋ/'],
            ['front' => 'on', 'back' => 'در (زمان)', 'pronunciation' => '/ɔːn/'],
            ['front' => 'horrible', 'back' => 'بسیار بد', 'pronunciation' => '/ˈhɔːr.ə.bəl/'],
            ['front' => 'windy', 'back' => 'طوفانی', 'pronunciation' => '/ˈwɪndi/'],
            ['front' => 'luxurious', 'back' => 'مجلل', 'pronunciation' => '/lʌɡˈʒʊriəs/'],
            ['front' => 'terrible', 'back' => 'افتضاح', 'pronunciation' => '/ˈtɛrəbəl/'],
            ['front' => 'foggy', 'back' => 'مه‌آلود', 'pronunciation' => '/ˈfɔːg.i/'],
            ['front' => 'basic', 'back' => 'ساده', 'pronunciation' => '/ˈbeɪ.sɪk/'],
            ['front' => 'stay', 'back' => 'اقامت داشتن', 'pronunciation' => '/steɪ/'],
            ['front' => 'go wrong', 'back' => 'خراب شدن', 'pronunciation' => '/goʊ rɔŋ/'],
            ['front' => 'cloudy', 'back' => 'ابری', 'pronunciation' => '/ˈklɑʊdi/'],
            ['front' => 'go on vacation', 'back' => 'در تعطیلات به مسافرت رفتن', 'pronunciation' => '/goʊ ɑn veɪˈkeɪʃən/'],
            ['front' => 'hotel', 'back' => 'هتل', 'pronunciation' => '/hoʊˈtel/'],
            ['front' => 'dirty', 'back' => 'کثیف', 'pronunciation' => '/ˈdɜrti/'],
            ['front' => 'go by bus', 'back' => 'با اتوبوس رفتن', 'pronunciation' => '/goʊ baɪ bʌs/'],
            ['front' => 'campsite', 'back' => 'محوطه اردوگاه', 'pronunciation' => '/ˈkæmp.sɑɪt/'],
            ['front' => 'wonderful', 'back' => 'شگفت‌انگیز', 'pronunciation' => '/ˈwʌn.dər.fəl/'],
            ['front' => 'go out', 'back' => 'بیرون رفتن', 'pronunciation' => '/goʊ ɑʊt/'],
            ['front' => 'uncomfortable', 'back' => 'نا راحت', 'pronunciation' => '/ʌnˈkʌmftəbl/'],
            ['front' => 'book', 'back' => 'رزرو کردن', 'pronunciation' => '/bʊk/'],
            ['front' => 'hostel', 'back' => 'مسافرخانه', 'pronunciation' => '/ˈhɑs.təl/'],
            ['front' => 'go by car', 'back' => 'با ماشین رفتن', 'pronunciation' => '/goʊ baɪ kɑr/'],
            ['front' => 'fantastic', 'back' => 'خارق‌العاده', 'pronunciation' => '/fænˈtæs.tɪk/'],
            ['front' => 'atmosphere', 'back' => 'محیط', 'pronunciation' => '/ˈæt.məsˌfɪr/'],
            ['front' => 'perfect', 'back' => 'عالی', 'pronunciation' => '/ˈpɜr.fɪkt/'],
            ['front' => 'helpful', 'back' => 'مفید', 'pronunciation' => '/ˈhelp.fəl/'],
            ['front' => 'invite', 'back' => 'دعوت کردن', 'pronunciation' => '/ɪnˈvɑɪt/'],
            ['front' => 'take photos', 'back' => 'عکس گرفتن', 'pronunciation' => '/teɪk ˈfoʊˌtoʊz/'],
            ['front' => 'as usual', 'back' => 'طبق معمول', 'pronunciation' => '/æz ˈjuʒəwəl/'],
            ['front' => 'great', 'back' => 'عالی', 'pronunciation' => '/greɪt/'],
            ['front' => 'flirt', 'back' => 'لاس زدن', 'pronunciation' => '/flɜːrt/'],
            ['front' => 'sunbathe', 'back' => 'حمام آفتاب گرفتن', 'pronunciation' => '/ˈsʌn.beɪð/'],
            ['front' => 'unhelpful', 'back' => 'غیر سودمند', 'pronunciation' => '/ʌnˈhelp.fəl/'],
            ['front' => 'cross', 'back' => 'عبور کردن', 'pronunciation' => '/krɔːs/'],
            ['front' => 'go sightseeing', 'back' => 'به گردش و بازدید از اماکن دیدنی رفتن', 'pronunciation' => '/goʊ ˈsaɪtˈsiɪŋ/'],
            ['front' => 'break up', 'back' => 'بهم زدن (رابطه عاشقانه)', 'pronunciation' => '/breɪk ʌp/'],
            ['front' => 'OK', 'back' => 'خوب', 'pronunciation' => '/oʊˈkeɪ/'],
            ['front' => 'go skiing', 'back' => 'به اسکی رفتن', 'pronunciation' => '/goʊ ˈskiɪŋ/'],
            ['front' => 'beautiful', 'back' => 'زیبا', 'pronunciation' => '/ˈbjuː.tɪ.fəl/'],
            ['front' => 'souvenir', 'back' => 'سوغاتی', 'pronunciation' => '/ˌsuː.vəˈnɪr/'],
            ['front' => 'democracy', 'back' => 'دموکراسی', 'pronunciation' => '/dɪˈmɒk.rə.si/'],
            ['front' => 'ending', 'back' => 'پایان', 'pronunciation' => '/ˈen.dɪŋ/'],
            ['front' => 'rent', 'back' => 'اجاره کردن', 'pronunciation' => '/rent/'],
            ['front' => 'go abroad', 'back' => 'به خارج کشور سفر کردن', 'pronunciation' => '/goʊ əˈbrɔd/'],
            ['front' => 'noisy', 'back' => 'پرسروصدا', 'pronunciation' => '/ˈnɔɪzi/'],
            ['front' => 'go by plane', 'back' => 'با هواپیما رفتن', 'pronunciation' => '/goʊ baɪ pleɪn/'],
            ['front' => 'demonstration', 'back' => 'تظاهرات', 'pronunciation' => '/ˌdem.ənˈstreɪ.ʃən/'],
            ['front' => 'madly', 'back' => 'دیوانه‌وار', 'pronunciation' => '/ˈmædli/'],
            ['front' => 'spend', 'back' => 'خرج کردن', 'pronunciation' => '/spend/'],
            ['front' => 'go away', 'back' => 'به مسافرت رفتن', 'pronunciation' => '/goʊ əˈweɪ/'],
            ['front' => 'crowded', 'back' => 'شلوغ', 'pronunciation' => '/ˈkrɑʊd.ɪd/'],
            ['front' => 'election', 'back' => 'انتخابات', 'pronunciation' => '/ɪˈlek.ʃən/'],
            ['front' => 'view', 'back' => 'منظره', 'pronunciation' => '/vjuː/'],
            ['front' => 'freedom', 'back' => 'آزادی', 'pronunciation' => '/ˈfriːd.əm/'],
            ['front' => 'delicious', 'back' => 'خوشمزه', 'pronunciation' => '/dɪˈlɪʃ.əs/'],
            ['front' => 'go walking', 'back' => 'پیاده‌روی کردن', 'pronunciation' => '/goʊ ˈwɔkɪŋ/'],
            ['front' => 'feel sorry for someone', 'back' => 'دل شخصی برای شخصی سوختن', 'pronunciation' => '/fil ˈsɑri fɔr ˈsʌmˌwʌn/'],
            ['front' => 'hold hands', 'back' => 'دست همدیگر را گرفتن', 'pronunciation' => '/hoʊld hændz/'],
            ['front' => 'apartment', 'back' => 'آپارتمان', 'pronunciation' => '/əˈpɑrtmənt/'],
            ['front' => 'go by train', 'back' => 'با قطار سفر کردن', 'pronunciation' => '/goʊ baɪ treɪn/'],
            ['front' => 'peace', 'back' => 'صلح', 'pronunciation' => '/piːs/'],
            ['front' => 'disaster', 'back' => 'مصیبت', 'pronunciation' => '/dɪˈzæs.tər/'],
            ['front' => 'online', 'back' => '(به صورت) آنلاین', 'pronunciation' => '/ˌɑːnˈlaɪn/'],
            ['front' => 'realize', 'back' => 'فهمیدن', 'pronunciation' => '/ˈrɪə.laɪz/'],
            ['front' => 'go for a walk', 'back' => 'پیاده روی کردن', 'pronunciation' => '/goʊ fɔr ə wɔk/'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 2 vocabulary added.');
    }
    protected function seedLesson3($deck)
    {
        // این متد لغات درس ۳ را به پایگاه داده اضافه می کند.
        $vocabulary = [
            ['front' => 'check-in', 'back' => 'محل پذیرش (هتل، فرودگاه و...)', 'pronunciation' => '/ʧɛk-ɪn /'],
            ['front' => 'Wi-Fi', 'back' => 'وای-فای', 'pronunciation' => '/ˈwʌɪfʌɪ /'],
            ['front' => 'somewhere', 'back' => 'جایی', 'pronunciation' => '/ˈsʌmwer /'],
            ['front' => 'gadget', 'back' => 'وسیله', 'pronunciation' => '/ˈgæʤət /'],
            ['front' => 'conference', 'back' => 'همایش', 'pronunciation' => '/ˈkɑnfərəns /'],
            ['front' => 'plan', 'back' => 'برنامه', 'pronunciation' => '/plæn /'],
            ['front' => 'news', 'back' => 'خبر', 'pronunciation' => '/nuːz /'],
            ['front' => 'dream', 'back' => 'هدف', 'pronunciation' => '/driːm /'],
            ['front' => 'art gallery', 'back' => 'گالری آثار هنری', 'pronunciation' => '/ˈɑːrt ɡæləri /'],
            ['front' => 'fall in love', 'back' => 'عاشق (کسی) شدن', 'pronunciation' => '/fɔl ɪn lʌv /'],
            ['front' => 'DJ', 'back' => 'دی‌جی', 'pronunciation' => '/di-ʤeɪ /'],
            ['front' => 'set', 'back' => 'معین کردن', 'pronunciation' => '/set /'],
            ['front' => 'customs', 'back' => 'گمرک', 'pronunciation' => '/ˈkʌs.təmz /'],
            ['front' => 'paraphrase', 'back' => 'به بیان دیگر گفتن', 'pronunciation' => '/ˈpɛrəˌfreɪz /'],
            ['front' => 'departures', 'back' => 'پروازهای خروجی (از یک فرودگاه)', 'pronunciation' => '/dɪˈpɑrʧərz /'],
            ['front' => 'nowadays', 'back' => 'امروزه', 'pronunciation' => '/ˈnɑʊ.əˌdeɪz /'],
            ['front' => 'perhaps', 'back' => 'شاید', 'pronunciation' => '/pərˈhæps /'],
            ['front' => 'both', 'back' => 'هر دو', 'pronunciation' => '/boʊθ /'],
            ['front' => 'gate', 'back' => 'گیت (فرودگاه)', 'pronunciation' => '/geɪt /'],
            ['front' => 'tweet', 'back' => 'پست گذاشتن در شبکه اجتماعی توئیتر', 'pronunciation' => '/twiːt /'],
            ['front' => 'How are things?', 'back' => 'اوضاع چطوره؟', 'pronunciation' => '/haʊ ɑr θɪŋz? /'],
            ['front' => 'elevator', 'back' => 'آسانسور', 'pronunciation' => '/ˈelɪveɪtər /'],
            ['front' => 'mime', 'back' => 'با ایما و اشاره گفتن', 'pronunciation' => '/maɪm /'],
            ['front' => 'update', 'back' => 'به روز رسانی کردن', 'pronunciation' => '/əpˈdeɪt /'],
            ['front' => 'security', 'back' => 'گیت بازرسی', 'pronunciation' => '/sɪˈkjʊrəti /'],
            ['front' => 'menu', 'back' => 'منو', 'pronunciation' => '/ˈmen.juː /'],
            ['front' => 'barista', 'back' => 'باریستا', 'pronunciation' => '/bəˈriːstə /'],
            ['front' => 'road rage', 'back' => 'خشم جاده‌ای', 'pronunciation' => '/ˈroʊd reɪdʒ /'],
            ['front' => 'delayed', 'back' => 'به تاخیر افتاده', 'pronunciation' => '/dɪˈleɪd /'],
            ['front' => 'appetizer', 'back' => 'پیش‌غذا', 'pronunciation' => '/ˈæpɪtaɪzər /'],
            ['front' => 'dessert', 'back' => 'دسر', 'pronunciation' => '/dɪˈzɜrt /'],
            ['front' => 'main course', 'back' => 'وعده اصلی (غذا)', 'pronunciation' => '/ˌmeɪnˈkɔːrs /'],
            ['front' => 'facility', 'back' => 'تسهیلات', 'pronunciation' => '/fəˈsɪl.ət̬.i /'],
            ['front' => 'passport control', 'back' => 'محل بررسی روادید', 'pronunciation' => '/ˈpæˌspɔrt kənˈtroʊl /'],
            ['front' => 'tip', 'back' => 'انعام', 'pronunciation' => '/tɪp /'],
            ['front' => 'example', 'back' => 'نمونه', 'pronunciation' => '/ɪgˈzæm.pəl /'],
            ['front' => 'arrangement', 'back' => 'برنامه', 'pronunciation' => '/əˈreɪndʒ.mənt /'],
            ['front' => 'waiter', 'back' => 'پیشخدمت (آقا)', 'pronunciation' => '/ˈweɪtər /'],
            ['front' => 'arrivals', 'back' => 'بخش پروازهای ورودی فرودگاه', 'pronunciation' => '/əˈraɪvlz /'],
            ['front' => 'terminal', 'back' => 'پایانه (اتوبوس، هواپیما و...)', 'pronunciation' => '/ˈtɜr.mɪn.əl /'],
            ['front' => 'like', 'back' => 'شبیه', 'pronunciation' => '/lɑɪk /'],
            ['front' => 'bill', 'back' => 'صورت‌حساب', 'pronunciation' => '/bɪl /'],
            ['front' => 'depend', 'back' => 'بستگی داشتن', 'pronunciation' => '/dəˈpend /'],
            ['front' => 'gastropub', 'back' => 'کافه-بار', 'pronunciation' => '/ˈɡæstroʊpʌb /'],
            ['front' => 'opposite', 'back' => 'مخالف', 'pronunciation' => '/ˈɑp.ə.zɪt /'],
            ['front' => 'restroom', 'back' => 'دستشویی', 'pronunciation' => '/ˈrest.ruːm /'],
            ['front' => 'connecting flight', 'back' => 'پرواز غیرمستقیم', 'pronunciation' => '/kəˈnɛktɪŋ flaɪt /'],
            ['front' => 'latte', 'back' => 'کافه لاته', 'pronunciation' => '/ˈlɑˌteɪ /'],
            ['front' => 'similar', 'back' => 'مشابه', 'pronunciation' => '/ˈsɪmələr /'],
            ['front' => 'cart', 'back' => 'چرخ‌دستی', 'pronunciation' => '/kɑrt /'],
            ['front' => 'smart phone', 'back' => 'تلفن هوشمند', 'pronunciation' => '/ˈsmɑːrt foʊn /'],
            ['front' => 'paradise', 'back' => 'بهشت', 'pronunciation' => '/ˈpærədaɪs /'],
            ['front' => 'baggage check-in', 'back' => 'بازرسی چمدان و تحویل بار (در فرودگاه)', 'pronunciation' => '/ˈbægəʤ ʧɛk-ɪn /'],
            ['front' => 'board', 'back' => 'سوار (هواپیما و...) شدن', 'pronunciation' => '/bɔrd /'],
            ['front' => 'somebody', 'back' => 'کسی', 'pronunciation' => '/ˈsʌmˌbɑd.i /'],
            ['front' => 'google', 'back' => 'گوگل کردن', 'pronunciation' => '/ˈgugəl /'],
            ['front' => 'passenger', 'back' => 'مسافر', 'pronunciation' => '/ˈpæs.ən.dʒər /'],
            ['front' => 'baggage claim', 'back' => 'محل دریافت بار/چمدان (فرودگاه)', 'pronunciation' => '/ˈbægəʤ kleɪm /'],
            ['front' => 'traveler', 'back' => 'مسافر', 'pronunciation' => '/ˈtræv.ə.lər /'],
            ['front' => 'something', 'back' => 'چیزی', 'pronunciation' => '/ˈsʌm.θɪŋ /'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 3 vocabulary added.');
    }

    protected function seedLesson4($deck)
    {
        $vocabulary = [
            ['front' => 'make the bed', 'back' => 'تختخواب را مرتب کردن', 'pronunciation' => '/meɪk ðə bɛd/'],
            ['front' => 'proceed', 'back' => 'پیش رفتن', 'pronunciation' => '/prəˈsid/'],
            ['front' => 'leather', 'back' => 'چرم', 'pronunciation' => '/ˈleð.ər/'],
            ['front' => 'teenager', 'back' => 'نوجوان', 'pronunciation' => '/ˈtiːˌneɪ.dʒər/'],
            ['front' => 'auction', 'back' => 'مزایده', 'pronunciation' => '/ˈɔːkʃn/'],
            ['front' => 'put away', 'back' => 'چیزی را سر جایش گذاشتن', 'pronunciation' => '/pʊt əˈweɪ/'],
            ['front' => 'create', 'back' => 'به وجود آوردن', 'pronunciation' => '/kriːˈeɪt/'],
            ['front' => 'site', 'back' => 'پایگاه اینترنتی', 'pronunciation' => '/saɪt/'],
            ['front' => 'knock', 'back' => '(در و...) زدن', 'pronunciation' => '/nɑːk/'],
            ['front' => 'do housework', 'back' => 'کار خانه انجام دادن', 'pronunciation' => '/du ˈhaʊˌswɜrk/'],
            ['front' => 'set the table', 'back' => 'چیدن میز غذا', 'pronunciation' => '/sɛt ðə ˈteɪbəl/'],
            ['front' => 'keep on', 'back' => 'ادامه دادن', 'pronunciation' => '/kip ɑn/'],
            ['front' => 'shopping cart', 'back' => 'چرخ‌دستی خرید', 'pronunciation' => '/ˈʃɑːpɪŋ kɑːrt/'],
            ['front' => 'make friends', 'back' => 'دوست شدن', 'pronunciation' => '/meɪk frɛndz/'],
            ['front' => 'encourage', 'back' => 'تشویق کردن', 'pronunciation' => '/ɛnˈkɜrɪʤ/'],
            ['front' => 'text', 'back' => 'پیام دادن (از طریق موبایل)', 'pronunciation' => '/tekst/'],
            ['front' => 'clear the table', 'back' => 'میز غذا را جمع کردن', 'pronunciation' => '/klɪr ðə ˈteɪbəl/'],
            ['front' => 'make dinner', 'back' => 'شام درست کردن', 'pronunciation' => '/meɪk ˈdɪnər/'],
            ['front' => 'payment', 'back' => 'پرداخت', 'pronunciation' => '/ˈpeɪ.mənt/'],
            ['front' => 'impress', 'back' => 'تحت‌تأثیر قرار دادن', 'pronunciation' => '/ˈɪmˌprɛs/'],
            ['front' => 'channel', 'back' => 'شبکه', 'pronunciation' => '/ˈtʃæn.əl/'],
            ['front' => 'take out', 'back' => 'بیرون بردن', 'pronunciation' => '/teɪk aʊt/'],
            ['front' => 'garbage', 'back' => 'زباله', 'pronunciation' => '/ˈgɑr.bɪdʒ/'],
            ['front' => 'click', 'back' => 'کلیک کردن', 'pronunciation' => '/klɪk/'],
            ['front' => 'clean', 'back' => 'تمیز کردن', 'pronunciation' => '/kliːn/'],
            ['front' => 'shopping', 'back' => 'خرید', 'pronunciation' => '/ˈʃɑp.ɪŋ/'],
            ['front' => 'bored', 'back' => 'کسل', 'pronunciation' => '/bɔrd/'],
            ['front' => 'do yoga', 'back' => 'ورزش یوگا انجام دادن', 'pronunciation' => '/du ˈjoʊgə/'],
            ['front' => 'delivery', 'back' => 'تحویل', 'pronunciation' => '/dɪˈlɪv.ə.ri/'],
            ['front' => 'boring', 'back' => 'خسته‌کننده', 'pronunciation' => '/ˈbɔːrɪŋ/'],
            ['front' => 'pick up', 'back' => 'بلند کردن', 'pronunciation' => '/pɪk ʌp/'],
            ['front' => 'shop', 'back' => 'مغازه', 'pronunciation' => '/ʃɑp/'],
            ['front' => 'depressed', 'back' => 'افسرده', 'pronunciation' => '/dɪˈprest/'],
            ['front' => 'store', 'back' => 'مغازه', 'pronunciation' => '/stɔːr/'],
            ['front' => 'do the dishes', 'back' => 'ظرف شستن', 'pronunciation' => '/du ðə ˈdɪʃəz/'],
            ['front' => 'closet', 'back' => 'کمد', 'pronunciation' => '/ˈklɑzɪt/'],
            ['front' => 'do a crossword', 'back' => 'جدول متقاطع حل کردن', 'pronunciation' => '/du ə ˈkrɔˌswɜrd/'],
            ['front' => 'item', 'back' => 'کالا', 'pronunciation' => '/ˈaɪtəm/'],
            ['front' => 'depressing', 'back' => 'غم‌انگیز', 'pronunciation' => '/dəˈpres.ɪŋ/'],
            ['front' => 'make plans', 'back' => 'برنامه‌ریزی کردن', 'pronunciation' => '/meɪk plænz/'],
            ['front' => 'dry', 'back' => 'خشک کردن', 'pronunciation' => '/drɑɪ/'],
            ['front' => 'make a mistake', 'back' => 'اشتباه کردن', 'pronunciation' => '/meɪk ə mɪsˈteɪk/'],
            ['front' => 'checkout', 'back' => 'صندوق (مغازه، فروشگاه و...)', 'pronunciation' => '/ˈʧəkˈaʊt/'],
            ['front' => 'complain', 'back' => 'شکایت کردن', 'pronunciation' => '/kəmˈpleɪn/'],
            ['front' => 'relaxed', 'back' => 'آسوده‌خاطر', 'pronunciation' => '/rɪˈlækst/'],
            ['front' => 'address', 'back' => 'نشانی', 'pronunciation' => '/ˈæ.dres/'],
            ['front' => 'relaxing', 'back' => 'آرامش‌بخش', 'pronunciation' => '/rɪˈlæks.ɪŋ/'],
            ['front' => 'fitting room', 'back' => 'اتاق پرو', 'pronunciation' => '/ˈfɪtɪŋ rum/'],
            ['front' => 'shopping basket', 'back' => 'سبد خرید', 'pronunciation' => '/ˈʃɑpɪŋ ˈbæskət/'],
            ['front' => 'receipt', 'back' => 'رسید', 'pronunciation' => '/rɪˈsiːt/'],
            ['front' => 'interested', 'back' => 'علاقه‌مند', 'pronunciation' => '/ˈɪn.trə.stəd/'],
            ['front' => 'invent', 'back' => 'اختراع کردن', 'pronunciation' => '/ɪnˈvent/'],
            ['front' => 'turn off', 'back' => 'خاموش کردن', 'pronunciation' => '/tɜrn ɔf/'],
            ['front' => 'do an exercise', 'back' => 'تمرین حل کردن', 'pronunciation' => '/du ən ˈɛksərˌsaɪz/'],
            ['front' => 'interesting', 'back' => 'جالب', 'pronunciation' => '/ˈɪntrəstɪŋ/'],
            ['front' => 'lie', 'back' => 'دروغ گفتن', 'pronunciation' => '/lɑɪ/'],
            ['front' => 'take back', 'back' => 'پس دادن (جنس خریداری‌شده)', 'pronunciation' => '/teɪk bæk/'],
            ['front' => 'salesperson', 'back' => 'فروشنده', 'pronunciation' => '/ˈseɪlzˌpɜr.sən/'],
            ['front' => 'bride', 'back' => 'عروس', 'pronunciation' => '/brɑɪd/'],
            ['front' => 'lie down', 'back' => 'دراز کشیدن', 'pronunciation' => '/laɪ daʊn/'],
            ['front' => 'size', 'back' => 'اندازه', 'pronunciation' => '/sɑɪz/'],
            ['front' => 'do homework', 'back' => 'تکلیف انجام دادن', 'pronunciation' => '/du ˈhoʊmˌwɜrk/'],
            ['front' => 'caregiver', 'back' => 'پرستار خانگی', 'pronunciation' => '/ˈkerɡɪvər/'],
            ['front' => 'wedding dress', 'back' => 'لباس عروس', 'pronunciation' => '/ˈwedɪŋ dres/'],
            ['front' => 'bridegroom', 'back' => 'داماد', 'pronunciation' => '/ˈbraɪdɡruːm/'],
            ['front' => 'try on', 'back' => 'پوشیدن', 'pronunciation' => '/traɪ ɑn/'],
            ['front' => 'paperwork', 'back' => 'کاغذ بازی', 'pronunciation' => '/ˈpeɪ.pər.wɜrk/'],
            ['front' => 'sew', 'back' => 'دوختن', 'pronunciation' => '/soʊ/'],
            ['front' => 'costume', 'back' => 'لباس', 'pronunciation' => '/ˈkɑːstuːm/'],
            ['front' => 'plate', 'back' => 'بشقاب', 'pronunciation' => '/pleɪt/'],
            ['front' => 'do the ironing', 'back' => 'اتو کردن', 'pronunciation' => '/du ði ˈaɪərnɪŋ/'],
            ['front' => 'press', 'back' => 'فشار دادن', 'pronunciation' => '/pres/'],
            ['front' => 'information', 'back' => 'اطلاعات', 'pronunciation' => '/ˌɪn.fərˈmeɪ.ʃən/'],
            ['front' => 'fashion designer', 'back' => 'طراح مد', 'pronunciation' => '/ˈfæʃən dɪˈzaɪnər/'],
            ['front' => 'fit', 'back' => 'اندازه بودن', 'pronunciation' => '/fɪt/'],
            ['front' => 'make a noise', 'back' => 'سروصدا کردن', 'pronunciation' => '/meɪk ə nɔɪz/'],
            ['front' => 'do the laundry', 'back' => 'رخت شستن', 'pronunciation' => '/dʊ ðə ˈlɔndri/'],
            ['front' => 'truth', 'back' => 'حقیقت', 'pronunciation' => '/truːθ/'],
            ['front' => 'reputation', 'back' => 'آوازه', 'pronunciation' => '/ˌrɛpjəˈteɪʃən/'],
            ['front' => 'customer', 'back' => 'مشتری', 'pronunciation' => '/ˈkʌs.tə.mər/'],
            ['front' => 'elevator', 'back' => 'آسانسور', 'pronunciation' => '/ˈelɪveɪtər/'],
            ['front' => 'survey', 'back' => 'نظرسنجی کردن', 'pronunciation' => '/sɜrˈveɪ/'],
            ['front' => 'excited', 'back' => 'هیجان‌زده', 'pronunciation' => '/ɪkˈsaɪtɪd/'],
            ['front' => 'suit', 'back' => 'آمدن (لباس به فرد)', 'pronunciation' => '/suːt/'],
            ['front' => 'high heels', 'back' => 'کفش پاشنه‌بلند', 'pronunciation' => '/haɪ hilz/'],
            ['front' => 'make a call', 'back' => 'تماس گرفتن (تلفن)', 'pronunciation' => '/meɪk ə kɔl/'],
            ['front' => 'exciting', 'back' => 'هیجان‌انگیز', 'pronunciation' => '/ɪkˈsaɪtɪŋ/'],
            ['front' => 'shopping bag', 'back' => 'کیسه خرید', 'pronunciation' => '/ˈʃɑːpɪŋ bæɡ/'],
            ['front' => 'button', 'back' => 'دکمه (لباس)', 'pronunciation' => '/ˈbʌtn/'],
            ['front' => 'do the shopping', 'back' => 'خرید خانه را انجام دادن', 'pronunciation' => '/du ðə ˈʃɑpɪŋ/'],
            ['front' => 'take off', 'back' => 'درآوردن (لباس، کفش و...)', 'pronunciation' => '/teɪk ɔf/'],
            ['front' => 'make lunch', 'back' => 'ناهار درست کردن', 'pronunciation' => '/meɪk lʌnʧ/'],
            ['front' => 'account', 'back' => 'حساب (بانکی)', 'pronunciation' => '/əˈkɑʊnt/'],
            ['front' => 'barefoot', 'back' => 'پابرهنه', 'pronunciation' => '/ˈbɛrˌfʊt/'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 4 vocabulary added.');
    }

    protected function seedLesson5($deck)
    {
        $vocabulary = [
            ['front' => 'nightlife', 'back' => 'تفریحات شبانه', 'pronunciation' => '/ˈnɑɪt.lɑɪf /'],
            ['front' => 'expression', 'back' => 'اصطلاح', 'pronunciation' => '/ɪkˈspreʃ.ən /'],
            ['front' => 'exciting', 'back' => 'هیجان‌انگیز', 'pronunciation' => '/ɪkˈsaɪtɪŋ /'],
            ['front' => 'several', 'back' => 'چند', 'pronunciation' => '/ˈsev.rəl /'],
            ['front' => 'museum', 'back' => 'موزه', 'pronunciation' => '/mjʊˈziːəm /'],
            ['front' => 'character', 'back' => 'شخصیت', 'pronunciation' => '/ˈkær.ɪk.tər /'],
            ['front' => 'crowded', 'back' => 'شلوغ', 'pronunciation' => '/ˈkrɑʊd.ɪd /'],
            ['front' => 'foreign', 'back' => 'خارجی', 'pronunciation' => '/ˈfɔːr.ən /'],
            ['front' => 'palace', 'back' => 'کاخ', 'pronunciation' => '/ˈpæl.əs /'],
            ['front' => 'nowadays', 'back' => 'امروزه', 'pronunciation' => '/ˈnɑʊ.əˌdeɪz /'],
            ['front' => 'dangerous', 'back' => 'خطرناک', 'pronunciation' => '/ˈdeɪn.dʒə.rəs /'],
            ['front' => 'village', 'back' => 'روستا', 'pronunciation' => '/ˈvɪləʤ /'],
            ['front' => 'illness', 'back' => 'بیماری', 'pronunciation' => '/ˈɪl.nəs /'],
            ['front' => 'cathedral', 'back' => 'کلیسای جامع', 'pronunciation' => '/kəˈθiː.drəl /'],
            ['front' => 'romantic', 'back' => 'عاشقانه', 'pronunciation' => '/roʊˈmæn.t̬ɪk /'],
            ['front' => 'city', 'back' => 'شهر', 'pronunciation' => '/ˈsɪt̬.i /'],
            ['front' => 'story', 'back' => 'داستان', 'pronunciation' => '/ˈstɔːr.i /'],
            ['front' => 'anxious', 'back' => 'مضطرب', 'pronunciation' => '/ˈæŋk.ʃəs /'],
            ['front' => 'modern', 'back' => 'مدرن', 'pronunciation' => '/ˈmɑːdərn /'],
            ['front' => 'save', 'back' => 'جمع کردن (پول)', 'pronunciation' => '/seɪv /'],
            ['front' => 'rude', 'back' => 'بی‌ادب', 'pronunciation' => '/rud /'],
            ['front' => 'church', 'back' => 'کلیسا', 'pronunciation' => '/tʃɜrtʃ /'],
            ['front' => 'town', 'back' => 'شهر (کوچک)', 'pronunciation' => '/tɑʊn /'],
            ['front' => 'irritable', 'back' => 'بدخلق', 'pronunciation' => '/ˈɪrətəbəl /'],
            ['front' => 'skin', 'back' => 'پوست', 'pronunciation' => '/skɪn /'],
            ['front' => 'noisy', 'back' => 'پرسروصدا', 'pronunciation' => '/ˈnɔɪzi /'],
            ['front' => 'pretend', 'back' => 'وانمود کردن', 'pronunciation' => '/prɪˈtend /'],
            ['front' => 'bone', 'back' => 'استخوان', 'pronunciation' => '/boʊn /'],
            ['front' => 'patient', 'back' => 'صبور', 'pronunciation' => '/ˈpeɪʃənt /'],
            ['front' => 'polluted', 'back' => 'آلوده', 'pronunciation' => '/pəˈlutəd /'],
            ['front' => 'shopping mall', 'back' => 'پاساژ خرید', 'pronunciation' => '/ˈʃɑːpɪŋ mɔːl /'],
            ['front' => 'face', 'back' => 'صورت', 'pronunciation' => '/feɪs /'],
            ['front' => 'brain', 'back' => 'مغز', 'pronunciation' => '/breɪn /'],
            ['front' => 'line', 'back' => 'خط', 'pronunciation' => '/lɑɪn /'],
            ['front' => 'statue', 'back' => 'مجسمه', 'pronunciation' => '/ˈstætʃ.uː /'],
            ['front' => 'sunlight', 'back' => 'نور خورشید', 'pronunciation' => '/ˈsʌn.lɑɪt /'],
            ['front' => 'stressed', 'back' => 'مضطرب', 'pronunciation' => '/strest /'],
            ['front' => 'sunscreen', 'back' => 'کرم ضدآفتاب', 'pronunciation' => '/ˈsʌnskriːn /'],
            ['front' => 'east', 'back' => 'شرق', 'pronunciation' => '/iːst /'],
            ['front' => 'temple', 'back' => 'معبد', 'pronunciation' => '/ˈtem.pəl /'],
            ['front' => 'stressful', 'back' => 'پراسترس', 'pronunciation' => '/ˈstrɛsfəl /'],
            ['front' => 'west', 'back' => 'غرب', 'pronunciation' => '/west /'],
            ['front' => 'clean', 'back' => 'تمیز', 'pronunciation' => '/kliːn /'],
            ['front' => 'save time', 'back' => 'صرفه‌جویی در وقت', 'pronunciation' => '/seɪv taɪm /'],
            ['front' => 'town hall', 'back' => 'ساختمان شهرداری', 'pronunciation' => '/ˌtaʊn ˈhɔːl /'],
            ['front' => 'tip', 'back' => 'راهنمایی', 'pronunciation' => '/tɪp /'],
            ['front' => 'department store', 'back' => 'فروشگاه بزرگ', 'pronunciation' => '/dɪˈpɑrt.məntˌstɔːr /'],
            ['front' => 'state', 'back' => 'ایالت', 'pronunciation' => '/steɪt /'],
            ['front' => 'empty', 'back' => 'خالی', 'pronunciation' => '/ˈem.ti /'],
            ['front' => 'river', 'back' => 'رود', 'pronunciation' => '/ˈrɪv.ər /'],
            ['front' => 'market', 'back' => 'بازار', 'pronunciation' => '/ˈmɑr.kɪt /'],
            ['front' => 'architecture', 'back' => 'معماری', 'pronunciation' => '/ˈɑrkəˌtɛkʧər /'],
            ['front' => 'mountain', 'back' => 'کوه', 'pronunciation' => '/ˈmɑʊnt.ən /'],
            ['front' => 'mosque', 'back' => 'مسجد', 'pronunciation' => '/mɑsk /'],
            ['front' => 'culture', 'back' => 'فرهنگ', 'pronunciation' => '/ˈkʌl.tʃər /'],
            ['front' => 'small', 'back' => 'کوچک', 'pronunciation' => '/smɔːl /'],
            ['front' => 'medium', 'back' => 'متوسط', 'pronunciation' => '/ˈmiːdiəm /'],
            ['front' => 'waste', 'back' => 'هدر دادن', 'pronunciation' => '/weɪst /'],
            ['front' => 'large', 'back' => 'بزرگ', 'pronunciation' => '/lɑːrdʒ /'],
            ['front' => 'in a hurry', 'back' => 'باعجله', 'pronunciation' => '/ɪn ə ˈhɜri /'],
            ['front' => 'interesting', 'back' => 'جالب', 'pronunciation' => '/ˈɪntrəstɪŋ /'],
            ['front' => 'prevent', 'back' => 'جلوگیری کردن', 'pronunciation' => '/prɪˈvent /'],
            ['front' => 'population', 'back' => 'جمعیت', 'pronunciation' => '/ˌpɑpjəˈleɪʃən /'],
            ['front' => 'spend', 'back' => 'صرف کردن', 'pronunciation' => '/spend /'],
            ['front' => 'old', 'back' => 'قدیمی', 'pronunciation' => '/oʊld /'],
            ['front' => 'on time', 'back' => 'به موقع', 'pronunciation' => '/ɑn taɪm /'],
            ['front' => 'quiet', 'back' => 'ساکت', 'pronunciation' => '/ˈkwɑɪət /'],
            ['front' => 'boring', 'back' => 'خسته‌کننده', 'pronunciation' => '/ˈbɔːrɪŋ /'],
            ['front' => 'inhabitant', 'back' => 'ساکن', 'pronunciation' => '/ɪnˈhæb.ɪ.tənt /'],
            ['front' => 'safe', 'back' => 'امن', 'pronunciation' => '/seɪf /'],
            ['front' => 'abbreviation', 'back' => 'مخفف', 'pronunciation' => '/əˌbriviˈeɪʃən /'],
            ['front' => 'without', 'back' => 'بدون', 'pronunciation' => '/wɪðˈɑʊt /'],
            ['front' => 'several', 'back' => 'چند', 'pronunciation' => '/ˈsev.rəl /'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 5 vocabulary added.');
    }

    protected function seedLesson6($deck)
    {
        $vocabulary = [
            ['front' => 'pull', 'back' => 'کشیدن', 'pronunciation' => '/pʊl/'],
            ['front' => 'stop', 'back' => 'متوقف کردن', 'pronunciation' => '/stɑp/'],
            ['front' => 'teach', 'back' => 'یاد دادن', 'pronunciation' => '/tiʧ/'],
            ['front' => 'borrow', 'back' => 'قرض گرفتن', 'pronunciation' => '/ˈbɑr.oʊ/'],
            ['front' => 'break', 'back' => 'شکستن', 'pronunciation' => '/breɪk/'],
            ['front' => 'frightened', 'back' => 'ترسیده', 'pronunciation' => '/ˈfraɪ.tənd/'],
            ['front' => 'catch', 'back' => 'رسیدن به (وسایل نقلیه)', 'pronunciation' => '/kætʃ/'],
            ['front' => 'sell', 'back' => 'فروختن', 'pronunciation' => '/sel/'],
            ['front' => 'fail', 'back' => 'رد شدن (در امتحان)', 'pronunciation' => '/feɪl/'],
            ['front' => 'remember', 'back' => 'به یاد آوردن', 'pronunciation' => '/rəˈmem.bər/'],
            ['front' => 'optimist', 'back' => 'آدم خوش‌بین', 'pronunciation' => '/ˈɑːptɪmɪst/'],
            ['front' => 'find', 'back' => 'پیدا کردن', 'pronunciation' => '/fɑɪnd/'],
            ['front' => 'hurt', 'back' => 'زخمی کردن (فیزیکی)', 'pronunciation' => '/hɜːrt/'],
            ['front' => 'forget', 'back' => 'فراموش کردن', 'pronunciation' => '/fərˈget/'],
            ['front' => 'afraid', 'back' => 'ترسیده', 'pronunciation' => '/əˈfreɪd/'],
            ['front' => 'turn on', 'back' => 'روشن کردن', 'pronunciation' => '/tɜrn ɑn/'],
            ['front' => 'previous', 'back' => 'قبلی', 'pronunciation' => '/ˈpriː.viː.əs/'],
            ['front' => 'turn off', 'back' => 'خاموش کردن', 'pronunciation' => '/tɜrn ɔf/'],
            ['front' => 'full', 'back' => 'پر', 'pronunciation' => '/fʊl/'],
            ['front' => 'fix', 'back' => 'تعمیر کردن', 'pronunciation' => '/fɪks/'],
            ['front' => 'go back', 'back' => 'برگشتن', 'pronunciation' => '/goʊ bæk/'],
            ['front' => 'start', 'back' => 'آغاز کردن', 'pronunciation' => '/stɑrt/'],
            ['front' => 'famous', 'back' => 'مشهور', 'pronunciation' => '/ˈfeɪ.məs/'],
            ['front' => 'extra large', 'back' => 'خیلی بزرگ', 'pronunciation' => '/ˈɛkstrə lɑrʤ/'],
            ['front' => 'finish', 'back' => 'تمام کردن', 'pronunciation' => '/ˈfɪn.ɪʃ/'],
            ['front' => 'psychoanalyst', 'back' => 'روانکاو', 'pronunciation' => '/ˌsaɪkoʊˈænəlɪst/'],
            ['front' => 'call back', 'back' => '(در جواب تماس ازدست‌رفته کسی) با کسی تماس گرفتن', 'pronunciation' => '/kɔl bæk/'],
            ['front' => 'order', 'back' => 'سفارش', 'pronunciation' => '/ˈɔːrd.ər/'],
            ['front' => 'angry', 'back' => 'عصبانی', 'pronunciation' => '/ˈæŋ.gri/'],
            ['front' => 'come back', 'back' => 'برگشتن', 'pronunciation' => '/kʌm bæk/'],
            ['front' => 'series', 'back' => 'سریال', 'pronunciation' => '/ˈsɪr.iːz/'],
            ['front' => 'violin', 'back' => 'ویولن', 'pronunciation' => '/ˌvɑɪ.əˈlɪn/'],
            ['front' => 'different', 'back' => 'متفاوت', 'pronunciation' => '/ˈdɪf.rənt/'],
            ['front' => 'repair', 'back' => 'تعمیر کردن', 'pronunciation' => '/rɪˈper/'],
            ['front' => 'stranger', 'back' => 'غریبه', 'pronunciation' => '/ˈstreɪn.dʒər/'],
            ['front' => 'give back', 'back' => 'پس دادن', 'pronunciation' => '/gɪv bæk/'],
            ['front' => 'freezing', 'back' => 'منجمد', 'pronunciation' => '/ˈfriː.zɪŋ/'],
            ['front' => 'pay back', 'back' => 'پس دادن (پول)', 'pronunciation' => '/peɪ bæk/'],
            ['front' => 'cheer up', 'back' => 'شاد کردن', 'pronunciation' => '/ʧɪr ʌp/'],
            ['front' => 'ice cream sundae', 'back' => 'دسر بستنی ساندی', 'pronunciation' => '/aɪs krim ˈsʌndeɪ/'],
            ['front' => 'get', 'back' => 'دریافت کردن', 'pronunciation' => '/get/'],
            ['front' => 'dream', 'back' => 'رویا دیدن', 'pronunciation' => '/driːm/'],
            ['front' => 'definitely', 'back' => 'قطعا', 'pronunciation' => '/ˈdef.ə.nət.li/'],
            ['front' => 'send back', 'back' => 'پس فرستادن', 'pronunciation' => '/sɛnd bæk/'],
            ['front' => 'get engaged', 'back' => 'نامزد کردن', 'pronunciation' => '/gɛt ɛnˈgeɪʤd/'],
            ['front' => 'receive', 'back' => 'دریافت کردن', 'pronunciation' => '/rɪˈsiːv/'],
            ['front' => 'successful', 'back' => 'موفق', 'pronunciation' => '/səkˈses.fəl/'],
            ['front' => 'lend', 'back' => 'قرض دادن', 'pronunciation' => '/lɛnd/'],
            ['front' => 'doubt', 'back' => 'شک', 'pronunciation' => '/daʊt/'],
            ['front' => 'take back', 'back' => 'پس دادن (جنس خریداری‌شده)', 'pronunciation' => '/teɪk bæk/'],
            ['front' => 'miss', 'back' => 'جا ماندن (از)', 'pronunciation' => '/mɪs/'],
            ['front' => 'get in touch', 'back' => 'در تماس بودن', 'pronunciation' => '/gɛt ɪn tʌʧ/'],
            ['front' => 'pessimist', 'back' => 'بدبین', 'pronunciation' => '/ˈpesɪmɪst/'],
            ['front' => 'pass', 'back' => 'با موفقیت گذراندن', 'pronunciation' => '/pæs/'],
            ['front' => 'win', 'back' => 'پیروز شدن', 'pronunciation' => '/wɪn/'],
            ['front' => 'push', 'back' => 'هل دادن', 'pronunciation' => '/pʊʃ/'],
            ['front' => 'learn', 'back' => 'یاد گرفتن', 'pronunciation' => '/lɜːrn/'],
            ['front' => 'arrive', 'back' => 'رسیدن', 'pronunciation' => '/əˈrɑɪv/'],
            ['front' => 'flower', 'back' => 'گل', 'pronunciation' => '/ˈflɑʊ.ər/'],
            ['front' => 'send', 'back' => 'فرستادن', 'pronunciation' => '/sɛnd/'],
            ['front' => 'owl', 'back' => 'جغد', 'pronunciation' => '/aʊl/'],
            ['front' => 'leave', 'back' => 'ترک کردن', 'pronunciation' => '/liːv/'],
            ['front' => 'lose', 'back' => 'گم کردن', 'pronunciation' => '/luːz/'],
            ['front' => 'buy', 'back' => 'خریدن', 'pronunciation' => '/baɪ/'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 6 vocabulary added.');
    }

    protected function seedLesson7($deck)
    {
        $vocabulary = [
            ['front' => 'try', 'back' => 'سعی کردن', 'pronunciation' => '/traɪ /'],
            ['front' => 'permit', 'back' => 'اجازه دادن', 'pronunciation' => '/pərˈmɪt /'],
            ['front' => 'feel like', 'back' => 'خواستن', 'pronunciation' => '/fil laɪk /'],
            ['front' => 'scales', 'back' => 'ترازو', 'pronunciation' => '/skeɪlz /'],
            ['front' => 'very', 'back' => 'خیلی', 'pronunciation' => '/ˈveri /'],
            ['front' => 'want', 'back' => 'خواستن', 'pronunciation' => '/wɒnt /'],
            ['front' => 'breathe', 'back' => 'نفس کشیدن', 'pronunciation' => '/briːð /'],
            ['front' => 'obligatory', 'back' => 'اجباری', 'pronunciation' => '/əˈblɪgəˌtɔri /'],
            ['front' => 'make conversation', 'back' => 'سر صحبت را باز کردن', 'pronunciation' => '/meɪk ˌkɑnvərˈseɪʃən /'],
            ['front' => 'would like', 'back' => 'خواستن', 'pronunciation' => '/wʊd laɪk /'],
            ['front' => 'choir', 'back' => 'گروه کر', 'pronunciation' => '/kwɑɪr /'],
            ['front' => 'decide', 'back' => 'تصمیم گرفتن', 'pronunciation' => '/dɪˈsɑɪd /'],
            ['front' => 'complete', 'back' => 'تمام', 'pronunciation' => '/kəmˈpliːt /'],
            ['front' => 'shake hands', 'back' => 'دست دادن (با کسی)', 'pronunciation' => '/ʃeɪk hændz /'],
            ['front' => 'really', 'back' => 'واقعاً', 'pronunciation' => '/ˈriː.li /'],
            ['front' => 'forget', 'back' => 'فراموش کردن', 'pronunciation' => '/fərˈget /'],
            ['front' => 'enjoy', 'back' => 'لذت بردن', 'pronunciation' => '/enˈdʒɔɪ /'],
            ['front' => 'high', 'back' => 'زیر (صدا)', 'pronunciation' => '/hɑɪ /'],
            ['front' => 'beginner', 'back' => 'مبتدی', 'pronunciation' => '/bɪˈgɪn.ər /'],
            ['front' => 'impression', 'back' => 'نظر', 'pronunciation' => '/ɪmˈpreʃ.ən /'],
            ['front' => 'note', 'back' => 'نت', 'pronunciation' => '/noʊt /'],
            ['front' => 'leftovers', 'back' => 'غذای باقی‌مانده', 'pronunciation' => '/ˈlɛfˌtoʊvərz /'],
            ['front' => 'finish', 'back' => 'تمام کردن', 'pronunciation' => '/ˈfɪn.ɪʃ /'],
            ['front' => 'magical', 'back' => 'سحرآمیز', 'pronunciation' => '/ˈmædʒ.ɪ.kəl /'],
            ['front' => 'entrance fee', 'back' => 'هزینه ورودی', 'pronunciation' => '/ˈɛntrəns fi /'],
            ['front' => 'hope', 'back' => 'امیدوار بودن', 'pronunciation' => '/hoʊp /'],
            ['front' => 'bargain', 'back' => 'چانه زدن', 'pronunciation' => '/ˈbɑr.gən /'],
            ['front' => 'soup', 'back' => 'سوپ', 'pronunciation' => '/suːp /'],
            ['front' => 'learn', 'back' => 'یاد گرفتن', 'pronunciation' => '/lɜːrn /'],
            ['front' => 'pharmacy', 'back' => 'داروخانه', 'pronunciation' => '/ˈfɑrməsi /'],
            ['front' => 'intensive course', 'back' => 'دوره آموزشی فشرده', 'pronunciation' => '/ɪnˈtɛnsɪv kɔrs /'],
            ['front' => 'go on', 'back' => 'ادامه دادن', 'pronunciation' => '/goʊ ɑn /'],
            ['front' => 'headache', 'back' => 'سردرد', 'pronunciation' => '/ˈhedeɪk /'],
            ['front' => 'need', 'back' => 'بایستن', 'pronunciation' => '/niːd /'],
            ['front' => 'hate', 'back' => 'متنفر بودن', 'pronunciation' => '/heɪt /'],
            ['front' => 'cough', 'back' => 'سرفه', 'pronunciation' => '/kɔːf /'],
            ['front' => 'offer', 'back' => 'پیشنهاد دادن', 'pronunciation' => '/ˈɔː.fər /'],
            ['front' => 'flu', 'back' => 'آنفلوآنزا', 'pronunciation' => '/fluː /'],
            ['front' => 'like', 'back' => 'دوست داشتن', 'pronunciation' => '/lɑɪk /'],
            ['front' => 'plan', 'back' => 'قصد داشتن', 'pronunciation' => '/plæn /'],
            ['front' => 'love', 'back' => 'عاشق بودن', 'pronunciation' => '/lʌv /'],
            ['front' => 'tactic', 'back' => 'طرح', 'pronunciation' => '/ˈtæktɪk /'],
            ['front' => 'a little', 'back' => 'کمی', 'pronunciation' => '/ə ˈlɪtəl /'],
            ['front' => 'feel-good', 'back' => 'شادی‌آور', 'pronunciation' => '/ˈfiːl ɡʊd /'],
            ['front' => 'pretend', 'back' => 'وانمود کردن', 'pronunciation' => '/prɪˈtend /'],
            ['front' => 'temperature', 'back' => 'تب', 'pronunciation' => '/ˈtemprətʃər /'],
            ['front' => 'greet', 'back' => 'خوشامد گفتن', 'pronunciation' => '/griːt /'],
            ['front' => 'mind', 'back' => 'اهمیت دادن', 'pronunciation' => '/mɑɪnd /'],
            ['front' => 'as soon as', 'back' => 'به محض اینکه', 'pronunciation' => '/æz sun æz /'],
            ['front' => 'a little bit', 'back' => 'کمی', 'pronunciation' => '/ə ˈlɪtəl bɪt /'],
            ['front' => 'promise', 'back' => 'قول دادن', 'pronunciation' => '/ˈprɑm.əs /'],
            ['front' => 'survive', 'back' => 'زنده ماندن', 'pronunciation' => '/sərˈvaɪv /'],
            ['front' => 'remember', 'back' => 'به یاد آوردن', 'pronunciation' => '/rəˈmem.bər /'],
            ['front' => 'modifier', 'back' => 'توصیف‌کننده (دستور زبان)', 'pronunciation' => '/ˈmɑːdɪfaɪər /'],
            ['front' => 'spend', 'back' => 'صرف کردن', 'pronunciation' => '/spend /'],
            ['front' => 'stomachache', 'back' => 'دل‌درد', 'pronunciation' => '/ˈstʌm.əkeɪk /'],
            ['front' => 'honest', 'back' => 'راستگو', 'pronunciation' => '/ˈɑnɪst /'],
            ['front' => 'experiment', 'back' => 'آزمایش', 'pronunciation' => '/ɪkˈsper.ə.mənt /'],
            ['front' => 'cold', 'back' => 'سرماخوردگی', 'pronunciation' => '/koʊld /'],
            ['front' => 'absolutely', 'back' => 'مسلماً', 'pronunciation' => '/ˈæb.səˌluːt.li /'],
            ['front' => 'delicious', 'back' => 'خوشمزه', 'pronunciation' => '/dɪˈlɪʃ.əs /'],
            ['front' => 'stop', 'back' => 'متوقف کردن', 'pronunciation' => '/stɑp /'],
            ['front' => 'fee', 'back' => 'دستمزد', 'pronunciation' => '/fiː /'],
            ['front' => 'extremely', 'back' => 'به شدت', 'pronunciation' => '/ɪkˈstriːm.li /'],
            ['front' => 'start', 'back' => 'آغاز کردن', 'pronunciation' => '/stɑrt /'],
            ['front' => 'punctual', 'back' => 'وقت‌شناس', 'pronunciation' => '/ˈpʌŋkʧuəl /'],
            ['front' => 'voice mail', 'back' => 'پیغام صوتی', 'pronunciation' => '/ˈvɔɪsmeɪl /'],
            ['front' => 'fairly', 'back' => 'نسبتاً', 'pronunciation' => '/ˈfer.li /'],
            ['front' => 'drug', 'back' => 'دارو', 'pronunciation' => '/drʌg /'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 7 vocabulary added.');
    }


    protected function seedLesson8($deck)
    {
        $vocabulary = [
            ['front' => 'expenses', 'back' => 'مخارج', 'pronunciation' => '/ɪkˈspɛnsəz/'],
            ['front' => 'keep in touch', 'back' => 'در ارتباط بودن', 'pronunciation' => '/kip ɪn tʌʧ/'],
            ['front' => 'look like', 'back' => 'شبیه بودن', 'pronunciation' => '/lʊk laɪk/'],
            ['front' => 'miss', 'back' => 'نرفتن', 'pronunciation' => '/mɪs/'],
            ['front' => "change one's mind", 'back' => 'نظر خود را تغییر دادن', 'pronunciation' => '/tʃˈeɪndʒ wˈʌnz mˈaɪnd/'],
            ['front' => 'earn', 'back' => '(پول) در آوردن', 'pronunciation' => '/ɜrn/'],
            ['front' => 'get married', 'back' => 'ازدواج کردن', 'pronunciation' => '/gɛt ˈmærɪd/'],
            ['front' => 'masterfully', 'back' => 'استادانه', 'pronunciation' => '/ˈmæstərfəli/'],
            ['front' => 'meet', 'back' => 'ملاقات کردن', 'pronunciation' => '/miːt/'],
            ['front' => 'calmly', 'back' => 'به آرامی', 'pronunciation' => '/ˈkɑm.li/'],
            ['front' => 'lose', 'back' => 'گم کردن', 'pronunciation' => '/luːz/'],
            ['front' => 'advice', 'back' => 'نصیحت', 'pronunciation' => '/ədˈvɑɪs/'],
            ['front' => 'bring', 'back' => 'آوردن', 'pronunciation' => '/brɪŋ/'],
            ['front' => 'cyclone', 'back' => 'گردباد', 'pronunciation' => '/sɪˈkloʊn/'],
            ['front' => 'earthquake', 'back' => 'زلزله', 'pronunciation' => '/ˈɜrθ.kweɪk/'],
            ['front' => 'wear', 'back' => 'پوشیدن', 'pronunciation' => '/wer/'],
            ['front' => 'flood', 'back' => 'سیل', 'pronunciation' => '/flʌd/'],
            ['front' => 'take', 'back' => 'گرفتن', 'pronunciation' => '/teɪk/'],
            ['front' => 'get on', 'back' => 'سوار شدن', 'pronunciation' => '/gɛt ɑn/'],
            ['front' => 'carry', 'back' => 'حمل کردن', 'pronunciation' => '/ˈkær.i/'],
            ['front' => 'get off', 'back' => 'پیاده شدن (از)', 'pronunciation' => '/gɛt ɔf/'],
            ['front' => 'attend', 'back' => 'حضور داشتن', 'pronunciation' => '/əˈtend/'],
            ['front' => 'forest fire', 'back' => 'آتش‌سوزی جنگل', 'pronunciation' => '/ˈfɔrəst ˈfaɪər/'],
            ['front' => 'get up', 'back' => 'بیدار شدن', 'pronunciation' => '/gɛt ʌp/'],
            ['front' => 'look for', 'back' => '(به) دنبال گشتن', 'pronunciation' => '/lʊk fɔr/'],
            ['front' => 'dreamily', 'back' => 'در حالت خواب و رویا', 'pronunciation' => '/ˈdriːmɪli/'],
            ['front' => 'win', 'back' => 'پیروز شدن', 'pronunciation' => '/wɪn/'],
            ['front' => 'risk', 'back' => 'خطر کردن', 'pronunciation' => '/rɪsk/'],
            ['front' => 'monsoon', 'back' => 'باد و باران موسمی', 'pronunciation' => '/mɑnˈsun/'],
            ['front' => 'find', 'back' => 'پیدا کردن', 'pronunciation' => '/fɑɪnd/'],
            ['front' => 'get', 'back' => 'خریدن', 'pronunciation' => '/get/'],
            ['front' => 'know', 'back' => 'شناختن', 'pronunciation' => '/noʊ/'],
            ['front' => 'macho', 'back' => 'نرینه رفتار', 'pronunciation' => '/ˈmɑʧoʊ/'],
            ['front' => 'hope', 'back' => 'امیدوار بودن', 'pronunciation' => '/hoʊp/'],
            ['front' => 'say', 'back' => 'گفتن', 'pronunciation' => '/seɪ/'],
            ['front' => 'get in shape', 'back' => 'روی فرم آمدن (بدن)', 'pronunciation' => '/gɛt ɪn ʃeɪp/'],
            ['front' => 'tsunami', 'back' => 'سونامی', 'pronunciation' => '/tsuːˈnɑːmi/'],
            ['front' => 'have second thoughts', 'back' => 'دو دل شدن', 'pronunciation' => '/hæv ˈsɛkənd θɔts/'],
            ['front' => 'instead', 'back' => 'در عوض', 'pronunciation' => '/ɪnˈsted/'],
            ['front' => 'exclaim', 'back' => 'با تعجب فریاد زدن', 'pronunciation' => '/ɪkˈskleɪm/'],
            ['front' => 'wait', 'back' => 'صبر کردن', 'pronunciation' => '/weɪt/'],
            ['front' => 'spill', 'back' => 'ریختن', 'pronunciation' => '/spɪl/'],
            ['front' => 'get divorced', 'back' => 'طلاق گرفتن', 'pronunciation' => '/gɛt dɪˈvɔrst/'],
            ['front' => 'get along', 'back' => 'کنار آمدن', 'pronunciation' => '/gɛt əˈlɔŋ/'],
            ['front' => 'slowly', 'back' => 'به آرامی', 'pronunciation' => '/ˈsloʊ.li/'],
            ['front' => 'trust', 'back' => 'اعتماد کردن', 'pronunciation' => '/trʌst/'],
            ['front' => 'tell', 'back' => 'گفتن', 'pronunciation' => '/tel/'],
            ['front' => 'parking space', 'back' => 'جای پارک', 'pronunciation' => '/ˈpɑrkɪŋ speɪs/'],
            ['front' => 'completely', 'back' => 'کاملا', 'pronunciation' => '/kəmˈpliːt.li/'],
            ['front' => 'get lost', 'back' => 'گم شدن', 'pronunciation' => '/gɛt lɔst/'],
            ['front' => 'watch', 'back' => 'تماشا کردن', 'pronunciation' => '/wɒtʃ/'],
            ['front' => 'avoid', 'back' => 'دوری کردن', 'pronunciation' => '/əˈvɔɪd/'],
            ['front' => 'lend', 'back' => 'قرض دادن', 'pronunciation' => '/lɛnd/'],
            ['front' => 'suspicious', 'back' => 'مشکوک', 'pronunciation' => '/səˈspɪʃ.əs/'],
            ['front' => 'natural disaster', 'back' => 'بلای طبیعی', 'pronunciation' => '/ˈnæʧərəl dɪˈzæstər/'],
            ['front' => 'go for', 'back' => 'برای به‌دست آوردن (چیزی یا کسی) تلاش کردن', 'pronunciation' => '/goʊ fɔr/'],
            ['front' => 'borrow', 'back' => 'قرض گرفتن', 'pronunciation' => '/ˈbɑr.oʊ/'],
            ['front' => 'suburb', 'back' => 'حومه', 'pronunciation' => '/ˈsʌb.ɜrb/'],
            ['front' => 'advantage', 'back' => 'مزیت', 'pronunciation' => '/ədˈvæn.t̬ɪdʒ/'],
            ['front' => 'blizzard', 'back' => 'کولاک', 'pronunciation' => '/ˈblɪzərd/'],
            ['front' => 'worth', 'back' => '(دارای) ارزش', 'pronunciation' => '/wɜrθ/'],
            ['front' => 'expect', 'back' => 'انتظار داشتن', 'pronunciation' => '/ɪkˈspekt/'],
            ['front' => 'look at', 'back' => 'نگاه کردن به', 'pronunciation' => '/lʊk æt/'],
            ['front' => 'go for it', 'back' => 'با شجاعت کاری را انجام دادن', 'pronunciation' => '/goʊ fɔr ɪt/'],
            ['front' => 'look', 'back' => 'به نظر رسیدن', 'pronunciation' => '/lʊk/'],
            ['front' => "change one's mind", 'back' => 'نظر خود را تغییر دادن', 'pronunciation' => '/tʃˈeɪndʒ wˈʌnz mˈaɪnd/'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 8 vocabulary added.');
    }

    protected function seedLesson9($deck)
    {
        $vocabulary = [
            ['front' => 'respected', 'back' => 'ارجمند', 'pronunciation' => '/rəˈspek.tɪd /'],
            ['front' => 'agoraphobia', 'back' => 'هراس از مکان‌های باز', 'pronunciation' => '/ˌæɡərəˈfoʊbiə /'],
            ['front' => 'talented', 'back' => 'بااستعداد', 'pronunciation' => '/ˈtæl.ənt.ɪd /'],
            ['front' => 'backward', 'back' => 'به عقب', 'pronunciation' => '/ˈbæk.wərd /'],
            ['front' => 'wave', 'back' => 'دست تکان دادن', 'pronunciation' => '/weɪv /'],
            ['front' => 'die', 'back' => 'مردن', 'pronunciation' => '/dɑɪ /'],
            ['front' => 'influence', 'back' => 'تاثیر گذاشتن', 'pronunciation' => '/ˈɪnfluəns /'],
            ['front' => 'bite', 'back' => 'گاز گرفتن', 'pronunciation' => '/bɑɪt /'],
            ['front' => 'crocodile', 'back' => 'کروکدیل', 'pronunciation' => '/ˈkrɑːkədaɪl /'],
            ['front' => 'bee', 'back' => 'زنبور', 'pronunciation' => '/biː /'],
            ['front' => 'cow', 'back' => 'گاو', 'pronunciation' => '/kaʊ /'],
            ['front' => 'glossophobia', 'back' => 'ترس از حرف زدن در جمع', 'pronunciation' => '/ˈɡlɑsˈoʊˈfoʊbiːə /'],
            ['front' => 'exposure therapy', 'back' => 'درمان از طریق مواجهه', 'pronunciation' => '/ɪkˈspoʊʒər ˈθɛrəpi /'],
            ['front' => 'dolphin', 'back' => 'دلفین', 'pronunciation' => '/ˈdɑːlfɪn /'],
            ['front' => 'bull', 'back' => 'گاو نر', 'pronunciation' => '/bʊl /'],
            ['front' => 'high school', 'back' => 'دبیرستان', 'pronunciation' => '/ˈhɑɪ skuːl /'],
            ['front' => 'sting', 'back' => 'نیش زدن', 'pronunciation' => '/stɪŋ /'],
            ['front' => 'elephant', 'back' => 'فیل', 'pronunciation' => '/ˈel.ə.fənt /'],
            ['front' => 'dog', 'back' => 'سگ', 'pronunciation' => '/dɔːɡ /'],
            ['front' => 'work', 'back' => 'موثر واقع شدن', 'pronunciation' => '/wɜːrk /'],
            ['front' => 'tie', 'back' => 'بستن (با طناب، سیم و ...)', 'pronunciation' => '/taɪ /'],
            ['front' => 'jellyfish', 'back' => 'عروس دریایی', 'pronunciation' => '/ˈdʒelifɪʃ /'],
            ['front' => 'giraffe', 'back' => 'زرافه', 'pronunciation' => '/dʒəˈræf /'],
            ['front' => 'goat', 'back' => 'بز', 'pronunciation' => '/ɡoʊt /'],
            ['front' => 'effective', 'back' => 'موثر', 'pronunciation' => '/ɪˈfek.tɪv /'],
            ['front' => 'shark', 'back' => 'کوسه (جانورشناسی)', 'pronunciation' => '/ʃɑːrk /'],
            ['front' => 'kangaroo', 'back' => 'کانگورو', 'pronunciation' => '/ˌkæŋɡəˈruː /'],
            ['front' => 'float', 'back' => 'شناور بودن', 'pronunciation' => '/floʊt /'],
            ['front' => 'horse', 'back' => 'اسب', 'pronunciation' => '/hɔːrs /'],
            ['front' => 'placebo', 'back' => 'دارونما', 'pronunciation' => '/pləˈsiːboʊ /'],
            ['front' => 'height', 'back' => 'جای بلند', 'pronunciation' => '/haɪt /'],
            ['front' => 'snake', 'back' => 'مار', 'pronunciation' => '/sneɪk /'],
            ['front' => 'pig', 'back' => 'خوک', 'pronunciation' => '/pɪg /'],
            ['front' => 'phobia', 'back' => 'هراس', 'pronunciation' => '/ˈfoʊbiə /'],
            ['front' => 'sheep', 'back' => 'گوسفند', 'pronunciation' => '/ʃiːp /'],
            ['front' => 'panic', 'back' => 'هراس', 'pronunciation' => '/ˈpænɪk /'],
            ['front' => 'be born', 'back' => 'به دنیا آمدن', 'pronunciation' => '/bi bɔrn /'],
            ['front' => 'lion', 'back' => 'شیر (حیوان)', 'pronunciation' => '/ˈlɑɪ.ən /'],
            ['front' => 'rational', 'back' => 'منطقی', 'pronunciation' => '/ˈræʃnəl /'],
            ['front' => 'butterfly', 'back' => 'پروانه', 'pronunciation' => '/ˈbʌtərflaɪ /'],
            ['front' => 'college', 'back' => 'کالج', 'pronunciation' => '/ˈkɑlɪdʒ /'],
            ['front' => 'monkey', 'back' => 'میمون', 'pronunciation' => '/ˈmʌŋ.ki /'],
            ['front' => 'footstep', 'back' => 'صدای پا', 'pronunciation' => '/ˈfʊtˌstɛp /'],
            ['front' => 'irrational', 'back' => 'غیرمنطقی', 'pronunciation' => '/ɪˈræʃənl /'],
            ['front' => 'fly', 'back' => 'مگس (حشره‌شناسی)', 'pronunciation' => '/flɑɪ /'],
            ['front' => 'mouse', 'back' => 'موش', 'pronunciation' => '/mɑʊs /'],
            ['front' => 'get divorced', 'back' => 'طلاق گرفتن', 'pronunciation' => '/gɛt dɪˈvɔrst /'],
            ['front' => 'directions', 'back' => 'آدرس', 'pronunciation' => '/dəˈrek.ʃənz /'],
            ['front' => 'scared', 'back' => 'ترسیده', 'pronunciation' => '/skeəd /'],
            ['front' => 'mosquito', 'back' => 'پشه', 'pronunciation' => '/məˈskiːt̬.oʊ /'],
            ['front' => 'form', 'back' => 'تأسیس کردن', 'pronunciation' => '/fɔːrm /'],
            ['front' => 'marry', 'back' => 'ازدواج کردن', 'pronunciation' => '/ˈmɛri /'],
            ['front' => 'rabbit', 'back' => 'خرگوش', 'pronunciation' => '/ˈræb.ɪt /'],
            ['front' => 'terrified', 'back' => 'وحشت‌زده', 'pronunciation' => '/ˈter.ə.fɑɪd /'],
            ['front' => 'band', 'back' => 'گروه (موسیقی و ...)', 'pronunciation' => '/bænd /'],
            ['front' => 'get married', 'back' => 'ازدواج کردن', 'pronunciation' => '/gɛt ˈmærɪd /'],
            ['front' => 'shout', 'back' => 'داد زدن', 'pronunciation' => '/ʃɑʊt /'],
            ['front' => 'turn', 'back' => 'پیچیدن', 'pronunciation' => '/tɜrn /'],
            ['front' => 'tiger', 'back' => 'ببر', 'pronunciation' => '/ˈtaɪɡər /'],
            ['front' => 'effect', 'back' => 'تأثیر', 'pronunciation' => '/ɪˈfekt /'],
            ['front' => 'graduate', 'back' => 'فارغ‌التحصیل شدن', 'pronunciation' => '/ˈgrædʒʊət /'],
            ['front' => 'acrophobia', 'back' => 'ترس از ارتفاع', 'pronunciation' => '/ˈækroʊˈfoʊbiə /'],
            ['front' => 'arachnophobia', 'back' => 'عنکبوت‌هراسی', 'pronunciation' => '/əˌræknəˈfoʊbiə /'],
            ['front' => 'whale', 'back' => 'نهنگ', 'pronunciation' => '/weɪl /'],
            ['front' => 'retire', 'back' => 'بازنشسته شدن', 'pronunciation' => '/rɪˈtaɪr /'],
            ['front' => 'award', 'back' => 'جایزه', 'pronunciation' => '/əˈwɔːrd /'],
            ['front' => 'bat', 'back' => 'خفاش', 'pronunciation' => '/bæt /'],
            ['front' => 'claustrophobia', 'back' => 'تنگناهراسی', 'pronunciation' => '/ˌklɔstrəˈfoʊbiə /'],
            ['front' => 'ahead', 'back' => 'جلو', 'pronunciation' => '/əˈhed /'],
            ['front' => 'elementary school', 'back' => 'دبستان', 'pronunciation' => '/ˌɛləˈmɛntri skul /'],
            ['front' => 'keep still', 'back' => 'آرام و بی‌حرکت ماندن', 'pronunciation' => '/kip stɪl /'],
            ['front' => 'bear', 'back' => 'خرس', 'pronunciation' => '/ber /'],
            ['front' => 'suck', 'back' => 'مکیدن', 'pronunciation' => '/sʌk /'],
            ['front' => 'captain', 'back' => 'کاپیتان (کشتی یا هواپیما)', 'pronunciation' => '/ˈkæp.tən /'],
            ['front' => 'bird', 'back' => 'پرنده', 'pronunciation' => '/bɜrd /'],
            ['front' => 'spider', 'back' => 'عنکبوت', 'pronunciation' => '/ˈspaɪdər /'],
            ['front' => 'separate', 'back' => 'جدا شدن', 'pronunciation' => '/ˈsep.ə.rət /'],
            ['front' => 'affect', 'back' => 'تحت‌تأثیر قرار دادن', 'pronunciation' => '/əˈfɛkt /'],
            ['front' => 'funeral', 'back' => 'مراسم خاک‌سپاری', 'pronunciation' => '/ˈfjuː.nə.rəl /'],
            ['front' => 'camel', 'back' => 'شتر', 'pronunciation' => '/ˈkæml /'],
            ['front' => 'cure', 'back' => 'درمان', 'pronunciation' => '/kjʊr /'],
            ['front' => 'fall in love', 'back' => 'عاشق (کسی) شدن', 'pronunciation' => '/fɔl ɪn lʌv /'],
            ['front' => 'chicken', 'back' => 'مرغ', 'pronunciation' => '/ˈtʃɪkən /'],
            ['front' => 'injure', 'back' => 'زخمی کردن', 'pronunciation' => '/ˈɪn.dʒər /'],
            ['front' => 'overcome', 'back' => 'غالب آمدن', 'pronunciation' => '/ˈoʊvərˌkʌm /'],
            ['front' => 'have children', 'back' => 'بچه‌دار شدن', 'pronunciation' => '/hæv ˈʧɪldrən /'],
            ['front' => 'eldest', 'back' => 'ارشد', 'pronunciation' => '/ˈeldɪst /'],
            ['front' => 'pocket', 'back' => 'جیب', 'pronunciation' => '/ˈpɑk.ɪt /'],
            ['front' => 'have children', 'back' => 'بچه‌دار شدن', 'pronunciation' => '/hæv ˈʧɪldrən /'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 9 vocabulary added.');
    }

    protected function seedLesson10($deck)
    {
        $vocabulary = [
            ['front' => 'organization', 'back' => 'سازمان', 'pronunciation' => '/ˌɔːr.gə.nəˈzeɪ.ʃən/'],
            ['front' => 'history', 'back' => 'تاریخ', 'pronunciation' => '/ˈhɪs.tə.ri/'],
            ['front' => 'indecisive', 'back' => 'ناقاطع (در تصمیم‌گیری)', 'pronunciation' => '/ˌɪndɪˈsaɪsɪv/'],
            ['front' => 'hammer', 'back' => 'چکش', 'pronunciation' => '/ˈhæm.ər/'],
            ['front' => 'education', 'back' => 'تحصیل', 'pronunciation' => '/ˌɛʤəˈkeɪʃən/'],
            ['front' => 'IT', 'back' => 'فناوری اطلاعات', 'pronunciation' => '/aɪ ti/'],
            ['front' => 'knife', 'back' => 'چاقو', 'pronunciation' => '/nɑɪf/'],
            ['front' => 'electrical', 'back' => 'برقی', 'pronunciation' => '/eˈlek.trɪ.kəl/'],
            ['front' => 'confusion', 'back' => 'سردرگمی', 'pronunciation' => '/kənˈfjuː.ʒən/'],
            ['front' => 'gadget', 'back' => 'وسیله', 'pronunciation' => '/ˈgæʤət/'],
            ['front' => 'logo', 'back' => 'علامت تجاری', 'pronunciation' => '/ˈloʊ.goʊ/'],
            ['front' => 'choice', 'back' => 'انتخاب', 'pronunciation' => '/ʧɔɪs/'],
            ['front' => 'literature', 'back' => 'ادبیات', 'pronunciation' => '/ˈlɪtˬ.ə.rə.tʃər/'],
            ['front' => 'be able to', 'back' => 'توانستن', 'pronunciation' => '/bi ˈeɪbəl tu/'],
            ['front' => 'life', 'back' => 'زندگی', 'pronunciation' => '/lɑɪf/'],
            ['front' => 'can opener', 'back' => 'قوطی‌ بازکن', 'pronunciation' => '/kæn ˈoʊpənər/'],
            ['front' => 'math', 'back' => 'ریاضی', 'pronunciation' => '/mæθ/'],
            ['front' => 'make a decision', 'back' => 'تصمیم گرفتن', 'pronunciation' => '/meɪk ə dɪˈsɪʒən/'],
            ['front' => 'death', 'back' => 'مرگ', 'pronunciation' => '/deθ/'],
            ['front' => 'invent', 'back' => 'اختراع کردن', 'pronunciation' => '/ɪnˈvent/'],
            ['front' => 'PE', 'back' => 'تربیت بدنی', 'pronunciation' => '/ˌpiː ˈiː/'],
            ['front' => 'success', 'back' => 'موفقیت', 'pronunciation' => '/səkˈses/'],
            ['front' => 'miss', 'back' => 'از دست دادن', 'pronunciation' => '/mɪs/'],
            ['front' => 'science', 'back' => 'علوم', 'pronunciation' => '/ˈsɑɪ.əns/'],
            ['front' => 'discover', 'back' => 'کشف کردن', 'pronunciation' => '/dɪˈskʌv.ər/'],
            ['front' => 'opportunity', 'back' => 'فرصت', 'pronunciation' => '/ˌɑp.ərˈtuː.nət̬.i/'],
            ['front' => 'base on', 'back' => 'بر اساس چیزی بودن', 'pronunciation' => '/beɪs ɑn/'],
            ['front' => 'design', 'back' => 'طراحی کردن', 'pronunciation' => '/dɪˈzaɪn/'],
            ['front' => 'call', 'back' => 'نامیدن', 'pronunciation' => '/kɔːl/'],
            ['front' => 'option', 'back' => 'انتخاب', 'pronunciation' => '/ˈɑːpʃn/'],
            ['front' => 'disposable', 'back' => 'یک بار مصرف', 'pronunciation' => '/dɪˈspoʊzəbəl/'],
            ['front' => 'decision', 'back' => 'تصمیم', 'pronunciation' => '/dɪˈsɪʒ.ən/'],
            ['front' => 'diaper', 'back' => 'پوشک بچه', 'pronunciation' => '/ˈdaɪpər/'],
            ['front' => 'pick up', 'back' => 'به دنبال رفتن', 'pronunciation' => '/pɪk ʌp/'],
            ['front' => 'canned', 'back' => 'کنسرو شده', 'pronunciation' => '/kænd/'],
            ['front' => 'imagination', 'back' => 'قوه‌ تخیل', 'pronunciation' => '/ɪˌmædʒ.əˈneɪ.ʃən/'],
            ['front' => 'art', 'back' => 'هنر', 'pronunciation' => '/ɑːrt/'],
            ['front' => 'pick', 'back' => 'انتخاب کردن', 'pronunciation' => '/pɪk/'],
            ['front' => 'windshield wiper', 'back' => 'برف پاک‌کن', 'pronunciation' => '/ˈwɪndˌʃild ˈwaɪpər/'],
            ['front' => 'information', 'back' => 'اطلاعات', 'pronunciation' => '/ˌɪn.fərˈmeɪ.ʃən/'],
            ['front' => 'foreign language', 'back' => 'زبان خارجی', 'pronunciation' => '/ˈfɔrən ˈlæŋgwəʤ/'],
            ['front' => 'take something seriously', 'back' => 'چیزی را جدی گرفتن', 'pronunciation' => '/teɪk ˈsʌmθɪŋ ˈsɪriəsli/'],
            ['front' => 'election', 'back' => 'انتخابات', 'pronunciation' => '/ɪˈlek.ʃən/'],
            ['front' => 'geography', 'back' => 'جغرافیا', 'pronunciation' => '/dʒiːˈɑg.rə.fi/'],
            ['front' => 'product', 'back' => 'محصول', 'pronunciation' => '/ˈprɑːdʌkt/'],
            ['front' => 'invitation', 'back' => 'دعوت', 'pronunciation' => '/ˌɪn.vəˈteɪ.ʃən/'],
            ['front' => 'dissatisfied', 'back' => 'ناراضی', 'pronunciation' => '/dɪˈsætəˌsfaɪd/'],
            ['front' => 'hair dryer', 'back' => 'سشوار', 'pronunciation' => '/ˈherˌ drɑɪ.ər/'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 10 vocabulary added.');
    }

    protected function seedLesson11($deck)
    {
        $vocabulary = [
            ['front' => 'wild', 'back' => 'طوفانی', 'pronunciation' => '/wɑɪld /'],
            ['front' => 'penalty', 'back' => 'پنالتی (ورزش)', 'pronunciation' => '/ˈpenəlti /'],
            ['front' => 'around', 'back' => 'دور', 'pronunciation' => '/əˈrɑʊnd /'],
            ['front' => 'coincidence', 'back' => 'اتفاق', 'pronunciation' => '/koʊˈɪn.səd.əns /'],
            ['front' => 'get off', 'back' => 'پیاده شدن (از)', 'pronunciation' => '/gɛt ɔf /'],
            ['front' => 'lane', 'back' => 'لاین (دو و میدانی، شنا و ...)', 'pronunciation' => '/leɪn /'],
            ['front' => 'track and field', 'back' => 'دو و میدانی', 'pronunciation' => '/ˌtræk ən ˈfiːld /'],
            ['front' => 'pick up', 'back' => 'بلند کردن', 'pronunciation' => '/pɪk ʌp /'],
            ['front' => 'neither', 'back' => 'همینطور', 'pronunciation' => '/ˈniː.ðər /'],
            ['front' => 'through', 'back' => 'از میان', 'pronunciation' => '/θruː /'],
            ['front' => 'volleyball', 'back' => 'والیبال', 'pronunciation' => '/ˈvɑliːbɔːl /'],
            ['front' => 'tennis', 'back' => 'تنیس', 'pronunciation' => '/ˈten.ɪs /'],
            ['front' => 'bowl', 'back' => 'کاسه', 'pronunciation' => '/boʊl /'],
            ['front' => 'boxing', 'back' => 'مشت‌زنی', 'pronunciation' => '/ˈbɑk.sɪŋ /'],
            ['front' => 'look forward to', 'back' => '(بی‌صبرانه) در انتظار (چیزی) بودن', 'pronunciation' => '/lʊk ˈfɔrwərd tu /'],
            ['front' => 'any time', 'back' => 'هر موقع', 'pronunciation' => '/ˈɛni taɪm /'],
            ['front' => 'similar', 'back' => 'مشابه', 'pronunciation' => '/ˈsɪmələr /'],
            ['front' => 'windsurfing', 'back' => 'بادسواری', 'pronunciation' => '/ˈwɪndˌsɜr.fɪŋ /'],
            ['front' => 'into', 'back' => '(به) داخل', 'pronunciation' => '/ˈɪn.tuː /'],
            ['front' => 'cycling', 'back' => 'دوچرخه‌سواری', 'pronunciation' => '/ˈsɑɪ.klɪŋ /'],
            ['front' => 'back', 'back' => 'به (سوی) عقب', 'pronunciation' => '/bæk /'],
            ['front' => 'serve', 'back' => 'سرویس زدن (ورزش)', 'pronunciation' => '/sɜːrv /'],
            ['front' => 'go away', 'back' => 'به مسافرت رفتن', 'pronunciation' => '/goʊ əˈweɪ /'],
            ['front' => 'crash', 'back' => 'تصادف کردن (اتومبیل)', 'pronunciation' => '/kræʃ /'],
            ['front' => 'toward', 'back' => 'به سوی', 'pronunciation' => '/tɔːrd /'],
            ['front' => 'get up', 'back' => 'بیدار شدن', 'pronunciation' => '/gɛt ʌp /'],
            ['front' => 'track', 'back' => 'زمین (مسابقه)', 'pronunciation' => '/træk /'],
            ['front' => 'social life', 'back' => 'روابط اجتماعی', 'pronunciation' => '/ˈsoʊʃəl laɪf /'],
            ['front' => 'hole', 'back' => 'سوراخ (بازی گلف)', 'pronunciation' => '/hoʊl /'],
            ['front' => 'get along with', 'back' => 'کنار آمدن', 'pronunciation' => '/gɛt əˈlɔŋ wɪð /'],
            ['front' => 'lap', 'back' => 'دور (دو و میدانی و ...)', 'pronunciation' => '/læp /'],
            ['front' => 'under', 'back' => 'زیر', 'pronunciation' => '/ˈʌndər /'],
            ['front' => 'look up', 'back' => 'جستجو کردن', 'pronunciation' => '/lʊk ʌp /'],
            ['front' => 'go out', 'back' => 'بیرون رفتن', 'pronunciation' => '/goʊ ɑʊt /'],
            ['front' => 'fill out', 'back' => 'تکمیل کردن', 'pronunciation' => '/fɪl aʊt /'],
            ['front' => 'along', 'back' => 'در امتداد', 'pronunciation' => '/əˈlɑŋ /'],
            ['front' => 'stand up', 'back' => 'بلند شدن', 'pronunciation' => '/stænd ʌp /'],
            ['front' => 'loser', 'back' => 'بازنده', 'pronunciation' => '/luzər /'],
            ['front' => 'buzz', 'back' => 'صدای زنگ', 'pronunciation' => '/bʌz /'],
            ['front' => 'so', 'back' => 'همین‌طور', 'pronunciation' => '/soʊ /'],
            ['front' => 'set', 'back' => 'کوک کردن', 'pronunciation' => '/set /'],
            ['front' => 'sit down', 'back' => 'نشستن', 'pronunciation' => '/sɪt daʊn /'],
            ['front' => 'over', 'back' => 'تمام', 'pronunciation' => '/ˈoʊ.vər /'],
            ['front' => 'down', 'back' => 'پایین', 'pronunciation' => '/dɑʊn /'],
            ['front' => 'across', 'back' => 'آن طرف', 'pronunciation' => '/əˈkrɑs /'],
            ['front' => 'try on', 'back' => 'پوشیدن', 'pronunciation' => '/traɪ ɑn /'],
            ['front' => 'golf', 'back' => 'گلف', 'pronunciation' => '/ɡɑːlf /'],
            ['front' => 'bar', 'back' => 'تیر دروازه', 'pronunciation' => '/bɑːr /'],
            ['front' => 'give up', 'back' => 'ترک کردن', 'pronunciation' => '/gɪv ʌp /'],
            ['front' => 'give back', 'back' => 'پس دادن', 'pronunciation' => '/gɪv bæk /'],
            ['front' => 'taste', 'back' => 'سلیقه', 'pronunciation' => '/teɪst /'],
            ['front' => 'out of', 'back' => 'خارج از', 'pronunciation' => '/ˈɑʊt̬.əv /'],
            ['front' => 'handball', 'back' => 'هندبال', 'pronunciation' => '/ˈhænd.bɑl /'],
            ['front' => 'take back', 'back' => 'پس دادن (جنس خریداری‌شده)', 'pronunciation' => '/teɪk bæk /'],
            ['front' => 'find out', 'back' => 'فهمیدن', 'pronunciation' => '/faɪnd aʊt /'],
            ['front' => 'sport', 'back' => 'ورزش', 'pronunciation' => '/spɔːrt /'],
            ['front' => 'as', 'back' => 'به اندازه', 'pronunciation' => '/æz /'],
            ['front' => 'hockey', 'back' => 'هاکی (ورزش)', 'pronunciation' => '/ˈhɑki /'],
            ['front' => 'bunker', 'back' => 'قسمت شنی زمین گلف', 'pronunciation' => '/ˈbʌŋkər /'],
            ['front' => 'throw away', 'back' => 'دور انداختن', 'pronunciation' => '/θroʊ əˈweɪ /'],
            ['front' => 'match point', 'back' => 'امتیاز نهایی (تنیس)', 'pronunciation' => '/ˌmætʃ ˈpɔɪnt /'],
            ['front' => 'energetic', 'back' => 'پرانرژی', 'pronunciation' => '/ˌɛnərˈʤɛtɪk /'],
            ['front' => 'put on', 'back' => 'پوشیدن', 'pronunciation' => '/pʊt ɑn /'],
            ['front' => 'umpire', 'back' => 'داور', 'pronunciation' => '/ˈʌmpaɪər /'],
            ['front' => 'rugby', 'back' => 'راگبی (ورزش)', 'pronunciation' => '/ˈrʌg.bi /'],
            ['front' => 'disqualify', 'back' => 'سلب صلاحیت کردن', 'pronunciation' => '/dɪˈskwɑləˌfaɪ /'],
            ['front' => 'live', 'back' => 'زنده', 'pronunciation' => '/lɪv /'],
            ['front' => 'soccer', 'back' => 'فوتبال', 'pronunciation' => '/ˈsɑːkər /'],
            ['front' => 'call back', 'back' => '(در جواب تماس ازدست‌رفته کسی) با کسی تماس گرفتن', 'pronunciation' => '/kɔl bæk /'],
            ['front' => 'take off', 'back' => 'درآوردن (لباس، کفش و...)', 'pronunciation' => '/teɪk ɔf /'],
            ['front' => 'over', 'back' => 'روی', 'pronunciation' => '/ˈoʊ.vər /'],
            ['front' => 'identical twins', 'back' => 'دوقلوهای همسان', 'pronunciation' => '/aɪˈdɛntɪkəl twɪnz /'],
            ['front' => 'coach', 'back' => 'مربی', 'pronunciation' => '/koʊtʃ /'],
            ['front' => 'skiing', 'back' => 'اسکی', 'pronunciation' => '/ˈskiː.ɪŋ /'],
            ['front' => 'auto racing', 'back' => 'اتومبیل‌رانی', 'pronunciation' => '/ˈɔːtoʊ reɪsɪŋ /'],
            ['front' => 'soccer club', 'back' => 'باشگاه ورزشی', 'pronunciation' => '/ˈsɑkər klʌb /'],
            ['front' => 'pay back', 'back' => 'پس دادن (پول)', 'pronunciation' => '/peɪ bæk /'],
            ['front' => 'look after', 'back' => 'مراقب بودن', 'pronunciation' => '/lʊk ˈæftər /'],
            ['front' => 'sleepy', 'back' => 'خواب‌آلود', 'pronunciation' => '/ˈsliːp.i /'],
            ['front' => 'up', 'back' => 'بالا', 'pronunciation' => '/ʌp /'],
            ['front' => 'race', 'back' => 'مسابقه', 'pronunciation' => '/reɪs /'],
            ['front' => 'write down', 'back' => 'یادداشت کردن', 'pronunciation' => '/raɪt daʊn /'],
            ['front' => 'match', 'back' => 'مسابقه', 'pronunciation' => '/mætʃ /'],
            ['front' => 'security guard', 'back' => 'حراست', 'pronunciation' => '/sɪˈkjʊrəti gɑrd /'],
            ['front' => 'turn down', 'back' => 'کم کردن', 'pronunciation' => '/tɜrn daʊn /'],
            ['front' => 'baseball', 'back' => 'بیسبال', 'pronunciation' => '/ˈbeɪsbɔːl /'],
            ['front' => 'put away', 'back' => 'چیزی را سر جایش گذاشتن', 'pronunciation' => '/pʊt əˈweɪ /'],
            ['front' => 'both', 'back' => 'هر دو', 'pronunciation' => '/boʊθ /'],
            ['front' => 'beat', 'back' => 'شکست دادن', 'pronunciation' => '/biːt /'],
            ['front' => 'corner', 'back' => 'کرنر (فوتبال و هاکی)', 'pronunciation' => '/ˈkɔːr.nər /'],
            ['front' => 'turn up', 'back' => 'زیاد کردن', 'pronunciation' => '/tɜrn ʌp /'],
            ['front' => 'away', 'back' => 'دور', 'pronunciation' => '/əˈweɪ /'],
            ['front' => 'score', 'back' => 'امتیاز کسب کردن', 'pronunciation' => '/skɔːr /'],
            ['front' => 'basketball', 'back' => 'بسکتبال', 'pronunciation' => '/ˈbɑːskɪtbɔːl /'],
            ['front' => 'identical', 'back' => 'همانند', 'pronunciation' => '/ɑɪˈdent.ɪ.kəl /'],
            ['front' => 'medal', 'back' => 'مدال', 'pronunciation' => '/ˈmed.əl /'],
            ['front' => 'goal', 'back' => 'گل', 'pronunciation' => '/goʊl /'],
            ['front' => 'past', 'back' => 'آن طرف', 'pronunciation' => '/pæst /'],
            ['front' => 'look for', 'back' => '(به) دنبال گشتن', 'pronunciation' => '/lʊk fɔr /'],
            ['front' => 'adopt', 'back' => 'به فرزندخواندگی قبول کردن', 'pronunciation' => '/əˈdɑːpt /'],
            ['front' => 'referee', 'back' => 'داور', 'pronunciation' => '/ˌref.əˈriː /'],
            ['front' => 'like', 'back' => 'شبیه', 'pronunciation' => '/lɑɪk /'],
            ['front' => 'in', 'back' => 'به داخل', 'pronunciation' => '/ɪn /'],
            ['front' => 'get on', 'back' => 'سوار شدن', 'pronunciation' => '/gɛt ɑn /'],
            ['front' => 'tights', 'back' => 'جوراب‌شلواری', 'pronunciation' => '/taɪts /'],
            ['front' => 'come on', 'back' => 'بی‌خیال!', 'pronunciation' => '/kʌm ɑn /'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 11 vocabulary added.');
    }

    protected function seedLesson12($deck)
    {
        $vocabulary = [
            ['front' => 'rob', 'back' => 'دزدی کردن', 'pronunciation' => '/rɑːb /'],
            ['front' => 'pass on', 'back' => 'رساندن (به‌دست کسی)', 'pronunciation' => '/pæs ɑn /'],
            ['front' => 'in general', 'back' => 'معمولا', 'pronunciation' => '/ɪn ˈʤɛnərəl /'],
            ['front' => 'arrest', 'back' => 'دستگیر کردن', 'pronunciation' => '/əˈrest /'],
            ['front' => 'gene', 'back' => 'ژن (زیست‌شناسی)', 'pronunciation' => '/ʤin /'],
            ['front' => 'gossip', 'back' => 'شایعه', 'pronunciation' => '/ˈgɑs.əp /'],
            ['front' => 'belong', 'back' => 'متعلق بودن', 'pronunciation' => '/bəˈlɔːŋ /'],
            ['front' => 'according to', 'back' => 'طبق', 'pronunciation' => '/əˈkɔːrd.ɪŋ.tuː /'],
            ['front' => 'feel guilty', 'back' => 'عذاب وجدان داشتن', 'pronunciation' => '/fil ˈgɪlti /'],
            ['front' => 'realize', 'back' => 'فهمیدن', 'pronunciation' => '/ˈrɪə.laɪz /'],
            ['front' => 'steal', 'back' => 'دزدیدن', 'pronunciation' => '/stiːl /'],
            ['front' => 'share', 'back' => 'سهیم بودن', 'pronunciation' => '/ʃer /'],
            ['front' => 'social', 'back' => 'اجتماعی', 'pronunciation' => '/ˈsoʊ.ʃəl /'],
            ['front' => 'skill', 'back' => 'مهارت', 'pronunciation' => '/skɪl /'],
            ['front' => 'outdoor', 'back' => 'بیرون', 'pronunciation' => '/ˌɑʊtˈdɔːr /'],
            ['front' => 'say', 'back' => 'گفتن', 'pronunciation' => '/seɪ /'],
            ['front' => 'tell', 'back' => 'گفتن', 'pronunciation' => '/tel /'],
            ['front' => 'fortunately', 'back' => 'خوشبختانه', 'pronunciation' => '/ˈfɔːr.tʃə.nət.li /'],
            ['front' => 'net', 'back' => 'تور', 'pronunciation' => '/nɛt /'],
        ];

        foreach ($vocabulary as $item) {
            $deck->vocabulary()->create([
                'front' => $item['front'],
                'back' => $item['back'],
                'pronunciation' => $item['pronunciation'],
            ]);
        }

        $this->command->info('Lesson 12 vocabulary added.');
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
