<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\AbstractCommand;
use BauerBox\NNTP\Util\Response;

class HelpCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->command = 'HELP';
        $this->expectTextResponse = true;
    }

    public function handleStatusResponse(Response $response)
    {
        if ($response->isFailure()) {
            $this->expectTextResponse = false;

            if ($response->isError()) {
                return 'Unsupported Command';
            }

            if ($response->isFailure()) {
                if ($response->requiresAuthentication()) {
                    return 'Authentication required for command';
                } else {
                    return 'Command failed: ' . $response->getMessage();
                }
            }
        }

        return $response->getMessage();
    }

    public function handleTextResponse(Response $response)
    {
        $lines = $response->getLineBuffer();

        if (true === is_array($lines) && count($lines) > 0) {
            $out = '';
            foreach ($lines as $line) {
                $out .= $line . PHP_EOL;
            }
            return $out;
        }

        return 'Empty text response from server';
    }
}
