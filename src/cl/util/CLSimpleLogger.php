<?php
/**
 * CLSimpleLogger.phper.php
 */

namespace cl\util;
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

/**
 * Class CLSimpleLogger
 * Used for logging. Available from within the ClHtmlApp or via the function _log
 * @package cl\util
 */
class CLSimpleLogger implements CLLogger
{
    private $logLevel = CLLogger::INFO, $oneFileForAll, $logFolder;

    /**
     * CLSimpleLogger constructor.
     * @param string $logLevel level of logging, as in info, error, etc
     * @param false $oneFileForAll when false different log files are created for each day, level and log type
     * @param string $logFolder folder to use for logging when $oneFileForAll is false (otherwise logging happens as per
     * server config). This folder can be relative to the app or an absolute path.
     */
    public function __construct($logLevel = CLLogger::INFO, $oneFileForAll = false, $logFolder = 'logs')
    {
        $this->logLevel = $logLevel;
        $this->oneFileForAll = $oneFileForAll;
        $this->logFolder = $logFolder ?? '';
        if (mb_strlen($this->logFolder) > 0 && !endsWith($this->logFolder, '/')) {
            $this->logFolder .= '/';
        }
    }

    /**
     * Add a log entry with specified logging level, type (to further sub-divide your log files) and optional prefix
     * @param string $msg the message to log
     * @param int $loglevel one of the constants defined by CLLogger: ERROR, INFO, DEBUG.
     * @param string $type additional subdivision, as in 'sys', 'support', etc
     * @param string $prefix optional prefix
     * @return mixed
     */
    public function addlog(string $msg, int $loglevel=CLLogger::INFO, string $type='', string $prefix="")
    {
        $lvls = ['','','error','info','debug'];
        $level = $lvls[$loglevel];
        return $this->log($msg, $level, $type, $prefix);
    }

    /**
     * Add a log entry at info log level, with an optional type
     * @param string $msg
     * @param string $type
     * @return mixed
     */
    public function info(string $msg, $type = '')
    {
        $this->log($msg, 'info', $type);
    }

    /**
     * Add a log entry at error log level, with an optional type
     * @param string $msg
     * @param string $type
     * @return mixed
     */
    public function error(string $msg, $type = '')
    {
        $this->log($msg, 'error', $type);
    }

    /**
     * Add a log entry at debug log level, with an optional type
     * @param string $msg
     * @param string $type
     * @return mixed
     */
    public function debug(string $msg, $type = '')
    {
        if ($this->logLevel < CLLogger::DEBUG) return;
        $this->log($msg, 'debug', $type);
    }

    /**
     * Set log level
     * @param int $logLevel
     * @return void
     */
    public function setLogLevel(int $logLevel)
    {
        $this->logLevel = $logLevel;
    }

    private function log(string $msg, string $level, string $type='', string $prefix="")
    {
        $date = date("Y-m-d");
        $fname = 'applog';
        if (!$this->oneFileForAll) {
            if(mb_strlen($prefix)>0)
                $prefix.='_';
            if (mb_strlen($type) > 0) {
                switch ($type) {
                    case 'sys':
                        $fname = $this->logFolder.'applog_sys_' . $prefix . $level . '_' . $date;
                        break;
                    case 'support':
                        $fname = $this->logFolder.'applog_sup_' . $prefix . $level . '_' . $date;
                        break;
                    case 'user':
                        $fname = $this->logFolder.'applog_use_' . $prefix . $level . '_' . $date;
                        break;
                }
            } else {
                $fname = $this->logFolder.'applog_' . $prefix . $level . '_' . $date;
            }
            return error_log("[".date("D Y/m/d H:i:s")."][".$level."]: ".$msg."\r\n",3,$fname);
        }
        return error_log($msg);
    }
}
