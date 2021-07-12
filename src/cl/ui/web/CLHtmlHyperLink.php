<?php
/**
 * CLHtmlHyperLink.php
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

/**
 * Class CLHtmlHyperLink
 * Represents an html hyperlink, ie, <a> element
 * @package cl\ui\web
 */
class CLHtmlHyperLink extends CLHtmlCtrl {

    /**
     * CLHtmlHyperLink constructor. Creates a <a href... element
     * @param string $value content of this link, as in <a href="">content</a>
     * @param string $href the url to use in the href portion of this link, ie, <a href="url"...
     * @param string $onclick optional event to trigger when the hyperlink is pressed
     * @param string $title optional title
     * @param string $cssId optional css id
     * @param string $cssClass optional css class
     */
    public function __construct($value,$href,$onclick='',$title='',$cssId='',$cssClass='clhtmlhyperlink'){
        parent::__construct('a',null,$value,true,$cssId,$cssClass);
        $this->addProperty('href',$href);
        $this->addEvent('onclick',$onclick);
        $this->addProperty('title',$title);
    }
}
