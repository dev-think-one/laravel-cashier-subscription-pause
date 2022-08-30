<?php

namespace CashierSubscriptionPause\Listeners;

use CashierSubscriptionPause\Eloquent\WithPauseCollection;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class CashierWebhookHandledEventListener
{
    /**
     * Deactivate default package listener.
     *
     * @var bool
     */
    public static bool $deactivateListener = false;

    /**
     * Webhook types.
     *
     * See https://stripe.com/docs/api/events/types
     *
     * @var array
     */
    public static array $listenTypes = [
        'customer.subscription.created',
        'customer.subscription.updated',
    ];

    /**
     * Deactivate listener.
     *
     * @return static
     */
    public static function deactivateListener(bool $inactive = true)
    {
        static::$deactivateListener = $inactive;

        return new static;
    }

    /**
     * Handle received Stripe webhooks.
     *
     * @param \Laravel\Cashier\Events\WebhookHandled $event
     *
     * @psalm-suppress UndefinedDocblockClass
     *
     * @return void
     */
    public function handle(WebhookHandled $event)
    {
        if (static::$deactivateListener) {
            return;
        }

        $payload = $event->payload;

        if (in_array($payload['type'], static::$listenTypes)) {
            if ($user = Cashier::findBillable($payload['data']['object']['customer'])) {
                $data = $payload['data']['object'];

                /** @var WithPauseCollection $subscription */
                $subscription = $user->subscriptions()
                                     ->where('stripe_id', $data['id'])
                                     ->first();

                if ($subscription instanceof WithPauseCollection) {
                    $subscription->syncStripePauseCollection();
                }
            }
        }
    }
}
