<?php

namespace BauerBox\NNTP\Command;

interface CommandInterface {
    public function execute(array $arguments = null);
    public function handleResponse(array $response);
}
