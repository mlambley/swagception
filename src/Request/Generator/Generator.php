<?php
namespace Swagception\Request\Generator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7;

class Generator implements GeneratesRequests
{
    protected $standardHeaders;

    public function __construct()
    {
        $this->loadStandardHeaders();
    }

    /**
     * @param string|UriInterface $uri
     * @param string $method
     * @return RequestInterface
     */
    public function generate($uri, $method = 'get')
    {
        return $this->applyStandardHeaders(new Psr7\Request($method, $uri));
    }

    protected function applyStandardHeaders($request)
    {
        foreach ($this->getStandardHeaders() as $key => $val) {
            $request = $request->withHeader($key, $val);
        }

        return $request;
    }

    public function getStandardHeaders()
    {
        return $this->standardHeaders;
    }

    public function withStandardHeaders($standardHeaders)
    {
        $this->standardHeaders = $standardHeaders;
        return $this;
    }

    protected function loadStandardHeaders()
    {
        $this->standardHeaders = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
    }
}
