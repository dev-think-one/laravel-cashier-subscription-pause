<?php

namespace CashierSubscriptionPause\Tests\Unit;

use Carbon\Carbon;
use CashierSubscriptionPause\Eloquent\WithPauseCollection;
use CashierSubscriptionPause\Tests\Fixtures\Subscription;
use CashierSubscriptionPause\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsesPauseCollectionTest extends TestCase
{
    use RefreshDatabase;

    protected function createSubscription(array $options = [])
    {
        return Subscription::find(Subscription::factory()->create($options)->getKey());
    }

    /** @test */
    public function pause_collection_is_null()
    {
        /** @var WithPauseCollection $subscription */
        $subscription = $this->createSubscription();

        $this->assertFalse($subscription->paused());
        $this->assertTrue($subscription->notPaused());
        $this->assertNull($subscription->pauseResumesAtTimestamp());
        $this->assertNull($subscription->pauseResumesAt());

        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertTrue($subscription->notPaused(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertNull($subscription->pauseResumesAtTimestamp(WithPauseCollection::BEHAVIOR_VOID));
        $this->assertNull($subscription->pauseResumesAt(WithPauseCollection::BEHAVIOR_VOID));

        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertTrue($subscription->notPaused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertNull($subscription->pauseResumesAtTimestamp(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));
        $this->assertNull($subscription->pauseResumesAt(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE));

        $this->assertFalse($subscription->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertTrue($subscription->notPaused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertNull($subscription->pauseResumesAtTimestamp(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
        $this->assertNull($subscription->pauseResumesAt(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT));
    }

    /** @test */
    public function paused_scope()
    {
        $resumesAt = Carbon::now()->addWeek();
        Subscription::factory()->count(4)->create([ 'pause_collection' => null ]);
        Subscription::factory()->count(5)->create([
            'pause_collection' => json_encode([
                'behavior' => '',
            ]),
        ]);
        Subscription::factory()->count(6)->create([
            'pause_collection' => json_encode([
                'behavior' => null,
            ]),
        ]);
        Subscription::factory()->count(7)->create([
            'pause_collection' => json_encode([
                'behavior' => WithPauseCollection::BEHAVIOR_VOID,
            ]),
        ]);
        Subscription::factory()->count(8)->create([
            'pause_collection' => json_encode([
                'behavior'   => WithPauseCollection::BEHAVIOR_VOID,
                'resumes_at' => $resumesAt->timestamp,
            ]),
        ]);
        Subscription::factory()->count(9)->create([
            'pause_collection' => json_encode([
                'behavior' => WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE,
            ]),
        ]);
        Subscription::factory()->count(10)->create([
            'pause_collection' => json_encode([
                'behavior'   => WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE,
                'resumes_at' => $resumesAt->timestamp,
            ]),
        ]);
        Subscription::factory()->count(11)->create([
            'pause_collection' => json_encode([
                'behavior' => WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT,
            ]),
        ]);
        Subscription::factory()->count(12)->create([
            'pause_collection' => json_encode([
                'behavior'   => WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT,
                'resumes_at' => $resumesAt->timestamp,
            ]),
        ]);

        $this->assertEquals(4 + 5 + 6 + 7 + 8 + 9 + 10 + 11 + 12, Subscription::query()->count());

        $this->assertEquals(7 + 8 + 9 + 10 + 11 + 12, Subscription::query()->paused()->count());
        $this->assertEquals(4 + 5 + 6, Subscription::query()->notPaused()->count());

        $this->assertEquals(7 + 8, Subscription::query()->paused(WithPauseCollection::BEHAVIOR_VOID)->count());
        $this->assertEquals(4 + 5 + 6 + 9 + 10 + 11 + 12, Subscription::query()->notPaused(WithPauseCollection::BEHAVIOR_VOID)->count());

        $this->assertEquals(9 + 10, Subscription::query()->paused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE)->count());
        $this->assertEquals(4 + 5 + 6 + 7 + 8 + 11 + 12, Subscription::query()->notPaused(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE)->count());

        $this->assertEquals(11 + 12, Subscription::query()->paused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT)->count());
        $this->assertEquals(4 + 5 + 6 + 7 + 8 + 9 + 10, Subscription::query()->notPaused(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT)->count());
    }
}
