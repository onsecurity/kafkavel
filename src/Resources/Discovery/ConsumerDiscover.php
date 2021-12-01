<?php

namespace OnSecurity\Kafkavel\Resources\Discovery;

use OnSecurity\Kafkavel\Resources\Contracts\KafkavelConsumer;

class ConsumerDiscover extends Discover
{
    protected static function getDirectories(): array
    {
        return collect(config('kafkavel.consumer_discovery.directories'))->map(fn($dir) => base_path($dir))->toArray();
    }

    protected static function getInterface(): string
    {
        return KafkavelConsumer::class;
    }

    protected static function getConfigItems(): array
    {
        return config('kafkavel.consumers');
    }

    protected static function isDiscoverEnabled(): bool
    {
        return config('kafkavel.consumer_discovery.enabled');
    }
}
