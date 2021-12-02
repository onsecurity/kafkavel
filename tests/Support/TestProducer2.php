<?php

namespace OnSecurity\Kafkavel\Tests\Support;

use OnSecurity\Kafkavel\Resources\Aggregate\DataAggregate;
use OnSecurity\Kafkavel\Resources\Aggregate\DtoAssembler;
use OnSecurity\Kafkavel\Resources\Producers\Producer;

class TestProducer2 extends TestProducer
{
    static public function getSchema(): string
    {
        return 'Test2';
    }

    static public function getTopic(): string
    {
        return 'testtopic2';
    }

    static public function getSchemaVersion(): int
    {
        return 2;
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
