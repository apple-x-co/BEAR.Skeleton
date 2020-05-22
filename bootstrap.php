<?php
namespace BEAR\Skeleton;

use BEAR\Skeleton\Module\App;
use BEAR\Sunday\Extension\Application\AppInterface;

return
    /**
     * @param array{_GET: array<string, string>, _POST: array<string, string>}               $globals $GLOBALS
     * @param array{REQUEST_URI: string, REQUEST_METHOD: string, HTTP_CONTENT_TYPE?: string} $server  $_SERVER
     */
    function (string $context, array $globals, array $server) : int {
        $app = (Injector::getInstance($context))->getInstance(AppInterface::class);
        assert($app instanceof App);
        if ($app->httpCache->isNotModified($server)) {
            $app->httpCache->transfer();

            return 1;
        }
        $request = $app->router->match($globals, $server);
        try {
            $response = $app->resource->{$request->method}->uri($request->path)($request->query);
            $response->transfer($app->responder, $server);

            return 0;
        } catch (\Exception $e) {
            $app->error->handle($e, $request)->transfer();

            return 1;
        }
    };
