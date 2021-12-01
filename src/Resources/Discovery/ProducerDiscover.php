<?php

namespace OnSecurity\Kafkavel\Resources\Discovery;

use Illuminate\Support\Collection;
use OnSecurity\Kafkavel\Resources\Contracts\KafkavelProducer;

class ProducerDiscover extends Discover
{
    public static function discoverEventClasses(): Collection
    {
        return self::discover()->map(fn($producerClass) => $producerClass::getEventClass())->unique();
    }

    protected static function getDirectories(): array
    {
        return collect(config('kafkavel.producer_discovery.directories'))->map(fn($dir) => base_path($dir))->toArray();
    }

    protected static function getInterface(): string
    {
        return KafkavelProducer::class;
    }

    protected static function getConfigItems(): array
    {
        return config('kafkavel.producers');
    }

    protected static function isDiscoverEnabled(): bool
    {
        return config('kafkavel.producer_discovery.enabled');
    }
}
