<?php

namespace BauerBox\NNTP;

use BauerBox\NNTP\Exception\ConnectionFailedException;
use BauerBox\NNTP\Util\Response;
use BauerBox\NNTP\Command\CommandInterface;
use BauerBox\NNTP\Command\CapabilitiesCommand;
use BauerBox\NNTP\Command\VersionCommand;

class NNTP
{
    protected $socket = false;

    protected $socketErrorNumber;
    protected $socketErrorMessage;
    protected $socketTimeout = 5;

    protected $host;
    protected $port;

    public function __construct($host, $port = 119, $socketTimeout = 5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->socketTimeout = $socketTimeout;
    }

    public function connect()
    {
        // Attempt to open socket
        try {
            $this->socket = fsockopen(
                $this->host,
                $this->port,
                $this->socketErrorNumber,
                $this->socketErrorMessage,
                $this->socketTimeout
            );
        } catch (\Exception $e) {
            ConnectionFailedException::toss($this->host, $this->port, $e->getMessage(), $e);
        }

        // Check if the socket is open
        if (false === $this->isConnected()) {
            ConnectionFailedException::toss(
                $this->host,
                $this->port,
                new \Exception($this->socketErrorMessage, $this->socketErrorNumber)
            );
        }

        return $this;
    }

    public function getCapabilities()
    {
        return $this->executeCommand(new CapabilitiesCommand());
    }

	public function getVersion()
	{
		return $this->executeCommand(new VersionCommand());
	}

    public function executeCommand(CommandInterface $command)
    {
        // Execute against the socket
        if (true === $this->isConnected()) {
            if (0 < fwrite($this->socket, $command->execute())) {
                $this->debug("Executing command", $command);
                return $command->handleResponse($this->getResponse());
            }
        } else {
            // TODO: Throw
            throw new \Exception('Command ' . $command . ' failed :: Server not connected!');
        }
    }

    public function disconnect()
    {
        if (true === $this->isConnected()) {
            fclose($this->socket);
        }

        return $this;
    }

    public function isConnected()
    {
        return (false !== $this->socket);
    }

    protected function debug()
    {
        foreach (func_get_args() as $arg) {
            echo "[NNTP] {$arg}" . PHP_EOL;
        }
    }

    protected function getRawResponse()
    {
        $buffer = '';
        while (false !== ($row = fgets($this->socket, 4096))) {
            $this->debug("+ [{$row}]");
            $buffer .= $row;
        }
        return $buffer;
    }

    protected function getResponse()
    {
        $this->debug("Getting Response");
        return Response::parseResponseString($this->getRawResponse());
    }
}
