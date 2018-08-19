<?php
namespace Swagception\Request\Runner;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Swagception\Exception;

class Runner implements RunsRequests
{
    protected $client;
    protected $args;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws Exception\ValidationException
     */
    public function run(RequestInterface $request, $allowError = false)
    {
        try {
            return $this->getClient()->send($request);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($allowError) {
                return $e->getResponse();
            }
            throw $e;
        }
    }

    public function getClient()
    {
        if (!isset($this->client)) {
            $this->loadDefaultClient();
        }
        return $this->client;
    }

    public function withClient($client)
    {
        $this->client = $client;
        return $this;
    }

    protected function loadDefaultClient()
    {
        $this->client = new \GuzzleHttp\Client($this->getArgs());
    }

    public function getArgs()
    {
        if (!isset($this->args)) {
            $this->loadDefaultArgs();
        }
        return $this->args;
    }

    public function withArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    protected function loadDefaultArgs()
    {
        $this->args = [];
    }
}
