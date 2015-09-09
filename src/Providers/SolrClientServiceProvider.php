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

namespace Opendi\Solr\Client\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use GuzzleHttp\Client as GuzzleClient;
use Opendi\Solr\Client\Client as SolrClient;

/**
 * A service provider for the Pimple DI container.
 */
class SolrClientServiceProvider implements ServiceProviderInterface
{
    /**
     * Guzzle options.
     *
     * @var array
     * @see http://guzzle.readthedocs.org/en/latest/request-options.html
     */
    private $options;

    public function __construct(array $options)
    {
        if (empty($options['base_uri'])) {
            throw new \InvalidArgumentException("You must specify the base_uri option.");
        }

        $this->options = $options;
    }

    /**
     * Factory method for simpler initiation.
     *
     * @param  string $host     Solr host URI.
     * @param  string $user     Username for basic auth.
     * @param  string $pass     Password for basic auth.
     * @param  array  $options  Additional Guzzle options.
     *
     * @see http://guzzle.readthedocs.org/en/latest/request-options.html
     *
     * @return SolrClientServiceProvider
     */
    public static function factory($host, $user = null, $pass = null, array $options = [])
    {
        $options = ['base_uri' => $host];
        if (!empty($user) && !empty($pass)) {
            $options['auth'] = [$user, $pass];
        }

        return new self($options);
    }

    public function register(Container $container)
    {
        $container['solr'] = function () {
            $guzzle = new GuzzleClient([
                'base_uri' => $this->options['base_uri']
            ]);

            return new SolrClient($guzzle);
        };
    }
}
