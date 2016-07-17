<?php namespace iWedmak\SubtitleSearch;

use iWedmak\ExtraCurl;

define('SRT_STATE_SUBNUMBER', 0);
define('SRT_STATE_TIME',      1);
define('SRT_STATE_TEXT',      2);
define('SRT_STATE_BLANK',     3);

class Search 
{

    public static function url($url_template, $str, $season, $ep, $lang_id='')
    {
        $string=urlencode(str_replace( array( '\'', '"', ',', "'", "!" , ';', '<', '>', ')', '('), '', $str));
        $search_string = str_replace("{lang_id}", $lang_id, $url_template);
        $search_string = str_replace("{season}", $season, $search_string);
        $search_string = str_replace("{ep}", $ep, $search_string);
        $search_string = str_replace("{serial_name}", $string, $search_string);
        return $search_string;
    }
    
    public static function makeRes($source, $url, $title, $uploded, $lang, $file)
    {
        $array['source']=$source;
        $array['url']=trim($url);
        $array['title']=trim($title);
        $array['uploded']=trim($uploded);
        $array['lang']=trim($lang);
        $array['file']=trim($file);
        return $array;
    }
    
    public static function makeError($client)
    {
        $array=[
                'error_code'=>($client->c->error_code)?$client->c->error_code:$client->c->http_status_code, 
                'error'=>$client->c->error, 
                'response_headers'=>$client->c->response_headers,
            ];
        return $array;
    }
    
    public static function str2Vtt($file_path)
    {
        $subtitleLines   = file($file_path);
        $result = "WEBVTT\n\n\n";
        $subs    = array();
        $state   = SRT_STATE_SUBNUMBER;
        $subNum  = 0;
        $subText = '';
        $subTime = '';
        foreach($subtitleLines as $line) {
            switch($state) {
                case SRT_STATE_SUBNUMBER:
                    $subNum = trim($line);
                    $state  = SRT_STATE_TIME;
                    break;
                case SRT_STATE_TIME:
                    $subTime = trim($line);
                    $state   = SRT_STATE_TEXT;
                    break;
                case SRT_STATE_TEXT:
                    if (trim($line) == '') {
                        $sub = new \stdClass;
                        $sub->number = $subNum;
                        $data = explode(' --> ', $subTime);
                        $sub->startTime=@$data[0];
                        $sub->stopTime=@$data[1];
                        $sub->text   = $subText;
                        $subText     = '';
                        $state       = SRT_STATE_SUBNUMBER;
                        $subs[]      = $sub;
                    } else {
                        $subText .= trim($line)."\n";
                    }
                    break;
            }
        }
        foreach ($subs as $sub) {
            $result .= $sub->number."\n";
            $result .= str_replace(',', '.', $sub->startTime)." --> ".str_replace(',', '.', $sub->stopTime)."\n";
            $result .= $sub->text."\n\n";
        }
        return $result;
    }
    
}
?>