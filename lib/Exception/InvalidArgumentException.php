<?php

namespace BauerBox\NNTP\Exception;

use BauerBox\NNTP\Exception\NntpException;

class InvalidArgumentException extends NntpException
{
    public static function toss(
        $command,
        $suppliedArgument = null,
        array $validArguments = null,
        \Exception $e = null
    ) {
        throw new InvalidArgumentException(
            sprintf(
                'Invalid command argument: %s',
                $suppliedArgument,
                $validArguments === null ? '' : ' :: Valid Arguments: ' . implode(', ', $validArguments)
            ),
            0,
            $e
        );
    }
}
