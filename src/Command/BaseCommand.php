<?php

namespace DeSmart\DeMaker\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    protected function configure()
    {
        $this->setDefinition(
            new InputDefinition([
                new InputArgument('fqn', InputArgument::REQUIRED, 'FQN of the class to be generated'),
                new InputArgument('testfqn', InputArgument::REQUIRED, 'FQN of the test for the class to be generated'),
            ])
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input->setArgument('fqn', $this->sanitizeFQN($input->getArgument('fqn')));
        $input->setArgument('testfqn', $this->sanitizeFQN($input->getArgument('testfqn')));
    }

    /**
     * @param string $fqn
     * @return string
     */
    protected function sanitizeFQN($fqn)
    {
        return preg_replace(["#^(\\\\+)#", "#\\\\{2,}#"], ["", "\\"], $fqn);
    }
}