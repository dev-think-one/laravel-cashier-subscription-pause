<?php

namespace CashierSubscriptionPause\Tests\Fixtures;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

class StripeTestProxy
{
    /**
     * @var string
     */
    protected string $filePath;

    /**
     * @var array
     */
    protected array $stripeTestConfig;

    public function __construct(string $filePath)
    {
        $this->filePath         = $filePath;

        if (!file_exists($this->filePath)) {
            echo "Creating proxy cache file [{ $this->filePath}]" . PHP_EOL;
            file_put_contents($this->filePath, '{}');
        }

        $this->stripeTestConfig = json_decode(file_get_contents(($this->filePath)), true);

        $this->putEnvIfSpecified();
    }

    public function store()
    {
        file_put_contents($this->filePath, json_encode($this->stripeTestConfig, JSON_PRETTY_PRINT));
    }

    protected function putEnvIfSpecified(): void
    {
        if (!empty($this->stripeTestConfig['env']) && is_array($this->stripeTestConfig['env'])) {
            foreach ($this->stripeTestConfig['env'] as $envKey => $envVal) {
                putenv("{$envKey}={$envVal}");
            }
        }
    }

    public function stripe(array $options = []): StripeClient
    {
        return Cashier::stripe(array_merge([ 'api_key' => getenv('STRIPE_SECRET') ], $options));
    }

    protected function getResult(string $key, string $method, \Closure $callback)
    {
        $fullKey = "{$key}." . Str::snake($method);
        $id      = Arr::get($this->stripeTestConfig, $fullKey);
        if (!$id) {
            $id = $callback();
            Arr::set($this->stripeTestConfig, $fullKey, $id);
        }

        return $id;
    }

    public function productId(string $key = 'default')
    {
        return $this->getResult($key, __FUNCTION__, function () {
            return $this->stripe()->products->create([
                'name' => 'Laravel Cashier Test Product',
                'type' => 'service',
            ])->id;
        });
    }

    public function priceId(string $key = 'default')
    {
        return $this->getResult($key, __FUNCTION__, function () use ($key) {
            return $this->stripe()->prices->create([
                'product'        => $this->productId($key),
                'nickname'       => 'Monthly $10',
                'currency'       => 'USD',
                'recurring'      => [
                    'interval' => 'month',
                ],
                'billing_scheme' => 'per_unit',
                'unit_amount'    => 1000,
            ])->id;
        });
    }
}
