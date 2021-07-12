<?php
/**
 * CLRestPlugin.php
 */

namespace cl\plugin;
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
 * Class CLRestPlugin
 * A simple REST server for CL Apps
 * In order to use, make your Plugin extend this Plugin, and implement in your Plugin the 4 abstract methods provided
 * by this class: postData, updateData, getData, and deleteData. Make sure to configure your plugin for your desired flow
 * or route, for instance, perhaps to handle rest.* (ie, any url including rest/).
 * When your App runs, one of your methods will be called for any valid route, depending on the http method of the request.
 *
 * @package cl\plugin
 */
abstract class CLRestPlugin extends CLBasePlugin
{
    /**
     * We map here http methods to our Plugin methods
     * @return string[] // specifically, for a GET request, we will call the getData() function, for a POST, the addData fn, etc
     */
    protected function mapHttpMethod() : array {
        return ['get' => 'getData', 'post' => 'postData', 'put' => 'updateData', 'delete' => 'deleteData'];
    }

    /**
     * Called when the http method is post
     * @return CLBaseResponse
     */
    abstract public function postData(): \cl\contract\CLResponse;

    /**
     * Called when the http method is put
     * @return CLBaseResponse
     */
    abstract public function updateData(): \cl\contract\CLResponse;

    /**
     * Called when the http method is get
     * @return CLBaseResponse
     */
    abstract public function getData(): \cl\contract\CLResponse;

    /**
     * Called when the http method is delete
     * @return CLBaseResponse
     */
    abstract public function deleteData(): \cl\contract\CLResponse;
}
