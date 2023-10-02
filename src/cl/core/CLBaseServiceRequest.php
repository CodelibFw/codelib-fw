<?php
/**
 * CLBaseServiceRequest.php
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


use cl\contract\CLRequest;
use cl\web\CLConfig;
use cl\web\CLSession;

/**
 * Class CLBaseServiceRequest
 * Used internally to pass data to a CL Extension, such as a Plugin
 * @package cl\core
 */
class CLBaseServiceRequest implements \cl\contract\CLServiceRequest
{
    private $requestedServer, $requestedService;
    private $clRequest, $clConfig, $clSession;

    /**
     * CLBaseServiceRequest constructor.
     * @param $requestedServer
     * @param $requestedService
     * @param $clRequest
     * @param $clConfig
     * @param $clSession
     */
    public function __construct($requestedServer, $requestedService, $clRequest, $clConfig, $clSession)
    {
        $this->requestedServer = $requestedServer;
        $this->requestedService = $requestedService;
        $this->clRequest = $clRequest;
        $this->clConfig = $clConfig;
        $this->clSession = $clSession;
    }


    /**
     * @param mixed $requestedServer
     */
    public function setRequestedServer($requestedServer): void
    {
        $this->requestedServer = $requestedServer;
    }

    /**
     * @param mixed $requestedService
     */
    public function setRequestedService($requestedService): void
    {
        $this->requestedService = $requestedService;
    }

    /**
     * @param mixed $clRequest
     */
    public function setClRequest($clRequest): void
    {
        $this->clRequest = $clRequest;
    }

    /**
     * @param mixed $clConfig
     */
    public function setClConfig($clConfig): void
    {
        $this->clConfig = $clConfig;
    }

    /**
     * @param mixed $clSession
     */
    public function setClSession($clSession): void
    {
        $this->clSession = $clSession;
    }

    public function getRequestedServer(): string
    {
        return $this->requestedServer;
    }

    public function getRequestedService(): string
    {
        return $this->requestedService;
    }

    public function &getCLRequest(): CLRequest
    {
        return $this->clRequest;
    }

    public function getCLConfig(): CLConfig
    {
        return $this->clConfig;
    }

    public function getCLSession(): CLSession
    {
        return $this->clSession;
    }
}
