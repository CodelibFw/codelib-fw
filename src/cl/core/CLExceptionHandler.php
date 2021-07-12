<?php
/**
 * CLExceptionHandler.php
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


use cl\web\CLHtmlApp;
use Throwable;

/**
 * Class CLExceptionHandler
 * @package cl\core
 */
class CLExceptionHandler
{
    const ERROR_EXCEPTION = 1;
    const ERROR_ERROR = 2;

    const ERROR_TYPE = 0;
    const ERROR_NO = 1;
    const ERROR_MESSAGE = 2;
    const ERROR_FILE = 3;
    const ERROR_LINE = 4;
    const ERROR_TRACE = 5;

    use CLLog;

    public static function handle(Throwable $ex) : void {
        self::process([1, $ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTraceAsString()]);

    }

    public static function error(int $errno, string $errstr, string $errfile, int $errline) : bool {
        self::process([2, $errno, $errstr, $errfile, $errline]);
        return true;
    }

    private static function process(array $errorCondition) {
        if (CLHtmlApp::$clapp != null) { // in case only CL bootstrapping and CL controls are being used, not the CL framework itself
            $config = CLHtmlApp::$clapp->getAppConfig();
        }

        $logger = self::getLog();
        if ($logger != null) {
            $logger->error('An error occurred. Error details: error number ' . $errorCondition[self::ERROR_NO] . ', file ' .
                $errorCondition[self::ERROR_FILE] . ', line ' . $errorCondition[self::ERROR_LINE] . ', message ' . $errorCondition[self::ERROR_MESSAGE]);
        } else {
            error_log('exception: '.'An error occurred. Error details: error number '. $errorCondition[self::ERROR_NO]. ', file '.
                $errorCondition[self::ERROR_FILE].', line '.$errorCondition[self::ERROR_LINE].', message '.$errorCondition[self::ERROR_MESSAGE]);
        }
        if ($errorCondition[self::ERROR_TYPE] == self::ERROR_EXCEPTION && isset($errorCondition[self::ERROR_TRACE])) {
            if ($logger != null) {
                self::getLog()->error('Stack trace for above error is ' . $errorCondition[self::ERROR_TRACE]);
            } else {
                error_log('Stack trace for above error is ' . $errorCondition[self::ERROR_TRACE]);
            }
        }
        $haltOnError = E_ERROR;
        if (CLHtmlApp::$clapp != null) {
            if ($errorCondition[self::ERROR_TYPE] == self::ERROR_EXCEPTION && $errorCondition[self::ERROR_NO] == 404) {
                CLHtmlApp::$clapp->render404();
            } else {
                CLHtmlApp::$clapp->exceptionResponse('');
            }
            $haltOnError = $config->getHaltOnErrorLevel();
        }
        if ($errorCondition[self::ERROR_TYPE] == 1 || ($haltOnError != null && (($errorCondition[self::ERROR_NO] & $haltOnError) == $errorCondition[self::ERROR_NO]))) {
            exit(1);
        }
    }
}
