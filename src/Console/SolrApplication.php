<?php

namespace Opendi\Solr\Client\Console;

use Symfony\Component\Console\Application;

class SolrApplication extends Application
{
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new Commands\CommitCommand();
        $defaultCommands[] = new Commands\DeleteCommand();
        $defaultCommands[] = new Commands\ImportCommand();
        $defaultCommands[] = new Commands\PingCommand();
        $defaultCommands[] = new Commands\OptimizeCommand();
        $defaultCommands[] = new Commands\StatusCommand();
        return $defaultCommands;
    }
}
