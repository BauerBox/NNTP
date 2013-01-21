<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\AbstractCommand;
use BauerBox\NNTP\Util\Response;
use BauerBox\NNTP\Exception\InvalidArgumentException;

class AuthinfoCommand extends AbstractCommand
{
    protected $validArguments;

    public function __construct(array $arguments = null)
    {
        $this->command = 'AUTHINFO';
        $this->expectTextResponse = false;

        $this->validArguments = array('USER <username>', 'PASS <password>');

        $this->validateCommandArguments($arguments);
    }

    public function handleStatusResponse(Response $response)
    {
        if ($response->isFailure()) {
            if ($response->isError()) {
                return self::STATUS_UNSUPPORTED_COMMAND;
            }

            if ($response->isFailure()) {
                return self::STATUS_FAILED;
            }
        }

        if ($response->isOk()) {
            if ($response->isComplete()) {
                return self::STATUS_OK;
            } else {
                return self::STATUS_SEND_MORE;
            }
        }

        return $response->getMessage();
    }

    protected function validateCommandArguments(array $arguments = null)
    {
        if ($this->argumentsValid) {
            return true;
        }

        if ($arguments === null) {
            InvalidArgumentException::toss($this->command, '', $this->validArguments);
        }

        $paramFound = false;

        foreach ($arguments as $argument => $value) {
            if (true === $paramFound) {
                InvalidArgumentException::toss(
                    $this->command,
                    $argument . ' :: Can only be ONE of',
                    $this->validArguments
                );
            }

            $paramFound = true;

            if (true === in_array($argument, array('USER', 'PASS'))) {
                if (false === empty($value)) {
                    $this->arguments[] = $argument;
                    $this->arguments[] = $value;
                } else {
                    InvalidArgumentException::toss($this->command, "{$argument} <{$value}>", $this->validArguments);
                }
            } else {
                InvalidArgumentException::toss($this->command, $argument, $this->validArguments);
            }
        }

        $this->argumentsValid = true;
    }
}
