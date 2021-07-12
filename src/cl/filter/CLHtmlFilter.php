<?php
/**
 * CLHtmlFilter.php
 */

namespace cl\filter;
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
use cl\contract\CLRequest;
use cl\contract\CLResponse;
use cl\web\CLHtmlApp;

/**
 * Class CLHtmlFilter
 * A filter to provide optional Html cleanup of submitted data
 * @package cl\filter
 */
class CLHtmlFilter implements \cl\contract\CLFilter
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function filter(CLRequest $clrequest, CLResponse $response): bool
    {
        _log('Executing CLHtmlFilter', CLLogger::DEBUG);
        $htmlFilterCfg = CLHtmlApp::$clapp->getAppConfig()->getAppConfig(HTML_FILTER);
        $convert2entities = $htmlFilterCfg[FILTER_SPECIAL_CHARS] ?? false;
        $removeTags = $htmlFilterCfg[FILTER_REMOVE_TAGS] ?? false;
        if (!$convert2entities && !$removeTags) return true;
        $data = $clrequest->getRequest();
        $isResponsePhase = $response->getVar('status') != null;
        if ($isResponsePhase) { $data = $response->getVars(); }
        if ($data == null || !is_array($data) || count($data) == 0) return true;
        foreach (array_keys($data) as $key) {
            if ($removeTags) {
                $data[$key] = strip_tags($data[$key]);
            }
            if ($convert2entities) {
                $data[$key] = htmlspecialchars($data[$key]);
            }
        }
        if ($isResponsePhase) {
            $response->addVars($data);
        } else {
            $clrequest->setRequest($data);
        }
        return true;
    }
}
