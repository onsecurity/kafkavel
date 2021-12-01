<?php

namespace Tests\Unit\Resources\KafkavelPackage;

use OnSecurity\Kafkavel\Resources\Aggregate\DataAggregate;
use OnSecurity\Kafkavel\Resources\Aggregate\DataAggregateField;
use OnSecurity\Kafkavel\Resources\Aggregate\DtoAssembler;
use PHPUnit\Framework\TestCase;

class DtoAssemblerTest extends TestCase
{

    /** @test */
    public function a_dto_assembler_can_be_created()
    {
        $dataAggregate = new DataAggregate();
        $dtoAssembler = new DtoAssembler($dataAggregate);
        $this->assertInstanceOf(DtoAssembler::class, $dtoAssembler);
    }

    /** @test */
    public function a_dto_assembler_can_output_with_no_data()
    {
        $dataAggregate = new DataAggregate();
        $dtoAssembler = new DtoAssembler($dataAggregate);
        $this->assertSame([], $dtoAssembler->toArray());
    }

    /** @test */
    public function a_dto_assembler_can_output_data_in_correct_format()
    {
        $dataAggregate = new DataAggregate();
        $dataAggregate->createAndAddField('test.source', 'target');
        $dtoAssembler = new DtoAssembler($dataAggregate);
        $dtoAssembler->addData('test', ['source' => 'foo']);
        $this->assertSame(['target' =>  'foo'], $dtoAssembler->toArray());
    }

    /** @test */
    public function a_dto_assembler_can_output_data_in_correct_format_with_multiple_tiers_of_data()
    {
        $dataAggregate = new DataAggregate();
        $dataAggregate->createAndAddField('test.source.foo', 'target');
        $dtoAssembler = new DtoAssembler($dataAggregate);
        $dtoAssembler->addData('test', ['source' => ['foo' => 'bar']]);
        $this->assertSame(['target' =>  'bar'], $dtoAssembler->toArray());
    }

    /** @test */
    public function a_dto_assembler_can_output_data_in_correct_format_with_multiple_tiers_of_data_in_output()
    {
        $dataAggregate = new DataAggregate();
        $dataAggregate->addField(
            (new DataAggregateField('test.source', 'target'))->setAggregate(
                (new DataAggregate())->createAndAddField('foo', 'target2')
            )
        );
        $dtoAssembler = new DtoAssembler($dataAggregate);
        $dtoAssembler->addData('test', ['source' => ['foo' => 'bar']]);
        $this->assertSame(['target' =>  ['target2' => 'bar']], $dtoAssembler->toArray());
    }

    /** @test */
    public function a_dto_assembler_can_output_data_in_correct_format_with_data_loops()
    {
        $dataAggregate = new DataAggregate();
        $dataAggregate->addField(
            (new DataAggregateField('test.source', 'target'))->setLoop()->setAggregate(
                (new DataAggregate())->createAndAddField('foo', 'target')
            )
        );
        $dtoAssembler = new DtoAssembler($dataAggregate);
        $dtoAssembler->addData('test', [
            'source' => [
                ['foo' => 'bar'],
                ['foo' => 'bar2'],
            ],
        ]);
        $this->assertSame(['target' =>  [['target' => 'bar'], ['target' => 'bar2']]], $dtoAssembler->toArray());
    }

    /** @test */
    public function a_dto_assembler_can_output_data_in_correct_format_with_flattened_data_loops()
    {
        $dataAggregate = new DataAggregate();
        $dataAggregate->addField(
            (new DataAggregateField('test.source', 'target'))->setLoop()->setAggregate(
                (new DataAggregate())->createAndAddField('foo.result', 'target')
            )
        );
        $dtoAssembler = new DtoAssembler($dataAggregate);
        $dtoAssembler->addData('test', [
            'source' => [
                ['foo' => ['result' => 'bar']],
                ['foo' => ['result' => 'bar2']],
            ],
        ]);
        $this->assertSame(['target' =>  [['target' => 'bar'], ['target' => 'bar2']]], $dtoAssembler->toArray());
    }

}
