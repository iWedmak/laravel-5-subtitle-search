<?php namespace iWedmak\SubtitleSearch;

use iWedmak\ExtraCurl\Parser;

class OpenSubtitles implements SubtitleSearchInterface 
{

    public static function page($url, $cache=5, $client=false)
    {
        if(!$client)
        {
            $client=new Parser;
        }
        $client->setAgent('mobile');
        if($resp=$client->get($url, $cache))
        {
            $html=new \Htmldom;
            $html->str_get_html($resp);
            $stream=Search::makeRes
                (
                    'VodLocker', 
                    $url, 
                    $html->find('td#file_title', 0)->plaintext, 
                    @$html->find('video', 0)->attr['poster'], 
                    @$html->find('video source', 0)->attr['src']
                );
            return $stream;
        }
        return Search::makeError($client);
        
    }
    
    public static function search($url, $cache=5, $client=false)
    {
        if(!$client)
        {
            $client=new Parser;
        }
        //$client->setAgent('mobile');
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
        return Search::makeError($client);
    }
    
}
?>