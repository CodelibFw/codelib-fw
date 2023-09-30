<?php
/**
 * Util.php
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

use cl\contract\CLRequest;
use cl\ui\web\CLHtmlCtrl;
use cl\ui\web\CLHtmlPage;

/**
 * Class Util
 * Utility functions, which provides a few common functions within a CL app
 * @package cl\util
 */
class Util {
    /**
     * Creates a CLHtmlPage with the specified look and feel and child controls
     * @param string|null $laf (optional) look and feel for the page
     * @param array|null $ctrls (optional) array of controls to add to the page, each control specified using an array as well.<br>
     *        Examples: <code>array(array('','register.php'), array('','stl2footer.php'))</code> <-- 2 anonymous controls with look&feel
     *                  register.php and stl2footer.php, respectively.
     *                  <code>array(array('','user.php', array('heading' => 'welcome back','title' => 'Dr')))</code> <-- 1 anonymous control,
     *                  with look&feel user.php and variables heading and title to be passed to the control
     * @return CLHtmlPage
     */
    public static function newPage(string $laf = null, array $ctrls = null): CLHtmlPage
    {
        $page = new CLHtmlPage();
        if (isset($laf)) { $page->setLookandFeel($laf); }
        if (!isset($ctrls)) { return $page; }
        $n = count($ctrls);
        for ($i=0; $i < $n; $i++) {
            $name = isset($ctrls[$i][0]) ? $ctrls[$i][0] : '';
            $ctrl = new CLHtmlCtrl($name);
            if (isset($ctrls[$i][1])) { $ctrl->setLookandFeel($ctrls[$i][1]); }
            if (isset($ctrls[$i][2]) && is_array($ctrls[$i][2]) && count($ctrls[$i][2]) > 0) {
                $ctrl->setVars($ctrls[$i][2]);
            }
            $page->addElement($ctrl);
        }
        return $page;
    }

    /**
     * adds extension to a filename, if it doesn't have it already
     * @param string $fileName
     * @param string $ext
     * @return string the filename with the extension
     */
    public static function addExt(string $fileName, string $ext): string
    {
        if ($ext[0] === '.') {
            $ext = substr($ext, 1);
        }
        if (strripos($fileName, '.'.$ext) === false) {
            $fileName = $fileName . '.'.$ext;
        }
        return $fileName;
    }

    /**
     * A trivial implementation of a pseudo-random alpha-numeric sequence of a certain length
     * @param int $length the desired sequence length
     * @return string returns the random sequence
     */
    public static function genAlphaNumRndVal(int $length): string
    {
        $templ = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));
        $lg = count($templ)-1;
        $val = '';
        for ($i=0; $i < $length; $i++) {
            $idx = mt_rand(0, $lg);
            $val .= $templ[$idx];
        }
        return $val;
    }

    /**
     * Return elements in the query string or clflow
     * @param CLRequest $clrequest
     * @return array
     */
    public static function getFlowElements(CLRequest $clrequest) : array {
        $clflow = $clrequest->getRequestId();
        $slash = mb_strpos($clflow, '/');
        if ($slash === false) return [];
        return explode('/', $clflow, 7);
    }

    /**
     * Returns the contents of a given file or null if file not found
     * @param $filepath
     * @return false|string|null
     */
    public static function loadFile($filepath) {
        if (!file_exists($filepath)) { return null; }
        return file_get_contents($filepath);
    }

    /**
     * Attempts to recursively create the folders in a given path, if they do not exist
     * @param string $path
     * @throws \Exception
     */
    public static function ensurePathExists(string $path) {
        if (!file_exists($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new \Exception('Unable to create requested path: '.$path);
            }
        }
        return true;
    }

    public static function getPluginClassName($plugin, $extFolder, $appNsPrefix, $throwException = false) {
        $pluginFolder = strtolower($plugin);
        $pluginFile = Util::addExt($plugin, 'php');
        if (file_exists(BASE_DIR . '/'.$extFolder.'/' . $pluginFile)) {
            $className = $appNsPrefix.'\\'.$extFolder.'\\' . $plugin;
        } elseif (file_exists(BASE_DIR . '/'.$extFolder.'/' . $pluginFolder . '/' . $pluginFile)) {
            $className = $appNsPrefix.'\\'.$extFolder.'\\' . $pluginFolder . '\\' . $plugin;
        } elseif (file_exists(CL_DIR . '/cl/plugin/' . $pluginFile)) {
            $className = '\cl\plugin\\' . $plugin;
        } else {
            if ($throwException) {
                throw new \Exception('Extension ' . $pluginFile . ' does not exist');
            } else { return null; }
        }
        return $className;
    }

    public static function prepareMessage($fromEmail, $usermail, $subject, $message) {
        return ['to' => $usermail, 'from' => $fromEmail, 'subject' => $subject, 'message' => $message];
    }

}
