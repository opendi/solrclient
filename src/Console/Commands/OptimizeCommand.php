<?php

namespace Opendi\Solr\Client\Console\Commands;

use Opendi\Solr\Client\Console\AbstractCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class OptimizeCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('optimize')
            ->setDescription("Optimizes a SOLR core.")
            ->addArgument(
                'core',
                InputArgument::REQUIRED,
                'Name of the core.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getClient($input, $output);

        $core = $input->getArgument('core');

        $result = $client->core($core)->optimize();

        $time = $result['responseHeader']['QTime'];

        if ($time >= 1000) {
        	$time = number_format($time / 1000, 2) . " s";
        } else {
        	$time .= " ms";
        }

        $output->writeln("Done.");
        $output->writeln("Time taken: <comment>$time</comment>\n");
    }
}
