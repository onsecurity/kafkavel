<?php

namespace OnSecurity\Kafkavel\Test\Support;

use Closure;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumeManager;
use PHPUnit\Framework\Assert as PHPUnit;

class TestConsumeManager extends ConsumeManager
{
    protected bool $started = false;
    protected bool $consumerCreated = false;

    public function start()
    {
        $this->started = true;
    }

    public function stop(?Closure $onStop = null): void
    {
        $this->started = false;
    }

    protected function createConsumer()
    {
        $this->consumerCreated = true;
    }

    public function testIsStarted(): void
    {
        PHPUnit::assertTrue(
            condition: $this->started,
            message: "ConsumerManager is not started"
        );
    }

    public function testIsStopped(): void
    {
        PHPUnit::assertTrue(
            condition: !$this->started,
            message: "ConsumerManager is not stopped"
        );
    }

    public function testConsumerCreated(): void
    {
        PHPUnit::assertTrue(
            condition: $this->consumerCreated,
            message: "ConsumerManager consumer has not been created"
        );
    }

    public function attemptHandleMessage(KafkaConsumerMessage $message): array
    {
        if (!$this->started) {
            throw new \Exception('Unable to handle message, consume manager is not started.');
        }
        return $this->handleMessage($message);
    }
}
