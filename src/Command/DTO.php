<?php

namespace DeSmart\DeMaker\Core\Command;

use DeSmart\DeMaker\Core\Dispatcher\Dispatcher;
use DeSmart\DeMaker\Core\Schema\DTOBuildStrategy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DTO extends Command
{
    const ARGUMENT_FQN = 'fqn';

    const OPTION_INPUT_PROPERTIES = 'inputProperties';

    protected function configure()
    {
        $this->setName('dto')
            ->addArgument(self::ARGUMENT_FQN, InputArgument::REQUIRED, 'FQN of the class to be generated')
            ->addOption(self::OPTION_INPUT_PROPERTIES, 'p', InputOption::VALUE_REQUIRED, 'Properties to generate (comma separated)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildStrategy = new DTOBuildStrategy($input);

        $dispatcherResponses = (new Dispatcher($buildStrategy))->run();

        foreach ($dispatcherResponses as $response) {
            $output->writeln("Generated DTO {$response->getFqn()} at {$response->getPath()}");
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->completeInput($input, $output);
    }

    /**
     * Tries to load all the necessary input from the user interactively
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function completeInput(InputInterface $input, OutputInterface $output)
    {
        if (true === $this->isInputDefined($input)) {
            return;
        }

        $helper = $this->getHelper('question');

        $this->askForFullyQualifiedName($input, $output, $helper);
        $this->askForProperties($input, $output, $helper);
    }

    /**
     * Checks if any input is defined on the CLI
     *
     * If so, then skip the interactive mode
     *
     * @param InputInterface $input
     * @return bool
     */
    protected function isInputDefined(InputInterface $input)
    {
        return !empty($input->getArgument(self::ARGUMENT_FQN));
    }

    /**
     * Asks user for fully qualified name for generated class, if not provided as cli argument
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $helper
     */
    protected function askForFullyQualifiedName(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        if (!empty($input->getArgument(self::ARGUMENT_FQN))) {
            return;
        }

        $question = new Question('Enter the <info>fully qualified name</info> for the generated class: ');
        $fqn = $helper->ask($input, $output, $question);

        $input->setArgument(self::ARGUMENT_FQN, $fqn);
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
