<?php

namespace OnSecurity\Kafkavel\Tests\Support;

class TestProducerEvent
{
    public function __construct(public bool $shouldProduce = true)
    {

    }

    public function data(): array
    {
        return [
            'foo' => 'bar',
            'hello' => 'world'
        ];
    }
}
