<?php

namespace OnSecurity\Kafkavel\Resources\Consumers;

use Closure;
use Illuminate\Support\Collection;
use Junges\Kafka\Config\Sasl;
use Junges\Kafka\Consumers\Consumer as KafkaConsumer;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use OnSecurity\Kafkavel\Exceptions\ConsumerManagerStartException;

class ConsumeManager
{
    protected KafkaConsumer $consumer;

    protected Collection $consumerClasses;
    protected ConsumerMap $consumerMap;
    protected ?Closure $beforeHandle = null;
    protected ?Closure $afterHandle = null;
    protected array $topics;
    protected int $handledMessages = 0;
    protected int $ignoredMessages = 0;

    public function __construct(?array $topicFilter = null)
    {
        $this->consumerMap = new ConsumerMap;
        $this->topics = $this->consumerMap->getTopics()->filter(fn($topic) => $topicFilter === null || in_array($topic, $topicFilter, true))->toArray();
        if (empty($this->topics)) {
            throw new ConsumerManagerStartException('Unable to create ' . static::class . ' no valid topics');
        }
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

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getHandledMessages(): int
    {
        return $this->handledMessages;
    }

    public function getIgnoredMessages(): int
    {
        return $this->ignoredMessages;
    }

    public function beforeHandle(?Closure $closure): self
    {
        $this->beforeHandle = $closure;
        return $this;
    }

    public function afterHandle(?Closure $closure): self
    {
        $this->beforeHandle = $closure;
        return $this;
    }

    protected function createConsumer()
    {
        $consumerBuilder = Kafka::createConsumer($this->topics)
            ->withConsumerGroupId(config('kafka.consumer_group_id'))
            ->withHandler(fn(KafkaConsumerMessage $message) => $this->handleMessage($message))
            ->withOptions(['security.protocol' => config('kafkavel.security.protocol')]);

        if (config('kafkavel.security.username') !== null && config('kafkavel.security.password') !== null && config('kafkavel.security.mechanism') !== null) {
            $consumerBuilder->withSasl(new Sasl(
                username: config('kafkavel.security.username'),
                password: config('kafkavel.security.password'),
                mechanisms: config('kafkavel.security.mechanism'),
                securityProtocol: config('kafkavel.security.protocol')
            ));
        }

        $this->consumer = $consumerBuilder->build();
    }

    protected function handleMessage(KafkaConsumerMessage $message): array
    {
        $consumerMessage = ConsumerMessage::makeFromKafkaConsumerMessage($message);
        $handlerClasses = $this->consumerMap->getTopicSchemaMap()[$message->getTopicName()][$consumerMessage->getSchema()][$consumerMessage->getSchemaVersion()] ?? [];

        $results = [];
        if (!empty($handlerClasses) && ($this->beforeHandle === null || Closure::fromCallable($this->beforeHandle)($consumerMessage, $handlerClasses))) {
            foreach ($handlerClasses as $handlerClass) {
                $handler = new $handlerClass($consumerMessage);
                $results[$handlerClass] = $handler->handle();
            }
            if ($this->afterHandle !== null) {
                Closure::fromCallable($this->afterHandle)($consumerMessage, $handlerClasses, $results);
            }
            $this->handledMessages++;
        } else {
            $this->ignoredMessages++;
        }
        return $results;
    }
}
