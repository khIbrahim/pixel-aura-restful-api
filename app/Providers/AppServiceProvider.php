<?php

namespace App\Providers;

use App\Contracts\V1\Media\MediaManagerInterface;
use App\Models\V1\Auth\PersonalAccessToken;
use App\Models\V1\Device;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use App\Providers\V1\CategoryServiceProvider;
use App\Providers\V1\ItemServiceProvider;
use App\Providers\V1\RateLimitServiceProvider;
use App\Providers\V1\StoreMemberAuthServiceProvider;
use App\Providers\V1\StoreMemberServiceProvider;
use App\Providers\V1\StoreServiceProvider;
use App\Services\V1\Media\ImageProcessor;
use App\Services\V1\Media\MediaManager;
use App\Services\V1\Media\MediaUrlGenerator;
use App\Services\V1\Media\MediaValidator;
use App\Services\V1\Media\UrlImageDownloader;
use Illuminate\Cache\RateLimiting\Limit;
//use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(StoreServiceProvider::class);
        $this->app->register(CategoryServiceProvider::class);
        $this->app->register(ItemServiceProvider::class);
        $this->app->register(StoreMemberServiceProvider::class);
        $this->app->register(StoreMemberAuthServiceProvider::class);
        $this->app->register(RateLimitServiceProvider::class);

        $this->app->bind(
            MediaManagerInterface::class,
            MediaManager::class
        );

        $this->app->singleton(MediaValidator::class);
        $this->app->singleton(UrlImageDownloader::class);
        $this->app->singleton(ImageProcessor::class);
        $this->app->singleton(MediaUrlGenerator::class);
    }

    public function boot(): void
    {
        RateLimiter::for('pin', function (Request $request): Limit {
            $key = sprintf("pin|%s|%s", $request->ip(), $request->attributes->get('store_member')?->id ?? 'guest');

            return Limit::perMinute(5)->by($key);
        });

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Sanctum::authenticateAccessTokensUsing(function (PersonalAccessToken $token, bool $isValid) {
            if (! $isValid) {
                return false;
            }

            $store = Store::query()
                ->whereKey($token->store_id)
                ->where('is_active', true)
                ->first();
            if (! $store) {
                return false;
            }

            $device = Device::query()
                ->whereKey($token->device_id)
                ->where('store_id', $store->id)
                ->where('is_active', true)
                ->first();
            if (! $device) {
                return false;
            }

            if ($token->store_member_id){
                $storeMember = StoreMember::query()
                    ->whereKey($token->store_member_id)
                    ->where('is_active', true)
                    ->where('store_id', $store->id)
                    ->first();
                if(! $storeMember){
                    return false;
                }

                app('request')->attributes->set('store_member', $storeMember);
            }

            app('request')->attributes->set('store', $store);
            app('request')->attributes->set('device', $device);

            app('request')->attributes->set('device_key', 'dev:'.$device->id);

            return true;
        });
    }
}
