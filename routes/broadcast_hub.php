<?php

use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Route;
use Seatplus\BroadcastHub\Http\Controllers\BroadcastController;
use Seatplus\BroadcastHub\Http\Controllers\ChannelController;
use Seatplus\BroadcastHub\Http\Controllers\GlobalSubscriptionController;
use Seatplus\BroadcastHub\Http\Controllers\RecipientController;
use Seatplus\BroadcastHub\Http\Controllers\SubscriptionController;

Route::middleware(['web', 'auth', Authorize::using('view broadcasts hub')])
    ->prefix('broadcast-hub')
    ->group(function () {
        Route::controller(BroadcastController::class)
            ->name('broadcasts.')
            ->group(function () {

                Route::get('/', 'index')
                    ->name('index');

                Route::get('/{broadcaster}', 'show')
                    ->name('show');

                Route::middleware(Authorize::using('can enable broadcasts channel'))
                    ->group(function () {

                        Route::post('/', 'store')
                            ->name('store');

                        Route::delete('/{broadcaster}', 'destroy')
                            ->name('destroy');
                    });

            });

        Route::get('/{broadcaster}/recipients', RecipientController::class)
            ->name('recipients.show');

        Route::controller(ChannelController::class)
            ->middleware(Authorize::using('manage broadcasts channel'))
            ->name('channels.')
            ->group(function () {

                Route::get('/{broadcaster}/channels', 'index')
                    ->name('index');

                Route::put('/{broadcaster}/channels', 'store')
                    ->name('store');
            });

        Route::controller(SubscriptionController::class)
            ->name('subscriptions.')
            ->prefix('subscriptions')
            ->group(function () {

                Route::get('/{broadcaster_id}/notifications', 'index')
                    ->name('index');

                Route::get('{notification_class}/subscriptions/{recipient_id}', 'show')
                    ->name('show');

                Route::post('/', 'store')
                    ->name('store');

                Route::delete('/{subscription_id}', 'destroy')
                    ->name('destroy');
            });

        Route::controller(GlobalSubscriptionController::class)
            ->name('global-subscriptions.')
            ->prefix('global-subscriptions')
            ->group(function () {

                Route::get('/broadcaster/{broadcaster_id}/recipient/{recipient_id}', 'index')
                    ->name('index');
            });
    });
