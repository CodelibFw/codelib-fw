<?php
/**
 * AppMagic.php
 */

namespace cl\tool;
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
 * Class AppMagic
 * Tool to auto-generate app from json script
 * @package cl\tool
 */
class AppMagic
{
    private $data = null, $plgUses = '';

    public function __construct($appfilepath)
    {
        $this->data = loadJson($appfilepath, true);
        $this->createApp();
    }

    private function createApp() {
        $path = $this->data['ws']??null;
        if ($path == null) {
            echo 'New App location must be provided in "ws" entry. Not found'.PHP_EOL;
            return;
        }
        if ($path === '.') {
            $path = getcwd();// maybe not needed? mkdir should work with . as current dir
            $this->data['ws'] = $path; // in case current dir is changed, and we getcwd again
        }
        if (!endsWith($path, ['/','\\'])) {
            $path .= DIRECTORY_SEPARATOR;
            $this->data['ws'] = $path;
        }
        if (isset($this->data['appname'])) {
            $path .= $this->data['appname'];
            makedir($path, 0777, true);
            $this->data['ws'] = $path;
        }
        if (!$this->createAppStructure($path)) return;
        $this->createIdx($path, $this->data['copyright']??'index.php');
        $this->createPlugins();
    }

    private function createIdx($path, $cp) {
        $idxtmpl = file_get_contents(CL_RES.'appmgc/app_idx_tmpl.clt');
        error_log('CL_DIR3 is: '.CL_DIR);
        $idxtmpl = str_replace('${basedir}', '__DIR__', $idxtmpl);
        $idxtmpl = str_replace('${cldir}', CL_DIR, $idxtmpl);
        $idxtmpl = str_replace('${CP}', $cp, $idxtmpl);
        if (!file_put_contents($path.'/index.php', $idxtmpl)) {
            echo 'Unable to create App entry point'.PHP_EOL;
            exit(1);
        }
    }

    private function createPlugins() {
        echo '---------- Creating Plugins -----------'.PHP_EOL;
        $plugins = $this->data['plugins'];
        if ($plugins == null || count($plugins) == 0) {
            echo 'No Plugins in specs'.PHP_EOL;
            return;
        }
        $tmpl = file_get_contents(CL_RES.'appmgc/plg_tmpl.clt');
        foreach ($plugins as $plugin) {
            $di = false;
            $pln_tmpl = $tmpl;
            $this->plgUses = '';
            $name = $plugin['name'] ?? null;//$plugin['extends'];$plugin['implements'][0]...array;$plugin['routes'][0]...;$plugin['fns'][0]...;$plugin['dependencies'][0]...
            if ($name == null) {
                echo 'Plugin name must be provided. One plugin entry does not have a "name" field, and the Plugin will not be created'.PHP_EOL;
                continue;
            }
            $pln_tmpl = str_replace('{PLGNAM}', $name, $pln_tmpl);
            $pln_tmpl = str_replace('{PLG_FLD}', strtolower($name), $pln_tmpl);
            $lic = $plugin['lic']??null;
            if ($lic == null) {
                $lic = $plugin['filelic'] ?? null;
                if ($lic != null && file_exists($lic)) {
                    $lic = file_get_contents($lic);
                }
            }
            if ($lic != null) {
                $pln_tmpl = $this->replace('{FILE_LIC}', $this->comment($lic), $pln_tmpl);
            } else {
                $pln_tmpl = str_replace('{FILE_LIC}', '', $pln_tmpl);
            }
            $construct = true;
            if (isset($plugin['extends'])) {
                if ($plugin['extends'] === 'CLBasePlugin') {
                    $construct = false;
                }
                $pln_tmpl = str_replace('{EXTENDS}', 'extends ' . $plugin['extends'], $pln_tmpl);
            } else {
                $pln_tmpl = str_replace('{EXTENDS}', '', $pln_tmpl);
            }
            if (isset($plugin['implements'])) {
                $impl = '';$sep = '';
                foreach ($plugin['implements'] as $interface) {
                    $impl .= $sep.$interface; $sep = ',';
                    if ($interface == 'CLInjectable') {
                        $di = true;
                    }
                }
                if (!$di && isset($plugin['dependencies']) && is_array($plugin['dependencies'])) {
                    $impl .= $sep.'CLInjectable';
                }
                if ($di && !isset($plugin['dependencies'])) {
                    echo 'Plugin '.$name.' implements CLInjectable, and must provide at least one dependency'.PHP_EOL;
                    exit(1);
                }
                $pln_tmpl = str_replace('{IMPLS}', 'implements ' . $impl, $pln_tmpl);
            } else {
                $impl = '';$sep = '';
                if (isset($plugin['dependencies']) && is_array($plugin['dependencies'])) {
                    $impl .= $sep.'CLInjectable';$sep = ',';
                }
                if (!isset($plugin['extends'])) {
                    $impl .= $sep.'\cl\contract\CLPlugin';$sep = ',';
                }
                if ($impl === '') {
                    $pln_tmpl = str_replace('{IMPLS}', '', $pln_tmpl);
                } else {
                    $pln_tmpl = str_replace('{IMPLS}', 'implements ' . $impl, $pln_tmpl);
                }
            }
            if (isset($plugin['dependencies'])) {
                $this->plgUses .= 'use cl\contract\CLInjectable'.PHP_EOL;
                $this->plgUses .= 'use cl\core\CLDependency'.PHP_EOL;
                $impl = '';
                $sep = '';
                foreach ($plugin['dependencies'] as $dependency) {
                    $dep = "'" . $dependency . "'";
                    if ($dependency === 'activerepo') {
                        $dep = 'ACTIVE_REPO';
                    }
                    $impl .= $sep . 'CLDependency::new(' . $dep . ')';
                    $sep = ',';
                }
                $depTmpl = $this->getDependsTempl();
                $depTmpl = str_replace('{DEPX}', $impl, $depTmpl);
                $pln_tmpl = str_replace('{DI}', $depTmpl, $pln_tmpl);
            } else {
                $pln_tmpl = str_replace('{DI}', '', $pln_tmpl);
            }
            if ($construct) {
                $pln_tmpl = str_replace('{CONSTR}', $this->getConstructorTempl(), $pln_tmpl);
                $pln_tmpl = str_replace('{RUN}', $this->getRunTempl(), $pln_tmpl);
            } else {
                $idx1 = mb_strpos($pln_tmpl, '{CONSTR}');
                $idx2 = mb_strpos($pln_tmpl, '{RUN}') + 5;
                $pln_tmpl = $this->delete($pln_tmpl, $idx1, $idx2);
            }
            if (isset($plugin['fns'])) {
                $impl = '';
                $sep = '';
                foreach ($plugin['fns'] as $fn) {
                    $impl .= $sep . $this->createFn($fn);
                    $sep = PHP_EOL.PHP_EOL;
                }
                $pln_tmpl = str_replace('{FNS}', $impl, $pln_tmpl);
            } else {
                $pln_tmpl = str_replace('{FNS}', '', $pln_tmpl);
            }
            $pln_tmpl = str_replace('{USES}', $this->plgUses, $pln_tmpl);
            $path = $this->data['ws'] . '/plugin/' . strtolower($name) .'/';
            makedir($path, 0777, true);
            if (!file_put_contents($path. $name.'.php', $pln_tmpl)) {
                echo 'Unable to save Plugin '.$name.'.php to: '. $path.PHP_EOL;
                echo 'Exiting execution till access is fixed'.PHP_EOL;
                exit(1);
            }
            echo 'Plugin '.$name.' successfully created'.PHP_EOL;
        }
    }

    private function createStores() {
        $stores = $this->data['stores'];
        if ($stores == null || count($stores) == 0) {
            return;
        }
        foreach ($stores as $store) {
            //$store['name'];$store[connection]['server'];$store[connection]['user']...
        }
    }

    private function createFrontEnds() {
        $fes = $this->data['frontends'];
        if (empty($fes)) return;
        foreach ($fes as $fe) {

        }
    }

    private function createComponents() {
        $comps = $this->data['components'];
        if (empty($comps)) return;
        foreach ($comps as $comp) {

        }
    }

    protected function createAppStructure($path): bool
    {
        echo 'creating app structure at '.$path.PHP_EOL;
        if (file_exists($path)) {
            try {
                makedir($path . '/core/', 0777, true);
                makedir($path . '/filter/', 0777, true);
                makedir($path . '/lookandfeel/', 0777, true);
                makedir($path . '/plugin/', 0777, true);
                makedir($path . '/resources/', 0777, true);
                makedir($path . '/logs/', 0777, true);
                makedir($path . '/vendor/', 0777, true);
                makedir($path . '/lookandfeel/css/', 0777, true);
                makedir($path . '/lookandfeel/html/', 0777, true);
                makedir($path . '/lookandfeel/img/', 0777, true);
                makedir($path . '/lookandfeel/js/', 0777, true);
                makedir($path . '/lookandfeel/vendor/', 0777, true);
                makedir($path . '/resources/language/en/', 0777, true);
                return true;
            } catch(\Exception $e) {
                echo $e->getMessage();
            }
        } else {
            error_log('src folder does not exist');
            echo 'Error: source folder does not exist';
        }
        return false;
    }

    protected function replace($search, $replace, $subj): string {
        if ($replace != null) {
            $subj = str_replace($search, $replace, $subj);
        } else {
            $subj = str_replace($search, '', $subj);
        }
        return $subj;
    }

    protected function comment($data): string {
        $cm = '/**'.PHP_EOL;
        $dataarr = explode(PHP_EOL, $data);
        if (!is_array($dataarr)) { return $data;}
        foreach ($dataarr as $line) {
            $cm .= '* '.$line.PHP_EOL;
        }
        $cm .= '*/'.PHP_EOL;
        return $cm;
    }

    protected function getConstructorTempl(): string
    {
        // do not indent
        $tmpl = 'public function __construct(CLServiceRequest $clServiceRequest, CLResponse $pluginResponse)
    {
        $this->clServiceRequest = $clServiceRequest;
        $this->pluginResponse = $pluginResponse;
    }';
        $this->plgUses .= 'use cl\contract\CLResponse;
use cl\contract\CLServiceRequest;'.PHP_EOL;
        return $tmpl;
    }

    protected function getRunTempl(): string
    {
        // do not indent
        $tmpl = 'public function run(): CLResponse
    {
        _log(\'executing Plugin run() method\');
		
		// get any $_POST variable, for instance mypostfield, like this:
		// $myvar = $this->clServiceRequest->getCLRequest()->post(\'mypostfield\');
		
		// add data to your response as key=>value pairs, for instance:
		// $this->pluginResponse->addVars(array(\'feedback\' => $feedback));
		
		// add your function calls here
		
        return $this->pluginResponse;
    }';
        return $tmpl;
    }

    protected function getDependsTempl() {
        // do not indent
        $tmpl = '/**
     * @return array with required dependencies
     */
    public function dependsOn(): array
    {
        return [{DEPX}];
    }';
        return $tmpl;
    }

    protected function createFn($fn) {
        $fn = trim($fn);
        if (!startsWith($fn, ['private', 'protected', 'public'])) {
            $fn = 'public '.$fn;
        }
        $fn = '    '.$fn;
        if (!endsWith($fn, '{')) { $fn .= '{';}
        $fn.= PHP_EOL.'    // write your function code below'.PHP_EOL.PHP_EOL.'    }';
        return $fn;
    }

    protected function delete($str, $from, $to) {
        return mb_substr($str, 0, $from).mb_substr($str, $to);
    }
}

define('CL_DIR', __DIR__.'/../../');
define('CL_RES', __DIR__.'/../../../resources/');
require_once CL_DIR . 'cl/util/Functions.php';
if ($argc != 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
    ?>

    This is a command line tool able to generate your Code-lib App.

    Usage:
    <?php echo $argv[0]; ?> <path to app definition file>

    where the app definition file is a json file in the accepted format.
    The --help, -help, -h, or -? options, will display this information.

    <?php
} else {
    $appmagic = new AppMagic($argv[1]);
}
