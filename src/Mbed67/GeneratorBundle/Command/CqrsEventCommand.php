<?php

namespace Mbed67\GeneratorBundle\Command;

use Mbed67\GeneratorBundle\Builder\EventBuilder;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use TwigGenerator\Builder\Generator;

class CqrsEventCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cqrs:event')
            ->setDescription('Creates a cqrs event')
            ->setDefinition(array(
                new InputArgument(
                    'event_name',
                    InputArgument::REQUIRED,
                    'The name of the event to create'
                ),
                new InputArgument(
                    'aggregate',
                    InputArgument::REQUIRED,
                    'The name of the aggregate'
                ),
                new InputOption(
                    'properties',
                    '' ,
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'The properties of the event'
                ),
            ))
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // name of the event
        $question = new Question('Please enter the name of the event: ');

        $event_name = $helper->ask($input, $output, $question);

        if (!$event_name) {
            return;
        }

        $input->setArgument('event_name', $event_name);

        // name of the aggregate
        $question = new Question('Please enter the name of the aggregate: ');

        $aggregate = $helper->ask($input, $output, $question);

        if (!$aggregate) {
            return;
        }

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
            $faker = $this->getPropertyFaker($input, $output, $helper) ?: '';

            $properties[$property] = ['type' => $type, 'validators' => $validators, 'faker' => $faker];

            $finished = $this->isFinished($input, $output, $helper);
        }

        $input->setOption('properties', $properties);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event_name = $input->getArgument('event_name');
        $aggregate = $input->getArgument('aggregate');
        $properties = $input->getOption('properties');

        // initialize a builder
        $builder = new EventBuilder();
        $builder->setOutputName($event_name . '.php');

        // add specific configuration for my builder
        $builder->setVariable('className', 'EventBuilder');
        $builder->setVariable('aggregate', $aggregate);
        $builder->setVariable('event_name', $event_name);
        $builder->setVariable('properties', $properties);

        // set template name
        $builder->setTemplateName('Event.php.twig');

        // initialize a test builder
        $testBuilder = new EventBuilder();
        $testBuilder->setOutputName($event_name . 'Test.php');

        // add specific configuration for my builder
        $testBuilder->setVariable('className', 'EventBuilder');
        $testBuilder->setVariable('aggregate', $aggregate);
        $testBuilder->setVariable('event_name', $event_name);
        $testBuilder->setVariable('properties', $properties);

        // set template name
        $testBuilder->setTemplateName('EventTest.php.twig');

        // create a generator
        $generator = new Generator();
        $generator->setTemplateDirs(array(
            __DIR__ . '/../Resources/skeleton/event',
        ));

        // allways regenerate classes even if they exist -> no cache
        $generator->setMustOverwriteIfExists(true);

        // set common variables
        $generator->setVariables(array(
            'namespace' => 'Mbed67\GeneratorBundle\Generated',
        ));

        // add the builder to the generator
        $generator->addBuilder($builder);
        $generator->addBuilder($testBuilder);

        // Run generation for all builders
        $generator->writeOnDisk(__DIR__ . '/../Generated/' . $aggregate);

        $output->writeln('Event ' . $event_name . ' has been written to disk.');
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
        $output->writeln('You have just selected: ' . $type);

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

    private function getPropertyFaker(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper
    ): string {
        $question = new ChoiceQuestion(
            'Please select faker for the property: ',
            ['uuid', 'email', 'string', 'int', 'firstName', 'infix', 'surname', 'atomDate'],
            '0'
        );

        $question->setErrorMessage('Faker %s is invalid.');

        $faker = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: ' . $faker);

        return $faker;
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
