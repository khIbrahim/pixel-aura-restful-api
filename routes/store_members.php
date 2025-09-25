<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\StoreMemberAuthController;
use App\Http\Controllers\V1\StoreMembersController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Store Members - Ressources standards
        |--------------------------------------------------------------------------
        | Routes principales pour la gestion des membres
        |
        */
        Route::apiResource('stores.members', StoreMembersController::class)
            ->parameters(['members' => 'store_member', 'stores' => 'store'])
            ->whereNumber(['store', 'store_member'])
            ->middleware('ability:' . StoreTokenAbilities::MEMBERS_READ);

        /*
        |--------------------------------------------------------------------------
        | Store Members - Opérations avancées
        |--------------------------------------------------------------------------
        | Routes pour les opérations spécifiques aux membres
        |
        */
        Route::controller(StoreMembersController::class)
            ->middleware('ability:' . StoreTokenAbilities::MEMBERS_READ)
            ->prefix('stores/{store}')
            ->whereNumber('store')
            ->group(function () {
                Route::post('members/import', 'import')
                    ->middleware('ability:members:create')
                    ->name('store-members.import');

                Route::get('members/export', 'export')
                    ->middleware('ability:exports:create')
                    ->name('store-members.export');

                Route::get('exports/{jobId}/download', 'download')
                    ->middleware('ability:exports:read')
                    ->name('store-members.exports.download');

                // Filtrage et recherche avancée
                Route::get('members/search', 'search')
                    ->name('store-members.search');
            });

        /*
        |--------------------------------------------------------------------------
        | Store Members - Opérations individuelles
        |--------------------------------------------------------------------------
        | Routes pour les opérations sur un membre spécifique
        |
        */
        Route::controller(StoreMembersController::class)
            ->prefix('store-members')
            ->group(function () {
                // Gestion des membres (suppression, restauration)
                Route::delete('{store_member}', 'destroy')
                    ->middleware('ability:' . StoreTokenAbilities::MEMBERS_DELETE)
                    ->name('store-members.destroy')
                    ->whereNumber('store_member');

                Route::delete('{id}/force-destroy', 'forceDestroy')
                    ->middleware('ability:' . StoreTokenAbilities::MEMBERS_DELETE)
                    ->name('store-members.force-destroy');

                Route::post('{id}/restore', 'restore')
                    ->middleware('ability:' . StoreTokenAbilities::MEMBERS_UPDATE)
                    ->name('store-members.restore');

                Route::get('{store_member}/abilities', 'listAbilities')
                    ->middleware('ability:members.read')
                    ->name('store-members.abilities')
                    ->whereNumber('store_member');

                Route::get('{store_member}/audit', 'audit')
                    ->middleware('ability:audit:read')
                    ->name('store-members.audit')
                    ->whereNumber('store_member');

                Route::get('{store_member}/stats', 'stats')
                    ->middleware('ability:analytics:read')
                    ->name('store-members.stats')
                    ->whereNumber('store_member');
            });

        /*
        |--------------------------------------------------------------------------
        | Store Member Authentication
        |--------------------------------------------------------------------------
        | Routes pour l'authentification des membres
        |
        */
        Route::controller(StoreMemberAuthController::class)
            ->group(function () {
                // Authentification
                Route::post('stores/{store}/members/{store_member}/authenticate', 'authenticate')
                    ->middleware(['ability:' . StoreTokenAbilities::MEMBERS_AUTH, 'throttle:pin'])
                    ->name('store-members.authenticate')
                    ->whereNumber(['store', 'store_member']);

                Route::post('members/logout', 'logout')
                    ->middleware(['auth.store_member:' . StoreTokenAbilities::MEMBERS_LOGOUT])
                    ->name('store-members.logout');

                Route::get('members/me', 'me')
                    ->middleware('auth.store_member:' . StoreTokenAbilities::MEMBERS_READ)
                    ->name('store-members.me');
            });
    });
