<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Util\Response;

abstract class AbstractCommand
{
    const STATUS_OK = 1;
    const STATUS_SEND_MORE = 2;
    const STATUS_FAILED = 3;
    const STATUS_ERROR = 4;
    const STATUS_UNSUPPORTED_COMMAND = 5;

    protected $arguments;
    protected $argumentSeparator = ' ';

    protected $command = 'UNDEFINED';
    protected $commandTerminator = "\r\n";

    protected $expectTextResponse = false;

    protected $argumentsValid = false;

    /**
     * @var Response
     */
    protected $responseStatus;

    /**
     * @var Response
     */
    protected $responseText;

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
        if (null !== $arguments) {
            $this->validateCommandArguments($arguments);
        } else {
            $this->validateCommandArguments();
        }

        return $this->formatCommand();
    }

    public function expectTextReponse()
    {
        return $this->expectTextResponse;
    }

    /**
     * @return Response
     */
    public function getStatusResponse()
    {
        return $this->responseStatus;
    }

    /**
     * @return Response
     */
    public function getTextResponse()
    {
        return $this->responseText;
    }

    public function handleStatusResponse(Response $response)
    {
        $this->responseStatus = $response;
    }

    public function handleTextResponse(Response $response)
    {
        $this->responseText = $response;
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

        $buffer = array();

        foreach ($this->arguments as $arg => $value) {
            $value = trim($value);

            if (true === is_int($arg)) {
                if (trim($value) != '') {
                    $buffer[] = $value;
                }
            } else {
                if (trim($arg) != '') {
                    $buffer[] = $arg;
                }

                if (trim($value) != '') {
                    $buffer[] = $value;
                }
            }
        }

        return ' ' . implode($this->argumentSeparator, $buffer);
    }

    protected function validateCommandArguments(array $arguments = null)
    {
        if (null !== $arguments) {
            $this->arguments = $arguments;
        }
    }
}
