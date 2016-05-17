<?php namespace iWedmak\SubtitleSearch;

use iWedmak\ExtraCurl\Parser;
use iWedmak\Helper\Mate;
use \Comodojo\Zip\Zip as Zip;

class OpenSubtitles implements SubtitleSearchInterface 
{

    public static function page($url, $cache=5, $client=false)
    {
        if(!$client)
        {
            $client=new Parser;
        }
        if($resp=$client->get($url, $cache, 'file'))
        {
            $html=new \Htmldom;
            $html->str_get_html($resp);
            list($title, $trash)=explode('.srt', $html->find('img[title*=Subtitle filename]', 0)->parent()->plaintext, 2);
            $lang=Mate::match('- [*] (', $html->find('a[title*=All subtitles for this movie in this language]', 0)->attr['title']);
            $id=preg_replace('/[^0-9]/', '', $html->find('a[title=Download]', 0)->attr['href']);
            $subtitle=Search::makeRes
                (
                    'OpenSubtitles', 
                    $url, 
                    $title,
                    $html->find('time', 0)->attr['datetime'], 
                    $lang,
                    $file="http://dl.opensubtitles.org/en/download/sub/vrf-".dechex($id)."/".$id
                );
            return $subtitle;
        }
        $error=Search::makeError($client);
        if($error['error_code']==301 || $error['error_code']==302)
        {
            return OpenSubtitles::page($client->redirect(), $cache, $client);
        }
        return $error;
    }
    
    public static function search($url, $cache=5, $client=false)
    {
        if(!$client)
        {
            $client=new Parser;
        }
        if($resp=$client->get($url, $cache))
        {
            $html=new \Htmldom;
            $html->str_get_html($resp);
            $result=[];
            foreach($html->find('table#search_results tr[id*=name]') as $tr)
            {
                $id=preg_replace('/[^0-9]/', '', $tr->attr['id']);
                list($name, $file_name, $trash)=explode(PHP_EOL,$tr->find('td#main'.$id, 0)->plaintext);
                $file="http://dl.opensubtitles.org/en/download/sub/vrf-".dechex($id)."/".$id;
                $result[]=Search::makeRes
                    (
                        'OpenSubtitles', 
                        'http://www.opensubtitles.org'.$tr->find('td strong a', 0)->attr['href'], 
                        $file_name, 
                        $tr->find('time', 0)->attr['datetime'], 
                        $tr->find('div.flag', 0)->parent()->attr['title'], 
                        $file
                    );
            }
            return $result;
        }
        $error=Search::makeError($client);
        pre($url);
        pre($error);
        if($error['error_code']==301 || $error['error_code']==302)
        {
            return OpenSubtitles::page($client->redirect(), $cache, $client);
        }
        return $error;
    }
    
    public static function file($url, $cache=5, $client=false)
    {
        if(!$client)
        {
            $client=new Parser;
        }
        $sub=$client->get($url, $cache, 'file');
        $name=Mate::clat($url);
        $path=public_path('subtitles/temp/'.Mate::clat($url).'.zip');
        file_put_contents($path, $sub);
        return $path;
    }
    
}
?>