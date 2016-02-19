<?php

namespace DeSmart\DeMaker\Core\Locator;

class Fqn
{

    /**
     * @var array
     */
    protected $sources;

    /**
     * @var array
     */
    protected $fqnArray;

    /**
     * @var string
     */
    protected $className;

    public function __construct($fqn, array $sources)
    {
        $fqnArray = explode('\\', $fqn);
        $this->sources = $sources;
        $this->className = array_pop($fqnArray);
        $this->fqnArray = array_filter($fqnArray, function ($fqnpart) {
            return true !== empty($fqnpart);
        });
    }

    /**
     * Returns file's path.
     *
     * @return string
     */
    public function getFilePath()
    {
        $dir = $this->getDir();

        return $dir . '/' . $this->className . '.php';
    }

    /**
     * Returns dir path from namespace.
     *
     * @return string
     */
    public function getDir()
    {
        $ns = join('\\', $this->fqnArray);

        if (true === is_array($this->sources)) {
            $fqnArray = $this->getDirWithNamespace($ns);
        }

        return join('/', $fqnArray);
    }

    /**
     * Swap part of dir path based on PSR-4 namespaces.
     *
     * @return array
     */
    public function getDirWithNamespace()
    {
        $nsArray = $this->fqnArray;
        $ns = join('\\', $nsArray);

        foreach ($this->sources as $sourceNamespace => $dir) {
            if (strstr($ns, $sourceNamespace)) {
                $nsToReplace = explode('\\', $sourceNamespace);

                array_splice($nsArray, 0, count($nsToReplace), [$dir]);

                break;
            }
        }

        return $nsArray;
    }
}
