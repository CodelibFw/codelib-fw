<?php
/**
 * CLBaseResponse.php
 */

namespace cl\plugin;
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

use cl\contract\CLResponse;

/**
 * Class CLBaseResponse
 * Implements a base class for a Plugin response
 * @package cl\plugin
 */
class CLBaseResponse implements CLResponse {
    private $response = array();
    private $payload = array();
    private $headers = array();

    /**
     * Returns the specified response entry
     * @param string $key key for the response entry
     * @return mixed|null
     */
    public function getVar(string $key)
    {
        if (array_key_exists($key, $this->response)) {
            return $this->response[$key];
        }
        return null;
    }

    /**
     * Sets a specific response entry
     * @param string $key key for the response entry
     * @param mixed $value value for the response entry
     * @param bool $append if true: appends value to existing key, false: replaces value if key exists
     * @return CLResponse
     */
    public function setVar(string $key, $value, bool $append = false): CLResponse
    {
        if ($append && isset($this->response[$key])) {
            $this->response[$key] .= $value;
        } else {
            $this->response[$key] = $value;
        }
        return $this;
    }

    /**
     * Add a bulk of response entries or variables as an associative array
     * Any existing entry will be overwritten
     * @param array $vars
     * @return CLResponse
     */
    public function addVars(array $vars): CLResponse
    {
        $this->response = array_merge($this->response, $vars);
        return $this;
    }

    /**
     * Returns an array with all the variables added to this response object
     * @return array
     */
    public function getVars(): array
    {
        return $this->response;
    }

    /**
     * Add a more complex structure to this response, such as an Object or an Array
     * @param $payload
     * @return CLResponse
     */
    public function addPayload($payload): CLResponse
    {
        $this->payload[] = $payload;
        return $this;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Add a response header to this object
     * @param string $header
     * @param string $value
     * @return CLResponse
     */
    public function setHeader(string $header, string $value): CLResponse
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Returns the value of a previously added response header
     * @param string $header
     * @return mixed
     */
    public function getHeader(string $header)
    {
        return $this->headers[$header];
    }

    /**
     * Bulk add headers to this response object
     * @param array $headers key/value pairs specifying headers to add to this response object
     * @return CLResponse
     */
    public function addHeaders(array $headers): CLResponse
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Returns an array with all the headers added to this response object
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
