<?php

namespace OnSecurity\Kafkavel\Resources\Contracts;

use Junges\Kafka\Contracts\ConsumerMessage as JungesConsumerMessage;

interface KafkavelConsumerMessage extends JungesConsumerMessage
{
    public function getSchema(): ?string;

    public function getSchemaVersion(): int|string|null;
}
