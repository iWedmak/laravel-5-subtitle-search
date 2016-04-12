<?php namespace iWedmak\SubtitleSearch;

interface SubtitleSearchInterface
{
    public static function page($url, $cache, $client);
    public static function search($url, $cache, $client);
}