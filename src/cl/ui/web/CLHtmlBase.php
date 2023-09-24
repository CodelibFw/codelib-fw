<?php
/**
 * CLHtmlBase.php
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

use cl\contract\CLRequest;
use cl\ui\contract\CLUIControl;
use cl\web\CLHttpRequest;

/**
 * Class CLHtmlBase
 * Base class for Html Elements
 * @package cl\ui\web
 */
abstract class CLHtmlBase implements CLUIControl {
    /** @var array events */
	protected $events = array();
    /**
     * @var string $element element name
     * @var string $clkey flow key
     * @var array $children children elements
     * @var $vars variables available to this element
     */
	protected $element, $children = array(),
              $vars; // variables defined for this control, used by the look and feel, plugins, etc. Each variable defined as key => val;
	/** @var array allowed element types for the container/element */
    private $allowedChildren = array();
    /**
     * @var string $type the type of the element
     * @var string $value the value of the element
     * @var string the css id of the element
     * @var string the css class of the element
     */
	protected $type,$value='',$cssId='',$cssClass='';
	/** @var array properties of the element */
	protected $properties = array();
	/** @var CLRequest the request object sent to the instance  */
    protected $clrequest = null;

    /**
     * Creates a new instance of this control
     * @param string|null $name the name of the control, for instance the 'name' field in an <input type="text" name="" value="" />
     * @param string $cssId css id of this control
     * @param string $cssClass css class of this control
     */
	public function __construct(?string $name,$cssId='',$cssClass='') {
		$this->addProperty('name', $name);
		$this->cssId = $this->labelField('id', $cssId);
		$this->cssClass = $this->labelField('class', $cssClass);
	}

    /**
     * Adds an event handler to this control
     * @param string $evName event name
     * @param $evHandler event handler
     * @return CLHtmlBase
     */
	public function addEvent(string $evName,$evHandler): CLHtmlBase
    {
		if($evName!=null && mb_strlen($evName)>0 && $evHandler!=null && mb_strlen($evHandler)>0){
			$this->events[] = $this->labelField($evName, $evHandler);
		}
		return $this;
	}

    /**
     * Adds $element as a child of this control
     * @param mixed $element a control that extends HtmlBase
     * @return $this
     */
	public function addElement($element): CLHtmlBase
    {
		$this->children[] = &$element;
		return $this;
	}

    /**
     * Adds a property to this control
     * @param string $name property name
     * @param string $value property value
     * @return $this
     */
	public function addProperty(string $name, ?string $value): CLHtmlBase
    {
		if (isset($value)) {
			$this->properties[$name] = $value;
		}
        return $this;
	}

	/**
     * Returns the children elements of this control
	 * @return array the child elements of this html control
	 */
	public function getElements() {
		return $this->children;
	}

	/**
     * Returns this control's properties
	 * @return array this element's properties
	 */
	public function getProperties() {
		return $this->properties;
	}

    /**
     * Returns the requested property value
     * @param string $name property name
     * @return mixed the value of the specified property, or null if none found
     */
	public function getProperty(string $name) {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
        return null;
	}

	/**
     * Returns a Json representation of this control, including its properties and children
	 * @return string a json representation of this control
	 */
	public function toJSon() {
		// TODO: express control hierarchy in json
		$json = '{"element":"'.$this->element.'"';
		if(count($this->properties)>0){
			$prop = '{';$sep = '';
			foreach($this->properties as $k=>$v) {
				if ($v != null && mb_strlen($v)>0) {
					if (is_numeric($v) || is_bool($v)) {
						$prop .= $sep . '"' . $k . '":' . $v;
					} else {
						$prop .= $sep . '"' . $k . '":"' . $v . '"';
					}
					$sep = ',';
				}
			}
			if (mb_strlen($sep) > 0) {
				$json .= ',"properties":' . $prop . '}';
			}
		}
		if (count($this->children) > 0) {
			$childjson = '[';$sep = '';
			foreach($this->children as $child) {
				$childjson .= $sep.$child->toJSon();
				$sep = ',';
			}
			$json .= ',"children:'.$childjson.']';
		}
		$json .= '}';
		return $json;
	}

    /**
     * Returns this element or control tag's name
     * @return element's tag name, as in div, img, form, input, etc
     */
    public function getTagName() {
        return $this->element;
    }

    /**
     * Adds a specific control as an allowed child of this control
     * @param string $className
     */
    public function addAllowed(string $className): CLHtmlBase
    {
        $this->allowedChildren[] = $className;
        return $this;
    }

    /**
     * Sets the CLHttpRequest sent to the App invoking this control
     * @param null $clrequest
     * @return CLHtmlBase
     */
    public function setClrequest(&$clrequest): CLHtmlBase
    {
        $this->clrequest = &$clrequest;
        if (count($this->children) > 0) {
            foreach ($this->children as $child) {
                $child->setClrequest($clrequest);
            }
        }
        return $this;
    }

    /**
     * Sets this control's variables
     * @param $vars array of key/value pairs which defines the variables used by this control.
     * example: array('title' => 'my title', 'value' => 'my value'
     * @return CLHtmlBase
     */
    public function setVars(array $vars): CLHtmlBase
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * Adds variables to this control
     * @param $vars array of key/value pairs to add to this control. The values of existing keys will be updated with the
     * new values for those keys in the provided $vars parameter
     * @return CLHtmlBase
     */
    public function addVars(array $vars): CLHtmlBase
    {
        if (!isset($this->vars)) { return $this->setVars($vars);}
        $this->vars = array_merge($this->vars, $vars);
        return $this;
    }

    public function getVar(string $key) {
        if ($this->vars == null) { return null; }
        return $this->vars[$key] ?? null;
    }

    public function submitted() {
        if ($this->clrequest == null) {
            $this->clrequest = new CLHttpRequest($_GET, $_POST, $_SERVER, $_FILES);
        }
        if ($this instanceof CLHtmlForm) {
            return isset($this->clrequest->getRequest()['frm_submission_id']);
        }
        return isset($this->clrequest->getRequest()[$this->getProperty('name')]);
    }

    public function getPostVal($name) {
        if ($this->clrequest == null) {
            $this->clrequest = new CLHttpRequest($_GET, $_POST, $_SERVER, $_FILES);
        }
        if (!$this->clrequest->isPost()) return null;
        return $this->clrequest->getPost()[$name] ?? null;
    }

	protected function childrenToHtml($parms='') {
		$html='';
		foreach($this->children as $child){
			if(!$child instanceof CLHtmlBase){
				error_log('@'.get_class($this).': One html child element is invalid:'.get_class($child));
				throw new \Exception('Invalid html child:'.get_class($child).' found in '.get_class($this));
			}
			if(count($this->allowedChildren)>0){
				$isallowed = false;
				foreach($this->allowedChildren as $allowed){
					if($child instanceof $allowed){
						$isallowed = true;
					}
				}
				if (!$isallowed) {
					throw new \Exception('Illegal child element:'.get_class($child).' not allowed in '.get_class($this));
				}
			}
			if (isset($this->vars) && count($this->vars) > 0) {
                $child->addVars($this->vars);
            }
			$html.=$child->toHtml($parms);
		}
		return $html;
	}

	protected function propertiesToHtml() {
		$html='';
		if ($this instanceof CLHtmlHead) { return $html; }
		if(count($this->properties)>0){
            foreach($this->properties as $k=>$v) {
                if (mb_strpos($k, '_') === 0) continue;
                if ($k == 'name' && mb_strpos($v, '_') === 0) continue;
                $html .= $this->labelField($k,$v);
            }
		}
		return $html;
	}

	protected function eventsToHtml() {
		$html='';
		if(count($this->events)>0){
			foreach($this->events as $ev){
				$html.=$ev.' ';
			}
		}
		return $html;
	}

	protected function labelField($label,$value) {
		if($value!=null && mb_strlen($value)>0){
			$value=' '.$label.'="'.$value.'" ';
		}else{
			$value='';
		}
		return $value;
	}
}
