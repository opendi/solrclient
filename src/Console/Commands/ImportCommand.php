<?php

namespace Opendi\Solr\Client\Console\Commands;

use Opendi\Solr\Client\Console\AbstractCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Imports JSON encoded data from a file or a folder into a Solr core.
 */
class ImportCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('import')
            ->setDescription("Import JSON encoded data into a Solr core.")
            ->addArgument(
                'core',
                InputArgument::REQUIRED,
                'Name of the core to import into.'
            )
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'Path to a file or folder to import from (recursively).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $coreName = $input->getArgument('core');
        $source = $input->getArgument('source');

        $client = $this->getClient($input, $output);
        $core = $client->core($coreName);

        $output->writeln("Collection: <info>$coreName</info>");
        $output->writeln("--");

        if (!file_exists($source)) {
            throw new \Exception("Source not found: $source");
        }

        if (is_dir($source)) {
            $finder = new Finder();
            $finder->files()->in($source);

            foreach ($finder as $file) {
                $this->importFile($core, $coreName, $file, $output);
            }
        } else {
            $this->importFile($core, $coreName, $source, $output);
        }

        $output->writeln("<info>Done.</info>");
    }

    private function importFile($core, $coreName, $source, $output)
    {
        $output->write("Importing data from: <info>$source</info>");

        $path = "$coreName/update";
        $query = ["commit" => "true"];
        $body = fopen($source, 'r');
        $headers = ['Content-Type' => 'application/json'];

        $reply = $core->post($path, $query, $body, $headers);

        if ($reply['responseHeader']['status'] != 0) {
            throw new \Exception("Solr returned an error.");
        }

        $time = $reply['responseHeader']['QTime'];
        $output->writeln(" (<info>$time ms</info>)");
    }
}
