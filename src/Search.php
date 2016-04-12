<?php namespace iWedmak\SubtitleSearch;

use iWedmak\ExtraCurl;

class Search 
{

    public static function url($url_template, $str, $lang, $lang_id, $season, $ep)
    {
        $string=urlencode(str_replace( array( '\'', '"', ',', "'", "!" , ';', '<', '>', ')', '('), '', $str));
        $search_string = str_replace("{lang}", $lang, $url_template);
        $search_string = str_replace("{lang_id}", $lang_id, $search_string);
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
                'error_code'=>$client->c->error_code, 
                'error'=>$client->c->error, 
                'response_headers'=>$client->c->response_headers,
            ];
        return $array;
    }
    
}
?>