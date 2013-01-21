<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\AbstractCommand;
use BauerBox\NNTP\Util\Response;
use BauerBox\NNTP\Group\Group;

class HeadCommand extends AbstractCommand
{
    protected $filterRegex;

    public function __construct($args = array())
    {
        $this->command = 'HEAD';
        $this->expectTextResponse = true;

        $this->arguments = $args;
    }

    public function handleStatusResponse(Response $response)
    {
        parent::handleStatusResponse($response);

        if ($response->isFailure()) {
            $this->expectTextResponse = false;
            return self::STATUS_FAILED;
        }

        return self::STATUS_OK;
    }

    public function handleTextResponse(Response $response)
    {
        parent::handleTextResponse($response);

        $lines = $response->getLineBuffer();

        foreach ($lines as $line) {
            echo "HEAD: {$line}" . PHP_EOL;
        }

        return $lines;
    }

    protected function filterGroup($groupLine)
    {
        if (null === $this->filterRegex) {
            return $groupLine;
        }

        if (0 < preg_match($this->filterRegex, $groupLine)) {
            return Group::instanceFromListResponse($groupLine);
        }

        return null;
    }
}
