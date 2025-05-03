<?php

namespace MiguelAngelMP10\DOFRetriever\Adapters;

use MiguelAngelMP10\DOFRetriever\Contracts\HttpClientInterface;

class FileGetContentsClient implements HttpClientInterface
{

    public function get(string $url, array $options = []): string
    {
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false]
        ]);
        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            throw new \RuntimeException("Failed to fetch content from $url");
        }

        return $content;
    }
}