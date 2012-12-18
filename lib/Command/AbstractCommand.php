<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\CommandInterface;

abstract class AbstractCommand implements CommandInterface
{
    protected $arguments;
    protected $argumentSeparator = ' ';
    protected $command = 'UNDEFINED';
    protected $commandTerminator = "\r\n";

    public function __construct(array $arguments = null)
    {
        $this->arguments = $arguments;
    }

    public function __toString()
    {
        return $this->formatCommand();
    }

    public function execute(array $arguments = null)
    {
        $this->arguments = $arguments;

        $this->validateCommandArguments();

        return $this->formatCommand();
    }

    public function handleResponse(array $response)
    {
        return true;
    }

    protected function formatCommand()
    {
        return sprintf('%s%s%s', $this->command, $this->parseCommandArguments(), $this->commandTerminator);
    }

    protected function parseCommandArguments()
    {
        if (null === $this->arguments) {
            return '';
        }

        return implode($this->argumentSeparator, $this->arguments);
    }

    protected function validateCommandArguments()
    {
        return true;
    }
}
