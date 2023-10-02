<?php
/**
 * CLRequest.php
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
 * Interface CLRequest
 * Specifies the contract for a Code-lib request, for instance a client request such as an Http Request.
 * @package cl\contract
 */
interface CLRequest
{
    public function get($key, $newVal = null);

    public function post($key, $newVal = null);

    public function isUserRequest();

    public function isGet();

    public function isPost();

    public function isOptions() : bool;

    public function isJson();

    public function acceptJson();

    public function isSecure();

    public function getAttachments();
    
    public function getFilesInfo($key = null);

    /**
     * Returns the $_POST array
     * @return mixed
     */
    public function &getPost();

    /**
     * Returns the $_GET array
     * @return mixed
     */
    public function &getGet();

    /**
     * Returns the $_SERVER array
     * @return mixed
     */
    public function getServer();

    /**
     * Returns the specified header
     * @param string $header entry into the server array
     * @return string or null
     */
    public function getHeader(string $header) : ?string;

    /**
     * @return mixed
     */
    public function getMethod();

    public function &getRequest();

    public function setRequest(array $request);

    public function isAjax();

    /**
     * @return mixed
     */
    public function &getJsonData();

    /**
     * @param bool $isTextInput set to true if you are reading text data and require line ending translation. By default a binary stream is assumed
     * @return bool|resource
     */
    public function getInputStream($isTextInput = false);

    /**
     * Returns the id of the current request, ie, the CL Flow id
     * @return mixed
     */
    public function getRequestId();

    /**
     * @param mixed $appConfig
     */
    public function setAppConfig($appConfig): void;

    /**
     * @return mixed
     */
    public function getLang();

    public function redirect($newuri, $http_response_code = null);
}
