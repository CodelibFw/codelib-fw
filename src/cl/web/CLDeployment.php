<?php
/**
 * CLDeployment.php
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

/**
 * Class CLDeployment
 * Defines a specific deployment, such as dev, prod, test, staging or any user-defined one
 * @package cl\web
 */
class CLDeployment {
    const DEV = 'dev';
    const PROD = 'prod';
    const TEST = 'test';

    private $deploymentType = self::DEV;
    private $config;

    public function __construct($deploymentType, CLConfig $config = null)
    {
        $this->deploymentType = $deploymentType;
        $this->config = $config;
    }

    /**
     * Returns this deployment type
     * @return string
     */
    public function getDeploymentType(): string
    {
        return $this->deploymentType;
    }

    /**
     * Sets the deployment type
     * @param string $deploymentType
     */
    public function setDeploymentType(string $deploymentType): void
    {
        $this->deploymentType = $deploymentType;
    }

    /**
     * Returns the configuration instance associated to this deployment
     * @return null
     */
    public function getConfig(): CLConfig
    {
        return $this->config;
    }

    /**
     * Sets the configuration for this deployment
     * @param null $config
     */
    public function setConfig(CLConfig $config): void
    {
        $this->config = $config;
    }
}
