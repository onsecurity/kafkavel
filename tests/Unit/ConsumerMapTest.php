<?php

namespace Tests\Unit\Resources\KafkavelPackage;

use OnSecurity\Kafkavel\Resources\Consumers\ConsumerMap;
use OnSecurity\Kafkavel\Resources\Topic\Rewriter;
use OnSecurity\Kafkavel\Tests\Support\TestConsumer;
use OnSecurity\Kafkavel\Tests\Support\TestConsumer2;
use Orchestra\Testbench\TestCase;

class ConsumerMapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Rewriter::clearRewriteMapCache();
    }
    /** @test */
    public function a_consumer_map_can_be_created()
    {
        $consumerMap = new ConsumerMap([TestConsumer::class]);
        $this->assertInstanceOf(ConsumerMap::class, $consumerMap);
    }

    /** @test */
    public function a_consumer_map_gets_the_correct_topics()
    {
        $consumerMap = new ConsumerMap([TestConsumer::class]);
        $this->assertSame(['testtopic'], $consumerMap->getTopics()->toArray());
        $consumerMap2 = new ConsumerMap([TestConsumer::class, TestConsumer2::class]);
        $this->assertSame(['testtopic', 'testtopic2'], $consumerMap2->getTopics()->toArray());
    }

    /** @test */
    public function a_consumer_map_calculates_the_topic_schema_map_correctly()
    {
        $consumerMap = new ConsumerMap([TestConsumer::class]);
        $this->assertSame(['testtopic' => ['Test' => [1 => [TestConsumer::class]]]], $consumerMap->getTopicSchemaMap());

        $consumerMap2 = new ConsumerMap([TestConsumer::class, TestConsumer2::class]);
        $this->assertSame([
            'testtopic' => ['Test' => [1 => [TestConsumer::class]]],
            'testtopic2' => ['Test2' => [1 => [TestConsumer2::class], 2 => [TestConsumer2::class]]]
        ], $consumerMap2->getTopicSchemaMap());
    }

}
