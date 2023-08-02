<?php
/**
 * CLSimpleHttpClient.php
 */

namespace cl\core;
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


use cl\contract\CLHttpClientRequest;
use cl\contract\CLResponse;
use cl\plugin\CLBaseResponse;
use cl\web\CLHtmlApp;

/**
 * Class CLSimpleHttpClient
 * Provides a simple to use HttpClient you can use to send http requests from your App
 * It requires an underlying client to be configured. It currently supports Guzzle and cURL.
 * Make sure you have one of those clients properly installed, and then configure it in you App's config like this:
 * $config['httpclient'] = 'guzzle'; or $config['httpclient'] = 'curl';
 * Then populate and instance of CLHttpClientRequest (implemented by CLBasicHttpClientRequest) with your request data,
 * and pass it to an instance of CLSimpleHttpClient.
 * @package cl\core
 */
class CLSimpleHttpClient implements \cl\contract\CLHttpClient
{
    public function get(CLHttpClientRequest $request): CLResponse
    {
        $request->setMethod(HTTP_GET);
        return $this->send($request);
    }

    public function post(CLHttpClientRequest $request): CLResponse
    {
        $request->setMethod(HTTP_POST);
        return $this->send($request);
    }

    public function send(CLHttpClientRequest $request): CLResponse {
        $httpclient = CLHtmlApp::$clapp->getAppConfig()->getAppConfig('httpclient');
        $response = new CLBaseResponse();
        if ($httpclient == null) {
            _log('No http client configured, unable to send http client request');
            $response->setVar('status', '501');
            return $response;
        }
        if ($httpclient == 'guzzle') {
            return $this->sendWithGuzzle($request);
        }
        if ($httpclient == 'curl') {
            return $this->sendWithCurl($request);
        }
        return $response;
    }

    /**
     * Uses Guzzle to fulfill the request.
     * Requires it to be added to the App's composer.json or directly to the vendors folder of the App
     * Once added, configure it in your App's CLConfig as the default httpclient, with an entry such as:
     * $config['httpclient'] = 'guzzle';
     * @param CLHttpClientRequest $request
     * @param int $timeout
     * @return CLResponse
     */
    private function sendWithGuzzle(CLHttpClientRequest $request, $timeout = 100): CLResponse {
        $client = new \GuzzleHttp\Client();
        $options = ['timeout' => $timeout];
        $credentials = $request->getCredentials();
        if ($credentials != null && count($credentials) >= 2) {
            $options['auth'] = $credentials;
        }
        if ($request->getMethod() == HTTP_POST) {
            $options['form_params'] = $request->getData() ?? [];
        } elseif ($request->getMethod() == HTTP_JSON) {
            $options['json'] = $request->getData() ?? [];
            $request->setMethod(HTTP_POST);
        }
        $options['headers'] = $request->getHeaders();
        $options['verify'] = $request->getVerifyHost();
        $res = $client->request($request->getMethod(), $request->getUrl(), $options);
        $response = new CLBaseResponse();
        $response->setVar(STATUS_CODE, $res->getStatusCode()); // ex. '200'
        if ($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {
            $response->setHeader('content-type', $res->getHeader('content-type')[0]);
            $response->addPayload($res->getBody());
        }
        return $response;
    }

    /**
     * Curl send post request, support HTTPS protocol
     * @param string $url The request url
     * @param array $data Data to send
     * @param string $refer request refer
     * @param int $timeout Timeout seconds
     * @param array $header request headers
     * @return mixed
     */
    private function sendWithCurl(CLHttpClientRequest $request, $timeout = 100)
    {
        set_time_limit(0);
        // check if cURL is installed
        if (!defined('CURLOPT_AUTOREFERER')) {
            //throw new \Exception('cUrl selected in Config but appears not installed');
        }
        $response = new CLBaseResponse();
        $curlObj = curl_init();
        $ssl = stripos($request->getUrl(),'https://') === 0 ? true : false;
        $options = [
            CURLOPT_URL => $request->getUrl(),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_AUTOREFERER => 1,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_HTTPHEADER => ['Expect:'],
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ];
        $credentials = $request->getCredentials();
        if ($credentials != null && count($credentials) >= 2) {
            $auth_method = $credentials[2] ?? CURLAUTH_BASIC | CURLAUTH_DIGEST;
            $options[CURLOPT_HTTPAUTH] = $auth_method;
            $options[CURLOPT_USERPWD] = "$credentials[0]:$credentials[1]";
            if ($auth_method & CURLAUTH_BASIC === CURLAUTH_BASIC) {
                $request->addHeader('Authorization', 'Basic '. base64_encode("$credentials[0]:$credentials[1]"));
            }
        }
        $headers = $request->getHeaders();
        if (!empty($headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        if ($ssl) {
            //support https
            $options[CURLOPT_SSL_VERIFYHOST] = false;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        if ($request->getMethod() == HTTP_POST) {
            $options[CURLOPT_POSTFIELDS] = $request->getData() ?? [];
        } elseif ($request->getMethod() == HTTP_JSON) {
            $json = \json_encode($value, $options, $depth);
            if (\JSON_ERROR_NONE !== \json_last_error()) {
                throw new \Exception('Invalid json data in the request');
            }
            $options[CURLOPT_POSTFIELDS] = $json;
            $request->setMethod(HTTP_POST);
        }

        curl_setopt_array($curlObj, $options);
        $returnData = curl_exec($curlObj);
        if (curl_errno($curlObj)) {
            $response->setVar('status', 'failure');
            $response->setVar('error_message', curl_error($curlObj));
        }
        $info = curl_getinfo($curlObj);
        if (isset($info['http_code'])) {
            $response->setVar(STATUS_CODE, $info['http_code']);
        }
        $response->setHeader('content-type', $info['content_type']??'');
        if ($returnData != null) {
            $response->addPayload($returnData);
        } else {
            $response->addPayload('No data returned');
        }
        curl_close($curlObj);
        return $response;
    }
}
