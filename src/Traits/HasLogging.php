<?php

namespace App\Traits;

/**
 * Trait HasLogging
 *
 * @package App\Traits
 */
trait HasLogging
{
    /**
     * Log a message to the console.
     *
     * @param string $message
     */
    public function log($message = '')
    {
        echo "$message\n";
    }
}