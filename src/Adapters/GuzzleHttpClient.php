<?php

namespace MiguelAngelMP10\DOFRetriever\Adapters;

use GuzzleHttp\Exception\GuzzleException;
use MiguelAngelMP10\DOFRetriever\Contracts\HttpClientInterface;
use GuzzleHttp\Client;

class GuzzleHttpClient implements HttpClientInterface
{
    protected Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client([
            'timeout' => 10.0,
            'verify' => false,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $url, array $options = []): string
    {
        $response = $this->client->request('GET', $url, $options);
        return $response->getBody()->getContents();
    }
}