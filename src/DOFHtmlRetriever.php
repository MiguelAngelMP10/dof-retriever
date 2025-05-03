<?php

namespace MiguelAngelMP10\DOFRetriever;

use DOMElement;
use MiguelAngelMP10\DOFRetriever\Contracts\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class DOFHtmlRetriever
{
    private const BASE_URL = 'https://dof.gob.mx/indicadores_detalle.php';
    private const DOF_URL = 'https://dof.gob.mx/indicadores.php';

    public function __construct(
        protected HttpClientInterface $httpClient
    ) {}

    /**
     * Retrieves available indicators from DOF select list.
     *
     * @return array<int, string> Indicator ID => Description
     */
    public function getAvailableIndicators(): array
    {
        $html = $this->httpClient->get(self::DOF_URL);

        $crawler = new Crawler($html);
        $options = $crawler->filter('select[name="cod_tipo_indicador"] option');

        $indicators = [];

        foreach ($options as $option) {
            /** @var DOMElement $option */
            $value = $option->getAttribute('value');
            $label = trim($option->textContent);

            if ($value !== '') {
                $indicators[(int)$value] = $label;
            }
        }

        return $indicators;
    }

    /**
     * Retrieves exchange rates from Mexico's DOF for the given date range.
     *
     * @param int $since Start date as a timestamp
     * @param int $until End date as a timestamp
     * @param int $indicatorId Indicator code from DOF
     * @return array<string, string> Date (Y-m-d) => Value
     */
    public function retrievePeriod(int $since, int $until, int $indicatorId = 158): array
    {
        $url = $this->buildUrl($since, $until, $indicatorId);
        $html = $this->httpClient->get($url);

        if (empty($html) || str_contains($html, 'Database error') || str_contains($html, 'Session halted')) {
            throw new \RuntimeException("Unable to fetch content from $url.");
        }

        return $this->extractExchangeRates($html, $since, $until);
    }

    /**
     * Builds the full URL for a given indicator and date range.
     */
    private function buildUrl(int $since, int $until, int $indicatorId): string
    {
        return sprintf(
            '%s?cod_tipo_indicador=%d&hfecha=%s&dfecha=%s&accionI=imprimir',
            self::BASE_URL,
            $indicatorId,
            date('d/m/Y', $until),
            date('d/m/Y', $since)
        );
    }

    /**
     * Parses the HTML content and extracts the exchange rates.
     */
    private function extractExchangeRates(string $html, int $since, int $until): array
    {
        $crawler = new Crawler($html);
        $result = [];

        for ($date = $since; $date <= $until; $date = strtotime('+1 day', $date)) {
            $formatted = date('d-m-Y', $date);
            $node = $crawler->filterXPath(sprintf("//td[text()='%s']", $formatted));

            if ($node->count() === 0) {
                continue;
            }

            $value = $node->siblings()->last()->text();

            if (is_numeric($value)) {
                $result[date('Y-m-d', $date)] = $value;
            }
        }

        return $result;
    }
}
