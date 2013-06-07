<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\AbstractCommand;
use BauerBox\NNTP\Util\Response;
use BauerBox\NNTP\Group\Group;

class ListCommand extends AbstractCommand
{
    protected $filterRegex;

    public function __construct($groupFilterRegex = null, $args = array())
    {
        $this->command = 'LIST';
        $this->expectTextResponse = true;

        $this->arguments = $args;

        $this->filterRegex = $groupFilterRegex;
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

        if (true === is_array($lines)) {
            $return = array();

            foreach ($lines as $line) {
                $group = $this->filterGroup($line);
                if ($group instanceof Group) {
                    if ($group->isActive()) {
                        $return[] = $group;
                    }
                }
            }

            return $return;
        }

        return null;
    }

    protected function filterGroup($groupLine)
    {
        if (null === $this->filterRegex) {
            return Group::instanceFromListResponse($groupLine);
        }

        if (0 < preg_match($this->filterRegex, $groupLine)) {
            return Group::instanceFromListResponse($groupLine);
        }

        return null;
    }
}
