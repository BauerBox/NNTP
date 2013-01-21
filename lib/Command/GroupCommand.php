<?php

namespace BauerBox\NNTP\Command;

use BauerBox\NNTP\Command\AbstractCommand;
use BauerBox\NNTP\Group\Group;
use BauerBox\NNTP\Util\Response;

class GroupCommand extends AbstractCommand
{
    public function __construct($group)
    {
        $this->command = 'GROUP';
        $this->expectTextResponse = false;

        $this->arguments = (is_array($group)) ? $group : array($group);
    }

    public function handleStatusResponse(Response $response)
    {
        if ($response->isOk()) {
            return Group::instanceFromGroupResponse($response->getMessage());
        }

        return null;
    }
}
