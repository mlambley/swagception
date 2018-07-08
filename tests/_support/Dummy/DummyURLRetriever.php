<?php
namespace tests\Dummy;

use GuzzleHttp\Psr7\Response;

class DummyURLRetriever implements \Swagception\URLRetriever\CanRetrieveURLs
{
    protected $responses;

    protected $mode;
    const MODE_VALID = 1;
    const MODE_INVALID = 2;

    public function __construct($mode = 1)
    {
        if ($mode === static::MODE_VALID) {
            $this->loadValidResponses();
        } elseif ($mode === static::MODE_INVALID) {
            $this->loadInvalidResponses();
        } else {
            throw new \Exception(sprintf('Invalid DummyURLRetriever load mode %1$s', $mode));
        }
    }

    public function request($url, $method = 'get')
    {
        if (isset($this->responses[$url][$method])) {
            return $this->responses[$url][$method];
        }
        return null;
    }

    protected function loadValidResponses()
    {
        $this->responses = [
            'http://localhost:8000/api/users/' => [
                'get'  => new Response(200, [], json_encode([
                    $this->arrayToObject([
                        'Id' => 1,
                    ]),
                    $this->arrayToObject([
                        'Id' => 2,
                    ]),
                ])),
                'post' => new Response(201, [], json_encode($this->arrayToObject([
                    'Id' => 1,
                    'Details' => [
                        'Name' => 'Fred Smith',
                        'Thing' => 203320,
                    ],
                ]))),
            ],
            'http://localhost:8000/api/users/1' => [
                'get' => new Response(200, [], json_encode($this->arrayToObject([
                    'Id' => 1,
                    'Details' => [
                        'Name' => 'Fred Smith',
                        'Thing' => 203320,
                    ],
                ]))),
                'patch' => new Response(204),
                'delete' => new Response(204),
            ],
            'http://localhost:8000/api/comments/' => [
                'get' => new Response(200, [], json_encode([
                    $this->arrayToObject([
                        'Id' => 1,
                        'Text' => 'This is text',
                        'User' => [
                            'Id' => 1,
                        ],
                    ]),
                    $this->arrayToObject([
                        'Id' => 2,
                        'Text' => 'This is more text',
                        'User' => [
                            'Id' => 1,
                        ],
                    ]),
                ])),
                'post' => new Response(201, [], json_encode($this->arrayToObject([
                    'Id' => 1,
                    'Text' => 'This is text',
                    'User' => [
                        'Id' => 1,
                        'Details' => [
                            'Name' => 'Fred Smith',
                            'Thing' => 203320,
                        ],
                    ],
                ]))),
            ],
            'http://localhost:8000/api/comments/1' =>  [
                'get' => new Response(200, [], json_encode($this->arrayToObject([
                    'Id' => 1,
                    'Text' => 'This is text',
                    'User' => [
                        'Id' => 1,
                        'Details' => [
                            'Name' => 'Fred Smith',
                            'Thing' => 203320,
                        ],
                    ],
                ]))),
                'patch' => new Response(204),
                'delete' => new Response(204),
            ],
            'http://localhost:8000/api/single/1' => ['get' => new Response(200, [], json_encode('test string'))],
        ];
    }

    protected function loadInvalidResponses()
    {
        $this->responses = [
            'http://localhost:8000/api/users/' => [
                'get' => new Response(200, [], json_encode([
                    $this->arrayToObject([
                        'Id' => '1',
                    ]),
                    $this->arrayToObject([
                        'Id' => 2,
                    ]),
                ])),
            ],
            'http://localhost:8000/api/users/1' => [
                'get' => new Response(200, [], json_encode($this->arrayToObject([
                    'Id' => 1,
                    'Details' => [
                        'Name' => 'Fred Smith',
                        'Thing' => '203320',
                    ],
                ]))),
            ],
            'http://localhost:8000/api/comments/' => [
                'get' => new Response(200, [], json_encode([
                    $this->arrayToObject([
                        'Id' => 1,
                        'Text' => 'This is text',
                    ]),
                    $this->arrayToObject([
                        'Id' => 2,
                        'Text' => 'This is more text',
                        'User' => [
                            'Id' => 1,
                        ],
                    ]),
                ])),
            ],
            'http://localhost:8000/api/comments/1' => [
                'get' => new Response(200, [], json_encode($this->arrayToObject([
                    'Id' => '1',
                    'Text' => 'This is text',
                    'User' => [
                        'Id' => 1,
                        'Details' => [
                            'Name' => 'Fred Smith',
                            'Thing' => 203320,
                        ],
                    ],
                ]))),
            ],
            'http://localhost:8000/api/single/1' => ['get' => new Response(200, [], 123)],
        ];
    }

    protected function arrayToObject($array)
    {
        $obj = new \stdClass();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $obj->$key = $this->arrayToObject($val);
            } else {
                $obj->$key = $val;
            }
        }
        return $obj;
    }
}

//EOF
