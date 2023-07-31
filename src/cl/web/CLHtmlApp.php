<?php
/**
 * CLHtmlApp.php
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

use cl\contract\CLApp;
use cl\contract\CLInjectable;
use cl\contract\CLLogger;
use cl\contract\CLRequest;
use cl\core\CLBaseServiceRequest;
use cl\core\CLFlag;
use cl\core\CLInstantiator;
use cl\core\CLRoute;
use cl\error\CLAppException;
use cl\plugin\CLBaseResponse;
use cl\store\cache\CLMemCacheRepository;
use cl\util\CLSimpleLogger;
use cl\tool\Diagnostics;
use cl\util\Util;
use Exception;
use cl\ui\web\CLHtmlCtrl;
use cl\ui\web\CLHtmlHead;
use cl\ui\web\CLHtmlPage;

/**
 * Class CLHtmlApp
 * Main or entry point, for an app, into the Code-lib framework
 * @package cl\web
 */
class CLHtmlApp implements CLApp
{
    private $header;
    private $pages = array();
    private $pageDef = array();
    private $footer;
    private $plugins = array(), $inlPlugins = array();
    private $pluginResponse = null;
    private $defaultPage = null, $notFoundPage = null;
    private $clrequest;
    private $clsession;
    private $loader;
    private $appConfig = null;
    private $deployments = array();
    private $activeDeployment = null;
    private $clkey;
    private $requestFilters = array(), $responseFilters = array();
    private $cllogger = null;
    private $inspection = null;
    private $extFolder = 'plugin';
    private $cache = null;
    private $csrfToken = null;
    private $appNs = 'app', $appNsPrefix = '';
    private $vars = array();
    static public $clapp = null;

    const VERSION = '1.0.0';
    const ONLOAD = 'cl_onload', ONREQUEST = 'cl_on_request',
          BEFORE_RENDER = 'cl_b4render', AFTER_RENDER = 'cl_after_render',
          DEFAULT = 'cl_default';
    const ERRORPAGE = 'cl_error_page';
    /**
     * @var string
     */
    private $lookandFeel;

    public function __construct() {
        global $start;
        $this->bootstrapped() ?? die('Please bootstrap your App correctly');
        $this->clrequest = new CLHttpRequest($_GET, $_POST, $_SERVER, $_FILES);
        $this->loader = $start->getLoader();
        $this->clkey = 'clkey';
        $this->appNs = $start->getAppNs();
        CLHtmlApp::$clapp = $this;
        $this->pluginResponse = new CLBaseResponse();
    }

    /**
     * Call this method, if you want to set a common header (as in html <head></head>) for all your pages
     * @param $header (optional) a CLHtmlHead control, or plain html, as head template for all pages,
     *                otherwise, specify the header in each page you create
     * @return CLHtmlApp
     * @throws Exception
     */
    public function setHeader($header) {
        if (!isset($header)) return $this;
        if ($header instanceof CLHtmlHead) {
            $this->header = $header;
        } else {
            if (is_string($header)) {
                $this->header = $header;
            } else {
                throw new Exception('Invalid header: must be either a CLHtmlHead object or a plain html string');
            }
        }
        return $this;
    }

    /**
     * Call this method, if you want to set a common footer for all your pages
     * @param $footer (optional) a CLHtmlCtrl or plain html, to use as footer template for all pages,
     *                otherwise, specify the footer in each page you create
     * @return CLHtmlApp
     * @throws Exception
     */
    public function setFooter($footer) {
        if (!isset($footer)) return $this;
        if ($footer instanceof CLHtmlCtrl) {
            $this->footer = $footer;
        } else {
            if (is_string($footer)) {
                $this->footer = $footer;
            } else {
                throw new Exception('Invalid footer: must be either a CLHtmlCtrl object or a plain html string');
            }
        }
        return $this;
    }

    /**
     * Sets the App look and feel
     * the look and feel must be a php file (not a class) located in the lookandfeel folder of the App
     * @param $laf
     * @return $this
     */
    public function setLookandFeel($laf) {
        $laf = Util::addExt($laf, 'php');
        $this->lookandFeel = $laf;
        return $this;
    }

    /**
     * Adds a CLHtmlPage to this app
     * @param mixed $key an identifier for this element. If the id was already added, this control will replace the control
     * previously added with the same id. If id is an array of strings (instead of a string), the Page will be added to
     * each flow (value) in that array. In that way you can indicate that the same page must handle the view for a
     * certain number of flows (use case scenarios)
     * @param CLHtmlPage $element a CLHtmlPage instance (object) to add to the app
     * @param bool $isdefault true if this element is the default element (displayed as root or home page of the App)
     * @return mixed
     * @throws Exception if element is not a valid CLHtmlPage
     */
    public function addElement($key, $element, bool $isdefault = false): CLHtmlApp {
        if (!($element instanceof CLHtmlPage)) {
            throw new Exception('Invalid App element: only CLHtmlPage is allowed');
        }
        if (isset($this->header)) {
            $element->addHeader($this->header);
        }
        if (isset($this->footer)) {
            $element->addFooter($this->footer);
        }
        if (isset($this->lookandFeel)) {
            $element->setLookandFeel($this->lookandFeel);
        }
        $element->setClrequest($this->clrequest);

        if (is_array($key)) {
            foreach ($key as $flowKey) {
                $this->pages[$flowKey] = $element;
                if ($flowKey == '404') {
                    $this->notFoundPage = $flowKey;
                }
            }
            if ($isdefault) {
                $this->defaultPage = $key[0];
            }
            return $this;
        }
        $this->pages[$key] = $element;
        if ($isdefault) {
            $this->defaultPage = $key;
        }
        if ($key == '404') {
            $this->notFoundPage = $key;
        }
        return $this;
    }

    public function getElement($key) {
        return $this->pages[$key] ?? null;
    }

    /**
     * Adds a Page Definition to the App
     * @param mixed $key route or flow key for the page or pages. If array, page will handle several flows
     * @param array $lf one or more look and feels for this page (for instance to specify: heading, content, footer)
     * @param array $vars optional array of variables for the page (key/value pairs)
     * @param bool $isdefault specify whether to use page as the default page. For array of pages, the 1st page is set
     * @return $this
     */
    public function addPage($key, array $lf, array $vars = [], $protection = 'none', bool $isdefault = false) {
        if (is_array($key)) {
            $default = $isdefault;
            foreach ($key as $flowKey) {
                $this->pageDef[$flowKey] = array('lf' => $lf, 'vars' => $vars, 'protection' => $protection,
                    'default' => $default);
                $default = false;
                if ($flowKey == '404') {
                    $this->notFoundPage = $flowKey;
                }
            }
            if ($isdefault) {
                $this->defaultPage = $key[0];
            }
            return $this;
        }
        $this->pageDef[$key] = array('lf' => $lf, 'vars' => $vars, 'protection' => $protection, 'default' => $isdefault);
        if ($isdefault) {
            $this->defaultPage = $key;
        }
        if ($key == '404') {
            $this->notFoundPage = $key;
        }
        return $this;
    }

    /**
     * Adds a Plugin to this App. A Plugin is expected to implement the CLPlugin interface, and the containing file
     * should be placed in the plugin folder of the app.
     * @param mixed $key the flow key (route) the plugin will hook into. If array, then specifies sevral keys (routes)
     * the Plugin handles.
     * As an example, a plugin could register for user/register, to handle user registration,
     * or maybe for user.*, to handle all activities in the user flow.
     * See: @{CLRoute::create} for more options
     * @param string $plugin classname (string) of a given plugin class
     * @return CLHtmlApp
     */
    public function addPlugin($key, $plugin, $pluginMethod = 'run', $httpMethods = [], $roles = [], $httpsOnly = false)
    {
        if (is_array($key)) {
            foreach ($key as $flowKey) {
                $this->addPluginRoute($flowKey, $plugin, $pluginMethod, $httpMethods, $roles, $httpsOnly);
            }
        } else {
            $this->addPluginRoute($key, $plugin, $pluginMethod, $httpMethods, $roles, $httpsOnly);
        }
        return $this;
    }

    private function addPluginRoute($key, $plugin, $pluginMethod = 'run', $httpMethods = [], $roles = [], $httpsOnly = false) {
        if ($plugin instanceof \cl\contract\CLPlugin) {
            $pluginRef = 'cl.ref.'.count($this->inlPlugins);
            $this->inlPlugins[$pluginRef] = &$plugin;
            $this->plugins[] = CLRoute::create($key, "$pluginRef:$pluginMethod", $httpsOnly, $httpMethods)
                ->roles($roles ?? []);
        } else {
            $this->plugins[] = CLRoute::create($key, "$plugin:$pluginMethod", $httpsOnly, $httpMethods)
                ->roles($roles ?? []);
        }
        return $this;
    }

    /**
     * Adds a CL request filter to this app. Filters are always called for any request, and can peruse or modify such request,
     * and determine whether the App should continue executing, or should send an immediate response to the user.
     * So, use filters for checks that must always happen. For checks/actions/events specifics to a particular flow,
     * use Plugins instead.
     * For an example of a Filter implementation, take a look @\cl\contract\CLCsrfFilter
     * @param string $method specifies when this filter will be executed. Possible values are: get, post, ajax, any
     * @param string $configKey the filter will be linked to this config entry, so it can be enabled/disabled
     * @param string $filter the filter class (must be in the filter folder of the app)
     */
    public function addRequestFilter($method, $configKey, $filter) {
        if ($method == 'any') {
            $this->requestFilters['get'][] = array($configKey => $filter);
            $this->requestFilters['ajax'][] = array($configKey => $filter);
            $this->requestFilters['options'][] = array($configKey => $filter);
            $this->requestFilters['head'][] = array($configKey => $filter);
            $this->requestFilters['put'][] = array($configKey => $filter);
            $method = 'post';
        }
        $this->requestFilters[$method][] = array($configKey => $filter);
    }

    public function addResponseFilter($configKey, $filter) {
        $this->responseFilters[] = array($configKey => $filter);
    }

    /**
     * Configures a namespace for auto loading
     * @param string $prefix namespace prefix
     * @param string $base_dir base directory for class files in this namespace
     * @param bool $prepend set to true to search this namespace before previously added ones
     * @return $this
     */
    public function addNamespace($prefix, $base_dir, $prepend = false) {
        $this->loader->addNamespace($prefix, $base_dir, $prepend);
        return $this;
    }

    /**
     * Returns the configuration class, either a default one, or one set as part of a deployment (see setDeployment)
     * and the <code>CLDeployment</code> class
     * @return CLConfig returns the configuration class of the app
     */
    public function getAppConfig(): CLConfig {
        if ($this->appConfig == null) {
            $deployment = $this->getActiveDeployment();
            if ($deployment == null || $deployment->getConfig() == null) {
                $this->setAppConfig(new CLConfig());
            } else {
                $this->setAppConfig($deployment->getConfig());
            }
        }
        return $this->appConfig;
    }

    /**
     * Adds a deployment under a certain type (dev, prod, etc).
     * If the deployment doesn't have a config class, the current config class will be added to the deployment.
     * @param CLDeployment $deployment
     * @param bool $setAsActive
     * @return CLHtmlApp
     * @throws Exception
     */
    public function setDeployment(CLDeployment $deployment, bool $setAsActive = false) {
        $deploymentType = $deployment->getDeploymentType();
        if ($deploymentType == null) { throw new Exception('Undefined deployment type @ ClHtmlApp::setDeployment'); }
        $this->deployments[$deploymentType] = $deployment;
        if ($setAsActive) {
            $this->setActiveDeployment($deploymentType);
        } else {
            if ($deployment->getConfig() == null && $this->appConfig != null) {
                $this->deployments[$deploymentType]->setConfig($this->appConfig);
            }
        }
        return $this;
    }

    /**
     * Selects one of the defined deployments as active
     * @param string $deploymentType the deployment type to set as active, such as 'dev', 'prod', etc
     * @return CLHtmlApp
     * @throws Exception
     */
    public function setActiveDeployment($deploymentType) {
        if (count($this->deployments) == 0) { throw new Exception('Deployment '.$deploymentType.' cannot be set to active'); }
        if (!isset($this->deployments[$deploymentType])) { throw new Exception('Deployment '.$deploymentType.' does not exist'); }
        $this->activeDeployment = $deploymentType;
        if ($this->deployments[$this->activeDeployment]->getConfig() != null) {
            $this->setAppConfig($this->deployments[$this->activeDeployment]->getConfig());
            $this->extFolder = $this->appConfig->getAppConfig(EXTENSIONS_FOLDER, 'plugin');
        } else {
            $this->deployments[$this->activeDeployment]->setConfig($this->getAppConfig());
        }
        $this->appConfig->setDeploymentType($deploymentType);
        return $this;
    }

    private function setAppConfig(CLConfig $appConfig) {
        $this->appConfig = $appConfig;
        $this->appConfig->loadSettings();
        $repoList = $this->appConfig->getClRepositoryList();
        if ($repoList != null && count($repoList) > 0) {
            foreach ($repoList as $repoKey => $repoDef) {
                CLInstantiator::addRef($repoKey, $repoDef, null, null);
            }
        }
    }

    /**
     * Returns the active deployment object
     * @return CLDeployment
     */
    public function getActiveDeployment() : ?CLDeployment {
        if (count($this->deployments) == 0) { return null; }
        if ($this->activeDeployment != null) {
            return $this->deployments[$this->activeDeployment];
        }
        return array_values($this->deployments)[0];
    }

    /**
     * @return CLRequest|null
     */
    public function getClrequest(): ?CLRequest
    {
        return $this->clrequest;
    }

    /**
     * @return CLSession
     */
    public function &getClsession(): ?CLSession
    {
        return $this->clsession;
    }

    /**
     * Use to set an alternative logging functionality (must implement CLLogger interface)
     * @param CLLogger|null $cllogger
     */
    public function setLogger(?CLLogger $cllogger): void
    {
        $this->cllogger = $cllogger;
    }

    /** create instance of given object
     * @param $object
     * @param mixed ...$args
     * @return mixed
     */
    protected function &newObj($object, ...$args) {
        if ($args == null) {
            $obj = new $object();
            return $obj;
        } else {
            $obj = new $object(...$args);
            return $obj;
        }
    }

    /**
     * Starts execution of the app
     * @param bool $introspection (optionally) run introspection (diagnostics) instead of normal run.
     *             Should help to discover issues and speed up development
     * @return bool
     * @throws Exception
     */
    public function run(bool $introspection = false): bool
    {
        if ($introspection) { return $this->runInspection(); }
        $startTime = microtime(true);
        $this->setDefaultErrorPage();
        $this->setDefaults();
        if ($this->isUserRequest()) {
            $this->clkey = $this->getAppConfig()->getClKey();

            $requestId = $this->clrequest->getRequestId();
            if ($requestId == null) { // shouldn't happen once everything is thoroughly tested
                throw new CLAppException('Invalid request');
            }
            if (endsWith($requestId, ['.png', '.jpg', '.gif', '.css', '.js'])) {
                $this->getCllogger()->debug('Resource requested: '.$requestId.' and ignored');
                echo $this->render404();
                return true;
            }
            if ($requestId == 'knowthyself') {
                return $this->runInspection();
            }
            if (!$this->callRequestFilters($this->clrequest->getMethod())) {
                $duration = microtime(true) - $startTime;
                $this->getCllogger()->debug('Execution completed in ' . $duration . ' seconds');
                throw new CLAppException('Request rejected as invalid');
            }
            $this->dispatch($requestId);
            ////
            echo $this->render($requestId);
        } elseif ($this->clrequest->isOptions()) {
            $this->callRequestFilters($this->clrequest->getMethod());
            $duration = microtime(true) - $startTime;
            $this->getCllogger()->debug('Execution (Options) completed in ' . $duration . ' seconds');
            echo $this->render($this->defaultPage);
        } else {
            if (!isset($this->defaultPage)) {
                $duration = microtime(true) - $startTime;
                $this->getCllogger()->debug('Execution completed in ' . $duration . ' seconds');
                throw new \cl\error\CLResourceFoundException('No default page to render in App');
            }
            if (!$this->callRequestFilters($this->clrequest->getMethod())) {
                $duration = microtime(true) - $startTime;
                $this->getCllogger()->debug('Execution completed in ' . $duration . ' seconds');
                throw new CLAppException('Request rejected as invalid');
            }
            $this->dispatch($this->defaultPage);
            echo $this->render($this->defaultPage);
        }
        $duration = microtime(true) - $startTime;
        $this->getCllogger()->debug('Execution completed in ' . $duration . ' seconds');
        return true;
    }

    /**
     * @return null
     */
    public function getInspection()
    {
        return $this->inspection;
    }

    /**
     * Returns the configured cache instance, if any
     * @return null
     */
    public function getCache()
    {
        return $this->cache;
    }

    protected function dispatch($requestId) {
        $installedDispatcher = $this->appConfig->getAppConfig(APP_DISPATCHER);
        if ($installedDispatcher != null && $installedDispatcher instanceof \cl\contract\CLDispatcher) {
            $this->pluginResponse = $installedDispatcher->dispatch($this->clrequest, $this->getActiveDeployment());
        } else {
            try {
                $this->callPlugins(CLHtmlApp::ONREQUEST);
                if ($requestId != null) {
                    $this->callPlugins($requestId);
                }
                $this->callPlugins(CLHtmlApp::BEFORE_RENDER);
            } catch (Exception $e) {
                \cl\core\CLExceptionHandler::handle($e);
            }
        }
    }

    protected function render($key)
    {
        try {
            if ($this->pluginResponse->getVar('nocontent') != null) {
                return $this->sendHeaders();
            }
            $this->callResponseFilters();
            if ($key == null) {
                $key = $this->defaultPage;
                $this->getCllogger()->debug('rendering Default page because page key is null');
            }
            $installedRenderer = $this->appConfig->getAppConfig(APP_RENDERER);
            if ($installedRenderer != null && $installedRenderer instanceof \cl\contract\CLRenderer) {
                return $installedRenderer->toHtml($key, $this->pluginResponse, $this->getActiveDeployment());
            }
            $cfgValue = $this->appConfig->getAppConfig(RENDER_ALL);
            if ($cfgValue != null && $cfgValue) {
                $html = '';
                if (count($this->pageDef) > 0) {
                    foreach (array_keys($this->pageDef) as $pgkey) {
                        $element = $this->processPluginResponses($pgkey);
                        $element->setClrequest($this->clrequest);
                        $html .= $element->toHtml(null);
                    }
                } else {
                    foreach (array_keys($this->pages) as $pgkey) {
                        $element = $this->processPluginResponses($pgkey);
                        $element->setClrequest($this->clrequest);
                        $html .= $element->toHtml(null);
                    }
                }
                return $html;
            }

            $element = $this->processPluginResponses($key);
            $this->setLoginStatus();
            if (count($this->vars) > 0) {
                $element->addVars($this->vars);
            }
            $element->setClrequest($this->clrequest);
            $this->sendHeaders();
            return $element->toHtml(null);
        } catch(Exception $e) {
            \cl\core\CLExceptionHandler::handle($e);
        }
        return '';
    }

    public function render404() {
        header(CLWebResponseCode::getResponseCode(404), true, 404);
        if ($this->notFoundPage == null) {

            return '';
        }
        $element = isset($this->pages[$this->notFoundPage]) ? $this->pages[$this->notFoundPage] : null;
        if ($element === null && isset($this->pageDef[$this->notFoundPage])) {
            $element = $this->mkPage($this->notFoundPage);
        }
        return $element->toHtml(null);
    }

    protected function renderErrorPage() {

        $element = $this->getErrorPage();
        $statusCode = $element->getVar(STATUS_CODE);
        if ($statusCode != null) {
            $responseCode = CLWebResponseCode::getResponseCode(404);
            if ($responseCode != null) {
                header($responseCode, true, 404);
            }
        }
        return $element->toHtml(null);
    }

    protected function processPluginResponses($key): ?CLHtmlCtrl
    {
        $isJson = $this->clrequest->isJson() || $this->clrequest->acceptJson() || ('json' === $this->pluginResponse->getVar(CLFlag::PRODUCES));
        if ($isJson) {
            // if json according to request, but the Plugin changes it, we go with the Plugin
            $pluginProduces = $this->pluginResponse->getVar(CLFlag::PRODUCES);
            if ($pluginProduces != null && $pluginProduces != 'json') {
                $isJson = false;
            }
        }

        if ($isJson) {
            $element = new CLHtmlCtrl('');
        }

        $pgkey = $this->pluginResponse->getVar('page');
        if (isset($pgkey) && !$isJson) {
            if (isset($this->pages[$pgkey])) {
                $element = $this->pages[$pgkey];
            } else {
                if (isset($this->pageDef[$pgkey])) {
                    $element = $this->mkPage($pgkey);
                }
                if ($element === null) {
                    $element = $this->getErrorPage();
                    $element->addVars(array('feedback' => 'Sorry, an internal error has ocurred and the app is unable to fulfill your request'));
                    $this->cllogger->info('Page ' . $pgkey . ', requested by Plugin, does not exist');
                    return $element;
                }
            }
            $key = $pgkey;
        } else if (!$isJson) {
            $element = isset($this->pages[$key]) ? $this->pages[$key] : null;
            if ($element === null && isset($this->pageDef[$key])) {
                $element = $this->mkPage($key);
            }
        }

        $vars = $this->pluginResponse->getVars();
        if (count($vars) > 0) {
            if (!isset($element)) {
                if (!isset($this->defaultPage)) {
                    throw new \cl\error\CLResourceFoundException('Page not found: ' . $key);
                }
                if (isset($this->pages[$this->defaultPage])) {
                    $element = $this->pages[$this->defaultPage];
                } else if (isset($this->pageDef[$this->defaultPage])) {
                        $element = $this->mkPage($this->defaultPage);
                }
            }
            if ($element !== null) {
                if (isset($vars['laf']) && !$isJson) {
                    $element->setLookandFeel($vars['laf']);
                }
                $element->addVars($vars);
            }
        }
        if ($element === null) {
            $this->cllogger->error('Page not found while processing Plugin responses for key: '.$key);
            $element = $this->getErrorPage();
            $element->addVars(array('feedback' => 'Sorry, an internal error has ocurred and the app is unable to fulfill your request'));
        }
        return $element;
    }

    private function mkPage($key) {
        $page = new CLHtmlPage(null, '');
        $accesslevel = $this->pageDef[$key]['protection'];
        if ($accesslevel !== 'none' && !$this->clsession->get(CLFlag::IS_LOGGED_IN)) {
            throw new CLAppException('Access to page denied. Please login first');
        }
        $page->setLookandFeel($this->pageDef[$key]['lf'][0]);
        $n = count($this->pageDef[$key]['lf']);
        if ($n > 1) {
            for ($i=1; $i < $n; $i++) {
                $page->addElement(Util::addExt($this->pageDef[$key]['lf'][$i], '.php'));
            }
        }
        if (count($this->pageDef[$key]['vars']) > 0) {
            foreach ($this->pageDef[$key]['vars'] as $pvar => $pvarVal) {
                if (startsWith($pvarVal, 'session.')) {
                    $sessionKey = mb_substr($pvarVal, 8);
                    $this->pageDef[$key]['vars'][$pvar] = $this->clsession->get($sessionKey, '');
                }
                if (startsWith($pvarVal, 'plugin.')) {
                    $varKey = mb_substr($pvarVal, 7);
                    $lsqb = mb_strpos($varKey, '[');
                    if ($lsqb !== false) {
                        $rsqb = mb_strpos($varKey, ']');
                        if ($rsqb !== false) {
                            $varname = mb_substr($varKey, 0, $lsqb);
                            $idxlg = $rsqb - $lsqb -1;
                            $varidx = mb_substr($varKey, ($lsqb+1), $idxlg);
                            $pluginVar = $this->pluginResponse->getVar($varname);
                            $this->pageDef[$key]['vars'][$pvar] = $pluginVar[$varidx];
                        }
                    }
                    if ($lsqb == false || $rsqb == false) {
                        $this->pageDef[$key]['vars'][$pvar] = $this->pluginResponse->getVar($varKey);
                    }
                }
            }
            $page->setVars($this->pageDef[$key]['vars']);
        }
        $this->addElement($key, $page, $this->pageDef[$key]['default']);
        return $page;
    }

    public function instantiatePlugin($plugin, $method) {
        $clServiceRequest = new CLBaseServiceRequest($plugin, $method, $this->clrequest, $this->getAppConfig(), $this->clsession);
        if (startsWith($plugin, 'cl.ref.') && isset($this->inlPlugins[$plugin])) {
            $plugin = $this->inlPlugins[$plugin];
            $plugin->init($clServiceRequest, $this->pluginResponse);
            return $plugin;
        } else {
            $pluginFile = Util::addExt($plugin, 'php');
            $pluginFolder = strtolower($plugin);
            if (file_exists(BASE_DIR . '/' . $this->extFolder . '/' . $pluginFile)) {
                $className = $this->appNsPrefix . '\\' . $this->extFolder . '\\' . $plugin;
            } elseif (file_exists(BASE_DIR . '/' . $this->extFolder . '/' . $pluginFolder . '/' . $pluginFile)) {
                $className = $this->appNsPrefix . '\\' . $this->extFolder . '\\' . $pluginFolder . '\\' . $plugin;
            } elseif (file_exists(CL_DIR . '/cl/plugin/' . $pluginFile)) {
                $className = '\cl\plugin\\' . $plugin;
            } else {
                throw new Exception('Extension ' . $pluginFile . ' does not exist');
            }
            return CLInstantiator::classInstance($className, 'cl\contract\CLPlugin', false, $clServiceRequest, $this->pluginResponse);
        }
    }

    protected function callPlugins($key) {
        $flowEntries = $this->findFlowEntries($key);
        foreach ($flowEntries as $flowEntry) {
            list ($plugin, $method) = $flowEntry->getPluginAndMethod();
            if ($method == null || $method == '*') {
                $flowElms = \cl\util\Util::getFlowElements($this->getClrequest());
                if (is_array($flowElms) && count($flowElms) > 1) {
                    $method = $flowElms[1];
                }
                if ($method == null) {
                    $this->cllogger->error('Null entry point specified for '.ucfirst($this->extFolder).' '.$plugin.'. Falling back to "run".');
                    $method = 'run';
                }
            }
            $plugin = $this->instantiatePlugin($plugin, $method);
            // try/catch to be backward compatible while also making setLogger optional
            try {
                $this->callMethod($plugin, 'setLogger', $this->cllogger);
            } catch (Exception $e) {
            }
            CLInstantiator::iocCheck($plugin, $this->getAppConfig(), $this);
            $this->pluginResponse = $this->callMethod($plugin, $method);
        }
    }

    public function setErrorPage($page = null): CLHtmlApp
    {
        if ($page != null) {
            $page->addVars(['status' => 'failure']);
            $this->addElement(CLHtmlApp::ERRORPAGE, $page);
        }
        return $this;
    }

    private function setLoginStatus() {
        if ($this->clsession->get(CLFlag::IS_LOGGED_IN)) {
            $this->vars['loggedIn'] = true;
        } else {
            $this->vars['loggedIn'] = false;
        }
    }
    /**
     * @return string
     */
    public function getExtFolder(): string
    {
        return $this->extFolder;
    }

    /**
     * @return string
     */
    public function getAppNsPrefix(): string
    {
        return $this->appNsPrefix;
    }

    /**
     * @return string
     */
    public function getAppNs(): string
    {
        return $this->appNs;
    }

    private function setDefaultErrorPage(): CLHtmlApp
    {
        $element = isset($this->pages[CLHtmlApp::ERRORPAGE]) ? $this->pages[CLHtmlApp::ERRORPAGE] : null;
        if ($element != null) { return $this; }
        if (isset($this->pageDef[CLHtmlApp::ERRORPAGE])) {
            $element = $this->mkPage(CLHtmlApp::ERRORPAGE);
        }
        if ($element != null) { return $this; }
        $errorPage = new CLHtmlPage(null, '');
        $errorPage->addElement('errorpage.php');
        $this->setErrorPage($errorPage);
        return $this;
    }

    private function runInspection() {
        global $installMode;

        $activeDeployment = $this->getActiveDeployment();
        if (!isset($installMode) && ($activeDeployment == null || $activeDeployment->getDeploymentType() != CLDeployment::DEV)) {
            _log('Diagnostics require Dev Mode. Please add a CLDeployment::DEV deployment and make it active');
            return false;
        }
        _log('Running Diagnostics in Dev Mode! Make sure to switch to CLDeployment::PROD when deploying to production!');
        $this->inspection = new Diagnostics($this);
        $reportStyle = Diagnostics::RPT_LOG;
        if ($installMode) {
            $reportStyle = Diagnostics::RPT_HTML_OUT;
        }
        return $this->inspection->knowThySelf($reportStyle);
    }

    protected function setDefaults() {
        if ($this->appNs != null) {
            $this->appNsPrefix = '\\'.$this->appNs;
        }
        // configure logging and error reporting
        $logFolder = BASE_DIR . '/' . ($this->getAppConfig()->getAppConfig(LOGFOLDER) ?? 'logs');
        Util::ensurePathExists($logFolder);
        $logFileStyle = $this->getAppConfig()->getAppConfig('singleLogFile') ?? false;
        $defaultLogLevel = CLLogger::INFO;
        //date_default_timezone_set
        $timezone = $this->getAppConfig()->getAppConfig(CURRENT_TIMEZONE) ?? 'America/Los_Angeles';
        date_default_timezone_set($timezone);
        $activeDeployment = $this->getActiveDeployment();
        if ($activeDeployment != null && $activeDeployment->getDeploymentType() == CLDeployment::DEV) {
            $defaultLogLevel = CLLogger::DEBUG;
            $this->addPlugin('tooling.*', 'CLToolingPlugin');
        }
        $logLevel = $this->getAppConfig()->getAppConfig(LOGLEVEL) ?? $defaultLogLevel;
        $this->cllogger = new CLSimpleLogger($logLevel, $logFileStyle, $logFolder);
        $errorLevel = $this->getAppConfig()->getErrorLevel();
        if ($errorLevel != null) { error_reporting($errorLevel); } else { $this->setDefaultErrorReporting(); }
        // configure cache
        $this->checkCacheConfig();
        // configure request
        $this->clrequest->setAppConfig($this->getAppConfig());
        // configure uploads
        $this->configureUploads();
        $this->clrequest->moveAttachments();
        // configure session handling
        $this->configureSessionHandling();
        // configure CSRF protection
        $csrfStatus = $this->getAppConfig()->getAppConfig(CSRFSTATUS);
        if (isset($csrfStatus) && $csrfStatus) {
            $this->configureCSRF($csrfStatus);
            $csrfExpires = $this->getAppConfig()->getAppConfig('csrfDuration', 1800);
            $csrf = $this->getCSRFValue();
            setcookie(CSRF_KEY, $csrf, time()+$csrfExpires);
        }
        CLInstantiator::addRef('emailService', 'cl\messaging\email\Email', null, [$this->getAppConfig()]);
        CLInstantiator::addRef('httpclient', 'cl\core\CLSimpleHttpClient', '\cl\contract\CLHttpClient');
        $this->addFilters();
    }

    private function configureUploads() {
        if ($this->getAppConfig()->getAppConfig(UPLOAD_CONFIG) != null) {
            $uploadCfg = $this->appConfig->getAppConfig(UPLOAD_CONFIG);
            $uploadDir = $uploadCfg[UPLOAD_DIR] ?? null;
            if ($uploadDir == null && !isset($uploadCfg[UPLOAD_FN])) {
                if (isset($uploadCfg[UPLOAD_AUTOCONFIG]) && $uploadCfg[UPLOAD_AUTOCONFIG]) {
                    $uploadCfg[UPLOAD_DIR] = $uploadDir = BASE_DIR . '/resources/uploads';
                    $this->appConfig->addAppConfig(UPLOAD_CONFIG, $uploadCfg);
                }
            }
            if ($uploadDir != null && !file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception(_T('Unable to create non-existent upload folder: ').$uploadDir);
                }
            }
        }
    }

    private function addFilters() {
        $csrfStatusCfg = $this->appConfig->getAppConfig(CSRFSTATUS);
        if ($csrfStatusCfg != null && $csrfStatusCfg) {
            $this->addRequestFilter('post', CSRFSTATUS, 'CLCsrfFilter');
        }
        $this->addRequestFilter('any', CORS, 'CLCorsFilter');
        $htmlFilterCfg = $this->appConfig->getAppConfig(HTML_FILTER);
        if ($htmlFilterCfg == null || $htmlFilterCfg === false || $htmlFilterCfg === OFF) return;
        if ($htmlFilterCfg['phase'] == CLREQUEST || $htmlFilterCfg['phase'] == ALL) {
            $this->addRequestFilter('any', HTML_FILTER, 'CLHtmlFilter');
        }
        if ($htmlFilterCfg['phase'] == CLRESPONSE || $htmlFilterCfg['phase'] == ALL) {
            $this->addResponseFilter(HTML_FILTER, 'CLHtmlFilter');
        }
    }

    private function configureCSRF($csrfStatus) {
        if (count($this->pages) > 0) {
            foreach ($this->pages as $key => $element) {
                $csrf = $this->getCSRFValue();
                $element->addVars(['csrf' => $csrf]);
            }
        }
    }

    private function configureSessionHandling() {
        $handlerCfg = $this->getAppConfig()->getAppConfig(SESSION_HANDLER);
        if ($handlerCfg == null || !is_array($handlerCfg)) {
            $this->startSession();
            return;
        }
        $handlerClass = $handlerCfg[SESSION_HANDLER_CLASS] ?? null;
        if ($handlerClass == null) {
            $handlerType = $handlerCfg[SESSION_HANDLER_TYPE] ?? null;
            if ($handlerType == null || $handlerType != DB_SESSION_HANDLING) {
                throw new Exception('Session handling misconfiguration');
            }
            $handlerClass = '\cl\web\CLDbSession';
        }
        $sessionHandler = CLInstantiator::classInstance($handlerClass, '\SessionHandlerInterface', false);
        if ($sessionHandler == null) {
            throw new Exception('Failed to instantiate Session handler: '.$handlerClass);
        }
        CLInstantiator::iocCheck($sessionHandler, $this->getAppConfig(), $this);
        session_set_save_handler($sessionHandler, true);
        $this->startSession();
    }

    private function startSession() {
        session_start();
        $this->clsession = new CLSession($_SESSION);
        if (count($this->plugins) > 0) {
            foreach ($this->plugins as $plugin) {
                $plugin->setClsession($this->clsession);
            }
        }
    }

    private function setDefaultErrorReporting() {
        if ($this->activeDeployment != null) {
            if ($this->getActiveDeployment()->getDeploymentType() === CLDeployment::PROD) {
                error_reporting(E_ERROR | E_PARSE);
            } else {
                error_reporting(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
            }
        }
    }

    private function checkCacheConfig() {
        $cacheConfig = $this->getAppConfig()->getAppConfig(CACHE_CFG);
        if ($cacheConfig == null || !is_array($cacheConfig)) return;
        if ($cacheConfig[CACHE_TYPE] == MEMCACHE) {
            $this->cache = new CLMemCacheRepository(null);
            $servers = $cacheConfig[CACHE_SERVERS] ?? null;
            if ($servers != null) {
                foreach ($servers as $server) {
                    if (!isset($server['host'])) {
                        $this->cllogger->error('Missing "host" in Cache Server configuration entry');
                        continue;
                    }
                    $weight = $server['weight'] ?? 0;
                    $this->cache->addServer($server['host'], $server['port'], $weight);
                }
            }
            CLInstantiator::addInstance('cache', $this->cache);
        }
    }

    private function callMethod($obj, $method, $m_args = null) {
        if ($obj == null) {
            throw new Exception('intended ->'.$method.' call on a null object');
        }
        if (isset($m_args)) {
            return $obj->$method($m_args);
        }
        return $obj->$method();
    }

    private function exec($className, $method, $c_args = null, $m_args = null, $exClass = null) {
        $comp = &$this->newObj($className, $c_args);
        if (isset($comp)) {
            if ($exClass != null && !($comp instanceof $exClass)) {
                throw new Exception($className.' should implement or extend '.$exClass);
            }
            if (isset($m_args)) {
                if (is_array($m_args)) {
                    return $comp->$method(...$m_args);
                }
                return $comp->$method($m_args);
            }
            return $comp->$method();
        }
        throw new Exception('Execution of '.$className.'->'.$method.' failed');
    }

    private function callResponseFilters(): bool
    {
        foreach ($this->responseFilters as $rfilters) {
            foreach ($rfilters as $configKey => $filter) {
                if (!$this->execFilter($configKey, $filter)) {
                    $this->cllogger->error('Execution of response filter: ' . $filter . ' returned false ');
                    return false;
                }
            }
        }
        return true;
    }

    private function callRequestFilters($method): bool
    {
        $this->cllogger->debug('Calling filters for method: '.$method);
        if (!isset($this->requestFilters[$method])) return true;
        foreach ($this->requestFilters[$method] as $mfilters) {
            foreach ($mfilters as $configKey => $filter) {
                if (!$this->execFilter($configKey, $filter)) {
                    $this->cllogger->error('Execution of filter: ' . $filter . ' rejected ' . $method);
                    return false;
                }
            }
        }
        return true;
    }

    private function execFilter($configKey, $filter) {
        $cfgValue = true;
        if ($configKey !== '') {
            $cfgValue = $this->appConfig->getAppConfig($configKey);
        }
        if (!isset($cfgValue) || $cfgValue === false) return true;
        $this->cllogger->debug('exec. filter '.$filter.' for key '.$configKey);
        $filterFile = Util::addExt($filter, 'php');
        $this->cllogger->debug('filter file '.$filterFile);
        if (file_exists(BASE_DIR . '/filter/' . $filterFile)) {
            $className = $this->appNsPrefix.'\\filter\\' . $filter;
            if ($this->exec($className, 'filter', null, [$this->clrequest, $this->pluginResponse]) === false) {
                return false;
            }
        } elseif (file_exists(CL_DIR . '/cl/filter/' . $filterFile)) {
            $className = '\cl\filter\\' . $filter;
            if ($this->exec($className, 'filter', null, [$this->clrequest, $this->pluginResponse]) === false) {
                return false;
            }
        }
        return true;
    }

    private function isUserRequest() {
        return $this->clrequest->isUserRequest();
    }

    private function findFlowEntries($key) {
        $flowEntries = [];
        if ($key == null) {
            $key = '*.*';
        }
        foreach ($this->plugins as $route) {
            $route->setLogger($this->getCllogger());
            if ($route->match($this->clrequest, $key)) {
                $flowEntries[] = $route;
            }
        }
        return $flowEntries;
    }

    private function getCSRFValue() {
        $csrfStyle = $this->getAppConfig()->getCSRFStyle();
        if ($csrfStyle == CLSESSION) {
            $csrfToken = $this->clsession->get('csrfToken');
            if ($csrfToken != null) { return $csrfToken; }
            $this->clsession->put('csrfToken', Util::genAlphaNumRndVal(15));
            return $this->clsession->get('csrfToken');
        }
        $val = $this->csrfToken;
        if (isset($this->csrfToken) && strlen($this->csrfToken) > 0) { return $this->csrfToken; }
        $this->csrfToken = Util::genAlphaNumRndVal(15);
        return $this->csrfToken;
    }

    private function sendHeaders() {
        $headers = $this->pluginResponse->getHeaders();
        if ($headers != null && count($headers) > 0) {
            foreach ($headers as $header => $value) {
                header($header.': '.$value);error_log('sending header: '.$header.': '.$value);
            }
        }
        return '';
    }

    /**
     * @return CLSimpleLogger|null
     */
    public function getCllogger(): ?CLSimpleLogger
    {
        return $this->cllogger;
    }

    /**
     * Renders an error output
     * @param $msg message to send as feedback
     */
    public function exceptionResponse($msg) {
        $this->pluginResponses = array();
        $element = $this->getErrorPage();
        $element->addVars(array("feedback" => $msg ?? 'Unfortunately an internal error occurred'));
        echo $this->renderErrorPage();
    }

    private function getErrorPage() {
        $element = isset($this->pages[CLHtmlApp::ERRORPAGE]) ? $this->pages[CLHtmlApp::ERRORPAGE] : null;
        if ($element === null && isset($this->pageDef[CLHtmlApp::ERRORPAGE])) {
            $element = $this->mkPage(CLHtmlApp::ERRORPAGE);
        }
        if ($element == null) {
            $this->setDefaultErrorPage();
            $element = $this->pages[CLHtmlApp::ERRORPAGE] ?? null;
        }
        return $element;
    }

    private function bootstrapped() {
        return defined('BASE_DIR') && defined('CL_DIR') && defined('APP_NS');
    }

    /**
     * @return CLBaseResponse|null
     */
    protected function getPluginResponse(): ?CLBaseResponse
    {
        return $this->pluginResponse;
    }


}
