<?php

namespace OnSecurity\Kafkavel\Test\Support;

use Junges\Kafka\Contracts\KafkaConsumerMessage;
use OnSecurity\Kafkavel\Resources\Contracts\KafkavelConsumer;
use OnSecurity\Kafkavel\Resources\Contracts\KafkavelConsumerMessage;

class TestConsumer implements KafkavelConsumer
{
    public bool $handled = false;

    public function __construct(protected KafkavelConsumerMessage $message)
    {

    }

    static public function getTopic(): string
    {
        return 'testtopic';
    }

    static public function getSchema(): string
    {
        return 'Test';
    }

    static public function getSchemaVersion(): int|string|array
    {
        return 1;
    }

    public function handle()
    {
        return 'TestConsumerHandled';
    }


}
