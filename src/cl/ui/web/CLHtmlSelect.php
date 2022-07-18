<?php
/**
 * CLHtmlSelect.php
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
 * Class CLHtmlSelect
 * Represents an html <select> element
 * @package cl\ui\web
 */
class CLHtmlSelect extends CLHtmlCtrl {
    /**
     * CLHtmlSelect constructor.
     * @param $name
     * @param string $value
     * @param string $cssId
     * @param string $cssClass
     */
	public function __construct($name,$value='',$cssId='',$cssClass='clhtmlselect'){
		parent::__construct('select',$name,$value,true,$cssId,$cssClass);
		$this->addAllowed('cl\ui\web\CLHtmlOption'); // limit child elements to the ones specified as allowed
	}
}
