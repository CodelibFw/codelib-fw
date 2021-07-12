<?php
/**
 * CLHtmlTextArea.php
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
 * Class CLHtmlTextArea
 * Represents an html <textarea> element
 * @package cl\ui\web
 */
class CLHtmlTextArea extends CLHtmlCtrl {
    /**
     * CLHtmlTextArea constructor.
     * @param string $name name attribute of the control
     * @param $rows number of rows
     * @param $cols number of columns
     * @param string $value the optional value of the control
     * @param string $cssId css id
     * @param string $cssClass css class of the control
     */
	public function __construct($name,$rows,$cols,$value='',$cssId='',$cssClass='clhtmltextarea'){
		parent::__construct('textarea',$name,$value,true,$cssId,$cssClass);
		$this->addProperty('rows', $rows);
		$this->addProperty('cols', $cols);
	}
}
