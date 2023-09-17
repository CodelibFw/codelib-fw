<?php
/**
 * CLHtmlPage.php
 */
namespace cl\ui\web;
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

use cl\ui\contract\CLUIControl;

/**
 * Class CLHtmlPage
 * Represents an html page, ie, from <html> ... </html>.
 * it provides functionality to add elements to the page's head and body.
 * it makes use of other html controls, which must be provided (included) by the caller:
 * requires: HtmlCtrl, HtmlLink, etc or it can use a specified look and feel
 * @package cl\ui\web
 */
class CLHtmlPage extends CLHtmlCtrl {
    private $header,$body,$footer = array();

    /**
     * Creates an html page as in <html><head>...</head><body>...</body></html>
     * @param mixed $head optional html content, or a child CL control
     * @param string $body
     */
    public function __construct($head = null, $body = null) {
        parent::__construct('html','','',true,'','');
        if ($head instanceof CLHtmlHead) {
            $this->header = $head;
        } else {
            $this->header = new CLHtmlHead();
            $this->header->add($head);
        }
        $this->body = new CLHtmlCtrl('body', '_body');
        $this->body->add($body);
        parent::addElement($this->header);
        parent::addElement($this->body);
    }

    /**
     * Overrides this method to set request into head and body too, in case it is needed
     * @param null $clrequest
     * @return CLHtmlBase
     */
    public function setClrequest(&$clrequest): CLHtmlBase
    {
        $this->clrequest = $clrequest;
        $this->header->setClrequest($clrequest);
        $this->body->setClrequest($clrequest);
        return $this;
    }

    /**
     * Adds a html to the header of this page
     * @param mixed $element adds a CL control or plain html content to the head of this page
     */
    public function addHeader($element): CLHtmlPage
    {
        $this->header->add($element);
        return $this;
    }

    /**
     * Adds a CL control or plain html to the body of this page
     * @param mixed $element adds a CL control or plain html content (not php code) to the body of this page
     */
    public function addBody($element): CLHtmlPage
    {
        $this->body->add($element);
        return $this;
    }

    /**
     * Adds $element as a child of this control
     * @param mixed $element a control that extends HtmlBase
     * @return CLHtmlBase
     */
    public function addElement($element): CLHtmlBase {
        $this->addBody($element);
        return $this;
    }

    /**
     * Adds a CL control or just plain html to the body of this control
     * @param mixed $value an element (ie, a CL control) or just plain html data (string) (not php code)
     * @return CLHtmlCtrl|void
     */
    public function add($value): CLHtmlCtrl {
        $this->addBody($value);
        return $this;
    }

    /**
     * Adds a CL control to the bottom or footer of this control
     * @param mixed $element element to add at the end of the body, just before the </body> tag
     */
    public function addFooter($element): CLHtmlPage
    {
        $this->footer[] = $element;
        return $this;
    }

    /**
     * Returns the html representation of this page and its children
     * @param null $flag
     * @return string
     * @throws \Exception
     */
    public function toHtml($flag = null) {
        if (count($this->footer) > 0) {
            foreach($this->footer as $element) {
                $this->addBody($element);
            }
        }
        return parent::toHtml($flag);
    }
}
