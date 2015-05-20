<?php

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
