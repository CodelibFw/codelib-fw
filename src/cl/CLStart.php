<?php
/**
 * CLStart.php
 */
namespace cl;
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

use cl\core\CLExceptionHandler;

ob_start();

/**
 * Class CLStart
 * Entry point in charge of bootstrapping the app
 * @package app
 */
class CLStart
{
    /** @var Psr4AutoloaderClass the autoloader */
    private $loader;
    private $appNs;

    /**
     * CLStart constructor.
     */
    public function __construct()
    {

        // define vendor locations
		if (is_dir(BASE_DIR . '/../vendor'. DIRECTORY_SEPARATOR)) {
			define('APP_VENDOR', realpath(BASE_DIR . '/../vendor') . DIRECTORY_SEPARATOR); // application's vendor folder
		} elseif (is_dir(BASE_DIR . '/vendor'. DIRECTORY_SEPARATOR)) {
			define('APP_VENDOR', realpath(BASE_DIR . '/vendor') . DIRECTORY_SEPARATOR);
		}
		if (!defined('APP_VENDOR')) { define('APP_VENDOR', realpath(BASE_DIR) . '/vendor' . DIRECTORY_SEPARATOR); } // for now, make sure the constant is at least defined
        define('CL_VENDOR', realpath(CL_DIR . '/../vendor') . DIRECTORY_SEPARATOR);    // code-lib's vendor folder

        if (is_dir(BASE_DIR . '/../resources'. DIRECTORY_SEPARATOR)) {
            define('APP_RES', realpath(BASE_DIR . '/../resources') . DIRECTORY_SEPARATOR); // application's vendor folder
        } elseif (is_dir(BASE_DIR . '/resources'. DIRECTORY_SEPARATOR)) {
            define('APP_RES', realpath(BASE_DIR . '/resources') . DIRECTORY_SEPARATOR);
        }
        if (!defined('APP_RES')) { define('APP_RES', realpath(BASE_DIR) . '/resources' . DIRECTORY_SEPARATOR); } // no resources folder, but we still define the constant
        $this->appNs = APP_NS;
        if ($this->appNs == null || $this->appNs === '') { $this->appNs = 'app'; }
        // start auto-loader
        require CL_DIR . 'cl/Psr4AutoloaderClass.php';
        $this->loader = new Psr4AutoloaderClass;
        $this->loader->register();
        $this->loader->addNamespace('cl', CL_DIR . '/cl');
        $this->loader->addNamespace($this->appNs, BASE_DIR);
        $this->loader->addNamespace('cl\ui\contract', CL_DIR . 'cl/ui/interface');
        $this->loader->addNamespace('cl\contract', CL_DIR . 'cl/interface');
        // set our exception and error handler
        set_exception_handler(['\cl\core\CLExceptionHandler', 'handle']);
        set_error_handler(['\cl\core\CLExceptionHandler', 'error']);
        mb_internal_encoding('UTF-8');
    }

    /**
     * Returns the Psr4 Auto loader
     * @return Psr4AutoloaderClass
     */
    public function getLoader() {
        return $this->loader;
    }

    /**
     * @return string App namespace
     */
    public function getAppNs(): string
    {
        return $this->appNs;
    }

}
$start = new CLStart;
// constants
require_once CL_DIR . 'cl/core/CLConstants.php';
// individual utility functions that will be available at a global scope
require_once CL_DIR . 'cl/util/Functions.php';
if (file_exists(BASE_DIR. '/util/Functions.php')) {
    require_once BASE_DIR. '/util/Functions.php';
}
