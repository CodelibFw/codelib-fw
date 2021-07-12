<?php
/**
 * CLHtmlTabs.php
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
 * Class CLHtmlTabs
 * Represents an html tabs control, implemented here as a list, similar to bootstrap's tabs.
 * The classes default to bootstrap classes, but you can pass different ones if using a different frontend library,
 * as long as those classes create a tab control in html
 *
 * @package cl\ui\web
 */
class CLHtmlTabs extends CLHtmlUl {

    private $contentParent;

    /**
     * CLHtmlTabs constructor.
     * @param string $cssId
     * @param string $cssClass css class for the tab control
     */
    public function __construct($cssId='',$cssClass='nav nav-tabs'){
        parent::__construct('',$cssId,$cssClass);
    }

    /**
     * Adds a new tab to this control
     * @param $title tab title
     * @param $contentLink hyperlink to this tab's content, as in #tab1content, where tab1content could be the id of a div
     * @param $isActiveTab boolean, true if this tab is the active tab
     * @param string $role for bootstrap use the default value below, for other libraries either ignore, use null, or a recommended value as per the library's docs
     */
    public function addTab($title,$contentLink,$isActiveTab = false,$role = 'presentation') {
        parent::addElement((new CLHtmlLi())->addProperty('role',$role)->addElement((new CLHtmlHyperLink($title,$contentLink))->addProperty('data-toggle','tab')));
        return $this;
    }

    /**
     * Sets the content for this element
     * @param $contentLink
     * @param $element
     */
    public function setTabContent($contentLink, $element) {
        if ($this->contentParent == null) {
            $this->contentParent = new CLHtmlDiv('','','tab-content');
        }
        $div = (new CLHtmlDiv('',$contentLink,'tab-pane fade'))->addElement($element);
        $this->contentParent->addElement($div);
    }

    /**
     * Returns an html representation of the control, including properties and children elements
     * @param null $flag
     * @return string
     */
    public function toHtml($flag = null){
        $content = '';
        if (isset($this->contentParent)) {
            $content = $this->contentParent->toHtml($flag);
        }
        return parent::toHtml($flag).$content;
    }
}
