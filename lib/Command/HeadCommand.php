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
        $meta = array();

        foreach ($lines as $line) {
            $temp = explode(':', $line, 2);
            if (count($temp) != 2) {
                continue;
            }
            $meta[$temp[0]] = trim($temp[1]);
        }

        return $meta;
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
