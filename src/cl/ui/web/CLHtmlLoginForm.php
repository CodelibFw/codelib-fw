<?php
/**
 * CLHtmlLoginForm.php
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
 * Class CLHtmlLoginForm
 * Creates a simple html <form> with username, password and other related fields
 * @package cl\ui\web
 */
class CLHtmlLoginForm extends CLHtmlForm
{
    /**
     * @param string $name
     * @param string $method post or get
     * @param string $action where must the form data be sent to
     * @param string $recoveryUrl password recovery url
     * @param string $registerUrl new user registration url
     * @param string $cssId
     * @param string $cssClass
     */
    public function __construct($name, $method, $action = '', $recoveryUrl = 'index.php/recpass', $registerUrl = 'index.php/register', $cssId = '', $cssClass = 'clhtmlloginform')
    {
        parent::__construct($name, $method, $action,$cssId, $cssClass);
        $panel = new CLHtmlPanel('Enter Login Details');
        $this->addElement($panel);
        // add username to the form
        $panel->addElement((new CLHtmlDiv('','','form-group'))
                ->addElement(new CLHtmlLabel('','username','Username','','col-sm-2 control-label'))
                ->addElement((new CLHtmlDiv('','','col-sm-10 vertspace'))
                                ->addElement((new CLHtmlInput('text','username','','','form-control'))->addProperty('placeholder','Username'))));
        // add password to the form
        $panel->addElement((new CLHtmlDiv('','','form-group'))
            ->addElement(new CLHtmlLabel('','password','Password','','col-sm-2 control-label'))
            ->addElement((new CLHtmlDiv('','','col-sm-10 vertspace'))
                ->addElement((new CLHtmlInput('password','password','','','form-control'))->addProperty('placeholder','Password'))));
        // add password recovery
        $panel->addElement((new CLHtmlDiv('','','row form-group'))
            ->addElement((new CLHtmlDiv('','','col-sm-10 col-sm-offset-2'))->addElement(new CLHtmlHyperLink('Forgot password',$recoveryUrl))));
        // add buttons
        $panel->addElement((new CLHtmlDiv('','','row form-group'))
            ->addElement((new CLHtmlDiv('','','col-sm-10 col-sm-offset-2'))
                ->addElement(new CLHtmlInput('submit','submit','submit','','btn btn-primary'))
                ->addElement(new CLHtmlNbsp(2))
                ->addElement(new CLHtmlHyperLink('Register',$registerUrl))));
    }
}
