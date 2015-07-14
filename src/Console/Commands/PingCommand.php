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

class PingCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('ping')
            ->setDescription("Pings the Solr server to check it's up.")
            ->addArgument(
                'core',
                InputArgument::REQUIRED,
                'Name of the core to ping.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getClient($input, $output);

        $core = $input->getArgument('core');

        $ping = $client->core($core)->ping();

        $time = $ping['responseHeader']['QTime'];
        $status = $ping['status'];

        $output->writeln("\nStatus: <info>$status</info>");
        $output->writeln("Time: <info>$time ms</info>");
    }
}
