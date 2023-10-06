<?php
/**
 * CLRoute.php
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
use cl\web\CLHtmlApp;

/**
 * Class CLRoute
 * @package cl\core
 */
class CLRoute
{
    private $key;
    private $https, $httpMethods = [];
    private $roles = [];
    private $plugin, $pluginMethod, $pluginParams = [];
    private $logger, $clsession;

    /**
     * CLRoute constructor.
     * @param $key
     * @param $https
     * @param $httpMethods
     */
    private function __construct(string $key, $https = false, $httpMethods = [])
    {
        $this->key = $key;
        $this->https = $https;
        $this->httpMethods = $httpMethods ?? [];
    }

    /**
     * Creates a new route. Called implicitly by the CLHtmlApp::addPlugin method when passed a string or an array, but
     * can be called explicitly to create the route to pass to that addPlugin method.
     * The key can be a simple string, a string with wildcards, or a regular expression. Examples:
     * '/\/rest\/users\/[\d]+/'
     * '/\/rest\/users\/{number}/'
     * '/\/mycall\/{alphanum}/'
     * 'users/*'
     * @param string $key accepts a simple uri such as user/login, wilcards like user.* or user/*, or a regular expression
     * @param string $plugin expects 'PluginClassName:method', for instance: 'HelloPlugin:run'
     * @param false $https make true if this route should only accept https
     * @param array $httpMethods
     * @return CLRoute
     */
    public static function create(string $key, string $plugin, $https = false, $httpMethods = []) {
        $pluginDetails = explode(':', $plugin);
        return (new CLRoute(self::transform($key), $https, $httpMethods))
            ->setPlugin($pluginDetails[0])
            ->setPluginMethod($pluginDetails[1]??'run');
    }

    public static function transform($key) {
        if (!self::isRegEx($key)) { return str_replace('.*', '*', $key); }
        return str_replace(['{number}','{alphanum}','*'], ['[\d]+','[\w]+','(?:.*)'], $key);
    }

    public function match(CLRequest $request, string $key): bool {
        if ($this->requiresSecure() && !$request->isSecure()) {
            if ($this->logger!=null) {
                $this->logger->debug('access to '.$this->key.' not further considered because https is required and http was detected');
            }
            return false;
        }
        if (count($this->httpMethods) > 0) {
            if ($request->getMethod() != null) {
                if (!in_array($request->getMethod(), $this->httpMethods)) { return false; }
            }
        }
        if ($this->isSystemEvent($key)) {
            return ($this->key === $key);
        }
        if ($this->key == '*/*') { return $this->allowed(); }
        if ($this->isRegEx($this->key)) {
            if (preg_match($this->key, $key) == 1) {
                return $this->allowed();
            }
        }
        if (endsWith($this->key, '*')) {
            $root = mb_substr($this->key, 0, mb_strlen($this->key)-1);
            if (startsWith($key, $root) !== false) {
                return $this->allowed();
            }
        } else {
            if ($this->key == $key) {
                return $this->allowed();
            }
        }
        return false;
    }

    public function roles(array $roles) {
        if ($roles == null) { $roles = []; }
        $this->roles = $roles;
        return $this;
    }

    public function requiresSecure() {
        return $this->https == true ?? false;
    }

    /**
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param mixed $plugin
     * @return CLRoute
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPluginMethod()
    {
        return $this->pluginMethod;
    }

    /**
     * @param mixed $pluginMethod
     * @return CLRoute
     */
    public function setPluginMethod($pluginMethod)
    {
        $this->pluginMethod = $pluginMethod;
        return $this;
    }

    public function getPluginAndMethod() {
        return [$this->plugin, $this->pluginMethod];
    }

    /**
     * @return array
     */
    public function getPluginParams(): array
    {
        return $this->pluginParams;
    }

    /**
     * @param array $pluginParams
     * @return CLRoute
     */
    public function setPluginParams(array $pluginParams): CLRoute
    {
        $this->pluginParams = $pluginParams;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param mixed $logger
     * @return CLRoute
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param mixed $clsession
     * @return CLRoute
     */
    public function setClsession($clsession)
    {
        $this->clsession = $clsession;
        return $this;
    }

    private static function isRegEx($key) {
        return startsWith($key,['/','^','#']);
    }

    private function isSystemEvent($event) {
        $sysEvents = CLHtmlApp::ONREQUEST.':'.CLHtmlApp::BEFORE_RENDER;
        return strpos($sysEvents, $event) !== false;
    }

    private function allowed() {
        if (count($this->roles) > 0) {
            if (!greenLight($this->roles, $this->clsession)) {
                if ($this->logger!=null) {
                    $this->logger->debug('access to '.$this->key.' denied because required user role not acquired');
                }
                return false;
            }
        }
        return true;
    }
}
