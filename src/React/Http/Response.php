<?php

namespace React\Http;

use Evenement\EventEmitter;
use Guzzle\Http\Message\Response as GuzzleResponse;
use React\Socket\ConnectionInterface;

class Response extends EventEmitter
{
    private $conn;
    private $headWritten = false;

    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
    }

    public function writeHead($status = 200, array $headers = array())
    {
        if ($this->headWritten) {
            throw new \Exception('Response head has already been written.');
        }

        $response = new GuzzleResponse($status, $headers);
        $response->setHeader('X-Powered-By', 'React/alpha');
        $data = (string) $response;
        $this->conn->write($data);

        $this->headWritten = true;
    }

    public function write($data)
    {
        if (!$this->headWritten) {
            throw new \Exception('Response head has not yet been written.');
        }

        $this->conn->write($data);
    }

    public function end($data = null)
    {
        if (null !== $data) {
            $this->write($data);
        }
        $this->emit('end');
        $this->removeAllListeners();
        $this->conn->end();
    }
}
