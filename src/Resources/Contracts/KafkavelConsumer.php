<?php

namespace OnSecurity\Kafkavel\Resources\Contracts;

use Junges\Kafka\Contracts\KafkaConsumerMessage;

interface KafkavelConsumer {
    public function __construct(KafkavelConsumerMessage $message);

    static public function getTopic(): string;

    static public function getSchema(): string;

    static public function getSchemaVersion(): int | string | array;

    public function handle();
}
