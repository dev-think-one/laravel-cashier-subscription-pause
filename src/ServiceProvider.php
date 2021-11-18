<?php

namespace CashierSubscriptionPause;

use CashierSubscriptionPause\Listeners\CashierWebhookHandledEventListener;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations/add_pause_collection_to_subscriptions_table.php.stub' => database_path('migrations/'. date('Y_m_d_His_') .'add_pause_collection_to_subscriptions_table.php'),
            ], 'migrations');


            $this->commands([
                //
            ]);
        }

        \Illuminate\Support\Facades\Event::listen(
            \Laravel\Cashier\Events\WebhookHandled::class,
            CashierWebhookHandledEventListener::class
        );
    }

    public function register()
    {
    }
}
