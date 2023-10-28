<?php
/**
 * Diagnostics.php
 */
namespace cl\tool;
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
 * Class Diagnostics
 * Utility class that runs diagnostics or self-analysis for a CL app. To run it, set introspection to true, in the run
 * method of your App. It will log, or alternatively, save a report for your App instead of running it.
 * Work in progress. Needs an update after latest code changes in Codelib
 * @package cl\tool
 */
class Diagnostics {
    const RPT_LOG = 1, RPT_HTML_OUT = 2, RPT_FILE_TXT = 3; // determine if we just log to error_log, create html or create text
    private $app;
    private $introspection;
    private $reportStyle = self::RPT_HTML_OUT; // generate report as html
    private $report = '';
    private $flowinfo = array();
    private $clkeyName = 'clkey';
    private $pluginInfo = array();
    private $tracedFlows = array();

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function knowThySelf($reportStyle = self::RPT_LOG) {
        try {
            $this->reportStyle = $reportStyle;
            $this->introspection = new \ReflectionClass($this->app);
            $this->takeNote('********* DIAGNOSTICS *********');
            $this->takeNote('Running Diagnostics in Dev Mode! Make sure to switch to CLDeployment::PROD when deploying to production!');
            $this->analyzePlugins();
            $this->analyzeFlow();
            $this->summary();
            return true;
        } catch (\ReflectionException $e) {
            $this->takeNote('Unable to perform diagnostics, due to: '.$e->getMessage().'\n'.$e->getTraceAsString());
            return false;
        }
    }

    private function analyzeFlow() {
        $this->takeNote('-----------------------------');
        $this->takeNote('Analysing flow of information in App');
        $this->clkeyName = $this->getPropertyValue($this->introspection->getProperty('clkey'), $this->app);
        $pages = $this->getPropertyValue($this->introspection->getProperty('pages'), $this->app);
        if ($pages == null) {
            $this->takeNote('No pages found in App...returning');
            return;
        }
        $flows = array_keys($pages);
//        $flows = array();
//        foreach ($p_k as $k) {
            //$fe = $this->findFlowEntries($k);
            //foreach($fe as $e) { $flows[] = $e; }
        //}
        $this->flowinfo['pages'] = $flows;
        foreach ($pages as $key => $pg) {
            $this->analyzePage($pg, $key);
        }
        foreach ($flows as $flow) {
            $this->findHandlers($flow, 'Page', '');
        }
        if (isset($this->flowinfo['laf']) && count($this->flowinfo['laf']) > 0) {
            $this->takeNote('---------------------------------');
            $this->takeNote('Figuring out handlers for registered l&fs (with flows)');
            foreach ($this->flowinfo['laf'] as $laf => $lafKey) {
                $this->findHandlers($lafKey, 'L&F', $laf);
            }
        } else {
            $this->takeNote('No L&F flow analysis required, as there are none registered');
        }
    }

    private function summary() {
        if (count($this->tracedFlows) > 0) {
            $this->takeNote('=======================');
            $this->takeNote('Known Application Flows');
            $this->takeNote('=======================');
            foreach ($this->tracedFlows as $tracedFlow) {
                $this->takeNote($tracedFlow);
            }
        }
    }

    private function analyzePage($page, $key) {
        $lafCollection = array();
        $this->getAllLaf($page, $lafCollection);
        $n = count($lafCollection);
        $this->takeNote($n.' L&F associated with page key: '.$key);
        if ($n > 0) {
            foreach ($lafCollection as $laf) {
                if ($laf != null) {
                    $lafKey = $this->getLafKey($laf);
                    if ($lafKey != null) {
                        $this->flowinfo['laf'][$laf] = $lafKey;
                        $this->takeNote('Registered l&f: ' . $laf. ' has flow key: '.$lafKey);
                    } else {
                        $this->takeNote('Registered l&f: ' . $laf. ' does not contain a flow key');
                    }
                }
            }
        }
    }

    private function getAllLaf($ctrl, &$lafCollection) {
        $laf = $ctrl->getLookandFeel();
        if ($laf != null) {
            $lafCollection[] = $laf;
        }
        $children = $this->getPropertyValue((new \ReflectionClass(get_class($ctrl)))->getProperty('children'), $ctrl);
        if ($children == null) { return $lafCollection; }
        foreach ($children as $child) {
            $this->getAllLaf($child, $lafCollection);
        }
        return $lafCollection;
    }

    private function getLafKey($laf) {
        if (file_exists(BASE_DIR.CLFRONT.'/html/'.$laf)) {
            $lafData = file_get_contents(BASE_DIR . CLFRONT.'/html/' . $laf);
            preg_match('/(input type="hidden"[\s]*)(name="clkey"[\s]*value=")([\S]+)"/', $lafData, $matches);
            if (isset($matches) && count($matches) > 3) {
                return $matches[3];
            }
        }
        return null;
    }

    private function analyzePlugins() {
        $pluginsReg = $this->getPropertyValue($this->introspection->getProperty('plugins'), $this->app);
        if ($pluginsReg == null || count($pluginsReg) == 0) {
            $this->takeNote('No plugins defined for the App');
            return;
        }
        $this->takeNote('Registered plugins');
        $i = 0;
        foreach ($pluginsReg as $route) {
            $fkey = $route->getKey();
            $plugin = $route->getPlugin();
            $this->takeNote('Plugin: '.$plugin.' handles '.$fkey);
            $methodNames = $this->getPluginDetails($plugin);
            if ($methodNames != null) {
                $this->pluginInfo[$fkey][$i]['name'] = $plugin;
                $this->pluginInfo[$fkey][$i]['methods'] = $methodNames;
                $i++;
            }
        }
    }

    private function findHandlers($flow, $originType = '', $origin = null) {
        $keys = array_keys($this->pluginInfo);
        $fe = $this->findFlowEntries($flow);
        $originType = 'Request originates from a '.$originType;
        if ($origin == null) { $origin = ''; }
        else { $origin = '-> '.$origin; }
        $n = 0;
        $fn = function($flow, $keys, $originType, $origin, $fname = '', $originalFlow = '')
        {
            $n = 0;
            foreach ($keys as $key) {
                if ($key === $flow || $key === '*/*') {
                    $n = count($this->pluginInfo[$key]);
                    if ($n > 0) { // it should be
                        $this->takeNote($n . ' handlers found for flow: ' . $flow . ' (' . $originType . $origin . '):');
                        for ($i = 0; $i < $n; $i++) {
                            //$handlers[] = $this->pluginInfo[$key];
                            $this->takeNote(' It is handled by Plugin: ' . $this->pluginInfo[$key][$i]['name']. ' which is configured to handle '. $key);
                            if (isset($this->pluginInfo[$key][$i]['methods'])) {
                                $methodNames = $this->pluginInfo[$key][$i]['methods'];
                                $mf = false;
                                foreach ($methodNames as $methodName) {
                                    if ($methodName === $key || $methodName === $fname) {
                                        $this->takeNote(' Handled by function: ' . $methodName);
                                        $mf = true;
                                        $this->tracedFlows[] = $origin.'['.$originalFlow.'] => '.$this->pluginInfo[$key][$i]['name'].'->'.$methodName.' => Response';
                                    }
                                }
                                if (!$mf) {
                                    $this->takeNote('No specific method found in Plugin to handle flow');
                                    $this->tracedFlows[] = $origin.'['.$originalFlow.'] => '.$this->pluginInfo[$key][$i]['name'].'->? => Response';
                                }
                            } else {
                                $this->takeNote('No methods found in Plugin to handle flow!');
                                $this->tracedFlows[] = $origin.'['.$originalFlow.'] => '.$this->pluginInfo[$key][$i]['name'].'->? => Response';
                            }
                        }
                    }
                }
            }
            return $n;
        };
        $this->takeNote('Analyzing flow: '.$flow);
        $n = $fn($flow, $keys, $originType, $origin, $flow);
        if ($n == 0) {
            $this->takeNote('No Plugin appear to be directly handling flow: '.$flow);
            if (count($fe) > 1) {
                foreach ($fe as $f) {
                    if (mb_strpos($f, '.*') !== false) {
                        $fname = null;
                        $idx = mb_strpos($flow, '.');
                        if ($idx !== false) {
                            $fname = mb_substr($flow, ($idx+1));
                        }
                        $this->takeNote('Analyzing the root flow...'.$f);
                        foreach ($keys as $key) {
                            if ($key === $f) {
                                $n = $fn($f, $keys, $originType, $origin, $fname, $flow);
                                if ($n == 0) {
                                    $this->takeNote('No Plugin appear to be handling the root flow for : '.$flow);
                                    $this->tracedFlows[] = $origin.'['.$flow.'] => Unknown handler';
                                }
                            }
                        }
                    }
                }
            } else {
                $this->tracedFlows[] = $origin.'['.$flow.'] => Unknown handler.';
            }
        }
    }

    private function getPropertyValue($property, $instance) {
        if ($property == null || $instance == null) { return null; }
        if ($property->isPrivate() || $property->isProtected()) {
            $property->setAccessible(true);
        }
        return $property->getValue($instance);
    }

    private function getPluginDetails($plugin) {
        $appNsPrefix = '\\'.$this->app->getAppNs();
        $extFolder = $this->app->getExtFolder();
        $className = Util::getPluginClassName($plugin, $extFolder, $appNsPrefix);
        if ($className == null) { return null; }
        $plRC = new \ReflectionClass($className);
        $methods = $plRC->getMethods();
        if ($methods != null && count($methods) > 0) {
            foreach ($methods as $method) {
                $methodNames[] = $method->getName();
            }
            return $methodNames;
        }
        return null;
    }

    private function findFlowEntries($key) {
        $ffe = $this->introspection->getMethod('findFlowEntries');
        $ffe->setAccessible(true);
        return $ffe->invoke($this->app, $key);
    }

    private function takeNote($note) {
        if ($this->reportStyle == self::RPT_LOG) {
            error_log($note);
        } else {
            $lnend = array("".self::RPT_HTML_OUT => '<br>', "".self::RPT_FILE_TXT => PHP_EOL);
            if ($this->reportStyle == self::RPT_HTML_OUT) {
                $this->report .=$note . '<br>';
            } else {
                $this->report.=$note.$lnend["".$this->reportStyle];
            }
        }
    }

    /**
     * @return string
     */
    public function getReport(): string
    {
        return $this->report;
    }
}
