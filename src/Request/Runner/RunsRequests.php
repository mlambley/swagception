<?php
namespace Swagception\Request\Runner;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RunsRequests
{
    /**
     * Performs the specified request synchronously, and returns the response.
     *
     * @param RequestInterface $request
     * @param bool $allowError If true, returns the Response even if it's a 4xx, 5xx, etc. If false, can do either.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, $allowError = false);
}
