<?php
/**
 * CLApp.php
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
 * Interface CLApp
 * Specs for a code-lib app
 * @package cl\contract
 */
interface CLApp
{
    /**
     * Use this if you wish to set a common header for all the pages in the app
     * @param $header (optional) use only to set CLHtmlHead or plain html as head template for all pages,
     *                otherwise, specify the header in each page you create
     */
    public function setHeader($header);

    /**
     * Use this if you wish to set a common footer for all the pages in the App
     * @param $footer (optional) use only to set a CLHtmlCtrl or plain html as footer template for all pages,
     *                otherwise, specify the footer in each page you create
     */
    public function setFooter($footer);

    /**
     * Adds a new page to the app
     * @param mixed $key an identifier for this element. Either a string or an array of strings, to assign alias for the
     * same element. See @CLHtmlApp::addElement as an example of alias usage
     * @param $element
     * @param bool $isdefault true if this element is the default element
     * @return mixed
     */
    public function addElement($key, $element, bool $isdefault);

    /**
     * Runs the app
     * @param bool $introspection (optionally) run introspection or inspection/diagnostics instead of normal run.
     *             Should help to discover issues and speed up development
     * @return mixed
     */
    public function run(bool $introspection = false);
}
