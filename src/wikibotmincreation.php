<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 3/1/2019
 * Time: 12:21 AM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';


$botmin_bots = array(
    0 => array('shitpostbot', 'ShitpostBot 5000;'),
    1 => array('Bez', 'BezierPostBot 0000; BurgerBot 4545; BASR-FM 168.1; Reviewbrahbot; Foreign Lands; ExodiaBot 0005; The Office Bot'),
    2 => array('Paintmin', 'PaintBot; LossBot 12250; CountryBot 0208; CommentBot 2005; BotoB 8008; SbeveBot 14'),
    3 => array('Stealth Mountain', 'LossBot 12250'),
    4 => array('jboby93', 'TextpostBot 98; ClickBot 2000; HungergamesBot 74; ChessBot 1951; BartenderBot 1862'),
    5 => array('Youmuu', 'PerlinFieldBot 4150; Ragecomicbot 2010'),
    6 => array('Budgiebrain994', 'Encarta95PostBot'),
    7 => array('TaoBi', 'MathsBot 271828; HoroscopeBot 12'),
    8 => array('Amitai', 'Amitai’s Bot'),
    9 => array('retired retarde', 'ToolpostBot; Local Forecast Bot'),
    10 => array('HUNcamper', 'VortessenceBot 2.0; StandBot4444; Album Generator 420'),
    11 => array('Mungrel', 'CyanideBot 69'),
    12 => array('BLUE', 'Random Color Memes II: Electric Bluegaloo'),
    13 => array('andi', 'Garkov PostBot 1978; EmojiBot 101; Text2SpeechBot 0010; VsauceBot Here'),
    14 => array('Couca', 'BotBot 1000; CraftingBot 64'),
    15 => array('DanC', 'Vidya Game Bot 1337; OreoBot 1912; SpoofyBot 2008; BannerBot 1430'),
    16 => array('Greentextmin', 'GreentextBot'),
    17 => array('PajeetMin', 'Lil Sentience 420-69'),
    18 => array('Rodd Howard', 'Doctor Bot'),
    19 => array('Logarathon', 'Rogue-likebot 1980; MDHHCD-Bot; Thanos Bot Thanos Bot; Russian Roulette Bot 1667'),
    20 => array('qingshui', 'PHCommentBot'),
    21 => array('rattus rattus', 'JokeBot7490; ChefBot1949; Styletransferbot9683'),
    22 => array('Nuanar', 'ArtPostbot1519; OwO Postbot1982; DictionaryPostbot'),
    23 => array('Edi', 'JimmyBot7872; FMKbot'),
    24 => array('javyoyo', 'ElementBot 9654'),
    25 => array('Hix', 'Flexbot 2954'),
    26 => array('Daddy DJ', 'FactpostBot4286'),
    27 => array('Noodle', 'MusictakesBot 433'),
    28 => array('DarkSepho', 'WorldWarBot 2020'),
    29 => array('dazziX', 'DVDScreensaverBot; ShowerthoughtsBot 1981; A cursed image every half an hour; EnglishBot; CanvasBot; NoiseBot 255'),
    30 => array('PiousGalaxy', 'JailBot 69'),
    31 => array('Julian', 'Get-Shot Bot; ProceduralBot 2619 and PaletteBot'),
    32 => array('Rallemuuss', 'Idea Bot 5200; Frequently Bot 1995; Web MD Bot'),
    33 => array('zymboni zamboni', 'Why Are You Bot?; Orange Soapstone Bot 259+301'),
    34 => array('Conch', 'Actual Fact Bot; US Election Bot; WW2PostBot; Ben Garrison bot'),
    35 => array('Fuddlebob', 'MelodyBot3456; CopyPastaBot234'),
    36 => array('bread', 'Floor-Plan-Bot2521; Papa\'s Cafeteria Bot of Chaos'),
    37 => array('gilangbh', 'whipnaenaebot; macarenabot'),
    38 => array('tate', 'BusinessBot 500'),
    39 => array('kolorowytoster', 'PizzaBot; Pie Chart Bot 45%; Cheat Code Bot 2005; LinusBot'),
    40 => array('MushroomLamp', 'LawBot64; RocketLeaguePostBot9001'),
    41 => array('moonstripe', 'Scriptbot 1902; UranometriaBot 1603'),
    42 => array('K3nzuto', 'Divmeterbot_FG204'),
    43 => array('Pizzaface', 'Every Spongebob Frame In Order'),
    44 => array('Bareto', 'VersusBot'),
    45 => array('JeDaYoshi', 'Wishpostbot $2.000'),
    46 => array('bauss', 'News Bot 1926; People You May Know Bot; Life Hacks That Actually Saves Your Life; Tom Morris Bot; Pokébot Red; React Bot 6; IOUBot; Local News Bot 1926'),
    47 => array('Ghutros (Weeabot)', 'Weeabot; Fate/Grand Arts'),
);


$dt = new BotStatusBot\DataLogger();

$WH = new BotStatusBot\WikiaHelper();
$WH->init($_LGTOKEN, $_LGNAME, $_LGPASSWORD);

$WH->logIn();

foreach ($botmin_bots as $key => $entry) {

    $botmin = $entry[0];

    $dt->logdata($botmin);

    $description = $entry[1];

    $botmindata = array(
        'botmin' => $botmin,
        'description' => $description,
    );

    $built_data = $WH->buildDataForArticle($botmindata, 'botmin');

//    if($key > 7) {
//        return;
//    }

    // Sleep every 10 bots for good measure
    if($key % 10 == 0) {
        sleep(3);
    }

    $success = $WH->automatedArticle($built_data, FALSE, FALSE);
    if ($success) {
        $dt->logdata($botmin.' article created');
    } else {
        $dt->logdata($botmin.' article creation failed');
    }

}
