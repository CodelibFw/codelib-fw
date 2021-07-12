<?php
/**
 * CLInjectable.php
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
 * Interface CLInjectable
 * @package cl\contract
 * Contract for IOC Plugin to receive injection of dependencies
 */
interface CLInjectable {
    /**
     * @return array required dependencies
     * each entry provides information about a dependency, in the form: CLDependency::new('key'), where key is the key
     * assigned to that dependency. A key is assigned to a dependency, either by Code-lib (CL), or, if not a dependency
     * pre-configured by CL, by a Plugin in your App the first time it is needed.
     * When a dependency is configured for the first time by your App, it must tell the framework where to find it, and
     * how you want it instantiated. Use additional parameters of CLDependency::new to achieve that.
     * For instance, the $classname parameter allows you to specify the full class name (namespace and class name) of
     * your dependency. $exClass allows CL to reinforce what parent class your $classname must extend or implement.
     * Use $params if you need to pass any parameters to the dependency, and $instType to indicate whether this dependency
     * can be shared or not, by using values CLFlag::SHARED or CLFlag::NOT_SHARED.
     * is an array as well, which specifies the dependency key, optional class, optional params, and optional instantiation
     * type. Ex.:
     * return [CLDependency::new('cache', null, null, null, CLFlag::SHARED)],  // <-- requires a cache instance. CL knows about
     * this key, so no class is required. Another example:
     * return [CLDependency::new('mysmartclass', '\app\core\Smartest.php', null, null, CLFlag::NOT_SHARED)]; // <-- requires this
     * App class, Smartest, which CL might not know about, so we tell it where to find it.
     * If CL finds the required dependency, it will inject it in your Plugin, using a setter method based on the dependency key.
     * So, in the example above, it would call: setCache(cacheInstance); and setMysmartclass(smartInstance);
     * as it would expect those setter methods to be available in your Plugin
     */
    public function dependsOn() : array;
}
