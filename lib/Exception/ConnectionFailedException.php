<?php

namespace BauerBox\NNTP\Exception;

use BauerBox\NNTP\Exception\NntpException;

class ConnectionFailedException extends NntpException
{
    public static function toss($host, $port, $message = 'Check configuration', \Exception $e = null)
    {
        throw new ConnectionFailedException(
            sprintf('Connection failed to %s:%d - %s', $host, $port, $message),
            0,
            $e
        );
    }
}
