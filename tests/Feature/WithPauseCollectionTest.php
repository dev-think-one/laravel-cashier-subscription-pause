<?php

namespace CashierSubscriptionPause\Tests\Feature;

use Carbon\Carbon;
use CashierSubscriptionPause\Eloquent\WithPauseCollection;
use CashierSubscriptionPause\Tests\Fixtures\Subscription;

class WithPauseCollectionTest extends FeatureTestCase
{

    /** @test */
    public function subscriptions_can_be_paused_with_stripe()
    {
        $user = $this->createCustomer(__FUNCTION__);

        // Create Subscription
        $user->newSubscription('main', static::$stripeTestProxy->priceId('main'))->create('pm_card_visa');

        $this->assertEquals(1, count($user->subscriptions));

        /** @var Subscription $subscription */
        $subscription = $user->subscription('main');

        $this->assertFalse($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertNull($subscription->pauseResumesAtTimestamp());
        $this->assertNull($subscription->pauseResumesAt());

        $subscription->syncStripePauseCollection();

        $this->assertFalse($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertNull($subscription->pauseResumesAtTimestamp());
        $this->assertNull($subscription->pauseResumesAt());

        $resumesAt = Carbon::now()->addWeek();
        $subscription->pause(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE, $resumesAt);

        $this->assertTrue($subscription->paused());
        $this->assertTrue($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAtTimestamp());
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAt()->timestamp);

        $subscription->unpause();

        $this->assertFalse($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertNull($subscription->pauseResumesAtTimestamp());
        $this->assertNull($subscription->pauseResumesAt());

        $resumesAt = Carbon::now()->addDays();
        $subscription->pauseBehaviorMarkUncollectible($resumesAt);

        $this->assertTrue($subscription->paused());
        $this->assertTrue($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAtTimestamp());
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAt()->timestamp);

        $resumesAt = Carbon::now()->addDays(2);
        $subscription->pauseBehaviorKeepAsDraft($resumesAt);

        $this->assertTrue($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertTrue($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAtTimestamp());
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAt()->timestamp);

        $subscription->pauseBehaviorVoid();

        $this->assertTrue($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertTrue($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertNull($subscription->pauseResumesAtTimestamp());
        $this->assertNull($subscription->pauseResumesAt());

        $resumesAt = Carbon::now()->addDays(3);
        $subscription->pauseBehaviorKeepAsDraft($resumesAt);

        $this->assertTrue($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertTrue($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAtTimestamp());
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAt()->timestamp);

        $subscription->pause_collection = null;
        $subscription->save();

        $this->assertFalse($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertNull($subscription->pauseResumesAtTimestamp());
        $this->assertNull($subscription->pauseResumesAt());

        $subscription->syncStripePauseCollection();

        $this->assertTrue($subscription->paused());
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertTrue($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAtTimestamp());
        $this->assertEquals($resumesAt->timestamp, $subscription->pauseResumesAt()->timestamp);
    }

    /** @test */
    public function subscriptions_can_not_be_paused_if_canceled()
    {
        $user = $this->createCustomer(__FUNCTION__);

        // Create Subscription
        $user->newSubscription('main', static::$stripeTestProxy->priceId('main'))->create('pm_card_visa');

        $this->assertEquals(1, count($user->subscriptions));

        /** @var Subscription $subscription */
        $subscription = $user->subscription('main');

        $subscription->cancel();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unable to pause subscription that is cancelled.');
        $subscription->pauseBehaviorKeepAsDraft();
    }

    /** @test */
    public function subscriptions_can_not_be_unpaused_if_not_paused()
    {
        $user = $this->createCustomer(__FUNCTION__);

        // Create Subscription
        $user->newSubscription('main', static::$stripeTestProxy->priceId('main'))->create('pm_card_visa');

        $this->assertEquals(1, count($user->subscriptions));

        /** @var Subscription $subscription */
        $subscription = $user->subscription('main');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unable to unpause subscription that is not paused.');
        $subscription->unpause();
    }
}
