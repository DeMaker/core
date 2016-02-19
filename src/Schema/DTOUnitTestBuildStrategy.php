<?php

namespace DeSmart\DeMaker\Core\Schema;

use Memio\Model\Method;
use Memio\Model\Object;
use Memio\Model\Phpdoc\MethodPhpdoc;
use Memio\Model\Phpdoc\PropertyPhpdoc;
use Memio\Model\Phpdoc\ReturnTag;
use Memio\Model\Phpdoc\VariableTag;
use Memio\Model\Property;
use Symfony\Component\Console\Input\InputInterface;

class DTOUnitTestBuildStrategy implements BuildStrategyInterface
{

    /**
     * @var string
     */
    protected $fqn;

    /**
     * @var string
     */
    protected $testedClassFqn;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->fqn = $input->getArgument('testfqn');
        $this->testedClassFqn = $input->getArgument('fqn');
        $properties = $input->getOption('inputProperties');

        if (false === is_null($properties)) {
            $this->properties = explode(',', $input->getOption('inputProperties'));
        }
    }

    /**
     * @return Object[]
     */
    public function make()
    {
        $phpunitTestCase = Object::make(\PHPUnit_Framework_TestCase::class);
        $dtoUnitTest = Object::make($this->fqn);
        $dtoUnitTest->extend($phpunitTestCase);
        $setUp = new Method('setUp');
        $setUp->makePublic();
        $setUp->setPhpdoc(MethodPhpdoc::make());

        $dtoUnitTest->addMethod($setUp);

        $bodyElements = $this->handleMethodProperties($setUp, $dtoUnitTest);

        $setUp->setBody(implode("\n", $bodyElements));

        return [$dtoUnitTest];
    }

    /**
     * @param $property
     * @return array
     */
    protected function getPropertyDefinition($property)
    {
        return explode(':', $property);
    }

    /**
     * @param Method $construct
     * @param Object $dto
     * @return array
     */
    protected function handleMethodProperties(Method $setUp, Object $dtoUnitTest)
    {
        $newProperty = new Property('dto');
        $newProperty->makePrivate();
        $newProperty->setPhpdoc(PropertyPhpdoc::make()
            ->setVariableTag(new VariableTag($this->testedClassFqn))
        );

        $dtoUnitTest->addProperty($newProperty);

        $bodyElements = [];
        $bodyElements[] = "        \$this->dto = new \\{$this->testedClassFqn}(";
        $propertyIndex = 0;

        foreach($this->properties as $property) {
            $propertyIndex++;

            list($propertyName, $propertyType) = $this->getPropertyDefinition($property);

            $newProperty = new Property($propertyName);
            $newProperty->makePrivate();
            $newProperty->setPhpdoc(PropertyPhpdoc::make()
                ->setVariableTag(new VariableTag($propertyType))
            );

            $dtoUnitTest->addProperty($newProperty);

            $separator = ($propertyIndex < count($this->properties)) ? ',' : '';

            $bodyElements[] = sprintf("            \$this->%s%s", $propertyName, $separator);

            $newMethod = new Method(sprintf('get%sTest', ucfirst($propertyName)));
            $newMethod->makePublic();
            $newMethod->setPhpdoc(MethodPhpdoc::make()
                ->setReturnTag(new ReturnTag($propertyType))
            );
            $getterName = sprintf('get%s', ucfirst($propertyName));
            $newMethod->setBody("        \$this->assertSame(\$this->{$propertyName}, \$this->dto->{$getterName}());");

            $dtoUnitTest->addMethod($newMethod);
        }

        $bodyElements[] = "        );";

        return $bodyElements;
    }
}