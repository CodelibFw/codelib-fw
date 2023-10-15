<?php
/**
 * CLHtmlCtrl.php
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

use cl\ui\contract\CLUIControl;
use cl\util\Util;
use cl\web\CLConfig;
use cl\web\CLHtmlApp;
use cl\web\CLWebResponseCode;

/**
 * Class CLHtmlCtrl
 * Represents a simple html element from which more complex html controls
 * can quickly be created. For example, look at any of the controls such as HtmlPage, HtmlHr.php, HtmlInput, etc
 * @package cl\ui\web
 */
class CLHtmlCtrl extends CLHtmlBase
{
    /**
     * @var bool|true $hasClosingTag whether the element uses a closing tag, as in, for instance <div>...</div>
     * @var mixed $flag use to pass flags/settings between parent/children controls, mostly via the toHtml method
     * @var string $lookandFeel stores the assigned lookandfeel, if any, of the control
     */
    private $hasClosingTag, $flag, $lookandFeel;
    private $inlineCode = false;

    /**
     * Creates a new instance of this control
     * @param string $element the element tag, such as div, input, etc (see other controls that extend HtmlCtrl for examples
     * @param string $name name of the control, as in the 'name' field in an element, ex. <input type="text" name="" value="" />
     * @param string $value value of the control, as in the 'value' field in an element, ex. <input type="text" name="" value="" />
     * @param bool|true $hasClosingTag indicates whether the element has a closing tag (like in <h3>...</h3> or not
     * @param string $cssId the css id of this element
     * @param string $cssClass the css class of this element
     */
    public function __construct($element,$name='',$value='',$hasClosingTag = true,$cssId='',$cssClass='') {
        parent::__construct($name,$cssId,$cssClass);
        $this->element = $element;
        $this->hasClosingTag = $hasClosingTag;
        $this->setValue($value);
    }

    /**
     * Sets the control's value
     * @param $value : set this control's value to this value. It replaces old value, if any
     * @return CLHtmlCtrl
     */
    public function setValue($value): CLHtmlCtrl
    {
        if ($this->hasClosingTag) {
            if (!isset($value)) $value = '';
            $this->value = $value;
        } else {
            $this->addProperty('value', $value);
        }
        return $this;
    }

    /**
     * Appends a value to the control's value
     * @param $value : append this value to current one
     * @return CLHtmlCtrl
     */
    public function addValue($value): CLHtmlCtrl
    {
        if ($this->hasClosingTag) {
            if (!isset($value)) $value = '';
            $this->value .= $value;
        } else {
            $currentValue = $this->getProperty('value');
            if (!isset($currentValue)) $currentValue = '';
            $this->addProperty('value', $currentValue.$value);
        }
        return $this;
    }

    /**
     * Adds a control as a child of the control, or just html to the value of the control
     * @param mixed $value : an element (ie, a CL control) or just plain html data (string) (not php code)
     * @return CLHtmlCtrl
     */
    public function add($value): CLHtmlCtrl
    {
        if ($value != null) {
            if ($value instanceof CLHtmlCtrl) {
                $value->setClrequest($this->clrequest);
                $this->addElement($value);
            } else {
                if (mb_strpos($value, '.php') !== false) {
                    $this->addElement((new CLHtmlCtrl(''))->setLookandFeel($value));
                } elseif (mb_strpos($value, '<?') !== false) {
                    $ctrl = new CLHtmlCtrl($value);
                    $ctrl->inlineCode = true;
                    $this->addElement($ctrl);
                } else {
                    $this->addElement(new CLHtmlSpan($value));
                }
            }
        }
        return $this;
    }

    /**
     * Returns an html representation of the control, including properties and children elements
     * @param null $flag used to pass a boolean or int value to child elements (see HtmlTr and HtmlTd for an example of its application). See also addFlag method
     * @return string html representation of this element
     * @throws \Exception
     */
    public function toHtml($flag = null) {
        if (!isset($this->vars)) { $this->vars = array(); }
        if (!isset($this->vars['status'])) { $this->vars['status'] = 'success'; }
        if ($this->clrequest != null && ($this->clrequest->isJson() || $this->clrequest->acceptJson()) || (isset($this->vars['produces']) && 'json' === $this->vars['produces'])) {
            return $this->toJSon();
        }
        if (mb_strlen($this->element) > 0 && $this->inlineCode) {
            return $this->renderInlineComponent($flag);
        }
        if ($this->hasClosingTag) {
            if (isset($flag) && isset($this->flag)) {
                $flag = $flag | $this->flag;
            } else if (isset($this->flag)) {
                $flag = $this->flag;
            }
            if (isset($this->lookandFeel)) {
                return $this->paintComponent();
            } else {
                if ($this->element == null) { return ''; }
                $this->rewriteComponentVars();
                $html = '<' . $this->element . ' ' . $this->cssId . $this->cssClass . $this->propertiesToHtml() . $this->eventsToHtml() . '>';
                $html .= $this->value . $this->childrenToHtml($flag) . '</' . $this->element . '>';
            }
        } else {
            if (isset($this->lookandFeel)) {
                return $this->paintComponent();
            } else {
                if ($this->element == null) { return ''; }
                $this->rewriteComponentVars();
                $html = '<' . $this->element . ' ' . $this->value . $this->cssId . $this->propertiesToHtml() . $this->cssClass . $this->eventsToHtml() . ' />';
            }
        }

        return $html;
    }

    /**
     * For future use. Creates javascript code for this component
     * @return mixed|void
     */
    public function toJs() {
    // TODO: express control hierarchy in javascript
    }

    /**
     * @param $flag a simple (boolean or int) flag to pass down to children elements
     * for instance, in a certain table layout, a <tr> could pass information to its <td> elements this way, to indicate it is a header row
     */
    public function addFlag($flag) {
        $this->flag = $flag;
    }

    /**
     * Generates json output, and set status and content type headers
     * @return false|string
     * @throws \Exception
     */
    public function toJSon()
    {
        if (isset($this->vars) && count($this->vars) > 0) {
            $json = json_encode($this->vars);
        }
        $statusCode = $this->getStatusCode();
        $responseCode = CLWebResponseCode::getResponseCode($statusCode);
        if ($responseCode == null) {
            throw new \Exception('Invalid Response Code. Make sure your ResponseCode is one of the codes specified in CLWebResponseCode');
        }
        header($responseCode, true, $statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        _log('data is: '.$json, \cl\contract\CLLogger::DEBUG);
        return $json;
    }

    private function getStatusCode() {
        return $this->vars[STATUS_CODE] ?? (isset($this->vars['status']) && $this->vars['status'] == 'success') ? 200 : 403;
    }

    private function renderInlineComponent($flag) {
        $evalInlineCode = CLHtmlApp::$clapp->getAppConfig()->getAppConfig(EVAL_INLINE_CODE);
        $html = $this->element;
        if ($evalInlineCode != null && $evalInlineCode) {
            if ($this->vars != null) {
                extract($this->vars);
            }
            $html = eval($html);
            if ($html == null) {
                $html = ob_get_clean();
                ob_start();
            }
        } else {
            if ($this->vars != null) {
                foreach ($this->vars as $k => $v) {
                    $html = str_replace('$'.$k, $v, $html);
                }
            }
            $html = str_replace(['<?php','<?','?>','echo(','echo '],'',$html);
        }
        $html .= $this->childrenToHtml($flag);
        return $html;
    }

    /**
     * used internally to overwrite variables for programmatic components without an external look and feel
     */
    private function rewriteComponentVars() {
        if ($this instanceof CLHtmlPage) return;
        if ($this->vars != null) {
            foreach ($this->vars as $k => $v) {
                if ($this instanceof CLHtmlHead && $k == 'value') {
                    $this->add(new CLHtmlCtrl('title', '', $v));
                } elseif (!$this->targetedVar($k, $v)) {
                    $this->$k = $v;
                }
            }
        }
    }

    private function targetedVar($k, $v) {
        if (mb_strpos($k, '.') === false) { return false; }
        $tokens = explode('.', $k);
        if ($tokens == null || !is_array($tokens) || count($tokens) != 2) {
            _log('Incorrectly named targeted variable: '.$k.' (value ignored)');
            return true;
        }
        $name = $this->getProperty('name');
        if (($name == null || $name !== $tokens[0]) && $this->element !== $tokens[0]) { return true; }
        if ($this instanceof CLHtmlHead && $tokens[1] == 'value') {
            $this->add(new CLHtmlCtrl('title', '', $v));
        } else {
            $this->{$tokens[1]} = $v;
        }
        return true;
    }

    /**
     * Use this function to install a look and feel for this component. If set, it will be used while rendering the component.
     * This refers to a resource relative to the lookandfeel/html/ folder of your App. The resource can be:
     * - a php file appropriate to render the given components, which can include variables declared by your Plugins, and
     * added to the response via addVars, setVar or setVars.
     * - an array specifying a list of filenames existing within the html folder or subfolders, which will be loaded as
     * part of this look and feel.
     * - it can be the name of a folder within the above specified path, in which case, the folder must contain a config.php
     * file, containing a $config array specifying a list of filenames existing within the html folder or subfolders, which will be loaded as
     * part of this look and feel.
     * @param mixed $laf look and feel file name (for instance neworder.php), or array, or folder name, as described above
     * @param string $ext defaults to .php, used to append an extension to the lookandfeel filename, if $ext is not null, and
     *                    $laf refers to a filename (instead of an array or directory).
     * @return CLHtmlCtrl
     */
    public function setLookandFeel($laf, string $ext = 'php') {
        if (!is_array($laf) && !is_dir(BASE_DIR . '/lookandfeel/html/' . $laf) && $ext != null) {
            $laf = Util::addExt($laf, $ext);
        }
        $this->lookandFeel = $laf;
        return $this;
    }

    /**
     * Returns the control's registered look and feel
     * @return mixed
     */
    public function getLookandFeel() {
        return $this->lookandFeel;
    }

    private function paintComponent() {

        $response = '';
        if ($this->lookandFeel != null) {
            $this->loadLAF($this->lookandFeel);
            $response = ob_get_clean();
            ob_start();
        }
        return $response.$this->childrenToHtml();
    }

    private function loadLAF($cllafentry, $prefix = null) {
        if (is_array($cllafentry)) {
            foreach ($cllafentry as $lafItem) {
                $this->loadLAF($lafItem, $prefix);
            }
            return;
        }
        if ($this->vars != null) {
            extract($this->vars);
        }
        if ($prefix != null) {
            $cllafentry = $prefix.$cllafentry;
        }
        $path = BASE_DIR . '/lookandfeel/html/' . $cllafentry;
        if (file_exists($path)) {
            if (is_dir($path)) {
                $this->loadLAFConfig($path, endsWith($cllafentry, '/') ? $cllafentry : $cllafentry.'/');
            } else {
                include_once($path);
            }
        } elseif (file_exists(CL_DIR . '../resources/lookandfeel/html/' . $cllafentry)) {
            include_once(CL_DIR . '../resources/lookandfeel/html/' . $cllafentry);
        } else throw new \Exception("Missing page: ".$cllafentry);
    }

    private function loadLAFConfig($path, $prefix) {
        $lafCfgPath = endsWith($path, ['/','\\'])?$path : $path.'/';
        $lafCfgPath .= 'config.php';
        if (file_exists($lafCfgPath)) {
            $lafCfg = include $lafCfgPath;
            if ($lafCfg != null) {
                if (is_array($lafCfg)) {
                    $this->loadLAF($lafCfg, $prefix);
                } else {
                    throw new \Exception('Missing array LAF configuration at '.$lafCfgPath);
                }
            } else {
                throw new \Exception('Unable to load LAF configuration at '.$lafCfgPath.' laf config must be like: return [file1.php, file2.php];');
            }
        } else {
            throw new \Exception('Missing configuration for LAF '.$lafCfgPath);
        }
    }
}
