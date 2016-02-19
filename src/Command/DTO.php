<?php

namespace DeSmart\DeMaker\Core\Command;

use DeSmart\DeMaker\Core\Dispatcher\Dispatcher;
use Symfony\Component\Console\Helper\QuestionHelper;
use DeSmart\DeMaker\Core\Schema\DTOWithUnitTestBuildStrategy;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DTO extends BaseCommand
{
    const OPTION_INPUT_PROPERTIES = 'inputProperties';

    protected function configure()
    {
        parent::configure();

        $this->setName('dto')
            ->setDescription('Generate DTO class with given properties')
            ->addOption('inputProperties', 'i', InputOption::VALUE_REQUIRED, 'Properties to generate (comma separated)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildStrategy = new DTOWithUnitTestBuildStrategy($input);

        parent::execute($input, $output);

        $dispatcherResponses = (new Dispatcher($buildStrategy))->run();

        foreach ($dispatcherResponses as $response) {
            $output->writeln("Generated DTO {$response->getFqn()} at {$response->getPath()}");
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $helper
     */
    protected function askForAdditionalInput(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $this->askForProperties($input, $output, $helper);
    }

    /**
     * Asks user for additional properties to generate on the DTO
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $helper
     */
    protected function askForProperties(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        if (!empty($input->getOption(self::OPTION_INPUT_PROPERTIES))) {
            return;
        }

        $question = new Question('Enter a <info>property name</info> to generate (name:type): ');
        $properties = [];

        while (true) {
            $property = $helper->ask($input, $output, $question);

            if (true === empty($property)) {
                break;
            }

            $properties[] = $property;
        }

        $input->setOption(self::OPTION_INPUT_PROPERTIES, join(',', $properties));
    }

}
