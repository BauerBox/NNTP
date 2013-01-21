<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\AbstractCommand;
use BauerBox\NNTP\Util\Response;

class VersionCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->command = 'VERSION';

        $this->expectTextResponse = false;
    }

    public function handleStatusResponse(Response $response)
    {
        if ($response->isError()) {
            return 'Unknown';
        }

        return $response->getMessage();
    }
}
