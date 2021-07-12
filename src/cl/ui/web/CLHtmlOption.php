<?php
/**
 * CLHtmlOption.php
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
 * Class CLHtmlOption
 * Represents an html option for a select's <option> element
 * @package cl\ui\web
 */
class CLHtmlOption extends CLHtmlBase{
	private $content;

    /**
     * CLHtmlOption constructor.
     * @param $name
     * @param string $value
     * @param string $content
     * @param string $cssId
     * @param string $cssClass
     * @param false $isSelected true, if the option must be selected
     */
	public function __construct($name,$value='',$content='',$cssId='',$cssClass='',$isSelected=false){
		parent::__construct($name,$cssId,$cssClass);
		$this->content = $content;
		$this->addProperty('value',$value);
		if($isSelected){
			$this->addProperty("selected","selected");
		}
		$this->element = 'option';
	}

    /**
     * Returns the html representation of this control
     * @return string
     */
	public function toHtml(){
		$html = '<'.$this->element.' '.$this->cssId.$this->cssClass.$this->propertiesToHtml().$this->eventsToHtml().' >';
		$html.=$this->content.'</'.$this->element.'>';
		return $html;
	}
}
