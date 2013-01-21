<?php

namespace BauerBox\NNTP\Command\Compound;

use BauerBox\NNTP\Command\AbstractCommand;

class AbstractCompoundCommand
{
    protected $commands;

    public function __construct()
    {
        $this->commands = array();
    }

    /**
     * Get the next command
     *
     * @return boolean|BauerBox\NNTP\Command\AbstractCommand
     */
    public function getNextCommand()
    {
        if (count($this->commands) > 0) {
            return array_shift($this->commands);
        }

        return false;
    }

    /**
     * Add a command to the bottom of the stack
     *
     * @param \BauerBox\NNTP\Command\AbstractCommand $command
     * @return \BauerBox\NNTP\Command\Compound\AbstractCompoundCommand
     */
    protected function attachCommand(AbstractCommand $command)
    {
        $this->commands[] = $command;
        return $this;
    }
}
