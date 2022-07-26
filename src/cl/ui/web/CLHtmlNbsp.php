<?php
/**
 * CLHtmlNbsp.php
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

use cl\ui\contract\a;
use cl\ui\contract\accepts;

/**
 * Class CLHtmlNbsp
 * Represents an html &nbsp;
 * @package cl\ui\web
 */
class CLHtmlNbsp extends CLHtmlBase{
    /**
     * @param int $count is the number of consecutive &nbsp; you want
     * @param string $cssId
     * @param string $cssClass
     */
    public function __construct($count=1,$cssId='',$cssClass=''){
        parent::__construct(null,$cssId,$cssClass);
        $this->value = $count;
        $this->element = 'nbsp';
    }

    /**
     * Returns as many html blank spaces as specified by this control's value (count)
     * @return string
     */
    public function toHtml($flag = null){
        $html = '';
        for ($i=0;$i < $this->value;$i++) {
            $html.='&nbsp;';
        }
        return $html;
    }

    public function setValue($value)
    {
        // TODO: Implement setValue() method.
    }

    public function addValue($value)
    {
        // TODO: Implement addValue() method.
    }

    public function add($value)
    {
        // TODO: Implement add() method.
    }

    public function toJs()
    {
        // TODO: Implement toJs() method.
    }

    public function addFlag($flag)
    {
        // TODO: Implement addFlag() method.
    }
}
