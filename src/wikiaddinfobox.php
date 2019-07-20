<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 3/1/2019
 * Time: 12:21 AM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';


$bot_link = array(
//	0 => array('NothingPostBot 0000', 'https://www.facebook.com/nothingpostbot/'),
//	1 => array('Namebot 1372', 'https://www.facebook.com/Namebot1372/'),
//	2 => array('MusictakesBot 433', 'https://www.facebook.com/MusicalTakesBot/'),
	3 => array('MusicalTakesBot', 'DELETED'),
	4 => array('MillennialBot 2019', 'https://www.facebook.com/MillenialsAreRuiningBots/'),
	5 => array('MathsBot 271828', 'https://www.facebook.com/mathsbot271828/'),
	6 => array('LossBot 12250', 'https://www.facebook.com/lossbot12250/'),
	7 => array('JokeBot7490', 'https://www.facebook.com/JokeBot7490-281788746015448/'),
	8 => array('JojoPostBot 5000', 'https://www.facebook.com/jojopostbot/'),
	9 => array('JimmyBot7872', 'https://www.facebook.com/jimmybot7872/'),
	10 => array('Jewishnamebot5779', 'https://www.facebook.com/BatshevaEhrman/'),
	11 => array('JermaBot 5985', 'https://www.facebook.com/jermabot5985/'),
	12 => array('InspiroBot Quotes', 'http://inspirobot.me/https://www.facebook.com/InspiroBotQuotesIGot/'),
	13 => array('ImposterBot Pmc2963468', 'https://www.facebook.com/imposterbot/'),
	14 => array('IdeaBot 5200', 'https://www.facebook.com/IdeaBot5200/'),
	15 => array('HoroscopeBot 12', 'https://www.facebook.com/horoscopebot12.0/'),
	16 => array('HankBot0419307', 'https://www.facebook.com/hankbot0419307/'),
	17 => array('GreentextBot', 'https://www.facebook.com/GreentextBot/'),
	18 => array('FunnyPost Bot 5000', 'https://www.facebook.com/funnypostbot/'),
	19 => array('Flexbot 2954', 'DELETED'),
	20 => array('FlameFoldBot 303', 'Merged into PerlinFieldBot 4150'),
	21 => array('Failarmybot 0000', 'https://www.facebook.com/randomstillfromrandomfailarmyvideosbot/'),
	22 => array('FactpostBot4286', 'https://www.facebook.com/FactpostMarkov/'),
	23 => array('Face Generation Bot 1955', 'https://www.facebook.com/facegenbot/'),
	24 => array('FMKbot', 'https://www.facebook.com/fmkbot/'),
	25 => array('Encarta95PostBot', 'https://www.facebook.com/encarta95postbot/'),
	26 => array('EmojiBot 101', 'https://www.facebook.com/emojibot101/?ref=br_rs'),
	27 => array('ElementBot 9654', 'https://www.facebook.com/elementbot9654/'),
	28 => array('DogpostBot', 'https://www.facebook.com/DogpostBot/'),
	29 => array('DogePost Bot 1619', 'https://www.facebook.com/DogePost-Bot-1619-448767758992760/'),
	30 => array('Diseasepostbot 1665', 'https://www.facebook.com/Diseasepostbot-1665-167522694156709/'),
	31 => array('DictionaryPostbot', 'https://www.facebook.com/dictionarypostbot/'),
	32 => array('DeathgripsBot 5000', 'https://www.facebook.com/deathgripsbot/'),
	33 => array('DadsGoοgləHistoryBot 3000', 'https://www.facebook.com/dghb3000/'),
	34 => array('CyanideBot69', 'https://www.facebook.com/cyanidebot69/'),
	35 => array('Creepypasta bot', 'https://www.facebook.com/creepypastabot/'),
	36 => array('CraftingBot 64', 'https://www.facebook.com/CraftingBot64/'),
	37 => array('CowManglerBot 5000', 'https://www.facebook.com/tf2bot/'),
	38 => array('CountryBot 0208', 'https://www.facebook.com/countrybot208/'),
	39 => array('Content Aware Bot', 'https://www.facebook.com/contentawarebot/'),
	40 => array('CommentBot 2005', 'https://www.facebook.com/commentbot2005/'),
	41 => array('Colormachine', 'https://www.facebook.com/colormachine/'),
	42 => array('ChristianMom Bot 1542', 'https://www.facebook.com/ChristianMomBot1542/'),
	43 => array('ChessBot 1951', 'https://www.facebook.com/chessbot1951/'),
	44 => array('ChefBot1949', 'https://www.facebook.com/ChefBot1949-596958330739456/'),
	45 => array('CensorBot 1111', 'https://www.facebook.com/CensorBot-1111-227202038206959/'),
	46 => array('BuzzfeedQuiz Bot 2006', 'https://www.facebook.com/BuzzfeedBot2006/'),
	47 => array('BottomText 5000 v2.0', 'https://www.facebook.com/BOTtomtext5k/?ref=br_rs'),
	48 => array('BottomText 5000', 'https://www.facebook.com/bottomtext5000/'),
	49 => array('BotoB 8008', 'https://www.facebook.com/botob8008/?ref=br_rs'),
	50 => array('Botbot 1000', 'https://www.facebook.com/botbot1k/'),
	51 => array('BiblepostBot 1111', 'https://www.facebook.com/BiblepostBot-1111-2017448901882151/'),
	52 => array('Ben Garrison Bot 1980', 'https://www.facebook.com/RealBenGarrison/'),
	53 => array('BartenderBot 1862', 'https://www.facebook.com/bartenderbot1862/'),
	54 => array('BabelBot 5000', 'https://www.facebook.com/babelbot5000/'),
	55 => array('BASR-FM 168.1', 'https://www.facebook.com/BASRFM1681/'),
	56 => array('AlbumBot 808', 'https://www.facebook.com/AlbumBot808/'),
	57 => array('A random texture from terraria until all have been posted and then i die', 'DELETED'),
);

$dt = new BotStatusBot\DataLogger();

$WH = new BotStatusBot\WikiaHelper();
$WH->init($_LGTOKEN, $_LGNAME, $_LGPASSWORD);

$WH->logIn();

foreach ($bot_link as $key => $entry) {

    $botname = $entry[0];

    $dt->logdata($botname);

    $image = $entry[0].'.png';
    $page_link = $entry[1];

    $extra_data = $WH->getExtraData($botname);

    $botdata = array(
        'botname' => $botname,
        'description' => '',
        'status' => $extra_data['status'],
        'facebook_url' => $page_link,
        'image' => $image,
        'imagecaption' => '',
        'botmins' => '',
        'type' => $extra_data['type'],
        'creation' => ''
    );

    if ($page_link == 'DELETED') {
        $botdata['status'] = 'Dead';
    } else {
        $botdata['status'] = 'Active';
    }

    $built_data = $WH->buildDataForArticle($botdata, 'bot');
//    if($key > 2) {
//        return;
//    }

    // Sleep every 10 bots for good measure
    if($key % 10 == 0) {
        sleep(3);
    }

        $success = $WH->automatedBotArticle($built_data, TRUE, FALSE);
    if ($success) {
        $dt->logdata($botname.' infobox added');
    } else {
        $dt->logdata($botname.' infobox addition failed');
    }

}
