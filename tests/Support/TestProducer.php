<?php

namespace OnSecurity\Kafkavel\Test\Support;

use OnSecurity\Kafkavel\Resources\Aggregate\DataAggregate;
use OnSecurity\Kafkavel\Resources\Aggregate\DtoAssembler;
use OnSecurity\Kafkavel\Resources\Producers\Producer;

class TestProducer extends Producer
{
    static public function getEventClass(): string
    {
        return TestProducerEvent::class;
    }

    static public function getSchema(): string
    {
        return 'Test';
    }

    static public function getSchemaVersion(): int
    {
        return 1;
    }

    static public function getTopic(): string
    {
        return 'testtopic';
    }

    public function shouldProduce(): bool
    {
        return $this->getEvent()->shouldProduce;
    }

    public function getAssembler(): DtoAssembler
    {
        return (new DtoAssembler(
            (new DataAggregate())
                ->createAndAddField('data.foo', 'bar')
                ->createAndAddField('data.hello', 'world')
        ))->addData('data', $this->getEvent()->data());
    }
}
