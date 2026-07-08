<?php

use App\Models\V1\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('store.{storeId}.orders', function(User $user, int $storeId) {
    return $user->memberStores()
        ->where('store_id', $storeId)
        ->exists();
});
