<?php
/**
 * CLConfig.php
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

use cl\ui\web\CLHtmlPage;

/**
 * Class CLConfig
 * Base code-lib configuration class. Apps, preferably, should have an AppConfig class (which should extend this class),
 * defined, and override default values as needed.
 * This class defines getters and setters for some common configuration options, and provides a way for users to
 * define any custom config option they might need via the 'addAppConfig' and 'getAppConfig' class functions
 * @package cl\web
 */
class CLConfig {

    // predefined variables, which can be re-configured via the specific App's AppConfig class
    protected $clkey = 'clkey'; // code-lib flow key
    protected $deploymentType = 'dev'; // dev or prod
    protected $clRepository = array(CLMYSQL => 'cl\store\mysql\CLMySqlRepository', CLREDIS => 'cl\store\redis\CLRedisRepository');
    protected $data = array(CLMYSQL => array('server' => 'localhost', 'user' => '', 'password' => '', 'dbname' => ''),
                            CLREDIS => array('redislib' => 'predis', 'scheme' => 'tcp', 'host' => '127.0.0.1', 'port' => 6379));
    protected $activeClRepository = CLMYSQL;
    protected $csrfStyle = CLREQUEST;
    protected $emailLib = null;
    protected $emailConfig = ['host' => '127.0.0.1'];
    // stores user defined configuration variables
    protected $customConfig = array();
    protected $errorLevel, $haltOnErrorLevel = E_ERROR | E_PARSE | E_COMPILE_ERROR;
    private $settingsLoaded = false;

    public function __construct() {
        if (isset($this->emailLib)) {
            $this->emailConfig['emailLib'] = $this->emailLib;
        }
        $this->setClKey("clkey");
    }

    /**
     * Sets Code-lib flow key, which by default is set to 'clkey'. When used by an App, in a form or get request, its value
     * specifies the flow or execution path that should handle the submitted request.
     * @param string $clkey
     */
    public function setClKey(string $clkey) : CLConfig {
        $this->clkey = $clkey;
        $this->customConfig['clkey'] = $clkey;
        return $this;
    }

    /**
     * Returns the Code-lib flow key
     * @return string
     */
    public function getClKey() {
        return $this->clkey;
    }

    /**
     * Sets the repository connection details for a given repo (key) such as mysql, postgresql, mongodb, etc
     * @param string $storeKey
     * @param array $connDetails associative array with following keys: server, user, password, dbname
     */
    public function setRepoConnDetails(string $storeKey, array $connDetails) : CLConfig {
        $this->data[$storeKey] = $connDetails;
        return $this;
    }

    /**
     * Returns the connection details for the specified repository key
     * @param string $storeKey
     * @return mixed|null
     */
    public function getRepoConnDetails(string $storeKey) {
        return isset($this->data[$storeKey]) ? $this->data[$storeKey] : null;
    }

    /**
     * Sets the deployment type, such as dev and prod
     * @param string $dpltype
     */
    public function setDeploymentType(string $dpltype) : CLConfig {
        $this->deploymentType = $dpltype;
        return $this;
    }

    /**
     * Returns the current deployment type
     * @return string
     */
    public function getDeploymentType() {
        return $this->deploymentType;
    }

    /**
     * Configures a given repsitory key, with its implementation class
     * @param string $storeKey
     * @param string $repoClass
     */
    public function setClRepository(string $storeKey, string $repoClass) : CLConfig {
        $this->clRepository[$storeKey] = $repoClass;
        return $this;
    }

    /**
     * Returns the implementation class of the given repository key
     * @param string $storeKey
     * @return mixed|null
     */
    public function getClRepository(string $storeKey) {
        return isset($this->clRepository[$storeKey]) ? $this->clRepository[$storeKey] : null;
    }

    /**
     * Returns the list of repositories configured for the app
     * @return string[]
     */
    public function getClRepositoryList() {
        return $this->clRepository;
    }

    /**
     * Sets one of the defined repositories as the active repository
     * @param string $storeKey
     */
    public function setActiveClRepository(string $storeKey) : CLConfig {
        $this->activeClRepository = $storeKey;
        return $this;
    }

    /**
     * Returns the active repository
     * @return string the repository key
     */
    public function getActiveClRepository() {
        return $this->activeClRepository;
    }

    /**
     * Adds a configuration entry for this app.
     * If the entry existed, it will be overwritten
     * @param $key
     * @param $value
     */
    public function addAppConfig($key, $value) : CLConfig {
        $this->customConfig[$key] = $value;
        return $this;
    }

    /**
     * Adds configuration entries in bulk (associative array)
     * Any existing entry will be overwritten
     * @param array $entries an array with key/value pairs
     */
    public function addAppConfigArray(array $entries) : CLConfig {
        foreach ($entries as $key => $value) {
            $this->customConfig[$key] = $value;
        }
        return $this;
    }

    /**
     * Reads a given configuration entry for the app, previously added with addAppConfig
     * @param $key
     * @param null $default value to return if entry not found
     * @return mixed|null
     */
    public function getAppConfig($key, $default = null) {
        return $this->customConfig[$key] ?? $default;
    }

    public function loadSettings() {
        if ($this->settingsLoaded) return;
        if (file_exists(BASE_DIR . '/settings/settings.php')) {
            include_once BASE_DIR . '/settings/settings.php';
            $this->settingsLoaded = true;
            if (isset($settings) && is_array($settings)) {
                $this->addAppConfigArray($settings);
            }
            if (isset($repositories) && is_array($repositories)) {
                foreach ($repositories as $k => $v) {
                    $repoClass = $v['class'] ?? null;
                    if ($repoClass == null) {
                        $repoClass = $this->getClRepository($k);
                        if ($repoClass == null) {
                            throw new \Exception('Unknown implementation for CLRepository '.$k.'. A "class" entry must be provided');
                        }
                    } else {
                        $this->setClRepository($k, $repoClass);
                    }
                    $this->setRepoConnDetails($k, $v);
                }
            }
        }
    }

    /**
     * Determines how to check for CSRF. self::OFF means no check done, REQUEST means generate a token per request
     * and SESSION means generate a token for the entire user session.
     * @param int $csrfStyle possible values are CLREQUEST, CLSESSION and OFF
     */
    public function setCSRFStyle(int $csrfStyle) : CLConfig {
        $csrfTypes = [OFF, CLREQUEST, CLSESSION];
        if (! ( in_array($csrfStyle, $csrfTypes))) { throw new \Exception('Invalid CSRF style attempted in App configuration'); }
        $this->csrfStyle = $csrfStyle;
        $this->addAppConfig(CSRFSTATUS, $csrfStyle !== OFF);
        return $this;
    }

    /**
     * Returns the CSRF style
     * @return int
     */
    public function getCSRFStyle() {
        return $this->csrfStyle;
    }

    public function setCors(array $corsConfig) {
        $this->customConfig['cors'] = $corsConfig;
        return $this;
    }

    /**
     * Configuration for Cross-Origin Resource Sharing
     * if not defined, the following permissive values will be used:
     * 'Access-Control-Allow-Origin' => ['*'],
     * 'Access-Control-Allow-Credentials' => false,
     * 'Access-Control-Allow-Methods' => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'],
     * 'Access-Control-Allow-Headers' => ['*'],
     * 'Access-Control-Expose-Headers' => [],
     * 'Access-Control-Max-Age' => 0
     * @return array key/value pairs for each cors related header
     */
    public function getCors() : ?array {
        return $this->customConfig['cors'] ?? null;
    }

    /**
     * Sets the email library to use. If not set, a simple internal email class will be used. Alternatively, it can be set
     * to 'swiftmailer' or 'phpmailer' or other you know. These are 3rd party robust and popular email libraries, that you
     * would need to add to the vendor folder of your app, and code-lib would know how to delegate to them.
     * The easiest way to add any of them is to go to your 'app' folder in the shell or console, and install
     * it via composer. Below are the commands you would need for each of them:
     * composer require "swiftmailer/swiftmailer:^6.0" (or a different version)
     * composer require "phpmailer/phpmailer"
     * @param string $emailLibrary if not set,
     */
    public function setEmailLibrary(string $emailLibrary) : CLConfig {
        $this->emailLib = $emailLibrary;
        $this->emailConfig['emailLib'] = $this->emailLib;
        return $this;
    }

    /**
     * Sets the email configuration to use
     * @param array $emailConfig email configuration to use. If it contains a 'emailLib' key defined, its value will
     * override any previously set email library, otherwise, this configuration will be applied to the current email library
     */
    public function setEmailConfig(array $emailConfig) : CLConfig {
        $this->emailConfig = $emailConfig;
        if (isset($this->emailConfig['emailLib'])) {
            $this->emailLib = $this->emailConfig['emailLib'];
        } else {
            $this->emailConfig['emailLib'] = $this->emailLib;
        }
        return $this;
    }

    /**
     * Returns the email configuration
     * @return array
     */
    public function getEmailConfig() {
        return $this->emailConfig;
    }

    public function setDomainName(string $domainName) {
        $this->customConfig['domain'] = $domainName ?? '';
    }

    public function getDomainName() {
        return $this->customConfig['domain'] ?? '';
    }

    public function setBaseUri(string $baseUri) {
        $this->customConfig['baseuri'] = $baseUri ?? '';
    }

    public function getBaseUri() {
        return $this->customConfig['baseuri'] ?? '/';
    }

    /**
     * @return mixed
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * @param mixed $errorLevel
     */
    public function setErrorLevel($errorLevel) : CLConfig
    {
        $this->errorLevel = $errorLevel;
        return $this;
    }

    /**
     * @return int
     */
    public function getHaltOnErrorLevel(): int
    {
        return $this->haltOnErrorLevel;
    }

    /**
     * Sets what kind of errors will cause the application to exit.
     * A bitwise mask or named constants can be used to specify this value. Example:
     * 0x05 or instead: E_ERROR | E_PARSE
     * @param int $haltOnErrorLevel
     */
    public function setHaltOnErrorLevel(int $haltOnErrorLevel): CLConfig
    {
        $this->haltOnErrorLevel = $haltOnErrorLevel;
        return $this;
    }

    /**
     * You can optionally overwrite this function in your own config class (which must extend CLConfig), to encapsulate
     * the definition of your App's flow. Alternatively you can define your flow elsewhere
     * @param CLHtmlApp $app
     * @throws \Exception
     */
    public function defineFlow(CLHtmlApp $app) : CLConfig {
        $page = new CLHtmlPage();
        $page->add('<h3>Hello, this works, but extend "defineFlow()" on your AppConfig to define your own flow</h3>');
        $app->addElement('pg1', $page, true);
        return $this;
    }
}
