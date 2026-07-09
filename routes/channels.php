<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('store.{storeId}.orders', function($user, int $storeId) {
    return true;
});

Broadcast::channel('store.{storeId}.notifications', function ($user, int $storeId) {
    return true;
});
