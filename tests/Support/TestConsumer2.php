<?php

namespace OnSecurity\Kafkavel\Tests\Support;

class TestConsumer2 extends TestConsumer
{
    public bool $handled = false;

    static public function getTopic(): string
    {
        return 'testtopic2';
    }

    static public function getSchema(): string
    {
        return 'Test2';
    }

    static public function getSchemaVersion(): int|string|array
    {
        return [1,2];
    }

    public function handle()
    {
        return 'TestConsumer2Handled';
    }
}
