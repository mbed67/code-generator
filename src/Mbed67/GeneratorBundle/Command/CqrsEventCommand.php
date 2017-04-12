<?php

namespace Mbed67\GeneratorBundle\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CqrsEventCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cqrs:event')
            ->setDescription('Creates a cqrs event')
            ->setDefinition(array(
                new InputArgument('event_name', InputArgument::REQUIRED, 'The name of the event to create'),
                new InputArgument('aggregate', InputArgument::REQUIRED, 'The name of the aggregate'),
                new InputOption('properties','' , InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The properties of the event'),
            ))
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // name of the event
        $question = new Question('Please enter the name of the event: ');

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $event_name = $helper->ask($input, $output, $question);
        $input->setArgument('event_name', $event_name);

        // name of the aggregate
        $question = new Question('Please enter the name of the aggregate: ');

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $aggregate = $helper->ask($input, $output, $question);
        $input->setArgument('aggregate', $aggregate);


        // get the properties
        $finished = false;
        $properties = [];

        while(!$finished) {
            $property = $this->getPropertyName($input, $output, $helper);
            if (!$property) {
                return;
            }

            $type = $this->getPropertyType($input, $output, $helper);
            if (!$type) {
                return;
            }

            $validators = $this->getPropertyValidators($input, $output, $helper) ?: '';

            $properties[$property] = ['type' => $type, 'validator' => $validators];

            $finished = $this->isFinished($input, $output, $helper);
        }

        $input->setOption('properties', $properties);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $properties = $input->getOption('properties');

        var_dump($properties);

        $output->writeln('Command result.');
    }

    private function getPropertyName(InputInterface $input, OutputInterface $output, QuestionHelper $helper): string
    {
        $question = new Question('Please enter the name of the property: ');
        return $helper->ask($input, $output, $question);
    }

    private function getPropertyType(InputInterface $input, OutputInterface $output, QuestionHelper $helper): string
    {
        $question = new ChoiceQuestion(
            'Please select the type of the property (defaults to string): ',
            ['string', 'int'],
            0
        );
        $question->setErrorMessage('Type %s is invalid.');

        $type = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: '.$type);

        return $type;
    }

    private function getPropertyValidators(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper
    ): array {
        $question = new ChoiceQuestion(
            'Please select zero or more validators of the property (separate with commas): ',
            ['', 'uuid', 'email', 'notBlank', 'nullOrString', 'nullOrNotBlank'],
            '0'
        );

        $question->setMultiselect(true);
        $question->setErrorMessage('Type %s is invalid.');

        $validators = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: ' . implode(', ', $validators));

        return $validators;
    }

    private function isFinished(InputInterface $input, OutputInterface $output, QuestionHelper $helper): bool
    {
        $question = new ConfirmationQuestion(
            'Do you want to stop adding properties?: ',
            false,
            '/^(y|j)/i'
        );

        if (!$helper->ask($input, $output, $question)) {
            return false;
        }

        return true;
    }
}
