<?php
/**
 * CLHttpRequest.php
 */
namespace cl\web;
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

use cl\contract\CLRequest;
use Exception;

/**
 * Class CLHttpRequest
 * Contains the data sent with a request into a PHP web app ($_GET and $_POST), as well as other relevant data
 * @package cl\web
 */
class CLHttpRequest implements CLRequest
{
    const POST = 'post';
    const GET = 'get';
    const AJAX = 'ajax';
    const INPUT_STREAM = 'php://input';
    // generally stores data from the $_POST array, $_GET array, etc...
    private $getData, $postData, $server, $files;
    private $appConfig;
    private $jsonData;
    private $lang;
    private $method;
    private $requestId;
    private $fileList = [];
    private $nodata; // to still return a reference when no data is available

    /**
     * CLHttpRequest constructor. Receives the get, post and server global arrays
     * @param $get
     * @param $post
     * @param $server
     */
    public function __construct($get, $post, $server, $files) {
        $this->getData = $get;
        $this->postData = $post;
        $this->server = $server;
        $this->files = $files;
        $this->lang = $this->setLang($server['HTTP_ACCEPT_LANGUAGE'] ?? null);
        if (($this->isPost() && $post == null) || $this->isPut()) {
            $this->postData = file_get_contents(CLHttpRequest::INPUT_STREAM);
        }
    }

    public function get($key, $newVal = null) {
        if ($this->getData == null) { return null; }
        if (isset($newVal)) {
            $this->getData[$key] = $newVal;
        }
        return $this->getData[$key] ?? null;
    }

    public function post($key, $newVal = null) {
        if ($this->postData == null) { return null; }
        if (isset($newVal)) {
            $this->postData[$key] = $newVal;
        }
        return $this->postData[$key] ?? null;
    }

    public function isUserRequest() {
        return $this->getRequestId() != null;
    }

    public function isGet() {
        return (isset($this->server['REQUEST_METHOD']) && $this->server['REQUEST_METHOD'] == 'GET');
    }

    public function isPost() {
        return (isset($this->server['REQUEST_METHOD']) && $this->server['REQUEST_METHOD'] == 'POST');
    }

    public function isPut() {
        return (isset($this->server['REQUEST_METHOD']) && $this->server['REQUEST_METHOD'] == 'PUT');
    }

    public function isOptions() : bool {
        return (isset($this->server['REQUEST_METHOD']) && $this->server['REQUEST_METHOD'] == 'OPTIONS');
    }

    public function isJson() { $cnttype = $this->server['CONTENT_TYPE'] ?? 'not set';
        return (isset($this->server['CONTENT_TYPE']) && mb_strpos($this->server['CONTENT_TYPE'], 'application/json') !== false);
    }

    public function acceptJson() {
        return (isset($this->server['HTTP_ACCEPT']) && mb_strpos($this->server['HTTP_ACCEPT'], 'application/json') !== false);
    }

    public function isSecure() {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
    }

    /**
     * Returns the $_POST array
     * @return mixed
     */
    public function &getPost() {
        if ($this->isJson()) {
            return $this->getJsonData();
        } elseif (isset($this->postData) && is_array($this->postData) && count($this->postData) > 0) {
            return $this->postData;
        }
        return $this->nodata;
    }

    /**
     * Returns the $_GET array
     * @return mixed
     */
    public function &getGet() {
        if (isset($this->getData)) {
            return $this->getData;
        }
        return $this->nodata;
    }

    /**
     * Returns the $_SERVER array
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        if ($this->method == null) {
            if (isset($this->server['REQUEST_METHOD'])) {
                $this->method = strtolower($this->server['REQUEST_METHOD']);
            }
        }
        return $this->method;
    }

    public function &getRequest() {
        if ($this->isGet()) {
            return $this->getGet();
        } elseif ($this->isPost()) {
            return $this->getPost();
        } elseif ($this->isJson()) {
            return $this->getJsonData();
        } else {
            return $this->nodata;
        }
    }

    public function isAjax() {
        $ajax = (isset($this->server['X-Requested-With']) && strtolower($this->server['X-Requested-With']) === 'xmlhttprequest') ||
            (isset($this->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        if ($ajax || $this->appConfig == null) { return $ajax; }
        if ($this->appConfig->getAppConfig('AJAX_HDR') != null) {
            return (isset($this->server[$this->appConfig->getAppConfig('AJAX_HDR')]) && strtolower($this->server[$this->appConfig->getAppConfig('AJAX_HDR')]) === 'true');
        }
    }

    /**
     * @return mixed
     */
    public function &getJsonData()
    {
        if ($this->jsonData == null) {
            $this->jsonData = json_decode($this->postData, true);
        }
        return $this->jsonData;
    }

    /**
     * @param bool $isTextInput set to true if you are reading text data and require line ending translation. By default a binary stream is assumed
     * @return bool|resource
     */
    public function getInputStream($isTextInput = false) {
        if ($isTextInput) {
            return fopen(CLHttpRequest::INPUT_STREAM, 'rt');
        }
        return fopen(CLHttpRequest::INPUT_STREAM, 'rb');
    }

    /**
     * Returns the id of the current request, ie, the CL Flow id
     * @return mixed
     */
    public function getRequestId()
    {
        if ($this->requestId == null) { $this->checkRequestId(); }
        return $this->requestId;
    }

    /**
     * @param mixed $appConfig
     */
    public function setAppConfig($appConfig): void
    {
        $this->appConfig = $appConfig;
        $this->checkRequestId();
    }

    private function checkRequestId() {
        $request = $this->getRequest();
        if ($request == null) { $this->checkRequestPath(); }
        else {
            $this->requestId = $this->appConfig != null ? $request[$this->appConfig->getClKey()] ?? null : null;
            if ($this->requestId == null) {
                $this->checkRequestPath();
            }
        }
    }

    private function checkRequestPath() {
        if (isset($this->server['PATH_INFO']) && mb_strlen($this->server['PATH_INFO']) > 0) {
            $this->requestId = $this->server['PATH_INFO'];
            if ($this->requestId[0] === '/') {
                $this->requestId = mb_substr($this->requestId, 1);
            }
        } else {
            $this->requestId = $this->get($this->appConfig->getClKey()) ?? null;
            if ($this->server['REQUEST_URI'] === $this->appConfig->getBaseUri()
                || endsWith($this->server['REQUEST_URI'], 'index.php')) {
                $this->requestId = DEFAULTPAGE;
            }
        }
    }

    private function setLang($lang) {
        // if not specified or * return a few common ones
        if ($lang == null || $lang == '*') { return ['en','sp','fr', 'de', 'ja', 'ko', 'zh', 'ar', 'he', 'ru', 'uk']; }
        $lang = explode(',', $lang);
        if ($lang == null || count($lang) == 0) { return ['en']; }
        $larray = [];$keys = [];
        foreach ($lang as $l) {
            if ($l == '*') { return ['en','sp','fr', 'de', 'ja', 'ko', 'zh', 'ar', 'he', 'ru', 'uk']; }
            if (mb_strlen($l) == 2) {
                if (!array_key_exists($l, $keys)) {
                    $larray[] = $l;
                    $keys[$l] = 1;
                }
            } elseif (mb_strpos($l, '-') !== false) {
                $p = mb_strpos($l, '-');
                $lcode = mb_substr($l, 0, $p);
                if (!array_key_exists($lcode, $keys)) {
                    $larray[] = $lcode;$keys[$lcode] = 1;
                }
            } elseif (mb_strpos($l, ';') !== false) {
                $p = mb_strpos($l, ';');
                $lcode = mb_substr($l, 0, $p);
                if (!array_key_exists($lcode, $keys)) {
                    $larray[] = $lcode;$keys[$lcode] = 1;
                }
            }
        }
        return $larray;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    public function redirect($newuri, $http_response_code = null) {
        if ($http_response_code != null) {
            header('Location: ' . $newuri, TRUE, $http_response_code);
        } else {
            header('Location: ' . $newuri);
        }
    }

    /**
     * Returns the specified header
     * @param string $header entry into the server array
     * @return string or null
     */
    public function getHeader(string $header): ?string
    {
        return $this->server[$header] ?? null;
    }

    public function getAttachments($name = null)
    {
        if (empty($this->files)) { return []; }
        if (count($this->fileList) > 0) {
            if ($name == null) {
                return $this->fileList;
            } else {
                return $this->fileList[$name] ?? null;
            }
        }
        if ($name != null) {
            return $this->files[$name] ?? null;
        } else {
            return $this->files;
        }
    }

    public function moveAttachments() {
        if (empty($this->files)) { return; }
        if ($this->appConfig->getAppConfig(UPLOAD_CONFIG) != null) {
            $uploadCfg = $this->appConfig->getAppConfig(UPLOAD_CONFIG);
            $uploadDir = $uploadCfg[UPLOAD_DIR] ?? null;
            if (isset($uploadCfg[UPLOAD_FN])) {
                call_user_func($uploadCfg[UPLOAD_FN], $this->files, $uploadCfg);
                return;
            }
            if ($uploadDir == null) return;
            foreach ($this->files as $k => $v) {
                if (is_array($v["error"])) {
                    foreach ($v["error"] as $idx => $error) {
                        if ($error == UPLOAD_ERR_OK) {
                            $tmp_name = $v["tmp_name"][$idx];
                            $name = basename($v["name"][$idx]);
                            $this->moveUploadedFile($name, $tmp_name, "$uploadDir/$name");
                        }
                    }
                } else {
                    if ($v["error"] == UPLOAD_ERR_OK) {
                        $tmp_name = $v["tmp_name"];
                        $name = basename($v["name"]);
                        $this->moveUploadedFile($name, $tmp_name, "$uploadDir/$name");
                    }
                }
            }
        }
    }

    public function getFilesInfo($key = null) {
        if ($key !== null) {
            return isset($this->files[$key]) ? $this->files[$key] : null;
        }
        return $this->files;
    }

    private function moveUploadedFile($name, $tmp_name, $destination) {
        if ($name == null || $tmp_name == null) return;
        if (move_uploaded_file($tmp_name, $destination)) {
            $this->fileList[$name][] = $destination;
        }
    }

    /**
     * Update the request, for instance after filtering it
     * @param array $request an associative array
     */
    public function setRequest(array $request)
    {
        if ($this->isGet()) {
            $this->getData = $request;
        } elseif ($this->isPost() || $this->isJson()) {
            $this->postData = $request;
        }
    }
}
