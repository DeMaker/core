<?php

namespace DeSmart\DeMaker\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends Command
{
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initializes DeMaker configuration of available commands');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        copy(__DIR__ . '/../../config/demaker.json', 'demaker.json');
    }
}