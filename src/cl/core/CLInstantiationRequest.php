<?php
/**
 * CLInstantiationRequest.php
 */

namespace cl\core;
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
 * Class CLInstantiationRequest
 * @package cl\core
 */
class CLInstantiationRequest
{
    private $key, $classname, $exClass, $params, $instType;

    /**
     * CLInstantiationRequest constructor.
     * @param $key
     * @param $classname
     * @param $exClass
     * @param $params
     * @param $instType
     */
    public function __construct($key, $classname = null, $exClass = null, $params = null, $instType = null)
    {
        $this->key = $key;
        $this->classname = $classname;
        $this->exClass = $exClass;
        $this->params = $params;
        $this->instType = $instType;
    }

    /**
     * Returns this request as an indexed array
     * @return array
     */
    public function asArray() {
        return [$this->key, $this->classname, $this->exClass, $this->params, $this->instType];
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return CLInstantiationRequest
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * @param mixed|null $classname
     * @return CLInstantiationRequest
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getExClass()
    {
        return $this->exClass;
    }

    /**
     * @param mixed|null $exClass
     * @return CLInstantiationRequest
     */
    public function setExClass($exClass)
    {
        $this->exClass = $exClass;
        return $this;
    }

}
