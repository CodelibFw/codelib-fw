<?php
/**
 * CLHtmlTable.php
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
 * Class CLHtmlTable
 * Represents an html table element (<table>)
 * @package cl\ui\web
 */
class CLHtmlTable extends CLHtmlCtrl {
    /**
     * CLHtmlTable constructor.
     * @param string $width
     * @param string $height
     * @param string $cssId
     * @param string $cssClass
     */
	public function __construct($width='',$height='',$cssId='',$cssClass='clhtmltable'){
		parent::__construct('table',null,null,true,$cssId,$cssClass);
		$this->addProperty('width', $width);
		$this->addProperty('height', $height);
		$this->addAllowed('cl\ui\web\CLHtmlTr'); // accepts only <tr> as child element
	}
}
