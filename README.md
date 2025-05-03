# DOFRetriever

DOFRetriever es una librería PHP para recuperar indicadores del Diario Oficial de la Federación (DOF) de México.

## Instalación

Puedes instalarlo usando Composer:

```bash
composer require miguelangelmp10/dof-retriever
```

## Ejemplo de uso

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use MiguelAngelMP10\DOFRetriever\Adapters\FileGetContentsClient;
use MiguelAngelMP10\DOFRetriever\DOFHtmlRetriever;
use MiguelAngelMP10\DOFRetriever\Adapters\GuzzleHttpClient;

$retriever = new DOFHtmlRetriever(new GuzzleHttpClient());

// Obtener indicadores disponibles
$indicators = $retriever->getAvailableIndicators();
print_r($indicators);

// Obtener datos del 1 al 5 de enero de 2024 para el indicador 159
$data = $retriever->retrievePeriod(strtotime('2024-01-01'), strtotime('2024-01-05'), 159);
print_r($data);
```

## Licencia

MIT