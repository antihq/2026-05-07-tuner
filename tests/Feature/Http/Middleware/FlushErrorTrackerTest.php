<?php

use App\Http\Middleware\FlushErrorTracker;
use App\Services\ErrorTracker;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

test('handle passes request through', function () {
    $middleware = new FlushErrorTracker;
    $request = Request::create('/test');
    $response = new Response;

    $result = $middleware->handle($request, fn () => $response);

    expect($result)->toBe($response);
});

test('terminate calls flush on error tracker', function () {
    $mock = Mockery::mock(ErrorTracker::class);
    $mock->shouldReceive('flush')->once();
    $this->app->instance(ErrorTracker::class, $mock);

    $middleware = new FlushErrorTracker;
    $request = Request::create('/test');
    $response = new Response;

    $middleware->terminate($request, $response);
});
