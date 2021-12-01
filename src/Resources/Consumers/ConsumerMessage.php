<?php

namespace OnSecurity\Kafkavel\Resources\Consumers;

use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Message\ConsumedMessage;
use OnSecurity\Kafkavel\Resources\Contracts\KafkavelConsumerMessage;

class ConsumerMessage extends ConsumedMessage implements KafkavelConsumerMessage
{
    public static function makeFromKafkaConsumerMessage(KafkaConsumerMessage $message): static
    {
        return new static(
            topicName: $message->getTopicName(),
            partition: $message->getPartition(),
            headers: $message->getHeaders(),
            body: $message->getBody(),
            key: $message->getKey(),
            offset: $message->getOffset(),
            timestamp: $message->getTimestamp()
        );
    }

    public function getSchema(): ?string
    {
        return $this->getHeaders()['schema'] ?? null;
    }

    public function getSchemaVersion(): int|string|null
    {
        return $this->getHeaders()['schemaVersion'] ?? null;
    }
}
