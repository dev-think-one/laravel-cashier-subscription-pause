# Support of Pausing and Resuming a Subscription.

![Packagist License](https://img.shields.io/packagist/l/think.studio/laravel-cashier-subscription-pause?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/think.studio/laravel-cashier-subscription-pause)](https://packagist.org/packages/think.studio/laravel-cashier-subscription-pause)
[![Total Downloads](https://img.shields.io/packagist/dt/think.studio/laravel-cashier-subscription-pause)](https://packagist.org/packages/think.studio/laravel-cashier-subscription-pause)
[![Build Status](https://scrutinizer-ci.com/g/dev-think-one/laravel-cashier-subscription-pause/badges/build.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-cashier-subscription-pause/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/dev-think-one/laravel-cashier-subscription-pause/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-cashier-subscription-pause/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dev-think-one/laravel-cashier-subscription-pause/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-cashier-subscription-pause/?branch=main)

## Installation

Install the package via composer:

```shell
composer require think.studio/laravel-cashier-subscription-pause
```

You can publish the migrations files with:

```shell
php artisan vendor:publish --provider="CashierSubscriptionPause\ServiceProvider" --tag="migrations"
```

## Usage

```php
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

```php
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

```php
Subscription::query()->paused()->count();
Subscription::query()->notPaused()->count();

Subscription::query()->paused(WithPauseCollection::BEHAVIOR_VOID)->count();
Subscription::query()->notPaused(WithPauseCollection::BEHAVIOR_VOID)->count();

Subscription::query()->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE)->count();
Subscription::query()->notPaused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE)->count();

Subscription::query()->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT)->count();
Subscription::query()->notPaused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT)->count();
```

Do not use default listener:

```php
<?php

namespace App\Providers;

use CashierSubscriptionPause\Listeners\CashierWebhookHandledEventListener;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
       // ...
        CashierWebhookHandledEventListener::deactivateListener();
    }
}
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
