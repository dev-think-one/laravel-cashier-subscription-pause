<?php

namespace CashierSubscriptionPause\Tests;

use CashierSubscriptionPause\Tests\Fixtures\Subscription;
use CashierSubscriptionPause\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\CashierServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Symfony\Component\Finder\Finder;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    public function defineDatabaseMigrations()
    {
        if (!class_exists('TestbenchCreateUsersTable')) {
            $app = app();
            File::copyDirectory($app->basePath('migrations'), $app->databasePath('migrations'));
        }
        $finder = new Finder();
        $finder->files()
               ->in(database_path('migrations'))
               ->name('*_add_pause_collection_to_subscriptions_table.php');
        if (!$finder->hasResults()) {
            $this->artisan('vendor:publish', [
                '--provider' => 'CashierSubscriptionPause\ServiceProvider',
                '--tag'      => 'migrations',
            ]);
        }
    }


    protected function getPackageProviders($app)
    {
        return [
            CashierServiceProvider::class,
            \CashierSubscriptionPause\ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        if (!env('SCRUTINIZER') && !env('USE_MYSQL')) {
            // Setup default database to use sqlite :memory:
            $app['config']->set('database.default', 'testbench');
            $app['config']->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
        }

        Cashier::useCustomerModel(User::class);
        Cashier::useSubscriptionModel(Subscription::class);
    }
}
