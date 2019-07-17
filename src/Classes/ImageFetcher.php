<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:45 PM
 */

namespace BotStatusBot;

use Intervention\Image\ImageManagerStatic as Image;

class ImageFetcher extends DataLogger
{





    /**
     * @param string $url
     * @param string $path
     * @param bool $resize
     * @return bool
     */
    public function saveImageLocally($url, $path, $resize = true)
    {

        $curl = curl_init($url);
        $fp = fopen($path, 'wb');

        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($curl, CURLOPT_USERAGENT, $agent);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);
        fclose($fp);

        if ($err) {
            $message = 'SaveImage cURL Error #:' . $err.' url:'.$url.' response:'.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  'SaveImage Http code error #:' . $httpcode.' url:'.$url.' response:'.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }

            // optimum res for facebook
            if ($resize) {
                /** @var \Intervention\Image\Image $img */
                $img = Image::make($path);
                $w = $img->getWidth();
                $h = $img->getHeight();

                if ($w > 1200 || $h > 630) {
                    if ($w > 1200) {
                        $img->resize(1200, null, function ($constraint) {
                            /** @var \Intervention\Image\Constraint $constraint */
                            $constraint->aspectRatio();
                        });
                    } else {
                        $img->resize(null, 630, function ($constraint) {
                            /** @var \Intervention\Image\Constraint $constraint */
                            $constraint->aspectRatio();
                        });
                    }
                }
                $img->save();
                $img->destroy();
            }

            return true;
        }
        return false;
    }
}
