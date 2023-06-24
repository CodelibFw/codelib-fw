<?php
/**
 * CLBasePlugin.php
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

use cl\contract\CLLogger;
use cl\contract\CLResponse;
use cl\contract\CLServiceRequest;
use cl\core\CLFlag;
use cl\store\CLBaseEntity;

/**
 * Class CLBasePlugin
 * Base class with essential functionality for CL Plugins. Not a reinforced requirement, it is up to you if your plugins
 * extend this class, but it is recommended, as it will save you time, and current and future base functions
 * for plugins will be added here.
 * @package cl\plugin
 */
class CLBasePlugin implements \cl\contract\CLPlugin
{
    protected $lastError = null;
    protected $clServiceRequest;
    protected $logger;
    protected $pluginResponse;
    protected $clSession;

    public function __construct(CLServiceRequest $clServiceRequest = null, CLResponse $pluginResponse = null)
    {
        $this->clServiceRequest = $clServiceRequest;
        $this->pluginResponse = $pluginResponse;
    }

    /**
     * Executes this plugin
     * @return CLResponse
     */
    public function run(): CLResponse
    {
        $clrequest = $this->clServiceRequest->getCLRequest();
        $clflow = $clrequest->getRequestId();
        $flowAction = $this->getFlowAction($clflow);
        $this->logger->debug('In '.get_class($this).', flowAction ='.$flowAction);
        if ($flowAction == null) {
            $this->logger->error('No specific flow entry found in '.get_class($this).'. Plugin execution cannot continue');
            return $this->prepareResponse('Unfortunately registration failed', CLFlag::FAILURE,CLFlag::REG_PAGE, $clrequest->getRequest());
        }
        return $this->$flowAction();
    }

    public function init(CLServiceRequest $clServiceRequest, CLResponse $pluginResponse)
    {
        $this->clServiceRequest = $clServiceRequest;
        $this->pluginResponse = $pluginResponse;
    }

    public function setClSession($clSession): void
    {
        $this->clSession = $clSession;
    }

    /**
     * @param string $message the message to send back to the client
     * @param string $status
     * @param null $page (optional) the key of the page to prepare the output for the client. Instead you could set the 'laf' in $extraVars
     * @param array|null $extraVars (optional) associative array of variables to send back (available to views/look and feels)
     * @param array|null $payloadArray (optional) numeric array of payload items to include with this response
     * @return CLBaseResponse
     */
    protected function prepareResponse($message = '', $status = CLFlag::SUCCESS, $page = null, array $extraVars = null, array $payloadArray = null): CLBaseResponse
    {
        $vars = array('feedback' => $message, 'status' => $status);
        if (isset($extraVars)) {
            $vars = array_merge($vars, $extraVars);
        }
        if ($this->pluginResponse == null) {
            $this->pluginResponse = new CLBaseResponse();
        }
        $this->pluginResponse->addVars($vars);
        if (isset($page)) {
            $this->pluginResponse->setVar('page', $page);
        }
        if (isset($payloadArray)) {
            foreach ($payloadArray as $payload) {
                $this->pluginResponse->addPayload($payload);
            }
        }
        return $this->pluginResponse;
    }

    protected function validateRequired($input, $fields) {
        foreach($fields as $field) {
            if (!isset($input[$field])) { return false; }
        }
        return true;
    }

    /**
     * This is a (optional) way of finding what plugin method to call, using a .fnname in the clkey or flow,
     * to identify quickly what method to call. Of course plugins can handle this in different ways.
     * For instance, instead a plugin could keep a map of flows (keys) they intercept or participate on, and the funcions that handles
     * that flow.
     * @param $clflow
     * @return bool|string|null
     */
    protected function getFlowAction($clflow) {
        $mapmeths = array_change_key_case($this->mapHttpMethod());
        if (count($mapmeths) > 0) {
            $clrequest = $this->clServiceRequest->getCLRequest();
            $method = $clrequest->getMethod();
            return $mapmeths[$method] ?? null;
        }
        $felms = $this->getFlowElements();
        if ($felms == null || !is_array($felms) || count($felms) < 2) { return null; }
        return $felms[1];
    }

    /**
     * Return elements in the query string or clflow
     * @return array
     */
    protected function getFlowElements() : array {
        $clrequest = $this->clServiceRequest->getCLRequest();
        $clflow = $clrequest->getRequestId();
        $dot = mb_strpos($clflow, '/');
        if ($dot === false) return [$clflow];
        return explode('/', $clflow, 7);
    }

    /**
     * Use to map http methods (POST, PUT, HEAD, PATCH, DELETE, OPTIONS) to specific functions in your
     * plugin. Override in your Extension (Plugin or Controller) with the desired mapping.
     * Note: Mappings to http methods will take precedence over any other call configuration.
     * @return array example: ['post' => 'addObject', 'put' => 'updateObject'] etc.
     */
    protected function mapHttpMethod() : array {
        return [];
    }

    /**
     * Populates an entity with data coming in a request
     * @param string $entityName name of the repository entity
     * @param array $fieldKeys keys to be populated in the entity (for instance, column names of a database table)
     * @param array|null $optional (optional) an array to indicate which fields are optional, to avoid validation errors. Ex. array('surname' => 1, 'age' => 1)
     *        [do not add any required field here.]
     * @param array|null $formNames (optional) an associative array to map store fields to form names, when they do not match. Ex. array('username' => 'user_name')
     * @return CLBaseEntity|null returns a populated entity, or null if a required field is not provided
     */
    protected function requestToEntity(string $entityName, array $fieldKeys, array $optional = null, array $formNames = null) {
        $clrequest = $this->clServiceRequest->getCLRequest()->getRequest();
        $entity = new CLBaseEntity($entityName);
        $data = array();
        foreach($fieldKeys as $key) {
            $name = (isset($formNames) && isset($formNames[$key])) ? $formNames[$key] : $key;
            if (isset($clrequest[$name])) {
                $data[$key] = $clrequest[$name];
            } elseif (isset($optional) && !isset($optional[$name])) {
                $this->lastError = $name. ' is required, but it was not provided';
                return null;
            }
        }
        $entity->setData($data);
        return $entity;
    }

    /**
     * Sets the logger for the plugin. This dependency will be injected by the framework
     * @param mixed $logger
     */
    public function setLogger(CLLogger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Validates the provided email address
     * Used by the CLUserPlugin and other
     * Override if you wish to provide a different implementation for your app
     * @param $email
     * @return bool true if email is valid, false otherwise
     */
    protected function validateEmail($email) : bool {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
    }
}
