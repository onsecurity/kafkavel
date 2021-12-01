<?php

namespace OnSecurity\Kafkavel\Resources\Contracts;

use Junges\Kafka\Contracts\KafkaConsumerMessage;

interface KafkavelConsumerMessage extends KafkaConsumerMessage
{
    public function getSchema(): ?string;

    public function getSchemaVersion(): int|string|null;
}
