<?php

namespace DeSmart\DeMaker\Core\Schema;

use Symfony\Component\Console\Input\InputInterface;

class DTOWithUnitTestBuildStrategy implements BuildStrategyInterface
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * @return Object[]
     */
    public function make()
    {
        $strategy = (new DTOBuildStrategy($this->input))->make();

        if (true === empty($this->input->getArgument('testfqn'))) {
            return $strategy;
        }

        return array_merge(
            $strategy,
            (new DTOUnitTestBuildStrategy($this->input))->make()
        );
    }
}