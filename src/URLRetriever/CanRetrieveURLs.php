<?php
namespace Swagception\URLRetriever;

use Psr\Http\Message\ResponseInterface;

interface CanRetrieveURLs
{
    /**
     * Performs a request upon the url, and returns the json decoded result.
     *
     * @param string $url
     * @param string $method
     * @return ResponseInterface
     */
    public function request($url, $method = 'get');
}
