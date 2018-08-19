<?php
namespace tests\Dummy;

class DummyRunner implements \Swagception\Request\Runner\RunsRequests
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
    
    public function run(\Psr\Http\Message\RequestInterface $request, $allowError = false)
    {
        $uri = (string)$request->getUri();
        if (isset($this->responses[$uri])) {
            return new \GuzzleHttp\Psr7\Response(200, [], json_encode($this->responses[$uri]));
        }
        return null;
    }
    
    protected function loadValidResponses()
    {
        $this->responses = [
            'http://localhost:8000/api/users/' => [
                $this->arrayToObject([
                    'Id' => 1
                ]),
                $this->arrayToObject([
                    'Id' => 2
                ])
            ],
            'http://localhost:8000/api/users/1' => $this->arrayToObject([
                'Id' => 1,
                'Details' => [
                    'Name' => 'Fred Smith',
                    'Thing' => 203320
                ]
            ]),
            'http://localhost:8000/api/comments/' => [
                $this->arrayToObject([
                    'Id' => 1,
                    'Text' => 'This is text',
                    'User' => [
                        'Id' => 1
                    ]
                ]),
                $this->arrayToObject([
                    'Id' => 2,
                    'Text' => 'This is more text',
                    'User' => [
                        'Id' => 1
                    ]
                ])
            ],
            'http://localhost:8000/api/comments/1' => $this->arrayToObject([
                'Id' => 1,
                'Text' => 'This is text',
                'User' => [
                    'Id' => 1,
                    'Details' => [
                        'Name' => 'Fred Smith',
                        'Thing' => 203320
                    ]
                ]
            ]),
            'http://localhost:8000/api/single/1' => 'test string'
        ];
    }
    
    protected function loadInvalidResponses()
    {
        $this->responses = [
            'http://localhost:8000/api/users/' => [
                $this->arrayToObject([
                    'Id' => '1'
                ]),
                $this->arrayToObject([
                    'Id' => 2
                ])
            ],
            'http://localhost:8000/api/users/1' => $this->arrayToObject([
                'Id' => 1,
                'Details' => [
                    'Name' => 'Fred Smith',
                    'Thing' => '203320'
                ]
            ]),
            'http://localhost:8000/api/comments/' => [
                $this->arrayToObject([
                    'Id' => 1,
                    'Text' => 'This is text'
                ]),
                $this->arrayToObject([
                    'Id' => 2,
                    'Text' => 'This is more text',
                    'User' => [
                        'Id' => 1
                    ]
                ])
            ],
            'http://localhost:8000/api/comments/1' => $this->arrayToObject([
                'Id' => '1',
                'Text' => 'This is text',
                'User' => [
                    'Id' => 1,
                    'Details' => [
                        'Name' => 'Fred Smith',
                        'Thing' => 203320
                    ]
                ]
            ]),
            'http://localhost:8000/api/single/1' => 123
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
