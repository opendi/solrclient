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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('status')
            ->setDescription("Displays core status info.")
            ->addArgument(
                'core',
                InputArgument::OPTIONAL,
                'Name of the core, if not given will display status for all cores.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $core = $input->getArgument('core');
        $client = $this->getClient($input, $output);

        $data = $client->status($core);

        foreach($data['status'] as $name => $status) {
            $this->witeCoreStatus($output, $status);
        }
    }

    protected function witeCoreStatus(OutputInterface $output, $status)
    {
        $lastModified = new \DateTime($status['index']['lastModified']);
        $startTime = new \DateTime($status['startTime']);
        $upTime = $startTime->diff(new \DateTime());

        $lastModified = $lastModified->format("Y-m-d H:i:s");
        $startTime = $startTime->format('Y-m-d H:i:s');
        $upTime = $upTime->format('%a days, %h hours, %i minutes, %s seconds');

        $numDocs = $status['index']['numDocs'];
        $maxDoc = $status['index']['maxDoc'];
        $deletedDocs = $status['index']['deletedDocs'];
        $size = $status['index']['size'];

        $name = $status['name'];
        $title = "Core \"$name\"";

        $output->writeln("\n<comment>$title</comment>");
        $output->writeln(str_repeat("=", strlen($title)));

        $output->writeln("lastModified: <info>$lastModified</info>");
        $output->writeln("   startTime: <info>$startTime</info>");
        $output->writeln("      uptime: <info>$upTime</info>");
        $output->writeln("     numDocs: <info>$numDocs</info>");
        $output->writeln("      maxDoc: <info>$maxDoc</info>");
        $output->writeln(" deletedDocs: <info>$deletedDocs</info>");
        $output->writeln("        size: <info>$size</info>");
    }
}
