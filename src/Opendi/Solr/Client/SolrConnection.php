<?php
/**
 *
 */

namespace Opendi\Solr\Client;


class SolrConnection
{
    private $config;
    private $endpoint;

    private $curlHandle;

    function __construct($config)
    {
        if ($config == null) {
            throw new SolrException("Configuration must not be null");
        }

        if (is_array($config)) {
            if (!isset($config['endpoint'])) {
                throw new SolrException("Configuration must not be null");
            }
            $this->endpoint = $config['endpoint'];
            $this->config = $config;
        } else {
            $this->endpoint = $config;
            $this->config = [ "endpoint" => $config ];
        }
    }

    public function connect()
    {
        $this->curlHandle = curl_init();

        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_HEADER, 0);
    }

    public function select(SolrSelect $select) {
        return $this->execute('select', $select->get());
    }

    public function execute($command, $query) {
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->endpoint . $command . '/?' . $query);

        if (!$result = curl_exec($this->curlHandle)) {
            throw new SolrException(curl_error($this->curlHandle));
        }
        return $result;
    }

    public function disconnect() {
        curl_close($this->curlHandle);
    }
}