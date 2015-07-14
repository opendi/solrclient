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

use Opendi\Solr\Client\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommitCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('commit')
            ->setDescription("Commits a SOLR core.")
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

        $result = $client->core($core)->commit();

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
