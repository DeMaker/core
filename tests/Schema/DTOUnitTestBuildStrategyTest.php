<?php

namespace tests\DeSmart\DeMaker\Core\Schema;

use DeSmart\DeMaker\Core\Schema\BuildStrategyInterface;
use DeSmart\DeMaker\Core\Schema\DTOUnitTestBuildStrategy;
use Memio\Model\Object;
use Symfony\Component\Console\Input\InputInterface;

class DTOUnitTestBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InputInterface
     */
    private $input;

    private $buildStrategy;

    private $fqn = 'Bar\Foo\Wat';

    private $testfqn = 'tests\Bar\Foo\WatTest';

    public function setUp()
    {
        $this->input = $this->prophesize(InputInterface::class);
        $this->input->getArgument('fqn')->willReturn($this->fqn);
        $this->input->getArgument('testfqn')->willReturn($this->testfqn);
        $this->input->hasOption('inputProperties')->willReturn(true);
        $this->input->getOption('inputProperties')->willReturn('firstname:string,lastname:string,dob:\Carbon\Carbon');

        $this->buildStrategy = new DTOUnitTestBuildStrategy($this->input->reveal());
    }

    /** @test */
    public function it_is_initalizable()
    {
        $this->assertInstanceOf(DTOUnitTestBuildStrategy::class, $this->buildStrategy);
    }

    /** @test */
    public function it_implements_build_strategy_interface()
    {
        $this->assertInstanceOf(BuildStrategyInterface::class, $this->buildStrategy);
    }

    /** @test */
    public function it_makes_dto_unit_test_schema()
    {
        /** @var Object $dtoUnitTest */
        $dtoUnitTest = $this->buildStrategy->make()[0];

        $this->assertInstanceOf(Object::class, $dtoUnitTest);
    }

    /** @test */
    public function it_makes_dto_unit_test_with_defined_test_fully_qualified_name()
    {
        /** @var Object $dtoUnitTest */
        $dtoUnitTest = $this->buildStrategy->make()[0];

        $this->assertEquals($this->testfqn, $dtoUnitTest->getFullyQualifiedName());
    }

    /** @test */
    public function it_makes_dto_unit_test_with_defined_properties()
    {
        /** @var Object $dtoTest */
        $dtoTest = $this->buildStrategy->make()[0];
        $properties = $dtoTest->allProperties();

        /** @var \Memio\Model\Property $dto */
        $dto = array_shift($properties);
        $this->assertEquals('dto', $dto->getName());
        $this->assertEquals('private', $dto->getVisibility());
        $this->assertEquals($this->fqn, $dto->getPhpdoc()->getVariableTag()->getType());

        /** @var \Memio\Model\Property $firstname */
        $firstname = array_shift($properties);
        $this->assertEquals('firstname', $firstname->getName());
        $this->assertEquals('private', $firstname->getVisibility());
        $this->assertEquals('string', $firstname->getPhpdoc()->getVariableTag()->getType());

        /** @var \Memio\Model\Property $lastname */
        $lastname = array_shift($properties);
        $this->assertEquals('lastname', $lastname->getName());
        $this->assertEquals('private', $lastname->getVisibility());
        $this->assertEquals('string', $lastname->getPhpdoc()->getVariableTag()->getType());

        /** @var \Memio\Model\Property $dob */
        $dob = array_shift($properties);
        $this->assertEquals('dob', $dob->getName());
        $this->assertEquals('private', $dob->getVisibility());
        $this->assertEquals('\Carbon\Carbon', $dob->getPhpdoc()->getVariableTag()->getType());
    }

}