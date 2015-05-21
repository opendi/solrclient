<?php
/*
 *  Copyright 2015 Opendi Software AG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */

namespace Opendi\Solr\Client\Console\Commands;

use GuzzleHttp\Event\ProgressEvent;

use Opendi\Solr\Client\Client;
use Opendi\Solr\Client\Console\AbstractCommand;

use SplFileInfo;

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
                InputArgument::IS_ARRAY,
                'Path to a file or folder to import from (recursively).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $core = $input->getArgument('core');
        $sources = $input->getArgument('source');

        $client = $this->getClient($input, $output);

        $output->writeln("Collection: <info>$core</info>");
        $output->writeln("--");

        foreach ($sources as $source) {
            if (!file_exists($source)) {
                throw new \Exception("Source not found: $source");
            }

            if (is_dir($source)) {
                $finder = new Finder();
                $finder->files()->in($source)->sortByName();

                foreach ($finder as $file) {
                    $this->importFile($client, $core, $file, $output);
                }
            } else {
                $file = new SplFileInfo($source);
                $this->importFile($client, $core, $file, $output);
            }
        }

        $output->writeln("<info>Done.</info>");
    }

    private function importFile(Client $client, $core, SplFileInfo $source, $output)
    {
        $output->writeln("Importing data from: <info>$source</info>");

        $path = "$core/update";
        $fp = fopen($source, 'r');

        $query = [
            'commit' => 'true',
            'wt' => 'json'
        ];

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $reply = $client->post($path, $query, $fp, $headers)->json();

        if ($reply['responseHeader']['status'] != 0) {
            throw new \Exception("Solr returned an error.");
        }

        $time = $reply['responseHeader']['QTime'];
        $output->writeln("Time taken: <comment>$time ms</comment>\n");
    }
}
