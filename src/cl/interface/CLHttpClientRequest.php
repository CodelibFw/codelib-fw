<?php
/**
 * CLHttpClientRequest.php
 */

namespace cl\contract;
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

/**
 * Interface CLHttpClientRequest
 * Contract for an Http client request
 * @package cl\contract
 */
interface CLHttpClientRequest
{
    /**
     * Sets the http method (CL constants HTTP_POST, HTTP_GET, HTTP_PUT, etc)
     * Used when creating a client request from your App to another server
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): CLHttpClientRequest;

    /**
     * Returns the http method set for this request
     * @return string
     */
    public function getMethod(): string;

    /**
     * Returns the destination url of this request
     * (as opposed to a regular request received by your App from a web browser)
     * @return string
     */
    public function getUrl(): string;

    /**
     * Used when creating a client request from your App, to set the destination url
     * @param string $url
     * @return void
     */
    public function setUrl(string $url): CLHttpClientRequest;

    /**
     * Only used when creating a client request from your App to another server
     * @return array user credentials as an indexed array containing the username and password
     */
    public function getCredentials(): array;

    /**
     * Only used when creating a client request from your App to another server
     * @param array $credentials contains username and password, as in ['user','mystrongpassword']
     * @return void
     */
    public function setCredentials(array $credentials): CLHttpClientRequest;

    /**
     * Data to be sent with this request (if a post or get request)
     * @param array $data an associative array
     * @return void
     */
    public function setData(array $data): CLHttpClientRequest;

    /**
     * Returns the post data of this request
     * @return array
     */
    public function getData(): array;

    /**
     * Set headers for the request
     * @param array $headers an associative array
     */
    public function setHeaders(array $headers): CLHttpClientRequest;

    /**
     * Add an individual header to the request
     * @param string $name
     * @param string $value
     */
    public function addHeader(string $name, string $value): CLHttpClientRequest;

    /**
     * Returns all the headers currently added to the request
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Return a specific header or null if the header doesn't exist
     * @param string $name
     * @return string
     */
    public function getHeader(string $name): ?string;

    /**
     * Set to true, if you want the http client to do host certificate verification. Defaults to false
     * @param bool $value
     * @return mixed
     */
    public function setVerifyHost(bool $value): CLHttpClientRequest;

    /**
     * Returns the state of the host verification flag for this request
     * @return bool
     */
    public function getVerifyHost(): bool;
}
