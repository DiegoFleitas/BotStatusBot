<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 7/17/2019
 * Time: 6:38 PM
 */

namespace BotStatusBot;


class WikiaHelper extends DataLogger
{

    private $CREDENTIALS = [];
    private $RETRY = 1;

    public function init($_LGTOKEN, $_LGNAME, $_LGPASSWORD)
    {
        $this->CREDENTIALS = array (
            'lgtoken' => $_LGTOKEN,
            'lgname' => $_LGNAME,
            'lgpassword' => $_LGPASSWORD,
            'cookies' => '',
            'edittoken' => ''
        );
    }

    public function logIn($cookies = '') {

        $logged_as = $this->checkLogin($cookies);
        if ($logged_as !== $this->CREDENTIALS['lgname']) {
            if (!empty($logged_as)) {
                $this->logdata('logged as '.$logged_as, 0);
            }
            $this->logdata('loggin failed', 1);
        } else {
            $this->CREDENTIALS['cookies'] = $cookies;
            return;
        }

        $curl = curl_init();

        $endpoint = "https://botappreciationsociety.fandom.com/api.php?action=login";
        $lgtoken = $this->CREDENTIALS['lgtoken'];
        $lgname = $this->CREDENTIALS['lgname'];
        $lgpassword = $this->CREDENTIALS['lgpassword'];

        $append_token = '';
        $bot_useragent = 'BASBOT/0.0 (https://botappreciationsociety.fandom.com/BASBOT/; diegoflekap2@gmail.com) UsedBaseLibrary/0.0';
        if (!empty($cookies)) {
            $append_token = "&lgtoken=".$lgtoken;
            $headers = array(
                "Accept: */*",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Host: botappreciationsociety.fandom.com",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
                "cookie: ".$cookies,
                "User-Agent: ".$bot_useragent
            );
        } else {
            $headers = array(
                "Accept: */*",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Host: botappreciationsociety.fandom.com",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
                "User-Agent: ".$bot_useragent
            );
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint."&lgname=".$lgname."&lgpassword=".$lgpassword."&format=json".$append_token,
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $request_headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  request headers: '.$request_headers.'  url: '.$endpoint.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  request headers: '.$request_headers.'  url: '.$endpoint.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            // Do thing
            $message =  $endpoint.' response_headers: '.$response_header;
            $this->logdata($message, 0);
            $json = json_decode($response_body, true);
            $cookies_new = $this->getCookiesFromHeader($response_header);
            if (!empty($json)) {
                try {
                    $lgtoken_new = $json['login']['token'];
                    if (!empty($lgtoken_new)) {
                        $this->CREDENTIALS['lgtoken'] = $lgtoken_new;
                    }
                    if ($this->RETRY > 0) {
                        $this->logdata('cookies aquired, retrying login', 0);
                        $this->RETRY = 0;
                        if (!empty($cookies_new)) {
                            $this->logIn($cookies_new);
                        } else {
                            $this->logdata('no cookies found to re attempt login', 1);
                        }
                    }
                } catch (Exception $e) {
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$e->getMessage(), 1);
                }
            }
        }
    }

    public function checkLogin() {
        $curl = curl_init();

        $cookie_data = $this->getDefaultCookies();

        $bot_useragent = 'BASBOT/0.0 (https://botappreciationsociety.fandom.com/BASBOT/; diegoflekap2@gmail.com) UsedBaseLibrary/0.0';

        $endpoint = "https://botappreciationsociety.fandom.com/api.php?action=query";

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint."&prop=info&format=json&meta=userinfo",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Host: botappreciationsociety.fandom.com",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
                "User-Agent: ".$bot_useragent,
                "cookie: ".$cookie_data
            ),
        ));

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            $json = json_decode($response_body, true);
            if (!empty($json)) {
                try {
                    // Do thing
//                    $message =  $endpoint.' response_headers: '.$response_header;
//                    $this->logdata($message, 0);
                    return $json['query']['userinfo']['name'];
                } catch (Exception $e) {
                    return '';
                }
            }
        }
    }

    public function getEditToken() {
        $curl = curl_init();

        $endpoint = "https://botappreciationsociety.fandom.com/api.php?action=query&prop=info";
        $bot_useragent = 'BASBOT/0.0 (https://botappreciationsociety.fandom.com/BASBOT/; diegoflekap2@gmail.com) UsedBaseLibrary/0.0';

        $cookie_data = $this->getDefaultCookies();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint."&titles=Main%20Page&intoken=edit&format=json",
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Host: botappreciationsociety.fandom.com",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
                "cookie: ".$cookie_data,
                "User-Agent: ".$bot_useragent,
            ),
        ));

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            // Do thing
//            $message =  $endpoint.' response_headers: '.$response_header;
//            $this->logdata($message, 0);
            $json = json_decode($response_body, true);
            if (!empty($json)) {
                try {
                    $token = $json['query']['pages']['81']['edittoken'];
                    return str_replace('+\\', '%2B%5C', $token);
                } catch (Exception $e) {
                    return '';
                }
            }
        }
    }

    public function getExtraData($botname) {

//        $string = file_get_contents(__DIR__.'\..\src\resources\bots.json');
        $string = file_get_contents('C:\Users\Diego\PhpstormProjects\BotStatusBot\src\resources\bots.json');
        $json = json_decode($string, true);

        $tags = null;
        foreach ($json as $entry) {
            if (isset($entry['title']) && $entry['title'] == $botname) {
                $tags = $entry['tags'];
            }
        }

        if ($tags == null) {
            $message = 'No extra bot data found';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 0);
            return [
                'type'  => '',
                'status'   => ''
            ];
        }

        $type = 'Text Bot';
        if (in_array('Video', $tags)) {
            $type = 'Video Bot';
        } elseif (in_array('Image', $tags)) {
            $type = 'Image Bots';
        }

        $status = 'Active';
        if (in_array('Dead', $tags)) {
            $status = 'Dead';
        }

        return [
            'type'  => $type,
            'status'   => $status
        ];

    }

    public function buildDataForArticle($data, $type) {

        if ($type == 'bot') {
            $aux_botminlink = '';
            if (!empty($data['botmins'])) {
                $aux_botminlink = '[['.$data['botmins'].']]';
            }
            $bot_description = '';
            if ($data['description'] !== '#N/A') {
                $bot_description = $data['description'];
            }

            $bot_infobox = '{{Bot_Infobox_Template '.
            '|botname = '.$data['botname'].
            '|status = '.$data['status'].
            '|facebook_url = ['.$data['facebook_url'].' '.$data['botname'].']'.
            '|image = '.$data['image'].
            '|imagecaption = '.$data['imagecaption'].
            '|botmins = '.$aux_botminlink.
            '|type = '.$data['type'].
            '|creation = '.$data['creation'].
            '}}';

            $categorization = '[[Category:Facebook Bots]]';

            switch ($data['type']) {
                case 'Text Bot':
                    $categorization .= '[[Category:Text Bots]]';
                    break;
                case 'Video Bot':
                    $categorization .= '[[Category:Video Bots]]';
                    break;
                case 'Image Bots':
                    $categorization .= '[[Category:Image Bots]]';
                    break;
            }
            if ($data['status'] == 'Dead') {
                $categorization .= '[[Category:Dead Bots]]';
            }

            $infobox = urlencode($bot_infobox);
            $footer = urlencode('{{Navbox/Bots}}'.$categorization);
            $name = urlencode($data['botname']);
            $description = urlencode($bot_description);

        } elseif ($type == 'botmin') {

            $bots = explode(';', $data['description']);
            $desc = $data['botmin'].' is one of the Bot Appreciation Society\'s many bot admins and the creator of ';
            foreach ($bots as $key => $bot) {
                if ($key == 0) {
                    $desc .= '[['.$bot.']]';
                } elseif ($key == count($bots) - 1) {
                    $desc .= ' and [['.$bot.']]';
                } else {
                    $desc .= ' ,[['.$bot.']]';
                }
            }

            $botmin_infobox = '{{Infobox_character|name = '.$data['botmin'].
            '|image = '.$data['botmin'].'.png'.
            '|imagecaption = '.$data['botmin'].' current Discord avatar'.
            '|aliases = '.
            '|affiliation = The Bot Appreciation Society'.
            '|gender = '.
            '}}';

            $categorization = '[[Category:Bot Admins]]';

            $infobox = urlencode($botmin_infobox);
            $footer = urlencode('{{Navbox/Bot Admins}}'.$categorization);
            $name = urlencode($data['botmin']);
            $description = urlencode($desc);
        }


        return array (
            'infobox' => $infobox,
            'name' => $name,
            'description' => $description,
            'footer' => $footer
        );
    }

    public function automatedArticle($data_article, $force_creation = false, $clean_article = false) {

        $cookie_data = $this->getDefaultCookies();
        $edit_token = $this->getEditToken();
        if (!empty($edit_token)) {
            $this->CREDENTIALS['edittoken'] = $edit_token;
        }

        $endpoint = 'https://botappreciationsociety.fandom.com/api.php?action=edit';
        $summary = 'infobox%20by%20bot';

        $bot_useragent = 'BASBOT/0.0 (https://botappreciationsociety.fandom.com/BASBOT/; diegoflekap2@gmail.com) UsedBaseLibrary/0.0';

        $infobox = $data_article['infobox'];
        $footer = $data_article['footer'];
        $page_name = $data_article['name'];
        $description = $data_article['description'];

        $article_text = '';
        // This without append nor prepend will override the content if the article already exists
        if (!empty($description)) {
            $article_text = '&text='.$description;
        }
        $prependtext = '';
        if (!empty($infobox)) {
            $prependtext = "&prependtext=".$infobox;
        }
        $appendtext = '';
        if (!empty($infobox)) {
            $appendtext = "&appendtext=".$footer;
        }

        if ($clean_article) {
            $message = 'cleaning '.$page_name.' ...';
            $this->logdata($message, 0);
            $article_text = '&text=%20';
            $prependtext = '';
            $appendtext = '';
        }

        // both prependtext and appendtext override text
        if (!empty($appendtext)) {
            $appendtext = $description.$appendtext;
            $article_text = '';
        } elseif (!empty($prependtext)) {
            $prependtext .= $description;
            $article_text = '';
        }

        $createonly = '&createonly=true';
        if ($force_creation) {
            $message = 'forcing creation '.$page_name.' ...';
            $this->logdata($message, 0);
            $createonly = '';
        }

        $curl = curl_init();

        $url = $endpoint."&title=".$page_name."&summary=".$summary."".$article_text.$prependtext.$appendtext."&bot=true".$createonly."&format=json&token=".$edit_token;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array(
                "Host: botappreciationsociety.fandom.com",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
                "Accept: application/json",
                "Accept-Language: en-US,en;q=0.5",
                "Connection: keep-alive",
                "Content-Type: application/x-www-form-urlencoded",
                "cache-control: no-cache",
                "cookie: ".$cookie_data,
                "User-Agent: ".$bot_useragent
            ),
        ));

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            // Do thing
//            $message =  $endpoint.' response_headers: '.$response_header;
//            $this->logdata($message, 0);
            $json = json_decode($response_body, true);
            if (!empty($json)) {
                try {
                    if (isset($json['edit']['result']) && $json['edit']['result'] == 'Success') {
                        return true;
                    }
                    if (isset($json['error'])) {
                        $message = $json['error']['code'].' - '.$json['error']['info'];
                    } else {
                        $message = $response_body;
                    }
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 0);
                    return false;
                } catch (Exception $e) {
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$e->getMessage(), 1);
                }
            }
        }

    }

    public function getCookiesFromHeader($header) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        // parse cookies
        $all_cookies = '';
        foreach ($cookies as $cname => $cvalue) {
            $all_cookies .= $cname.'='.$cvalue.'; ';
        }
        return $all_cookies;
    }

    public function getDefaultCookies() {
        // $cookie_data = "wikia_beacon_id=loN-N3Djts; wikicities_session=0c033d2602b0eaa9a94ccdc751cae30f; wikicitiesUserID=40175463; wikicitiesUserName=BASBOT; wikicitiesToken=434c4e44d0c460138eec0af4828fb354; access_token=YTI5ZWJjOTUtZWQ3YS00M2U5LWEyZjEtYTE0NmFjOGY1MzY0; wikia_session_id=rDNyWzrWZQ; Geo={%22region%22:%22MO%22%2C%22country%22:%22UY%22%2C%22continent%22:%22SA%22}";
        $region = urlencode('"region":"MO","country":"UY","continent":"SA"');
        $beacon_id = 'loN-N3Djts';
        $session = '0c033d2602b0eaa9a94ccdc751cae30f';
        $userID = '40175463';
        $UserName = 'BASBOT';
        $Token = '434c4e44d0c460138eec0af4828fb354';
        $access_token = 'YTI5ZWJjOTUtZWQ3YS00M2U5LWEyZjEtYTE0NmFjOGY1MzY0';
        $session_id = 'rDNyWzrWZQ';
        $cookie_array = array(
            'wikia_beacon_id='.$beacon_id,
            'wikicities_session='.$session,
            'wikicitiesUserID='.$userID,
            'wikicitiesUserName='.$UserName,
            'wikicitiesToken='.$Token,
            'access_token='.$access_token,
            'wikia_session_id='.$session_id,
            'Geo={'.$region.'}'
        );
        return implode('; ', $cookie_array);
    }

    public function classifyWikiImage($image, $tags) {

        $cookie_data = $this->getDefaultCookies();
        $edit_token = $this->getEditToken();
        if (!empty($edit_token)) {
            $this->CREDENTIALS['edittoken'] = $edit_token;
        }

        $endpoint = 'https://botappreciationsociety.fandom.com/api.php?action=edit';
        $summary = 'classifying%20bot%20images';
        $page_name = urlencode($image);

        $appendtext = '';
        if (!empty($tags)) {
            $appendtext = "&appendtext=".urlencode($tags);
        }
        $url = $endpoint."&title=".$page_name."&summary=".$summary."".$appendtext."&bot=true&format=json&token=".$edit_token;

        $curl = curl_init();
        $bot_useragent = 'BASBOT/0.0 (https://botappreciationsociety.fandom.com/BASBOT/; diegoflekap2@gmail.com) UsedBaseLibrary/0.0';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array(
                "Host: botappreciationsociety.fandom.com",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
                "Accept: application/json",
                "Accept-Language: en-US,en;q=0.5",
                "Connection: keep-alive",
                "Content-Type: application/x-www-form-urlencoded",
                "cache-control: no-cache",
                "cookie: ".$cookie_data,
                "User-Agent: ".$bot_useragent
            ),
        ));

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  request headers: '.$headers.'  url: '.$endpoint.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            // Do thing
//            $message =  $endpoint.' response_headers: '.$response_header;
//            $this->logdata($message, 0);
            $json = json_decode($response_body, true);
            if (!empty($json)) {
                try {
                    if (isset($json['edit']['result']) && $json['edit']['result'] == 'Success') {
                        return true;
                    }
                    if (isset($json['error'])) {
                        $message = $json['error']['code'].' - '.$json['error']['info'];
                    } else {
                        $message = $response_body;
                    }
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 0);
                    return false;
                } catch (Exception $e) {
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$e->getMessage(), 1);
                }
            }
        }
    }

}