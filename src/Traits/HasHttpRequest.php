<?php

namespace Keviswang\Pay\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

trait HasHttpRequest
{
    protected function get($endpoint, $query = [], $headers = [])
    {
        return $this->request('get', $endpoint, [
            'headers' => $headers,
            'query' => $query,
        ]);
    }

    protected function post($endpoint, $params = [], ...$options)
    {
        $options = isset($options[0]) ? $options[0] : [];

        if (!is_array($params)) {
            $options['body'] = $params;
        } else {
            $options['form_params'] = $params;
        }

        return $this->request('post', $endpoint, $options);
    }

    protected function request($method, $endpoint, $options = [])
    {
        return $this->unwrapResponse($this->getHttpClient($this->getBaseOptions())->{$method}($endpoint, $options));
    }

    protected function getBaseOptions()
    {
        $options = [
            'base_uri' => method_exists($this, 'getBaseUri') ? $this->getBaseUri() : '',
            'timeout' => property_exists($this, 'timeout') ? $this->timeout : 5.0,
        ];

        return $options;
    }


    protected function getHttpClient(array $options = [])
    {
        return new Client($options);
    }

    protected function unwrapResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents = $response->getBody()->getContents();
        if (false !== stripos($contentType, 'json') || stripos($contentType, 'javascript')) {
            return json_decode($contents, true);
        } elseif (false !== stripos($contentType, 'xml')) {
            return json_decode(json_encode(simplexml_load_string($contents)), true);
        }
        return $contents;
    }
}
