<?php
/**
 * CLAutoConfigurator.php
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

use cl\contract\CLAutoConfigurable;

/**
 * Class CLAutoConfigurator
 * @package cl\core
 * Used internally to configure extensions when auto-configuration is turned on
 * Not integrated yet, this is an upcoming feature
 */
class CLAutoConfigurator
{
    public function configurePlugins($appNs, $pluginFolder) {
        $plugins = $this->getPlugins($pluginFolder);
        if ($plugins != null && count($plugins) > 0) {
            foreach ($plugins as $plugin) {
                if (file_exists($plugin)) {
                    $idx = strpos($plugin, '.php');
                    if ($idx === false) { continue; }
                    $pluginClass = substr($plugin, 0, $idx);
                    $idx = strrpos($pluginClass, DIRECTORY_SEPARATOR);
                    if ($idx === false) { continue; }
                    $pluginClass = substr($pluginClass, $idx + 1);
                    $pluginClass = '\\'.$appNs.'\\'.$pluginFolder.'\\' . $pluginClass;
                    $plugin = CLInstantiator::classInstance($pluginClass, 'cl\contract\CLPlugin', false, null, null);
                    if ($plugin instanceof CLAutoConfigurable) {
                        $config = $plugin->config();
                    }
                }
            }
        }
    }

    private function getPlugins($pluginFolder) {
        $directory = dir($pluginFolder);
        $plugins = [];
        while (false !== ($entry = $directory->read())) {
            if($entry[0] != '.') {
                if(is_dir($pluginFolder.'/'.$entry)) {
                    $plugins = array_merge($plugins, $this->getPlugins($pluginFolder.'/'.$entry));
                }
                else {
                    $plugins[] = $pluginFolder.'/'.$entry;
                }
            }
        }
        $directory->close();
        return $plugins;
    }
}
$autoConfig = new CLAutoConfigurator('D:/Dev/Apache24/htdocs/mygithub/code-lib/samples/hellopluginfolder/plugin');
