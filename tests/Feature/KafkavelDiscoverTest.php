<?php

namespace OnSecurity\Kafkavel\Tests\Feature;

use OnSecurity\Kafkavel\Resources\Discovery\ConsumerDiscover;
use OnSecurity\Kafkavel\Resources\Discovery\ProducerDiscover;
use OnSecurity\Kafkavel\Tests\Support\TestConsumer;
use OnSecurity\Kafkavel\Tests\Support\TestConsumer2;
use OnSecurity\Kafkavel\Tests\Support\TestProducer;
use OnSecurity\Kafkavel\Tests\Support\TestProducer2;
use OnSecurity\Kafkavel\Tests\TestCase;

class KafkavelDiscoverTest extends TestCase
{
    /** @test */
    public function producer_discover_finds_manually_defined_producers()
    {
        $producers = [TestProducer::class];
        config([
            'kafkavel.producer_discovery.enabled' => false,
            'kafkavel.producers' => $producers,
        ]);
        $this->assertSame($producers, ProducerDiscover::discover()->toArray());
    }

    /** @test */
    public function producer_discover_finds_producers_using_directory_search()
    {
        config([
            'kafkavel.producer_discovery.enabled' => true,
            'kafkavel.producer_discovery.directories' => ['OnSecurity/Kafkavel/Test/Support'],
            'kafkavel.producers' => [],
        ]);
        $producers = ProducerDiscover::discover()->toArray();
        $this->assertContains(TestProducer::class, $producers);
        $this->assertContains(TestProducer2::class, $producers);
        $this->assertCount(2, $producers);
    }

    /** @test */
    public function producer_discover_finds_producers_using_directory_search_and_manual_correctly()
    {
        config([
            'kafkavel.producer_discovery.enabled' => true,
            'kafkavel.producer_discovery.directories' => ['OnSecurity/Kafkavel/Test/Support'],
            'kafkavel.producers' => [TestProducer::class],
        ]);
        $producers = ProducerDiscover::discover()->toArray();
        $this->assertContains(TestProducer::class, $producers);
        $this->assertContains(TestProducer2::class, $producers);
        $this->assertCount(2, $producers);
    }

    /** @test */
    public function consumer_discover_finds_manually_defined_consumers()
    {
        $consumers = [TestConsumer::class];
        config([
            'kafkavel.consumer_discovery.enabled' => false,
            'kafkavel.consumers' => $consumers,
        ]);
        $this->assertSame($consumers, ConsumerDiscover::discover()->toArray());
    }

    /** @test */
    public function consumer_discover_finds_consumers_using_directory_search()
    {
        config([
            'kafkavel.consumer_discovery.enabled' => true,
            'kafkavel.consumer_discovery.directories' => ['OnSecurity/Kafkavel/Test/Support'],
            'kafkavel.consumers' => [],
        ]);
        $consumers = ConsumerDiscover::discover()->toArray();
        $this->assertContains(TestConsumer::class, $consumers);
        $this->assertContains(TestConsumer2::class, $consumers);
        $this->assertCount(2, $consumers);
    }

    /** @test */
    public function consumer_discover_finds_consumers_using_directory_search_and_manual_correctly()
    {
        config([
            'kafkavel.consumer_discovery.enabled' => true,
            'kafkavel.consumer_discovery.directories' => ['OnSecurity/Kafkavel/Test/Support'],
            'kafkavel.consumers' => [TestConsumer::class],
        ]);
        $consumers = ConsumerDiscover::discover()->toArray();
        $this->assertContains(TestConsumer::class, $consumers);
        $this->assertContains(TestConsumer2::class, $consumers);
        $this->assertCount(2, $consumers);
    }
}
