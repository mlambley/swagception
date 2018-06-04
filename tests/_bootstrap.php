<?php
include __DIR__.'/../vendor/autoload.php'; // composer autoload

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'appDir' => __DIR__ . '/../',
    'cacheDir' => __DIR__ . '/../var/aspectmock',
    'debug' => true,
    'includePaths' => [__DIR__.'/../src'],
    'excludePaths' => [__DIR__]
]);

?>