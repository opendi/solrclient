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

class SolrClientServiceProvider implements ServiceProviderInterface
{
    public function __construct($settings)
    {
        $defaultSettings = [
            'base_uri' => null,
        ];

        $this->settings = array_merge($defaultSettings, $settings);

        if (empty($this->settings['base_uri'])) {
            throw new \Exception("You must give a base_uri for the solr provider.");
        }
    }

    public function register(Container $container)
    {
        $container['solr'] = function () {
            $guzzle = new GuzzleClient([
                'base_uri' => $this->settings['base_uri']
            ]);

            return new SolrClient($guzzle);
        };
    }
}
