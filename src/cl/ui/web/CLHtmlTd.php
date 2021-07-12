<?php
/**
 * CLHtmlTd.php
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
 * Class CLHtmlTd
 * Represents an html table <td> (or <th>) element
 * @package cl\ui\web
 */
class CLHtmlTd extends CLHtmlBase{
	private $width,$height,$colspan,$isheader;

    /**
     * CLHtmlTd constructor.
     * @param boolean $isheader true will make this a <th> otherwise it will be a <td>
     * @param string $width
     * @param string $height
     * @param string $colspan number of columns this cell must span
     * @param string $cssId
     * @param string $cssClass
     */
	public function __construct($isheader=false,$width='',$height='',$colspan='',$cssId='',$cssClass=''){
		parent::__construct(null,$cssId,$cssClass);
		$this->width = $this->labelField('width', $width);
		$this->height = $this->labelField('height', $height);
		$this->colspan = $this->labelField('colspan', $colspan);
		$this->isheader = $isheader;
		$this->element = 'td';
	}

    /**
     * Returns an html representation of the control, including properties and children elements
     * @param $isheader set to true, if this column is a header column (th)
     * @return string
     * @throws \Exception
     */
	public function toHtml($isheader){
		if($isheader!=null && $isheader==true) {
			$this->isheader = $isheader;
		}
		if ($this->isheader) {
			$html = '<th'.$this->width.$this->height.$this->colspan.$this->cssId.$this->cssClass.$this->eventsToHtml().' style="vertical-align: top;">';
		}else{
			$html = '<td'.$this->width.$this->height.$this->colspan.$this->cssId.$this->cssClass.$this->eventsToHtml().' style="vertical-align: top;">';
		}
		$html.=$this->childrenToHtml();
		if ($this->isheader) {
			$html.='</th>';
		}else{
			$html.='</td>';
		}
		return $html;
	}
}
