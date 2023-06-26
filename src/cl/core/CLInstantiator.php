<?php
/**
 * CLInstantiator.php
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

use cl\contract\CLInjectable;
use cl\contract\CLLogger;
use cl\web\CLHtmlApp;


/**
 * Class CLInstantiator
 * @package cl\core
 * Creates and maintains class instances. Mostly used internally by the framework
 * but available as a few static functions to CL Apps
 */
class CLInstantiator
{
    private static $instances = [];
    private static $mappings = []; // key: [class, extending class, params]

    /**
     * provides the requested instance of a class
     * @param CLInstantiationRequest $instantiationRequest expects a key, optional classname, params, and instantiation type
     * ex.: ['classKey', '\app\core\Smartest.php', null, CLFlag::NOT_SHARED]
     * @return mixed
     * @throws \Exception
     */
    public static function getInstance(CLInstantiationRequest $instantiationRequest)
    {
        list($key, $classname, $exClass, $params, $instType) = $instantiationRequest->asArray();
        if ($instType == CLFlag::NOT_SHARED) {
            if (array_key_exists($key, CLInstantiator::$mappings)) {
                list ($mp_className, $mp_exClass, $mp_params_array) = CLInstantiator::$mappings[$key];
                return CLInstantiator::classInstance($mp_className, $mp_exClass, false, ...$mp_params_array);
            } elseif ($classname == null) {
                throw new \Exception('Cannot create new instance of unspecified class');
            } else {
                if ($params == null || !is_array($params)) {
                    $comp = CLInstantiator::classInstance($classname, $exClass, false, $params);
                } else {
                    $comp = CLInstantiator::classInstance($classname, $exClass, false, ...$params);
                }
                if ($comp == null) { throw new \Exception('Failed to create instance of '.$classname); }
                CLInstantiator::$mappings[$key] = [$classname, $exClass, $params];
                return $comp;
            }
        }
        if (array_key_exists($key, CLInstantiator::$instances)) {
            return CLInstantiator::$instances[$key];
        } else {
            if (array_key_exists($key, CLInstantiator::$mappings)) {
                list ($mp_className, $mp_exClass, $mp_params_array) = CLInstantiator::$mappings[$key];
                if ($mp_params_array == null) { $mp_params_array = []; }
                return CLInstantiator::classInstance($mp_className, $mp_exClass, false, ...$mp_params_array);
            }
            if ($classname == null) {
                throw new \Exception('Cannot create new instance of unspecified class. Key used: '.$key);
            }
            if ($params == null || !is_array($params)) {
                $comp = CLInstantiator::classInstance($classname, $exClass, false, $params);
            } else {
                $comp = CLInstantiator::classInstance($classname, $exClass, false, ...$params);
            }
            if ($comp == null) { throw new \Exception('Failed to create instance of '.$classname); }
            CLInstantiator::$instances[$key] = $comp;
            return $comp;
        }
    }

    public static function addInstance($key, $comp) {
        CLInstantiator::$instances[$key] = $comp;
    }

    public static function addRef($key, $classname, $exClass, $params = null) {
        CLInstantiator::$mappings[$key] = [$classname, $exClass, $params];
    }

        /**
     * creates an instance of the given class
     * @param $className
     * @param null $exClass
     * @param false $failSilently
     * @param mixed ...$c_args
     * @return mixed
     * @throws \Exception
     */
    public static function classInstance($className, $exClass = null, $failSilently = false, ...$c_args) {
        $comp = &CLInstantiator::newObj($className, ...$c_args);
        if (isset($comp)) {
            if ($exClass != null && !($comp instanceof $exClass)) {
                throw new \Exception($className . ' should implement or extend ' . $exClass);
            }
        } elseif (!$failSilently) {
            throw new \Exception('Failed to instantiate '.$className);
        }
        return $comp;
    }

    public static function iocCheck(&$object, $appConfig, CLHtmlApp $app = null) {
        if ($object instanceof CLInjectable) {
            $dependencies = CLInstantiator::callMethod($object, 'dependsOn');
            if ($dependencies == null || !is_array($dependencies)) {
                _log('Injectable Plugin did not return any dependency');
            } else {
                _log('Injecting dependencies for '.get_class($object), CLLogger::DEBUG);
                foreach($dependencies as $instantiationRequest) {
                    $repoConnDetails = null;
                    if ($instantiationRequest->getKey() == CL_PLUGIN) {
                        $comp = $app->instantiatePlugin($instantiationRequest->getClassname(), 'run');
                        $met = 'set'.ucfirst($instantiationRequest->asArray()[1]);
                    } else {
                        if ($instantiationRequest->getKey() == ACTIVE_REPO) {
                            $activeRepo = $appConfig->getActiveClRepository();
                            if ($activeRepo == null) {
                                throw new \Exception('Active repo is required but not defined');
                            }
                            $instantiationRequest->setKey($activeRepo);
                            $repoConnDetails = $appConfig->getRepoConnDetails($activeRepo);
                            if ($repoConnDetails == null) {
                                throw new \Exception('CL Repository ' . $activeRepo . ' not properly configured. Connection details are missing');
                            }
                            $instantiationRequest->setExClass('CLRepository');
                        }
                        $comp = CLInstantiator::getInstance($instantiationRequest);
                        if ($comp == null) {
                            throw new \Exception('Unable to find dependency ' . $instantiationRequest->getKey);
                        }

                        $met = 'set' . ucfirst($instantiationRequest->asArray()[0]);
                        if ($repoConnDetails != null && $comp instanceof \cl\contract\CLRepository) {
                            $comp->setConnectionDetails($repoConnDetails);
                            $met = 'setActiveRepo';
                        }
                    }
                    CLInstantiator::callMethod($object, $met, $comp);
                }
            }
        }
    }

    public static function callMethod($obj, $method, $m_args = null) {
        if ($obj == null) {
            throw new \Exception('intended ->'.$method.' call on a null object');
        }
        if (isset($m_args)) {
            return $obj->$method($m_args);
        }
        return $obj->$method();
    }

    /** create instance of given class
     * @param $object
     * @param mixed ...$args
     * @return mixed
     */
    private static function &newObj($object, ...$args) {
        if ($args == null) {
            $obj = new $object();
            return $obj;
        } else {
            $obj = new $object(...$args);
            return $obj;
        }
    }

}
