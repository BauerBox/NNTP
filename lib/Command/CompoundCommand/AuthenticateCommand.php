<?php

namespace BauerBox\NNTP\Command\Compound;

use BauerBox\NNTP\Command\Compound\AbstractCompoundCommand;
use BauerBox\NNTP\Command\AuthinfoCommand;
use BauerBox\NNTP\Exception\InvalidArgumentException;

class AuthenticateCommand extends AbstractCompoundCommand
{
    protected $user;
    protected $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;

        $this
            ->attachCommand(new AuthinfoCommand(array('USER' => $this->user)))
            ->attachCommand(new AuthinfoCommand(array('PASS' => $this->password)));
    }
}
