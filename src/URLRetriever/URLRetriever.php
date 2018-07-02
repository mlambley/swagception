<?php

namespace Swagception\URLRetriever;

use GuzzleHttp\Client;
use Swagception\Exception;

class URLRetriever implements CanRetrieveURLs
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param string $uri
     * @param string $method
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception\ValidationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($uri, $method = 'get')
    {
        try {
            $response = $this->getClient()->request($method, $uri, $this->getOptions());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception\ValidationException(sprintf('Request returned a %1$s error: %2$s', $e->getResponse()->getStatusCode(), $e::getResponseBodySummary($e->getResponse())));
        }
        return $response;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!isset($this->client)) {
            $this->loadDefaultClient();
        }
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function withClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    protected function loadDefaultClient()
    {
        $this->client = new Client($this->getArgs());
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        if (!isset($this->args)) {
            $this->loadDefaultArgs();
        }
        return $this->args;
    }

    /**
     * @param array $args
     *
     * @return $this
     */
    public function withArgs(array $args)
    {
        $this->args = $args;
        return $this;
    }

    protected function loadDefaultArgs()
    {
        $this->args = [];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (!isset($this->options)) {
            $this->loadDefaultOptions();
        }
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function withOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    protected function loadDefaultOptions()
    {
        $this->options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ];
    }
}
