<?php
/**
 * CLCsrfFilter.php
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

use cl\contract\CLFilter;
use cl\contract\CLLogger;
use cl\contract\CLRequest;
use cl\contract\CLResponse;
use cl\web\CLConfig;

/**
 * Class CLCsrfFilter
 * Implements a CSRF Filter
 * @package cl\filter
 */
class CLCsrfFilter implements CLFilter {
    private $clrequest;

    /**
     * A CL Filter performs inspections or transformations on a request, and returns TRUE or FALSE to indicate
     * whether the flow should continue or a response should be sent to the client without further processing
     * CLFilter constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param CLRequest $clrequest
     * @param CLResponse $response add headers or variables to the response, if necessary
     * @return bool true means ok, false request rejection
     */
    public function filter(CLRequest $clrequest, CLResponse $response): bool
    {
        $csrfId = $clrequest->post(CSRF_KEY);
        if (!isset($csrfId)) { return false; }
        if (!isset($_COOKIE[CSRF_KEY])) { return false; }
        $alternativeCsrfId = $_COOKIE[CSRF_KEY];
        $valid = $csrfId === $alternativeCsrfId;
        if (!$valid) {
            _log('csrf validation failed for '.$csrfId.' and '.$alternativeCsrfId, CLLogger::ERROR);
            $response->setVar(CLFilter::FILTER_ERROR, 'Invalid CSRF request validation');
        }
        return $valid;
    }
}
