<?php

namespace Opendi\Solr\Client\Console\Commands;

use Opendi\Solr\Client\Console\AbstractCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeleteCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('delete')
            ->setDescription("Deletes documents from a core.")
            ->addArgument(
                'core',
                InputArgument::REQUIRED,
                'Name of the core to delete from.'
            )
            ->addOption(
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'Query to delete by, if not given deletes all entries in the core.',
                '*:*'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $core = $input->getArgument('core');
        $query = $input->getOption('query');

        $output->writeln("\n<comment>Deleting documents</comment>\n");

        $client = $this->getClient($input, $output);

        $output->writeln("    Core: <info>$core</info>");
        $output->writeln("   Query: <info>$query</info>\n");

        $count = $client->core($core)->count($query);

        if ($count === 0) {
            $output->writeln("<comment>No documents found matching query. Nothing to delete.</comment>");
            return;
        }

        $output->writeln("Found <comment>$count</comment> documents matching the query.");

        $helper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion('Are you sure you want to delete [yN]? ', false);

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("\n<comment>Aborted</comment>\n");
            return;
        }

        $output->writeln("Deleting <comment>$count</comment> documents...");

        $result = $client->core($core)->deleteByQuery($query);

        $output->writeln("<info>Done.</info>\n");
    }
}
