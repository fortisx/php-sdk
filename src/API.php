<?php

declare(strict_types=1);

namespace FortisX\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class API
{
    private ?string $apiKey;

    private string $baseUrl;

    private int $timeout;

    private Client $client;

    public function __construct(?string $apiKey = null, string $baseUrl = 'https://api.fortisx.fi/v1', int $timeout = 10)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->client = new Client([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => $this->timeout,
            'http_errors' => false,
        ]);
    }

    /**
     * Performs a GET request.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws APIError
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $params]);
    }

    /**
     * Performs a POST request.
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws APIError
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Performs a PUT request.
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws APIError
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Performs a DELETE request.
     *
     * @param string $endpoint
     * @return array
     * @throws APIError
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Core request handler.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array
     * @throws APIError
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if (!empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $options['headers'] = isset($options['headers'])
            ? array_merge($headers, $options['headers'])
            : $headers;

        $uri = ltrim($endpoint, '/');

        try {
            $response = $this->client->request($method, $uri, $options);

            return $this->parseResponse($response);
        } catch (ConnectException $e) {
            throw new APIError('Network error', 0, ['reason' => $e->getMessage()]);
        } catch (RequestException $e) {
            $resp = $e->getResponse();

            if ($resp instanceof ResponseInterface) {
                $details = $this->decodeBody((string) $resp->getBody(), $resp->getHeaderLine('Content-Type'));
                $message = $e->getMessage() ?: 'Request failed';

                throw new APIError(
                    $message,
                    $resp->getStatusCode(),
                    is_array($details) ? $details : ['raw' => $details]
                );
            }

            throw new APIError($e->getMessage() ?: 'Request failed', 0);
        } catch (\Throwable $e) {
            throw new APIError($e->getMessage() ?: 'Unexpected error', 0);
        }
    }

    /**
     * @param ResponseInterface $res
     * @return array
     * @throws APIError
     */
    private function parseResponse(ResponseInterface $res): array
    {
        $status = $res->getStatusCode();
        $contentType = $res->getHeaderLine('Content-Type');
        $body = (string) $res->getBody();
        $decoded = $this->decodeBody($body, $contentType);

        if ($status < 200 || $status >= 300) {
            $message = $res->getReasonPhrase() ?: 'Request failed';

            throw new APIError($message, $status, is_array($decoded) ? $decoded : ['raw' => $body]);
        }

        if (is_array($decoded)) {
            return $decoded;
        }

        // Fallback: if server returned non-JSON, wrap it.
        return ['raw' => $body];
    }

    /**
     * @param string $body
     * @param string $contentType
     * @return array|string
     */
    private function decodeBody(string $body, string $contentType)
    {
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($body, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }

        return $body;
    }
}
