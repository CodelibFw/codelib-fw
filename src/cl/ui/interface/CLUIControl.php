<?php
/**
 * CLUIControl.php
 */
namespace cl\ui\contract;
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
 * Interface CLUIControl
 * Specs for a code-lib user interface control
 * @package cl\ui\contract
 */
interface CLUIControl
{
    /**
     * Adds an event handler to the control
     * @param string $evName event name
     * @param mixed $evHandler event handler
     */
    public function addEvent(string $evName, $evHandler);

    /**
     * Adds $element as a child of the control
     * @param mixed $element in most cases, a valid CLUIControl
     * @return $this
     */
    public function addElement($element);

    /**
     * Adds a property to the control
     * @param string $name property name
     * @param string|null $value property value
     * @return $this
     */
    public function addProperty(string $name, ?string $value);

    /**
     * Returns the children elements of the control
     * @return array the child elements of the html control
     */
    public function getElements();

    /**
     * Returns the properties of the control
     * @return array the element's properties
     */
    public function getProperties();

    /**
     * Returns a specific property value
     * @param $name property name
     * @return mixed the value of the specified property, or null if none found
     */
    public function getProperty(string $name);

    /**
     * Returns a Json representation of the control
     * @return string a json representation of the control
     */
    public function toJSon();

    public function addAllowed(string $className);

    /**
     * Sets the control's value to the specified value
     * @param $value : the new value for the control. It replaces old value, if any
     */
    public function setValue($value);

    /**
     * Appends a value to the control's current value
     * @param $value : the value to add to the current value
     */
    public function addValue($value);

    /**
     * Adds a control as a child of the control, or just html to the value of the control
     * @param $value accepts an element (ie, a CL control) or just html data (string)
     */
    public function add($value);

    /**
     * Returns an html representation of the control, including properties and children elements
     * @param null $flag used to pass a boolean or int value to child elements (see HtmlTr and HtmlTd for an example of its application). See also addFlag method
     * @return string html representation of the element
     * @throws Exception
     */
    public function toHtml($flag = null);

    /**
     * Returns a Javascript representation of this control, when applicable
     * @return mixed
     */
    public function toJs();

    /**
     * Adds a flag to the control, which can be passed to children elements
     * @param $flag a simple (boolean or int) flag to pass down to children elements
     * for instance, in a certain table layout, a <tr> could pass information to its <td> elements this way, to indicate it is a header row
     */
    public function addFlag($flag);
}
