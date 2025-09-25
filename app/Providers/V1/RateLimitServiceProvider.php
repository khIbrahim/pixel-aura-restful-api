<?php

namespace App\Providers\V1;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('per-device', function (Request $request) {
            $device = $request->attributes->get('device');
            $key = $device ? 'dev:'.$device->id : 'ip:'.$request->ip();

            return Limit::perMinute(120)->by($key);
        });
    }
}
