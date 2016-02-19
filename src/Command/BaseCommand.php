<?php

namespace DeSmart\DeMaker\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class BaseCommand extends Command
{
    const ARGUMENT_FQN = 'fqn';

    protected function configure()
    {
        $this->addArgument(self::ARGUMENT_FQN, InputArgument::REQUIRED, 'FQN of the class to be generated')
            ->addArgument('testfqn', InputArgument::REQUIRED, 'FQN of the test for the class to be generated');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input->setArgument(self::ARGUMENT_FQN, $this->sanitizeFQN($input->getArgument('fqn')));
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
    protected final function completeInput(InputInterface $input, OutputInterface $output)
    {
        if (true === $this->isInputDefined($input)) {
            return;
        }

        $helper = $this->getHelper('question');

        $this->askForFullyQualifiedName($input, $output, $helper);
        $this->askForAdditionalInput($input, $output, $helper);
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
     * Placeholder method for additional inputs to be gathered in classes extending this one
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $helper
     */
    protected function askForAdditionalInput(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {

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
}