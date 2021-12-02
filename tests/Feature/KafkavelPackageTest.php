<?php

namespace OnSecurity\Kafkavel\Tests\Feature;

use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use OnSecurity\Kafkavel\Resources\Producers\ProducerEventHandler;
use OnSecurity\Kafkavel\Tests\Support\TestProducer;
use OnSecurity\Kafkavel\Tests\Support\TestProducerEvent;
use OnSecurity\Kafkavel\Tests\TestCase;

class KafkavelPackageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'kafkavel.producer_discovery.enabled' => false,
            'kafkavel.producers' => [TestProducer::class]
        ]);

        Kafka::fake();
    }

    /** @test */
    public function event_handler_dispatches_producers_correctly()
    {
        $expectedMessage = (new Message(TestProducer::getTopic()))
            ->withBody(['bar' => 'bar', 'world' => 'world'])
            ->withHeaders(['schema' => 'Test', 'schemaVersion' => 1]);

        $event = new TestProducerEvent();
        $eventHandler = new ProducerEventHandler($event);
        $eventHandler->handle();

        Kafka::assertPublishedOnTimes('testtopic', 1, $expectedMessage);
    }

    /** @test */
    public function event_handler_dispatches_producers_using_event_bus_correctly()
    {
        $expectedMessage = (new Message(TestProducer::getTopic()))
            ->withBody(['bar' => 'bar', 'world' => 'world'])
            ->withHeaders(['schema' => 'Test', 'schemaVersion' => 1]);

        event(new TestProducerEvent());
        event(new TestProducerEvent());
        event(new TestProducerEvent(false));

        Kafka::assertPublishedOnTimes('testtopic', 2, $expectedMessage);
    }

}
