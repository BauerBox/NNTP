<?php

namespace BauerBox\NNTP;

use BauerBox\NNTP\Exception\ConnectionFailedException;
use BauerBox\NNTP\Util\Response;
use BauerBox\NNTP\Command\VersionCommand;
use BauerBox\NNTP\Command\AbstractCommand;

class NNTP
{
    protected $socket = false;

    protected $socketErrorNumber;
    protected $socketErrorMessage;
    protected $socketTimeout = 5;

    protected $host;
    protected $port;
    protected $protocol;

    public function __construct($host, $port = 119, $protocol = 'tcp', $socketTimeout = 5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->socketTimeout = $socketTimeout;
        $this->protocol = $protocol;
    }

    public function connect()
    {
        // Attempt to open socket
        try {
            $this->socket = stream_socket_client(
                sprintf(
                    '%s://%s:%d',
                    $this->protocol,
                    $this->host,
                    $this->port
                ),
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

        // Check for error
        $response = $this->getStatusResponse();

        if (false === $response->isError()) {
            return $this;
        }

        throw new \Exception(
            'There was an error connecting. Server returned: ' . $response['code'] . ' ' . $response['message']
        );
    }

	public function getVersion()
	{
		return $this->executeCommand(new VersionCommand());
	}

    public function executeCommand(AbstractCommand $command)
    {
        // Execute against the socket
        if (true === $this->isConnected()) {
            $this->debug("C: " . substr("{$command}", 0, -2));

            if (0 < fwrite($this->socket, "{$command}")) {
                $response = $this->getStatusResponse();
                $this->debug('S: ' . $response->getStatus() . ' ' . $response->getMessage());

                $out = $command->handleStatusResponse($response);

                if ($command->expectTextReponse()) {
                    return $command->handleTextResponse($response->attachLineBuffer($this->getTextResponse()));
                } else {
                    return $out;
                }
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

    protected function getStatusResponse()
    {
        if (true === $this->isConnected()) {
            $response = Response::parseStatusResponse(@fgets($this->socket, 256));
            return $response;
        }
    }

    protected function getTextResponse()
    {
        if (true === $this->isConnected()) {
            $buffer = array();
            $line = '';

            while (!feof($this->socket)) {
                $data = @fgets($this->socket, 1024);
                $line .= $data;

                if (substr($line, -2) !== "\r\n" || strlen($line) < 2) {
                    continue;
                }

                if (substr($line, 0, 2) == '..') {
                    $line = substr($line, 1);
                }

                $line = substr($line, 0, -2);

                if ($line == '.') {
                    break;
                }

                $buffer[] = $line;

                $line = '';
            }

            return $buffer;
        }
    }
}
