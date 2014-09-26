<?php

namespace Opendi\Solr\Client\Console\Commands;

use Opendi\Solr\Client\Console\AbstractCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PingCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('ping')
            ->setDescription("Pings the Solr server to check it's up.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getClient($input, $output);

        $ping = $client->ping();

        $time = $ping['responseHeader']['QTime'];
        $status = $ping['status'];

        $output->writeln("Status: <info>$status</info>");
        $output->writeln("Time: <info>$time ms</info>");
    }
}
