<?php

namespace BauerBox\NNTP\Exception;


class ConnectionFailedException extends \Exception
{
    public static function toss($host, $port, $message = 'Check configuration', \Exception $e = null) {
        throw new ConnectionFailedException(
            sprintf('Connection failed to %s:%d - %s', $host, $port, $message),
            0,
            $e
        );
    }
}
