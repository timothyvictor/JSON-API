<?php

namespace TimothyVictor\JsonAPI;

class Helper
{
    public static function comma_to_array($string = '') : array
    {
        return explode(',', $string);
    }

    public static function dot_to_array($string = '') : array
    {
        return explode('.', $string);
        // if (count($items) < 2){
        //     return $items;
        // }
        // $value = array_pop($items);
        // $result = [];
        // $temp = &$result;
        // foreach($items as $key){
        //     $temp =& $temp[$key];
        // }
        // $temp = $value; 
        // return $result;
    }

    // public static function getNotIncludedRelationships($relationsMap, $includes)
    // {
    //     collect($includes)->map(function ($item, $key) {
    //         return $item[0];
    //     })->each(function ($item, $key) use(&$relationsMap){
    //         unset($relationsMap[$item]);
    //     })->toArray();
        
    //     return $relationsMap;
    // }
}