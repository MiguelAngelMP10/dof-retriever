<?php

namespace MiguelAngelMP10\DOFRetriever\Contracts;

interface HttpClientInterface
{
    public function get(string $url, array $options = []): string;
}