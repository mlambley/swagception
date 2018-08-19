<?php
namespace Swagception\Request\Generator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

interface GeneratesRequests
{
    /**
     * Generates a PSR7-compatible request object using the given uri and method.
     * URI can be already a PSR7 UriInterface, or a string to be converted to one.
     *
     * @param string|UriInterface $uri
     * @param string $method
     * @return RequestInterface
     */
    public function generate($uri, $method = 'get');
}
