<?php

namespace OnSecurity\Kafkavel\Resources\Consumers;

use Closure;
use Illuminate\Support\Collection;
use Junges\Kafka\Consumers\Consumer as KafkaConsumer;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;

class ConsumeManager
{
    protected KafkaConsumer $consumer;
    
    protected Collection $consumerClasses;
    protected ConsumerMap $consumerMap;

    public function __construct()
    {
        $this->consumerMap = new ConsumerMap;
        $this->createConsumer();
    }

    public function start()
    {
        $this->consumer->consume();
    }

    public function stop(?Closure $onStop = null): void
    {
        $this->consumer->stopConsume($onStop);
    }

    protected function createConsumer()
    {
        $this->consumer = Kafka::createConsumer($this->consumerMap->getTopics()->toArray())
            ->withConsumerGroupId(config('kafka.consumer_group_id'))
            ->withHandler(fn(KafkaConsumerMessage $message) => $this->handleMessage($message))
            ->build();
    }

    protected function handleMessage(KafkaConsumerMessage $message): array
    {
        $consumerMessage = ConsumerMessage::makeFromKafkaConsumerMessage($message);
        $handlerClasses = $this->consumerMap->getTopicSchemaMap()[$message->getTopicName()][$consumerMessage->getSchema()][$consumerMessage->getSchemaVersion()] ?? [];
        $results = [];
        foreach ($handlerClasses as $handlerClass) {
            $handler = new $handlerClass($consumerMessage);
            $results[$handlerClass] = $handler->handle();
        }
        return $results;
    }
}
