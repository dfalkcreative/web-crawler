<?php

namespace App\Traits;

/**
 * Trait TracksVisits
 *
 * @package App\Traits
 */
trait TracksVisits
{
    /**
     * A series of previously visited links.
     *
     * @var array
     */
    protected static $visited = [];


    /**
     * Registers a URL to the visited series.
     *
     * @param string $url
     */
    public static function addVisit($url = '')
    {
        self::$visited[] = $url;
    }


    /**
     * Verifies that a link does not exist.
     *
     * @param string $url
     * @return bool
     */
    public static function hasVisited($url = '')
    {
        return in_array($url, self::$visited);
    }
}