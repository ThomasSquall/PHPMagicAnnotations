<?php

namespace PHPAnnotations\Utils;

/**
 * Class TC_Utils
 */
class TC_Utils
{
    /**
     * Returns if the given string starts with the start string.
     * @param string $string
     * @param string $start
     * @return bool
     */
    public static function StringStartsWith($string, $start)
    {
        return substr($string, 0, strlen($start)) === $start;
    }

    /**
     * Tells if the given strings are equals.
     * @param string $string1
     * @param string $string2
     * @return bool
     */
    public static function StringEquals($string1, $string2)
    {
        return strcmp($string1, $string2) == 0;
    }

    /**
     * Returns if the given string ends with the end string.
     * @param string $string
     * @param string $end
     * @return bool
     */
    public static function StringEndsWith($string, $end)
    {
        $length = strlen($end);

        if ($length == 0) return true;

        return substr($string, -$length) === $end;
    }

    /**
     * Gets the string before another.
     * @param string $string
     * @param string $before
     * @return string|bool
     */
    public static function StringBefore($string, $before)
    {
        if (TC_Utils::StringContains($string, $before))
        {
            $tmp = explode($before, $string);
            return $tmp[0];
        }

        return false;
    }

    /**
     * Gets the string in between two others strings.
     * @param $string
     * @param $start
     * @param $end
     * @return string|bool
     */
    public static function StringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return false;
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Gets all the string in between of two others strings.
     * @param string $string
     * @param string $start
     * @param string $end
     * @return array
     */
    public static function StringsBetween($string, $start, $end)
    {
        $s = TC_Utils::StringBetween($string, $start, $end);

        $result = [];

        while (is_string($s))
        {
            $result[] = $s;
            $string = TC_Utils::ReplaceTokens($string, ["$start$s$end" => "----$$$$$$$----"]);
            $s = TC_Utils::StringBetween($string, $start, $end);
        }

        return $result;
    }
    
    /**
     * Returns true if the where string contains the find one, false otherwise.
     * @param string $where
     * @param string $find
     * @return bool
     */
    public static function StringContains($where, $find)
    {
        return strpos($where, $find) !== false;
    }

    /**
     * Replaces all the provided tokens with specified values in $text
     * @param string $text
     * @param array $replace
     * @return string
     */
    public static function ReplaceTokens($text, array $replace)
    {
        foreach ($replace as $token => $value)
        {
            $text = str_replace($token, $value, $text);
        }

        return $text;
    }

    /**
     * Splits a string with delimiter specified
     * @param string $string
     * @param string $delimiter
     * @return array
     */
    public static function Split($string, $delimiter = '_')
    {
        return explode($delimiter, $string);
    }

    /**
     * Returns true if the where string contains the find one, excluding every occurrence between start and end
     * @param string $where
     * @param string $find
     * @param string $start
     * @param string $end
     * @return bool
     */
    public static function StringContainsExcludingBetween($where, $find, $start, $end)
    {
        $between = TC_Utils::StringBetween($where, $start, $end);
        $where = TC_Utils::ReplaceTokens($where, [$start . $between . $end => ""]);

        return TC_Utils::StringContains($where, $find);
    }
}