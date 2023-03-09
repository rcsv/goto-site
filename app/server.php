<?php
// server file for Swoole

use Swoole\Http\Server ;
use Swoole\Http\Response ;
use Swoole\Http\Request ;


$server = new Server('0.0.0.0', 80);

// HOOK
$server->set([
    'hook_flags' => SWOOLE_HOOK_ALL,
    'enable_reuse_port' => true,
]);

// Request
$server->on('request', function (Request $request, Response $response){

    $response->end("<h1>Hello WORLD</h1>");
});
$server->start();

