<?php

namespace OnSecurity\Kafkavel\Test\Feature;

use Junges\Kafka\Message\ConsumedMessage;
use Mockery\MockInterface;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumeManager;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumerMap;
use Orchestra\Testbench\TestCase;
use OnSecurity\Kafkavel\Test\Support\TestConsumeManager;
use OnSecurity\Kafkavel\Test\Support\TestConsumer;
use OnSecurity\Kafkavel\Test\Support\TestConsumer2;
use OnSecurity\Kafkavel\Test\Support\TestProducer;
use OnSecurity\Kafkavel\Test\Support\TestProducer2;

class KafkavelConsumeManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->bind(ConsumeManager::class, TestConsumeManager::class);

        config([
            'kafkavel.producer_discovery.enabled' => false,
            'kafkavel.producers' => [TestProducer::class, TestProducer2::class],
            'kafkavel.consumer_discovery.enabled' => false,
            'kafkavel.consumers' => [TestConsumer::class, TestConsumer2::class]
        ]);
    }

    /** @test */
    public function consumer_map_gets_correct_data_from_config()
    {
        $consumerMap = new ConsumerMap;
        $this->assertSame(['testtopic', 'testtopic2'], $consumerMap->getTopics()->toArray());

        $this->assertSame([
            'testtopic' => ['Test' => [1 => [TestConsumer::class]]],
            'testtopic2' => ['Test2' => [1 => [TestConsumer2::class], 2 => [TestConsumer2::class]]]
        ], $consumerMap->getTopicSchemaMap());
    }

    /** @test */
    public function consumer_manager_starts_correctly()
    {
        $consumerManager = app()->make(ConsumeManager::class);
        $consumerManager->testConsumerCreated();
        $consumerManager->testIsStopped();
        $consumerManager->start();
        $consumerManager->testIsStarted();
    }

    /** @test */
    public function consumer_manager_stops_correctly()
    {
        $consumerManager = app()->make(ConsumeManager::class);
        $consumerManager->start();
        $consumerManager->testIsStarted();
        $consumerManager->stop();
        $consumerManager->testIsStopped();
    }

    /** @test */
    public function consumer_manager_handles_messages_with_handlers_correctly()
    {
        $consumerManager = app()->make(ConsumeManager::class);
        $consumerManager->start();

        $message = new ConsumedMessage(
            topicName: 'testtopic',
            partition: 0,
            headers: ['schema' => 'Test', 'schemaVersion' => 1],
            body: ['foo' => 'bar'],
            key: null,
            offset: 0,
            timestamp: time()
        );

        $message2 = new ConsumedMessage(
            topicName: 'testtopic2',
            partition: 0,
            headers: ['schema' => 'Test2', 'schemaVersion' => 2],
            body: ['foo' => 'bar'],
            key: null,
            offset: 0,
            timestamp: time()
        );
        $result = $consumerManager->attemptHandleMessage($message);
        $result2 = $consumerManager->attemptHandleMessage($message2);

        $this->assertCount(1, $result);
        $this->assertCount(1, $result2);

        $this->assertContains('TestConsumerHandled', $result);
        $this->assertNotContains('TestConsumer2Handled', $result);
        $this->assertContains('TestConsumer2Handled', $result2);
        $this->assertNotContains('TestConsumerHandled', $result2);
    }

    /** @test */
    public function consumer_manager_handles_messages_without_handlers_correctly()
    {
        $consumerManager = app()->make(ConsumeManager::class);
        $consumerManager->start();

        $message = new ConsumedMessage(
            topicName: 'testtopic',
            partition: 0,
            headers: ['schema' => 'TestNull', 'schemaVersion' => 1],
            body: ['foo' => 'bar'],
            key: null,
            offset: 0,
            timestamp: time()
        );

        $message2 = new ConsumedMessage(
            topicName: 'testtopic2',
            partition: 0,
            headers: ['schema' => 'Test2', 'schemaVersion' => 3],
            body: ['foo' => 'bar'],
            key: null,
            offset: 0,
            timestamp: time()
        );
        $result = $consumerManager->attemptHandleMessage($message);
        $result2 = $consumerManager->attemptHandleMessage($message2);

        $this->assertCount(0, $result);
        $this->assertCount(0, $result2);
    }

}
