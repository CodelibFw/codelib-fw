<?php
/**
 * CLHtmlPanel.php
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
 * Class CLHtmlPanel
 * Represents an html panel, ie, a window with a title and a content section, similar to bootstrap's panel.
 * @package cl\ui\web
 * @deprecated use CLHtmlCard instead
 */
class CLHtmlPanel extends CLHtmlDiv {
    private $title,$content;
    private $hdrClass,$titleClass,$contentClass;

    /**
     * CLHtmlPanel constructor.
     * @param string $title panel title
     * @param string $value optional panel initial content
     * @param string $cssId
     * @param string $cssClass css class
     * @param string $hdrClass css class for the header section
     * @param string $titleClass css class for the title section
     * @param string $contentClass css class for the content section
     */
    public function __construct($title,$value='',$cssId='',$cssClass='panel panel-default',$hdrClass='panel-heading',$titleClass='panel-title',$contentClass='panel-body'){
        parent::__construct('',$cssId,$cssClass);
        $this->content = $value;
        $this->title = $title;
        $this->hdrClass = $hdrClass;
        $this->titleClass = $titleClass;
        $this->contentClass = $contentClass;
        $this->createPanel();
    }

    /**
     * @param $content additional content for this panel
     */
    public function addContent($content) {
        $this->content .= $content;
    }

    protected function createPanel() {
        $this->addElement((new CLHtmlDiv('','',$this->hdrClass))->addElement(new CLHtmlCtrl('h3','',$this->title,true,'',$this->titleClass)));
        $this->addElement(new CLHtmlDiv($this->content,'',$this->contentClass));
    }

    /**
     * Returns an html representation of the control, including properties and children elements
     * @param null $flag
     * @return string
     * @throws \Exception
     */
    public function toHtml($flag = null){
        return parent::toHtml($flag);
    }
}
