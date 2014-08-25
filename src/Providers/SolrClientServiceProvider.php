<?php

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
            'base_url' => null,
        ];

        $this->settings = array_merge($defaultSettings, $settings);

        if (empty($this->settings['base_url'])) {
            throw new \Exception("You must give a base_url for the solr provider.");
        }
    }

    public function register(Container $container)
    {
        $container['solr'] = function () {
            $guzzle = new GuzzleClient([
                'base_url' => $this->settings['base_url']
            ]);

            return new SolrClient($guzzle);
        };
    }
}
