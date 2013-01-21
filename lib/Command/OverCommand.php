<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\HeadCommand;

class OverCommand extends HeadCommand
{
    protected $filterRegex;

    public function __construct($args = array())
    {
        $this->command = 'OVER';
        $this->expectTextResponse = true;

        $this->arguments = $args;
    }
}
