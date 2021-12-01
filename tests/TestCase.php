<?php

namespace OnSecurity\Kafkavel\Test;

use Illuminate\Support\Facades\File;
use OnSecurity\Kafkavel\Providers\KafkavelServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        File::copyDirectory(realpath(__DIR__ . '/Support'), base_path('OnSecurity/Kafkavel/Test/Support'));
    }
    
    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('kafkavel.enabled', true);
        $app['config']->set('kafkavel.producers', []);
        $app['config']->set('kafkavel.producer_discovery.enabled', false);
        $app['config']->set('kafkavel.producer_discovery.directories', []);
        $app['config']->set('kafkavel.consumers', []);
        $app['config']->set('kafkavel.consumer_discovery.enabled', false);
        $app['config']->set('kafkavel.consumer_discovery.directories', []);

        $app['config']->set('kafka.brokers', 'localhost:9092');
        $app['config']->set('kafka.consumer_group_id', 'group');
    }
    
    protected function getPackageProviders($app)
    {
        return [
            KafkavelServiceProvider::class,
        ];
    }
}