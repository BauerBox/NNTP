<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\HelpCommand;

class CapabilitiesCommand extends HelpCommand
{
    public function __construct()
    {
        $this->command = 'CAPABILITIES';
        $this->expectTextResponse = true;
    }
}
