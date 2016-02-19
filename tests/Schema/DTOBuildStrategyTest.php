<?php

namespace tests\DeSmart\DeMaker\Core\Schema;

class DTOBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    private $buildStrategy;

    private $fqn = 'Bar\Foo\WatCommand';

    public function setUp()
    {
        $this->input = $this->prophesize(\Symfony\Component\Console\Input\InputInterface::class);
        $this->input->getArgument('fqn')->willReturn($this->fqn);
        $this->input->hasOption('inputProperties')->willReturn(true);
        $this->input->getOption('inputProperties')->willReturn('firstname:string,lastname:string,dob:\Carbon\Carbon');

        $this->buildStrategy = new \DeSmart\DeMaker\Core\Schema\DTOBuildStrategy($this->input->reveal());
    }

    /** @test */
    public function it_is_initalizable()
    {
        $this->assertInstanceOf(\DeSmart\DeMaker\Core\Schema\DTOBuildStrategy::class, $this->buildStrategy);
    }

    /** @test */
    public function it_implements_build_strategy_interface()
    {
        $this->assertInstanceOf(\DeSmart\DeMaker\Core\Schema\BuildStrategyInterface::class, $this->buildStrategy);
    }

    /** @test */
    public function it_makes_dto_schema()
    {
        /** @var \Memio\Model\Object $dto */
        $dto = $this->buildStrategy->make()[0];

        $this->assertInstanceOf(\Memio\Model\Object::class, $dto);
    }

    /** @test */
    public function it_makes_dto_with_defined_fully_qualified_name()
    {
        /** @var \Memio\Model\Object $dto */
        $dto = $this->buildStrategy->make()[0];

        $this->assertEquals($this->fqn, $dto->getFullyQualifiedName());
    }

    /** @test */
    public function it_makes_dto_with_defined_properties()
    {
        /** @var \Memio\Model\Object $dto */
        $dto = $this->buildStrategy->make()[0];
        $properties = $dto->allProperties();

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

    /** @test */
    public function it_makes_dto_initialized_with_defined_properties()
    {
        /** @var \Memio\Model\Object $dto */
        $dto = $this->buildStrategy->make()[0];
        $methods = $dto->allMethods();

        /** @var \Memio\Model\Method $construct */
        $construct = array_shift($methods);
        $this->assertEquals('__construct', $construct->getName());
        $this->assertEquals('public', $construct->getVisibility());

        $phpDocParamTags = $construct->getPhpdoc()->getParameterTags();

        /** @var \Memio\Model\PhpDoc\ParameterTag $firstnameParamTag */
        $firstnameParamTag = array_shift($phpDocParamTags);
        $this->assertEquals('string', $firstnameParamTag->getType());
        $this->assertEquals('firstname', $firstnameParamTag->getName());

        /** @var \Memio\Model\PhpDoc\ParameterTag $lastnameParamTag */
        $lastnameParamTag = array_shift($phpDocParamTags);
        $this->assertEquals('string', $lastnameParamTag->getType());
        $this->assertEquals('lastname', $lastnameParamTag->getName());

        /** @var \Memio\Model\PhpDoc\ParameterTag $dobParamTag */
        $dobParamTag = array_shift($phpDocParamTags);
        $this->assertEquals('\Carbon\Carbon', $dobParamTag->getType());
        $this->assertEquals('dob', $dobParamTag->getName());

        $constructorArguments = $construct->allArguments();

        /** @var \Memio\Model\Argument $firstname */
        $firstname = array_shift($constructorArguments);
        $this->assertEquals('firstname', $firstname->getName());
        $this->assertEquals('string', $firstname->getType());

        /** @var \Memio\Model\Argument $lastname */
        $lastname = array_shift($constructorArguments);
        $this->assertEquals('lastname', $lastname->getName());
        $this->assertEquals('string', $lastname->getType());

        /** @var \Memio\Model\Argument $dob */
        $dob = array_shift($constructorArguments);
        $this->assertEquals('dob', $dob->getName());
        $this->assertEquals('\Carbon\Carbon', $dob->getType());

        $spacer = '        ';

        $expectedConstructBody = "{$spacer}\$this->firstname = \$firstname;\n{$spacer}\$this->lastname = \$lastname;\n{$spacer}\$this->dob = \$dob;";
        $this->assertEquals($expectedConstructBody, $construct->getBody());
    }

    /** @test */
    public function it_makes_dto_with_getters_for_defined_properties()
    {
        /** @var \Memio\Model\Object $dto */
        $dto = $this->buildStrategy->make()[0];
        $methods = $dto->allMethods();

        /** @var \Memio\Model\Method $construct */
        $construct = array_shift($methods);

        /** @var \Memio\Model\Method $getFirstname */
        $getFirstname = array_shift($methods);
        $this->assertEquals('getFirstname', $getFirstname->getName());
        $this->assertEquals('public', $getFirstname->getVisibility());

        /** @var \Memio\Model\PhpDoc\ReturnTag $getFirstnameReturnTag */
        $getFirstnameReturnTag = $getFirstname->getPhpdoc()->getReturnTag();
        $this->assertEquals('string', $getFirstnameReturnTag->getType());

        $expectedGetFirstnameBody = "        return \$this->firstname;";
        $this->assertEquals($expectedGetFirstnameBody, $getFirstname->getBody());

        /** @var \Memio\Model\Method $getLastname */
        $getLastname = array_shift($methods);
        $this->assertEquals('getLastname', $getLastname->getName());
        $this->assertEquals('public', $getLastname->getVisibility());

        /** @var \Memio\Model\PhpDoc\ReturnTag $getLastnameReturnTag */
        $getLastnameReturnTag = $getLastname->getPhpdoc()->getReturnTag();
        $this->assertEquals('string', $getLastnameReturnTag->getType());

        /** @var \Memio\Model\Method $getDob */
        $getDob = array_shift($methods);
        $this->assertEquals('getDob', $getDob->getName());
        $this->assertEquals('public', $getDob->getVisibility());

        /** @var \Memio\Model\PhpDoc\ReturnTag $getLastnameReturnTag */
        $getDobReturnTag = $getDob->getPhpdoc()->getReturnTag();
        $this->assertEquals('\Carbon\Carbon', $getDobReturnTag->getType());
    }
}