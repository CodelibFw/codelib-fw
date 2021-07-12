<?php
/**
 * CLHtmlInput.php
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
 * Class CLHtmlInput
 * Represents an html input element
 * @package cl\ui\web
 */
class CLHtmlInput extends CLHtmlCtrl {

    /**
     * CLHtmlInput constructor.
     * @param string $inputtype as in 'text', 'hidden', etc
     * @param string $name the name attribute of the control
     * @param string $value the value of the control
     * @param string $cssId css id of the control
     * @param string $cssClass css class of the control
     * @param false $isSelected applicable only to type checkbox, if true it will be set to checked
     */
	public function __construct($inputtype,$name,$value='',$cssId='',$cssClass='clhtmlinput',$isSelected=false){
		parent::__construct('input',$name,$value,false,$cssId,$cssClass);
		$this->addProperty('type', $inputtype);
		if($isSelected){
			$this->addProperty("checked","checked");
		}
	}
}
