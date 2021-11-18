# Support of Pausing and Resuming a Subscription.

[![Packagist License](https://img.shields.io/packagist/l/yaroslawww/laravel-cashier-subscription-pause?color=%234dc71f)](https://github.com/yaroslawww/laravel-cashier-subscription-pause/blob/master/LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/yaroslawww/laravel-cashier-subscription-pause)](https://packagist.org/packages/yaroslawww/laravel-cashier-subscription-pause)

## Installation

Install the package via composer:

```bash
composer require yaroslawww/laravel-cashier-subscription-pause
```

You can publish the migrations files with:

```bash
php artisan vendor:publish --provider="CashierSubscriptionPause\ServiceProvider" --tag="migrations"
```

## Usage

```injectablephp
use CashierSubscriptionPause\Eloquent\UsesPauseCollection;
use CashierSubscriptionPause\Eloquent\WithPauseCollection;

class Subscription extends \Laravel\Cashier\Subscription implements WithPauseCollection {
    
    use UsesPauseCollection;

    protected $table = 'stripe_subscriptions';

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
```

```injectablephp
$subscription = $user->subscription( 'main' );

$resumesAt = Carbon::now()->addWeek();
$subscription->pause(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE, $resumesAt);
$subscription->unpause();

$subscription->pauseBehaviorMarkUncollectible($resumesAt);
$subscription->pauseBehaviorKeepAsDraft($resumesAt);
$subscription->pauseBehaviorVoid($resumesAt);

$subscription->syncStripePauseCollection();

$subscription->paused());
$subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE);
$subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT);
$subscription->paused(WithPauseCollection::BEHAVIOR_VOID);
$subscription->pauseResumesAtTimestamp();
$subscription->pauseResumesAt();
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
