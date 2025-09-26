<?php

use App\Http\Controllers\V1\ItemAttachmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware([
        'auth:sanctum',
        'device.ctx',
        'device.throttle:per-device',
        'correlate',
        'store_member'
    ])
    ->group(function () {
        /** -------------- Ingredients -------------- */
        Route::get('/items/{item}/ingredients', [ItemAttachmentController::class, 'indexIngredients'])
            ->name('item_attachments.ingredients.index');

        Route::post('/items/{item}/ingredients', [ItemAttachmentController::class, 'attachIngredients'])
            ->name('item_attachments.ingredients.attach');

        Route::delete('/items/{item}/ingredients/{ingredient}', [ItemAttachmentController::class, 'detachIngredient'])
            ->name('item_attachments.ingredients.detach');

        /** -------------- Options -------------- */
        Route::get('/items/{item}/options', [ItemAttachmentController::class, 'indexOptions'])
            ->name('item_attachments.options.index');

        Route::post('/items/{item}/options', [ItemAttachmentController::class, 'attachOptions'])
            ->name('item_attachments.options.attach');

        Route::delete('/items/{item}/options/{option}', [ItemAttachmentController::class, 'detachOption'])
            ->name('item_attachments.options.detach');

        /** -------------- Options List -------------- */
        Route::get('/items/{item}/options-lists', [ItemAttachmentController::class, 'indexOptionLists'])
            ->name('item_attachments.options_lists.index');

        Route::post('/items/{item}/options-lists', [ItemAttachmentController::class, 'attachOptionLists'])
            ->name('item_attachments.options_lists.attach');

        Route::delete('/items/{item}/options-lists/{option_list}', [ItemAttachmentController::class, 'detachOptionList'])
            ->name('item_attachments.options_lists.detach');
    });
