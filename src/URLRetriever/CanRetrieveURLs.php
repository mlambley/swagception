<?php
namespace Swagception\URLRetriever;

interface CanRetrieveURLs
{
    /**
     * Performs a request upon the url, and returns the json decoded result.
     *
     * @param string $url
     * @param string $method
     * @return object
     */
    public function request($url, $method = 'get');
}
