<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return [

    'enabled' => env('ERROR_TRACKER_ENABLED', ! in_array(env('APP_ENV'), ['local', 'testing'])),

    'url' => env('ERROR_TRACKER_URL'),

    'excluded_exceptions' => [
        NotFoundHttpException::class,
        HttpException::class,
        ValidationException::class,
        AuthenticationException::class,
        AuthorizationException::class,
    ],

];
