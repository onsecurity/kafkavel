<?php

namespace OnSecurity\Kafkavel\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use OnSecurity\Kafkavel\Commands\KafkavelConsume;
use OnSecurity\Kafkavel\Resources\Discovery\ProducerDiscover;
use OnSecurity\Kafkavel\Resources\Producers\ProducerEventHandler;
use OnSecurity\Kafkavel\Tests\Support\TestProducerEvent;

class KafkavelServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php' , 'kafkavel'
        );

        $this->publishes([__DIR__ . '/../../config/config.php' => config_path('kafkavel.php')], 'laravel-kafkavel');

        if ($this->app->runningInConsole()) {
            $this->commands([
                KafkavelConsume::class,
            ]);
        }

        foreach (ProducerDiscover::discoverEventClasses() as $eventName) {
            Event::listen($eventName, fn($event) => (new ProducerEventHandler($event))->handle());
        }

        Event::listen(TestProducerEvent::class, fn($event) => (new ProducerEventHandler($event))->handle());
    }
}
