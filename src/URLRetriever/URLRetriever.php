<?php
namespace Swagception\URLRetriever;

use Swagception\Exception;

class URLRetriever implements CanRetrieveURLs
{
    protected $client;
    protected $args;
    protected $options;

    public function request($uri, $method = 'get')
    {
        try {
            $result = $this->getClient()->request($method, $uri, $this->getOptions());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception\ValidationException(sprintf('Request returned a %1$s error: %2$s', $e->getResponse()->getStatusCode(), $e::getResponseBodySummary($e->getResponse())));
        }
        return json_decode($result->getBody()->getContents());
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

    public function getOptions()
    {
        if (!isset($this->options)) {
            $this->loadDefaultOptions();
        }
        return $this->options;
    }

    public function withOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    protected function loadDefaultOptions()
    {
        $this->options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];
    }
}
