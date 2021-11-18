<?php

namespace CashierSubscriptionPause\Tests\Fixtures;

use CashierSubscriptionPause\Eloquent\UsesPauseCollection;
use CashierSubscriptionPause\Eloquent\WithPauseCollection;

class Subscription extends \Laravel\Cashier\Subscription implements WithPauseCollection
{
    use UsesPauseCollection;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity'         => 'integer',
        'pause_collection' => 'array',
    ];
}
