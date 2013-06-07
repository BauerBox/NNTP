<?php

namespace BauerBox\NNTP;

use BauerBox\NNTP\Exception\ConnectionFailedException;
use BauerBox\NNTP\Util\Response;
use BauerBox\NNTP\Command\AbstractCommand;
use BauerBox\NNTP\Command\AuthinfoCommand;
use BauerBox\NNTP\Command\ListCommand;

class NNTP
{
    protected $socket = false;

    protected $socketErrorNumber;
    protected $socketErrorMessage;
    protected $socketTimeout = 5;

    protected $host;
    protected $port;
    protected $protocol;

    protected $config;

    public static $debug = false;

    public function __construct($host, $port = 119, $protocol = 'tcp', $socketTimeout = 5)
    {
        if (true === $port && true === file_exists($host)) {
            $this->debug('Loading config file: ' . $host);
            $this->config = parse_ini_file($host, true);
            $this->debug('Config Dump', print_r($this->config, true));

            $this->host = $this->config['server']['host'];
            $this->port = $this->config['server']['port'];
            $this->protocol = $this->config['server']['protocol'];
            $this->socketTimeout = $this->config['server']['timeout'];
        } else {
            $this->host = $host;
            $this->port = $port;
            $this->socketTimeout = $socketTimeout;
            $this->protocol = $protocol;
        }
    }

    public function authenticate($username = null, $password = null)
    {
        if (null === $username) {
            if (true === is_array($this->config)) {
                $username = $this->config['auth']['username'];
                $password = $this->config['auth']['password'];
            }
        }

        if ($this->executeCommand(new AuthinfoCommand(array('USER' => $username))) === AuthinfoCommand::STATUS_SEND_MORE) {
            if ($this->executeCommand(new AuthinfoCommand(array('PASS' => $password))) === AuthinfoCommand::STATUS_OK) {
                return true;
            }
        }

        return false;
    }

    public function connect()
    {
        $this->debug(
            'Connecting ' . sprintf(
                '%s://%s:%d',
                $this->protocol,
                $this->host,
                $this->port
            )
        );

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

    public function getActiveGroups($filterRegex = null)
    {
        return $this->executeCommand(new ListCommand($filterRegex, array('ACTIVE')));
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
        if (true === static::$debug) {
            foreach (func_get_args() as $arg) {
                echo "[NNTP] {$arg}" . PHP_EOL;
            }
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
