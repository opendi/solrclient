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
