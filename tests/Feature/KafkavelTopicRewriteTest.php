<?php

namespace OnSecurity\Kafkavel\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;
use Junges\Kafka\Message\Message;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumeManager;
use OnSecurity\Kafkavel\Resources\Consumers\ConsumerMap;
use OnSecurity\Kafkavel\Resources\Consumers\TestConsumeManager;
use OnSecurity\Kafkavel\Resources\Producers\ProducerEventHandler;
use OnSecurity\Kafkavel\Resources\Topic\Rewriter;
use OnSecurity\Kafkavel\Tests\Support\TestConsumer;
use OnSecurity\Kafkavel\Tests\Support\TestConsumer2;
use OnSecurity\Kafkavel\Tests\Support\TestProducer;
use OnSecurity\Kafkavel\Tests\Support\TestProducerEvent;
use OnSecurity\Kafkavel\Tests\TestCase;

class KafkavelTopicRewriteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'kafka.brokers' => 'brokerurl',
            'kafkavel.enabled' => true,
            'kafkavel.security.protocol' => 'plaintext',
            'kafkavel.producer_queue' => 'sync',
            'kafkavel.producer_discovery.enabled' => false,
            'kafkavel.producers' => [TestProducer::class],
            'kafkavel.consumer_discovery.enabled' => false,
            'kafkavel.consumers' => [TestConsumer::class, TestConsumer2::class],
            'kafkavel.topic_rewrite' => 'testtopic->rewrotetopic,testtopic2->rewrotetopic2',
        ]);

        app()->bind(ConsumeManager::class, TestConsumeManager::class);

        Kafka::fake();
    }

    /** @test */
    public function consumer_map_gets_correct_data_from_config_with_topic_rewrite()
    {

        $consumerMap = new ConsumerMap;
        $this->assertSame(['rewrotetopic', 'rewrotetopic2'], $consumerMap->getTopics()->toArray());

        $this->assertSame([
            'rewrotetopic' => ['Test' => [1 => [TestConsumer::class]]],
            'rewrotetopic2' => ['Test2' => [1 => [TestConsumer2::class], 2 => [TestConsumer2::class]]]
        ], $consumerMap->getTopicSchemaMap());
    }

    /** @test */
    public function consumer_manager_handles_messages_with_handlers_correctly_with_topic_rewrite()
    {
        $consumerManager = app()->make(ConsumeManager::class);
        $consumerManager->start();

        $message = new ConsumedMessage(
            topicName: 'rewrotetopic',
            partition: 0,
            headers: ['schema' => 'Test', 'schemaVersion' => 1],
            body: ['foo' => 'bar'],
            key: null,
            offset: 0,
            timestamp: time()
        );

        $message2 = new ConsumedMessage(
            topicName: 'rewrotetopic2',
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
    public function consumer_manager_handles_messages_without_handlers_correctly_with_topic_rewrite()
    {
        $consumerManager = app()->make(ConsumeManager::class);
        $consumerManager->start();

        $message = new ConsumedMessage(
            topicName: 'rewrotetopic',
            partition: 0,
            headers: ['schema' => 'TestNull', 'schemaVersion' => 1],
            body: ['foo' => 'bar'],
            key: null,
            offset: 0,
            timestamp: time()
        );

        $message2 = new ConsumedMessage(
            topicName: 'rewrotetopic2',
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

    /** @test */
    public function event_handler_dispatches_producers_correctly_with_topic_rewrite()
    {
        $expectedMessage = (new Message(TestProducer::getTopic()))
            ->withBody(['bar' => 'bar', 'world' => 'world'])
            ->withHeaders(['schema' => 'Test', 'schemaVersion' => 1]);

        $event = new TestProducerEvent();
        $eventHandler = new ProducerEventHandler($event);
        $eventHandler->handle();

        Kafka::assertPublishedOnTimes('rewrotetopic', 1, $expectedMessage);
    }

    /** @test */
    public function event_handler_dispatches_producers_using_event_bus_correctly_with_topic_rewrite()
    {
        $expectedMessage = (new Message(Rewriter::map(TestProducer::getTopic())))
            ->withBody(['bar' => 'bar', 'world' => 'world'])
            ->withHeaders(['schema' => 'Test', 'schemaVersion' => 1]);

        event(new TestProducerEvent());
        event(new TestProducerEvent());
        event(new TestProducerEvent(false));

        Kafka::assertPublishedOnTimes('rewrotetopic', 2, $expectedMessage);
    }

}
