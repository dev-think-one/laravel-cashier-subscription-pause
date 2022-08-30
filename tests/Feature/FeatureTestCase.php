<?php

namespace CashierSubscriptionPause\Tests\Feature;

use CashierSubscriptionPause\Tests\Fixtures\StripeTestProxy;
use CashierSubscriptionPause\Tests\Fixtures\User;
use CashierSubscriptionPause\Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    /**
     * @var StripeTestProxy
     */
    protected static StripeTestProxy $stripeTestProxy;

    public static function setUpBeforeClass(): void
    {
        static::$stripeTestProxy = new StripeTestProxy(__DIR__ . '/../.conf/stripe.config.json');

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$stripeTestProxy->store();
    }

    public function setUp(): void
    {
        if (!getenv('STRIPE_SECRET')) {
            $this->markTestSkipped('Stripe secret key not set.');
        }

        parent::setUp();
    }

    protected function createCustomer($description = 'taylor', array $options = []): User
    {
        return User::create(array_merge([
            'email'    => "{$description}@cashier-test.com",
            'name'     => 'Taylor Otwell',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ], $options));
    }
}
