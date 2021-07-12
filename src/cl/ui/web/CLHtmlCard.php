<?php
/**
 * CLHtmlCard.php
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
 * Class CLHtmlCard
 * Creates an html card, like the Twitter Bootstrap cards
 * A flexible card, to with you can add, optionally a header, a footer, a top image, a title, a subtitle, apart from
 * content.
 * Implemented using Bootstrap classes, but you can replace the default cssClass, in each of the public functions below,
 * with your own, to create a card without using Bootstrap
 * Examples of usage:
 * $card = new \cl\ui\web\CLHtmlCard();
 * $card->addHeader('My Own Card')->addText('A very simple Card, indeed!')->addLink('#','Replace me with a link somewhere!');
 * @package cl\ui\web
 */
class CLHtmlCard extends CLHtmlDiv
{
    private $body, $topImg, $title, $subtitle, $header, $footer;
    private $text = [], $link = [];

    /**
     * CLHtmlCard constructor.
     * @param string $cssId
     * @param string $cssClass
     * @param string $cssBodyClass
     */
    public function __construct($cssId='',$cssClass='card', $cssBodyClass = 'card-body')
    {
        parent::__construct($cssId, $cssClass);
        $this->body = new CLHtmlDiv('', '', $cssBodyClass);
    }

    public function addTopImage($src, $alt) {
        $this->topImg = new CLHtmlImg($src, $alt);
        return $this;
    }

    public function addTitle($title, $cssClass = 'card-title') {
        $this->title = new CLHtmlCtrl('h5','', $title, true, '', $cssClass);
        return $this;
    }

    public function addSubTitle($subtitle, $cssClass = 'card-subtitle mb-2 text-muted') {
        $this->subtitle = new CLHtmlCtrl('h6','', $subtitle, true, '', $cssClass);
        return $this;
    }

    public function addText($text, $cssClass = 'card-text') {
        $this->text[] = new CLHtmlCtrl('p','', $text, true, '', $cssClass);
        return $this;
    }

    public function addLink($href, $childContent, $cssClass = 'card-link', $onclick = '', $title = '') {
        $this->link[] = new CLHtmlHyperLink($childContent, $href, '', '', '', $cssClass);
        return $this;
    }

    public function addHeader($header, $cssClass = 'card-header') {
        $this->header = new CLHtmlDiv($header, '', $cssClass);
        return $this;
    }

    public function addFooter($footer, $cssClass = 'card-footer text-muted') {
        $this->footer = new CLHtmlDiv($footer, '', $cssClass);
        return $this;
    }

    /**
     * Returns an html representation of the control, including properties and children elements
     * @param null $flag
     * @return string
     * @throws \Exception
     */
    public function toHtml($flag = null){
        $this->prepareCard();
        return parent::toHtml($flag);
    }

    private function prepareCard() {
        if ($this->topImg != null) { $this->addElement($this->topImg); }
        if ($this->header != null) { $this->addElement($this->header); }
        if ($this->title != null) { $this->body->addElement($this->title); }
        if ($this->subtitle != null) { $this->body->addElement($this->subtitle); }
        $this->addCollection($this->text);
        $this->addCollection($this->link);
        $this->addElement($this->body);
        if ($this->footer != null) { $this->addElement($this->footer); }
    }

    private function addCollection(array $collection) {
        if (count($collection) > 0) {
            foreach ($collection as $element) {
                $this->body->addElement($element);
            }
        }
    }
}
