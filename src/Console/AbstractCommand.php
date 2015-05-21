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

use Opendi\Solr\Client\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\HeadersEvent;
use GuzzleHttp\Exception\ParseException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    private $client;

    protected function configure()
    {
        // Read defaults from env variables.
        // If they are not set, use some sensible defaults.
        $baseURL = getenv('OPENDI_SOLR_URL');
        if ($baseURL === false) {
            $baseURL = 'http://localhost:8983/solr/';
        }

        $username = getenv('OPENDI_SOLR_USER');
        if ($username === false) {
            $username = null;
        }

        $password = getenv('OPENDI_SOLR_PASS');
        if ($password === false) {
            $password = null;
        }

        $this
            ->addOption(
                'url',
                'u',
                InputOption::VALUE_REQUIRED,
                'Base solr URL',
                $baseURL
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Username for basic HTTP authentication',
                $username
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'Password for basic HTTP authentication',
                $password
            );
    }

    /**
     * Constructs a Solr client from input params.
     *
     * @return Client
     */
    protected function getClient(InputInterface $input, OutputInterface $output)
    {
        if (isset($this->client)) {
            return $this->client;
        }

        $baseURL = $input->getOption('url');
        $username = $input->getOption('username');
        $password = $input->getOption('password');

        // Add trailing slash if one doesn't exist
        if ($baseURL[strlen($baseURL) - 1] !== '/') {
            $baseURL .= '/';
        }

        $output->writeln("Solr URL: <info>$baseURL</info>");

        if (!empty($username)) {
            $output->writeln("Basic auth: <info>$username</info>");
        }

        // Guzzle options
        $options = ['base_url' => $baseURL];
        if (isset($username)) {
            $options['defaults']['auth'] = [$username, $password];
        }

        // Construct and return the client
        $guzzle = new GuzzleClient($options);

        // Setup logging and progress bars
        $subscriber = new OutputSubscriber($output);
        $guzzle->getEmitter()->attach($subscriber);

        return new Client($guzzle);
    }
}
