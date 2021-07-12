<?php
/**
 * CLCorsFilter.php
 */

namespace cl\filter;
/*
 * MIT License
 *
 * Copyright Codelib Framework (https://codelibfw.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

use cl\contract\CLFilter;
use cl\contract\CLRequest;
use cl\contract\CLResponse;
use cl\web\CLHtmlApp;

/**
 * Class CLCorsFilter
 * Implements a CORS Filter
 * @package cl\filter
 */
class CLCorsFilter implements CLFilter
{
    private $requestHeaders = ['HTTP_ORIGIN', 'HTTP_ACCESS_CONTROL_REQUEST_METHOD', 'HTTP_ACCESS_CONTROL_REQUEST_HEADERS'];

    /**
     * A CL Filter performs inspections or transformations on a request, and returns TRUE or FALSE to indicate
     * whether the flow should continue or a response should be sent to the client without further processing
     * CLFilter constructor.
     */
    public function __construct()
    {
    }

    /**
     *
     * @param CLResponse $response will store response data (headers, etc) produced by this filter
     * @return bool
     */
    public function filter(CLRequest $clrequest, CLResponse $response): bool
    {
        error_log('executing cors filter');
        _log('executing cors');
        $corsConfig = CLHtmlApp::$clapp->getAppConfig()->getCors();
        if ($corsConfig == null) { $corsConfig = $this->getDefaultCorsConfig(); }
        $requestedHeaders = $this->getRequestedCorsHeaders($clrequest);
        if (($originAssessment = $this->isOriginAllowed($requestedHeaders[$this->requestHeaders[0]] ?? null, $corsConfig, $response)) != null) {
            $response->setHeader('Access-Control-Allow-Origin', $originAssessment);
        } else {
            $response->setHeader('Access-Control-Allow-Origin', $corsConfig['Access-Control-Allow-Origin'][0]); // TODO: double check this line
        }
        if ($clrequest->isOptions()) {
            if (isset($requestedHeaders[$this->requestHeaders[1]])) {
                if (isset($corsConfig['Access-Control-Allow-Methods'])) {
                    $response->setHeader('Access-Control-Allow-Methods', implode(',', $corsConfig['Access-Control-Allow-Methods']));
                } else {
                    $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, HEAD, OPTIONS');
                }
            }
            if (isset($requestedHeaders[$this->requestHeaders[2]])) {
                if (isset($corsConfig['Access-Control-Allow-Headers'])) {
                    $response->setHeader('Access-Control-Allow-Headers', implode(',', $corsConfig['Access-Control-Allow-Headers']));
                } else {
                    $response->setHeader('Access-Control-Allow-Headers', '*');
                }
            }
            $response->setVar('nocontent', true);
            return false; // no further processing
        }
        return true;
    }

    private function getDefaultCorsConfig() {
        return array(
            'Access-Control-Allow-Origin' => ['*'],
            'Access-Control-Allow-Credentials' => false,
            'Access-Control-Allow-Methods' => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'],
            'Access-Control-Allow-Headers' => ['*'],
            'Access-Control-Expose-Headers' => [],
            'Access-Control-Max-Age' => 86400);
    }

    private function getRequestedCorsHeaders(CLRequest $clrequest) {
        $headers = [];
        foreach ($this->requestHeaders as $header) {
            if ($clrequest->getHeader($header) != null) {
                $headers[$header] = $clrequest->getHeader($header);
            }
        }
        return $headers;
    }

    private function isOriginAllowed($origin, $corsConfig, CLResponse $response) {
        if ($origin == null) { return null; }
        if ($corsConfig['Access-Control-Allow-Origin'][0] == '*') { return $origin; }
        $response->setHeader('Vary', 'Origin');
        foreach ($corsConfig['Access-Control-Allow-Origin'] as $allowedOrigin) {
            if ($origin === $allowedOrigin) { return $origin; }
        }
        return null;
    }
}
