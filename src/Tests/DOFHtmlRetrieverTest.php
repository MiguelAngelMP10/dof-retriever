<?php

namespace MiguelAngelMP10\DOFRetriever\Tests;

use MiguelAngelMP10\DOFRetriever\Adapters\GuzzleHttpClient;
use MiguelAngelMP10\DOFRetriever\Contracts\HttpClientInterface;
use MiguelAngelMP10\DOFRetriever\DOFHtmlRetriever;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DOFHtmlRetrieverTest extends TestCase
{
    private MockObject|HttpClientInterface $httpClient;
    private DOFHtmlRetriever $retriever;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->retriever = new DOFHtmlRetriever($this->httpClient);
    }

    public function testRetrievePeriod(): void
    {
        $retriever = new DOFHtmlRetriever(new GuzzleHttpClient());
        $since = strtotime('2025-01-01');
        $until = strtotime('2025-04-21');

        // Recupera las tasas de cambio para el rango de fechas
        $exchangeRates = $retriever->retrievePeriod($since, $until);

        // Verifica que se han recuperado las 74 fechas esperadas
        $this->assertCount(74, $exchangeRates);

        // Verifica que el 2025-01-01 no esté en las tasas (feriado)
        $this->assertArrayNotHasKey('2025-01-01', $exchangeRates);

        // Verifica que el 2025-01-02 esté en las tasas (hábil)
        $this->assertArrayHasKey('2025-01-02', $exchangeRates);

        // Verifica que el 2025-04-17 no esté en las tasas (feriado)
        $this->assertArrayNotHasKey('2025-04-17', $exchangeRates);

        // Verifica que el 2025-04-18 no esté en las tasas (feriado)
        $this->assertArrayNotHasKey('2025-04-18', $exchangeRates);

        // Verifica que el 2025-04-19 no esté en las tasas (sábado)
        $this->assertArrayNotHasKey('2025-04-19', $exchangeRates);

        // Verifica que el 2025-04-20 no esté en las tasas (domingo)
        $this->assertArrayNotHasKey('2025-04-20', $exchangeRates);

        // Verifica que el 2025-04-21 esté en las tasas (lunes)
        $this->assertArrayHasKey('2025-04-21', $exchangeRates);

        // Verifica las tasas de cambio para fechas específicas
        $this->assertSame('20.786200', $exchangeRates['2025-01-02']);
        $this->assertSame('20.691700', $exchangeRates['2025-01-03']);
        $this->assertSame('20.606800', $exchangeRates['2025-02-04']);
        $this->assertSame('20.426800', $exchangeRates['2025-02-05']);
        $this->assertSame('20.508000', $exchangeRates['2025-03-03']);
        $this->assertSame('20.433300', $exchangeRates['2025-03-04']);
        $this->assertSame('20.438000', $exchangeRates['2025-04-01']);
        $this->assertSame('19.973700', $exchangeRates['2025-04-21']);
    }

    public function testRetrievePeriodEmptyRange(): void
    {
        $retriever = new DOFHtmlRetriever(new GuzzleHttpClient());
        $since = strtotime('2025-01-01');
        $until = strtotime('2025-01-01'); // Solo un día, posiblemente sin datos

        $exchangeRates = $retriever->retrievePeriod($since, $until);

        // Verifica que el array esté vacío si no hay datos
        $this->assertEmpty($exchangeRates);
    }

    public function testRetrievePeriodSingleBusinessDay(): void
    {
        $retriever = new DOFHtmlRetriever(new GuzzleHttpClient());
        $since = strtotime('2025-01-02');
        $until = strtotime('2025-01-02'); // Solo un día

        $exchangeRates = $retriever->retrievePeriod($since, $until);

        // Verifica que solo haya un elemento
        $this->assertCount(1, $exchangeRates);
        $this->assertArrayHasKey('2025-01-02', $exchangeRates); // Verifica que el día esté presente
    }

    public function testRetrievePeriodWeekend(): void
    {
        $retriever = new DOFHtmlRetriever(new GuzzleHttpClient());
        $since = strtotime('2025-04-18'); // Viernes
        $until = strtotime('2025-04-20'); // Domingo

        $exchangeRates = $retriever->retrievePeriod($since, $until);

        // Verifica que el sábado y domingo no estén en el array
        $this->assertArrayNotHasKey('2025-04-19', $exchangeRates); // Sábado
        $this->assertArrayNotHasKey('2025-04-20', $exchangeRates); // Domingo
    }

    public function testRetrievePeriodNoData(): void
    {
        $retriever = new DOFHtmlRetriever(new GuzzleHttpClient());
        $since = strtotime('2026-01-01'); // Año futuro
        $until = strtotime('2026-01-01');

        $exchangeRates = $retriever->retrievePeriod($since, $until);

        // Verifica que el resultado esté vacío porque no hay datos para el rango
        $this->assertEmpty($exchangeRates);
    }

    public function testRetrievePeriodThrowsException(): void
    {
        $this->httpClient->method('get')->willThrowException(new \RuntimeException('Error en la conexión'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error en la conexión');

        $this->retriever->retrievePeriod(strtotime('2025-01-01'), strtotime('2025-04-21'));
    }

    public function testRetrievePeriodPerformance(): void
    {
        $retriever = new DOFHtmlRetriever(new GuzzleHttpClient());
        $since = strtotime('2020-01-01');
        $until = strtotime('2025-12-31');

        // Inicia el temporizador
        $start = microtime(true);
        $exchangeRates = $retriever->retrievePeriod($since, $until);
        $end = microtime(true);

        // Verifica que el tiempo de ejecución no sea excesivo (ejemplo: 2 segundos)
        $this->assertLessThan(2, $end - $start);
    }

}
