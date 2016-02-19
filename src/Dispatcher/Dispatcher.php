<?php

namespace DeSmart\DeMaker\Core\Dispatcher;

use DeSmart\DeMaker\Core\Config\Psr4;
use DeSmart\DeMaker\Core\Output\Writer;
use DeSmart\DeMaker\Core\Schema\BuildStrategyInterface;
use DeSmart\DeMaker\Core\Locator\Fqn;
use Memio\Memio\Config\Build;

/**
 * Dispatcher responsible for running Stamp.
 */
class Dispatcher
{

    /**
     * @var BuildStrategyInterface
     */
    protected $buildStrategy;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var array
     */
    protected $sources = null;

    public function __construct(BuildStrategyInterface $buildStrategy)
    {
        $this->buildStrategy = $buildStrategy;
    }

    /**
     * @return DispatcherResponse[]
     */
    public function run()
    {
        $writer = new Writer(Build::prettyPrinter());
        $objects = $this->buildStrategy->make();

        foreach ($objects as $object) {
            $fqn = new Fqn($object->getFullyQualifiedName(), $this->getSources());

            $writer->makeClass($object, $fqn);

            $this->results[] = new DispatcherResponse(
                $object->getFullyQualifiedName(),
                $fqn->getFilePath()
            );
        }

        return $this->results;
    }

    /**
     * @return array
     */
    protected function getSources()
    {
        if (true === is_null($this->sources)) {
            $loader = new Psr4;

            $this->sources = $loader->getFromComposerFile(file_get_contents('composer.json'));
        }

        return $this->sources;
    }
}
