<?php
/**
 * CLLogger.php
 */

namespace cl\contract;
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
 * Specifies the interface for a CL Logging class
 * Interface CLLogger
 * @package cl\contract
 */
interface CLLogger
{
    /**
     * Log Levels
     */
    const ERROR = 2;
    const INFO  = 3;
    const DEBUG = 4;

    /**
     * Add a log entry with specified logging level, type (to further sub-divide your log files) and optional prefix
     * @param string $msg the message to log
     * @param int $loglevel one of the constants defined by CLLogger: ERROR, INFO, DEBUG.
     * @param string $type additional subdivision, as in 'sys', 'support', etc
     * @param string $prefix optional prefix
     * @return mixed
     */
    public function addlog(string $msg, int $loglevel=CLLogger::INFO, string $type='', string $prefix="");

    /**
     * Add a log entry at info log level, with an optional type
     * @param string $msg
     * @param string $type
     * @return mixed
     */
    public function info(string $msg, $type = '');

    /**
     * Add a log entry at error log level, with an optional type
     * @param string $msg
     * @param string $type
     * @return mixed
     */
    public function error(string $msg, $type = '');

    /**
     * Add a log entry at debug log level, with an optional type
     * @param string $msg
     * @param string $type
     * @return mixed
     */
    public function debug(string $msg, $type = '');

    /**
     * Set log level
     * @param int $logLevel
     * @return void
     */
    public function setLogLevel(int $logLevel);
}
