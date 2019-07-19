<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/19/2019
 * Time: 11:47 PM
 */

namespace BotStatusBot;

//require_once __DIR__ . '../../../../vendor/autoload.php';
//require_once __DIR__ . '/../resources/secrets.php';
require_once 'CommandInterpreter.php';

use Stringy\Stringy as S;

class FacebookHelper extends DataLogger
{

    /**
     * @param string $_APP_ID
     * @param string $_APP_SECRET
     * @param string $_ACCESS_TOKEN_DEBUG
     * @return \Facebook\Facebook
     */
    public function init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG)
    {
        try {
            # v5 with default access token fallback
            $fb = new \Facebook\Facebook([
                'app_id' => $_APP_ID,
                'app_secret' => $_APP_SECRET,
                'default_graph_version' => 'v3.2',
            ]);
            $fb->setDefaultAccessToken($_ACCESS_TOKEN_DEBUG);
            return $fb;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
        $message = 'something when wrong at initializing Facebook object';
        $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $POST_ID
     * @param bool $PHOTO_COMMENT
     * @param bool $COMMAND_COMMENT
     * @return array
     */
    public function getFirstComment($fb, $POST_ID, $PHOTO_COMMENT = false, $COMMAND_COMMENT = false)
    {
        if (!empty($POST_ID)) {
            try {
                // after underscore
                // TODO: whats the deal with this
                $POST_ID = substr($POST_ID, strpos($POST_ID, "_") + 1);

                $imagequery = '';
                if ($PHOTO_COMMENT) {
                    $imagequery = '?fields=attachment';
                }

                /** @var $response \Facebook\FacebookResponse */
                $response = $fb->get('/' . $POST_ID . '/comments' . $imagequery);

                /** @var $graphEdge \Facebook\GraphNodes\GraphEdge */
                $graphEdge = $response->getGraphEdge();
                var_dump($graphEdge->asArray());

                // Iterate over all the GraphNode's returned from the edge
                /** @var $graphNode \Facebook\GraphNodes\GraphNode */
                foreach ($graphEdge as $graphNode) {
                    // ignore blacklisted users
                    /** @var $from \Facebook\GraphNodes\GraphNode */
                    $from = $graphNode->getField('from');
                    if (isset($from)) {
                        $name = $from->getField('name');
                        if (isset($name)) {
//                            $blacklist = array('BotStatusBot 7245', 'ExampleApp');
                            $blacklist = array();
                            if (!in_array($name, $blacklist)) {
                                $text = $graphNode->getField('message');
                                $text = strtolower($text);

                                /** @var \Stringy\Stringy $comment */
                                $comment = S::create($text);

                                // resources
                                if ($PHOTO_COMMENT) {
                                    $attachment = $graphNode->getField('attachment');
                                    if (isset($attachment)) {
                                        $message = 'comment made by: ' . $name;
                                        $this->logdata($message);

                                        // return first photo comment
                                        $photo = $attachment->getField('url');
                                        return [
                                            'who'   => $name,
                                            'text'  => '',
                                            'photo' => $photo
                                        ];
                                    }
                                } elseif ($COMMAND_COMMENT) {
                                    $CI = new CommandInterpreter();
                                    $possiblecommand = $comment->startsWithAny($CI->getAvailableCommands());
                                    $length = strlen($comment);
                                    if ($possiblecommand && $length <= $CI->getMaxlength() && $length >= $CI->getMinlength()) {
                                        $message = 'comment made by: ' . $name;
                                        $this->logdata($message);


                                        return [
                                            'who'   => $name,
                                            'text'  => $text,
                                            'photo' => ''
                                        ];
                                    }
                                } else {
                                    $message = 'comment made by: ' . $name;
                                    $this->logdata($message);

                                    // return first comment
                                    return [
                                        'who'   => $name,
                                        'text'  => $text,
                                        'photo' => ''
                                    ];
                                }
                            } else {
                                $logmessage = 'blacklisted user: ' . $name;
                                $this->logdata($logmessage);
                            }
                        }
                    }
                }
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                $logmessage = 'Facebook SDK returned an error: ' . $e->getMessage();
                $this->logdata('[' . __METHOD__ . ' ERROR] ' . __FILE__ . ':' . __LINE__ . ' ' . $logmessage, 1);
            }
        }
        return [];
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $ID_REFERENCE
     * @param string $COMMENT
     * @param string $COMMENT_PHOTO
     */
    public function postCommentToReference($fb, $ID_REFERENCE, $COMMENT, $COMMENT_PHOTO = '')
    {
        try {
            $data = array ();

            if (!empty($COMMENT)) {
                $data['message'] = $COMMENT;
            }

            if (!empty($COMMENT_PHOTO)) {
                $data['source'] = $fb->fileToUpload($COMMENT_PHOTO);
            }

            // $ID_REFERENCE Could either be a post or a comment
            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->post($ID_REFERENCE.'/comments', $data);
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $POST_ID
     * @return \Facebook\GraphNodes\GraphNode
     */
    public function getPost($fb, $POST_ID)
    {
        try {
            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->get('/'.$POST_ID);

            return $response->getGraphNode();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $IMAGE_PATH
     * @param string $POST_TITLE
     * @param string $COMMENT
     * @param string $BOT_COMMENT
     * @param string $COMMENT_PHOTO
     */
    public function newPost($fb, $IMAGE_PATH, $POST_TITLE, $COMMENT = '', $BOT_COMMENT = '', $COMMENT_PHOTO = '')
    {

        try {
            # fileToUpload works with remote and local images
            $data = array(
                'message' => $POST_TITLE
            );

            if (!empty($IMAGE_PATH)) {
                /** @var \Facebook\FileUpload\FacebookFile $fbfile */
                $fbfile = $fb->fileToUpload($IMAGE_PATH);
                $data['source'] = $fbfile;
                $endpoint = '/me/photos';
            } else {
                $endpoint = '/me/feed';
                $this->logdata('Text only');
            }

            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->post($endpoint, $data);

            /** @var $graphNode \Facebook\GraphNodes\GraphNode */
            $graphNode = $response->getGraphNode();
            $post_id = $graphNode->getField('id');

            // if data has been passed post BOT comment
            if (!empty($BOT_COMMENT)) {
                if (is_array($BOT_COMMENT)) {
                    // remove empty
                    $BOT_COMMENT = array_filter($BOT_COMMENT);
                    foreach ($BOT_COMMENT as $comment) {
                        $this->postCommentToReference($fb, $post_id, $comment);
                    }
                } else {
                    $this->postCommentToReference($fb, $post_id, $BOT_COMMENT);
                }
            }

            // if data has been passed post comment
            if (!empty($COMMENT) && empty($COMMENT_PHOTO)) {
                if (is_array($COMMENT)) {
                    // remove empty
                    $COMMENT = array_filter($COMMENT);
                    foreach ($COMMENT as $comment) {
                        $this->postCommentToReference($fb, $post_id, $comment);
                    }
                } else {
                    $this->postCommentToReference($fb, $post_id, $COMMENT);
                }
            } elseif (!empty($COMMENT) && !empty($COMMENT_PHOTO)) {
                //TODO array support for $COMMENT_PHOTO
                $this->postCommentToReference($fb, $post_id, $COMMENT, $COMMENT_PHOTO);
            }

            if (!empty($IMAGE_PATH)) {
                // Close stream so we are able to unlink the image later
                $fbfile->close();

                // Move image to avoid posting it again
                // Formatted this way so files get sorted correctly
                copy($IMAGE_PATH, 'debug/posted/'.date("Y-m-d H_i_s").'.jpg');
                if (unlink($IMAGE_PATH)) {
                    $this->logdata('the file was copied and deleted.');
                } else {
                    $this->logdata('the file couldn\'t deleted.');
                }
            }
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }

    /**
     * @param \Facebook\Facebook $fb
     * @return string
     */
    public function getLastPost($fb)
    {

        try {

            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->get(
                '/me/feed'
            );

            /** @var $graphEdge \Facebook\GraphNodes\GraphEdge */
            $graphEdge = $response->getGraphEdge();
//            var_dump($graphEdge->asArray());

            /** @var $graphNode \Facebook\GraphNodes\GraphNode */
            foreach ($graphEdge as $graphNode) {
                // avoid polls
                $story = $graphNode->getField('story');
                if (strpos($story, 'poll') === false) {
                    return $graphNode->getField('id');
                }
            }

            $message = 'No valid post found.';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
        return '';
    }


    /**
     * @param \Facebook\Facebook $fb
     * @return array
     */
    public function firstCommentFromLastPost($fb)
    {
        $post = $this->getLastPost($fb);

        if (!empty($post)) {
            return $this->getFirstComment($fb, $post);
        }

        return [];
    }

    /**
     * @param \Facebook\Facebook $fb
     * @return array
     */
    public function firstCommandFromLastPost($fb)
    {
        $post = $this->getLastPost($fb);

        $res = $this->getFirstComment($fb, $post, false, true);
        if (!empty($res)) {
            //FILTER_SANITIZE_STRING: Strip tags, optionally strip or encode special characters.
            //FILTER_FLAG_STRIP_LOW: strips bytes in the input that have a numerical value <32, most notably null bytes and other control characters such as the ASCII bell.
            //FILTER_FLAG_STRIP_HIGH: strips bytes in the input that have a numerical value >127. In almost every encoding, those bytes represent non-ASCII characters such as ä, ¿, 堆 etc
            $safe_comment = filter_var($res['text'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

            $res['text'] = strtolower($safe_comment);
            return $res;
        } else {
            return [];
        }
    }

    /**
     * @param \Facebook\Facebook $fb
     * @return array
     */
    public function firstPhotocommentFromLastPost($fb)
    {
        $post = $this->getLastPost($fb);

        if (!empty($post)) {
            return $this->getFirstComment($fb, $post, true, false);
        }

        return [];
    }

    public function getPicture($fb, $page_id)
    {
        // first we get the pictures
        /** @var $response \Facebook\FacebookResponse */
        $response = $fb->get($page_id.'/picture?width=400');

        $headers = $response->getHeaders();

        return array(
            'link' => $headers['Location'],
            'extension' => $headers['Content-Type']
        );
    }

    public function getPageData($fb, $page_id)
    {
        // first we get the pictures
        /** @var $response \Facebook\FacebookResponse */
        $response = $fb->get($page_id.'/locations');

        /** @var $graphEdge \Facebook\GraphNodes\GraphEdge */
        $graphEdge = $response->getGraphEdge();
        var_dump($graphEdge->asArray());

    }

    public function getPageIdFromLink($link) {

        $url = 'https://findmyfbid.com/?__amp_source_origin=https%3A%2F%2Ffindmyfbid.com';

        $curl = curl_init();

        $data = "-----------------------------12207140318114
Content-Disposition: form-data; name='url'

".$link."
-----------------------------12207140318114
Content-Disposition: form-data; name=''

Find numeric ID →
-----------------------------12207140318114--
";
        $content_length = strlen($data);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLINFO_HEADER_OUT => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Host: findmyfbid.com',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0',
                'Accept: application/json',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate, br',
                'Referer: https://findmyfbid.com/',
                'AMP-Same-Origin: true',
                'Content-Type: multipart/form-data; boundary=---------------------------12207140318114',
                'Origin: https://findmyfbid.com',
                'Content-Length: '.$content_length,
                'Connection: keep-alive'
            ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  request headers: '.$headers.'  url: '.$link.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  request headers: '.$headers.'  url: '.$link.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            // caveman way to avoid scientific notation since its a big number
            if (!empty($response)) {
                $aux = str_replace('{"id":','', $response);
                return str_replace('}','', $aux);
            }
        }
        return '';

    }
}
