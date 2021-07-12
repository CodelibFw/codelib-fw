<?php
/**
 * CLFilter.php
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
 * Interface CLFilter
 * Specifies the interface of a CL Filter. CL Filters intercept all requests, regardless of the CL Flow they belong to
 * @package cl\contract
 */
interface CLFilter
{
    const FILTER_ERROR = 'filtererror'; // use as $vars array key to return error message to caller

    /**
     * A CL Filter performs inspections or transformations on a request or response, and returns TRUE or FALSE to indicate
     * whether the flow should continue or a response should be sent to the client without further processing
     * CLFilter constructor.
     */
    public function __construct();

    /**
     *
     * @param CLRequest $clrequest the request, for instance, a CLHttpRequest for a web request (with data for POST, GET, etc)
     * @param CLResponse $response the response, so the filter can modify or add variables and headers, if needed
     * @return bool
     */
    public function filter(CLRequest $clrequest, CLResponse $response) : bool ;
}
