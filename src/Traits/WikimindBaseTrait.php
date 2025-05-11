<?php

namespace Denason\Wikimind\Traits;

use Denason\Wikimind\Exceptions\WikimindException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use function PHPUnit\Framework\isEmpty;

/**
 * Trait WikimindBaseTrait
 *
 * Provides basic HTTP methods and configuration loading for Wikidata and SPARQL endpoints.
 */
trait WikimindBaseTrait
{
    /**
     * The base URL for Wikidata API.
     *
     * @var string
     */
    protected string $wikidataUrl;

    /**
     * The SPARQL endpoint URL for advanced queries.
     *
     * @var string
     */
    protected string $sparqlUrl;

    /**
     * Initialize configuration values for API endpoints.
     *
     * @return void
     */
    protected function initWikimindConfig(): void
    {
        $this->wikidataUrl = config('wikimind.api.base_url');
        $this->sparqlUrl = config('wikimind.api.sparql_url');
    }

    /**
     * Perform a GET request to the specified Wikidata endpoint with optional parameters and headers.
     *
     * @param string $url The API URL to send the request to.
     * @param array $params Optional query parameters to include.
     * @param array $headers Optional HTTP headers to send.
     * @return array The decoded JSON response as an associative array.
     *
     * @throws WikimindException If the request fails or response is invalid.
     */
    protected function fetch(string $url, array $params = [], array $headers = []): array
    {
        try {
            $http = Http::timeout(config('wikimind.api.timeout'))
                ->retry(...config('wikimind.api.retry'));

            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->get($url, $params);

            if ($response->failed()) {
                throw new WikimindException("Wikidata API request failed with status: " . $response->status());
            }

            $json = $response->json();

            if (!is_array($json)) {
                throw new WikimindException("Invalid JSON response from Wikidata.");
            }

            return $json;

        } catch (\Throwable $e) {
            $this->logError($e, $url, $params);
            throw new WikimindException("Unexpected error during Wikidata API call.", 0, $e);
        }
    }

    /**
     * Log an API error with full context.
     *
     * @param \Throwable $e The exception thrown.
     * @param string $url The URL that was called.
     * @param array $params The parameters sent in the request.
     * @return void
     */
    protected function logError(\Throwable $e, string $url = '', array $params = []): void
    {
        logger()->error("Wikidata API Error", [
            'message' => $e->getMessage(),
            'url' => $url,
            'params' => $params,
        ]);
    }

    /**
     * Execute a SPARQL query against the Wikidata SPARQL endpoint.
     *
     * @param string $query The SPARQL query string.
     * @return array The decoded results.
     *
     * @throws WikimindException If the request fails or response is invalid.
     */
    protected function runSparqlQuery(string $query): array
    {


        try {

            $response = Http::timeout(config('wikimind.api.timeout'))
                ->retry(...config('wikimind.api.retry'))
                ->withHeaders([
                    'Accept' => 'application/sparql-results+json',
                ])
                ->get($this->sparqlUrl, [
                    'query' => $query,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                throw new WikimindException("SPARQL request failed with HTTP status code: " . $response->status());
            }

            $json = $response->json();

            if (!isset($json['results']['bindings'])) {
                throw new WikimindException("Malformed SPARQL response: 'results.bindings' key is missing.");
            }

            return $json;

        } catch (ConnectException $e) {
            $this->logError($e, $this->sparqlUrl, ['query' => $query]);
            throw new WikimindException("Failed to connect to the SPARQL endpoint. Please check your internet connection or the server availability.", 0, $e);

        } catch (RequestException $e) {
            $this->logError($e, $this->sparqlUrl, ['query' => $query]);
            throw new WikimindException("Network error occurred while executing the SPARQL query. Please verify your network settings or proxy configuration.", 0, $e);

        } catch (\Throwable $e) {
            $this->logError($e, $this->sparqlUrl, ['query' => $query]);
            throw new WikimindException("An unexpected error occurred while executing the SPARQL query.", 0, $e);
        }
    }


    /**
     * Execute a simple API query to Wikidata.
     *
     * @param string $url The full API URL.
     * @return array The decoded results.
     *
     * @throws WikimindException If the request fails or response is invalid.
     */
    protected function runApiQuery(string $url): array
    {
        return $this->fetch($url);
    }

    /**
     * Format a raw entity response from Wikidata into a simple associative array.
     *
     * @param array $raw The raw entity data from Wikidata.
     * @return array Formatted entity with id, label, and description.
     */
    protected function formatEntityResult(array $raw): array
    {
        return [
            'id' => $raw['id'] ?? '',
            'label' => $raw['labels']['en']['value'] ?? '',
            'description' => $raw['descriptions']['en']['value'] ?? '',
        ];
    }

    /**
     * Get the label (name) of an entity based on its ID.
     *
     * @param string $entityId The Wikidata entity ID (e.g., Q42).
     * @return string The entity label, or an empty string if not found.
     */
    protected function getEntityLabel(string $entityId): string
    {
        $url = $this->wikidataUrl . '?action=wbgetentities&ids=' . $entityId . '&format=json&languages=en';

        $data = $this->runApiQuery($url);

        return $data['entities'][$entityId]['labels']['en']['value'] ?? '';
    }

    /**
     * Normalize an entity input (URL or ID) into a clean Wikidata ID (e.g., Q42).
     *
     * @param string $input The input which may be a full URL or a simple ID.
     * @return string The normalized entity ID.
     */
    protected function normalizeEntityId(string $input): string
    {
        $id = trim($input);
        if (!preg_match('/^[QP]\d+$/i', $input)) {
            throw new WikimindException("Invalid entity ID format: {$input}");
        }
        return strtoupper($input);
    }
}
