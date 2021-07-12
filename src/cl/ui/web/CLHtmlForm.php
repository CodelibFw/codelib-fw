<?php
/**
 * CLHtmlForm.php
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
 * Class CLHtmlForm
 * Creates an html <form> element
 * @package cl\ui\web
 */
class CLHtmlForm extends CLHtmlCtrl
{
    /**
     * CLHtmlForm constructor.
     * @param $name
     * @param $method as in Get or Post
     * @param string $action destination when form is submitted
     * @param string $cssId css id of this form
     * @param string $cssClass css class of the form
     */
    public function __construct($name, $method, $action = '', $cssId = '', $cssClass = 'clhtmlform')
    {
        parent::__construct('form',$name, null, true,$cssId, $cssClass);
        $this->addProperty('method', $method);
        $this->addProperty('action', $action);
        $this->addElement(new CLHtmlInput('hidden', 'frm_submission_id', 'formid'));
    }

    /**
     * Make form able to handle files (file uploads)
     * @param string $maxFileSize
     * @return $this
     */
    public function makeMultiPart(string $maxFileSize = '0') {
        $this->addProperty('enctype', 'multipart/form-data');
        //if ($maxFileSize === '0') {
        //    $maxFileSize = ini_get('upload_max_filesize');
        //}
        //if ($maxFileSize != null) {
            // adding this, seems to return empty uploads, with other the name of the uploaded file, without content
            //$this->addElement(new CLHtmlInput('hidden', 'MAX_FILE_SIZE', $maxFileSize));
        //}
        return $this;
    }

    public function toHtml($flag = null) {
        if ($this->vars != null) {
            if (isset($this->vars['csrf'])) {
                $this->addElement(new CLHtmlInput('hidden',CSRF_KEY, $this->vars['csrf']));
            }
        }
        return parent::toHtml($flag);
    }
}
