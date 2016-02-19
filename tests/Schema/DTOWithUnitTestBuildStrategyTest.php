<?php

namespace tests\DeSmart\DeMaker\Core\Schema;

class DTOWithUnitTestBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    private $buildStrategy;

    private $fqn = 'Bar\Foo\Wat';
    private $expectedClassFqn = 'Bar\Foo\Wat';
    private $testfqn = 'Bar\Foo\WatTest';
    private $expectedClassUnitTestFqn = 'Bar\Foo\WatTest';


    public function setUp()
    {
        $this->input = $this->prophesize(\Symfony\Component\Console\Input\InputInterface::class);
        $this->input->getArgument('fqn')->willReturn($this->fqn);
        $this->input->getArgument('testfqn')->willReturn($this->testfqn);
        $this->input->hasOption('inputProperties')->willReturn(true);
        $this->input->getOption('inputProperties')->willReturn('firstname:string,lastname:string,dob:\Carbon\Carbon');
        $this->input->hasOption('outputProperties')->willReturn(true);
        $this->input->getOption('outputProperties')->willReturn('person:Person');

        $this->buildStrategy = new \DeSmart\DeMaker\Core\Schema\DTOWithUnitTestBuildStrategy($this->input->reveal());
    }

    /** @test */
    public function it_is_initializable()
    {
        $this->assertInstanceOf(\DeSmart\DeMaker\Core\Schema\DTOWithUnitTestBuildStrategy::class, $this->buildStrategy);
    }

    /** @test */
    public function it_is_build_strategy()
    {
        $this->assertInstanceOf(\DeSmart\DeMaker\Core\Schema\BuildStrategyInterface::class, $this->buildStrategy);
    }

    /** @test */
    public function it_makes_dto_and_unit_test()
    {
        $response = $this->buildStrategy->make();

        /** @var \Memio\Model\Object $dto */
        $dto = array_shift($response);
        $this->assertEquals($this->expectedClassFqn, $dto->getFullyQualifiedName());

        /** @var \Memio\Model\Object $dtoUnitTest */
        $dtoUnitTest = array_shift($response);
        $this->assertEquals($this->expectedClassUnitTestFqn, $dtoUnitTest->getFullyQualifiedName());
    }
}