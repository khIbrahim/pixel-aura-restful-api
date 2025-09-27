<?php

use App\Http\Middleware\V1\CheckAbility;
use App\Http\Middleware\V1\CorrelateRequest;
use App\Http\Middleware\V1\EnsureDeviceContext;
use App\Http\Middleware\V1\EnsureStoreMember;
use App\Http\Middleware\V1\Media\HasMediaMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/api.php',
            __DIR__.'/../routes/store_members.php',
            __DIR__.'/../routes/items.php',
            __DIR__.'/../routes/categories.php',
            __DIR__.'/../routes/media.php',
            __DIR__.'/../routes/ingredients.php',
            __DIR__.'/../routes/options.php',
            __DIR__.'/../routes/option_lists.php',
            __DIR__.'/../routes/item_attachments.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'device.ctx'      => EnsureDeviceContext::class,
            'device.throttle' => ThrottleRequests::class,
            'correlate'       => CorrelateRequest::class,
            'ability'         => CheckAbility::class,
            'store_member'    => EnsureStoreMember::class,
            'has_media'      => HasMediaMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
