<?php

namespace Tests\Unit\Resources\KafkavelPackage;

use OnSecurity\Kafkavel\Test\Support\TestProducer;
use OnSecurity\Kafkavel\Test\Support\TestProducerEvent;
use PHPUnit\Framework\TestCase;

class ProducerTest extends TestCase
{

    /** @test */
    public function a_producer_can_be_created()
    {
        $testProducer = new TestProducer(new TestProducerEvent);
        $this->assertInstanceOf(TestProducer::class, $testProducer);
    }

    /** @test */
    public function a_producer_should_produce_returns_correct_value()
    {
        $testProducerFalse = new TestProducer(new TestProducerEvent(false));
        $this->assertFalse($testProducerFalse->shouldProduce());
        $testProducerTrue = new TestProducer(new TestProducerEvent(true));
        $this->assertTrue($testProducerTrue->shouldProduce());
    }

    /** @test */
    public function a_producer_produces_the_correct_message_data()
    {
        $testProducer = new TestProducer(new TestProducerEvent);
        $message = $testProducer->getMessage();
        $this->assertSame(['schema' => TestProducer::getSchema(), 'schemaVersion' => 1], $message->getHeaders());
        $this->assertSame(['bar' => 'bar', 'world' => 'world'], $message->getBody());
    }
}
