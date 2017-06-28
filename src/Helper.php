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
    }
}
