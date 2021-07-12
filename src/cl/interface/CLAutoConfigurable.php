<?php
/**
 * CLAutoConfigurable.php
 */

namespace cl\contract;
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
 * Interface CLAutoConfigurable
 * @package cl\contract
 * Contract for Plugin to be auto-configurable
 * Auto-configurable Plugins will be detected and configured if the App includes a AUTO_CONFIG
 * configuration entry, like this: $myAppConfig->addAppConfig(AUTO_CONFIG, true);
 * where $myAppConfig is an instance of CLConfig. See samples and documentation for further details.
 *
 */
interface CLAutoConfigurable
{
    /**
     * Returns an associative array with configuration information for this plugin. The array should specify at least
     * the route to the plugin (the first 2 entries in the example below):
     * example: return ['key'=>'/rest/user/', 'plugin'=>'MyPluginClass:run', 'https'=>false, 'roles'=>'user,admin',
     * 'httpmethods'=>'get,post'];
     * @return array
     */
    public function config(): array;
}
