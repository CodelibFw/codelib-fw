<?php


class NewAppWzd
{
    private $instructions, $uselg = false;

    public function __construct($instructions)
    {
        $this->instructions = $instructions;
    }

    public function createAppStructure() {
        $path = $this->instructions['src'];
        if (file_exists($path)) {
            try {
                $this->mkdir($path . '/core/', 0777, true);
                $this->mkdir($path . '/filter/', 0777, true);
                $this->mkdir($path . '/lookandfeel/', 0777, true);
                $this->mkdir($path . '/plugin/', 0777, true);
                $this->mkdir($path . '/resources/', 0777, true);
                $this->mkdir($path . '/logs/', 0777, true);
                $this->mkdir($path . '/vendor/', 0777, true);
                $this->mkdir($path . '/lookandfeel/css/', 0777, true);
                $this->mkdir($path . '/lookandfeel/html/', 0777, true);
                $this->mkdir($path . '/lookandfeel/img/', 0777, true);
                $this->mkdir($path . '/lookandfeel/js/', 0777, true);
                $this->mkdir($path . '/lookandfeel/vendor/', 0777, true);
                $this->mkdir($path . '/resources/language/en/', 0777, true);
                return 'ok';
            } catch(Exception $e) {
                return $e->getMessage();
            }
        } else {
            error_log('src folder does not exist');
            return 'Error: source folder does not exist';
        }
    }

    public function createApp() {
        $cfgtmpl = file_get_contents('tmpl/app_cfg_tmpl.php');
        $cfgtmpl = $this->replaceInstruction('{COPYRIGHT_NOTICE}', 'cpright', $cfgtmpl);
        if (isset($this->instructions['lg']) && $this->instructions['lg'] == 'true') {
            $cfgtmpl = str_replace('//lgcall', '', $cfgtmpl);
            $cfgtmpl = str_replace('endlgcall', '', $cfgtmpl);
            $cfgtmpl = str_replace('// loginComp', '/** adding a login component */', $cfgtmpl);
            $cfgtmpl = str_replace('// end of LoginComp', '', $cfgtmpl);
            $this->uselg = true;
        } else {
            $cfgtmpl = str_replace('//lgcall$this->loginComp($app);endlgcall', '', $cfgtmpl);
            $idx1 = mb_strpos($cfgtmpl, '// loginComp');
            $idx2 = mb_strpos($cfgtmpl, '// end of LoginComp');
            $cfgtmpl = $this->delete($cfgtmpl, $idx1, $idx2);
        }
        $path = $this->instructions['src'];
        $path = str_replace('\\','/', $path);
        $path = rtrim($path,'/\\');
        if (!file_put_contents($path.'/core/AppConfig.php', $cfgtmpl)) { return 'Unable to create App configuration file'; }
        $idxtmpl = file_get_contents('tmpl/app_idx_tmpl.php');
        error_log('CL_DIR3 is: '.CL_DIR);
        $idxtmpl = str_replace('${basedir}', '__DIR__', $idxtmpl);
        $idxtmpl = str_replace('${cldir}', CL_DIR, $idxtmpl);
        if (!file_put_contents($path.'/index.php', $idxtmpl)) { return 'Unable to create App entry point'; }
        return $this->copyDependencies($path);
    }

    protected function copyDependencies($path) {
        try {
            if ($this->uselg) {
                $this->copyTmpl('tmpl/register.php', $path . '/lookandfeel/html/register.php');
                $this->copyTmpl('tmpl/successpage.php', $path . '/lookandfeel/html/successpage.php');
                $this->copyTmpl('tmpl/dashboard.php', $path . '/lookandfeel/html/dashboard.php');
                $this->copyTmpl('tmpl/header.php', $path . '/lookandfeel/html/header.php');
                $this->copyTmpl('tmpl/footer.php', $path . '/lookandfeel/html/footer.php');
            }
            $this->copyTmpl('tmpl/about.php', $path . '/lookandfeel/html/about.php');
            $this->copyDirDependencies('tmpl/css', $path . '/lookandfeel/css');
            $this->copyDirDependencies('tmpl/img', $path . '/lookandfeel/img');
            $this->copyDirDependencies('tmpl/js', $path . '/lookandfeel/js');
            $this->copyDirDependencies('tmpl/vendor', $path . '/lookandfeel/vendor');
            return 'ok';
        } catch(Exception $e) {
            return 'Error: '.$e->getMessage();
        }
        return 'ok';
    }

    protected function copyDirDependencies($srcPath, $dstPath) {
        if (is_dir($srcPath)) {
            if (!file_exists($dstPath)) { mkdir($dstPath); }
            $files = scandir($srcPath);
            foreach ($files as $file) {
                if ($file == "." || $file == "..") continue;
                $this->copyDirDependencies("$srcPath/$file", "$dstPath/$file");
            }
        }
        else $this->copyTmpl($srcPath, $dstPath);
    }

    protected function copyTmpl($src, $dest, $search = null, $replace = null) {
        $cnt = file_get_contents($src);
        if ($search != null && $replace != null) {
            $n = count($search);
            for ($i = 0; $i < $n; $i++) {
                $cnt = str_replace($search[$i], $replace[$i], $cnt);
            }
        }
        if (file_put_contents($dest, $cnt) == false) {
            throw new Exception('Unable to create '.$dest);
        }
    }

    protected function replaceInstruction($search, $replaceIdx, $subj) {
        if (isset($this->instructions[$replaceIdx])) {
            $subj = str_replace($search, $this->instructions[$replaceIdx], $subj);
        } else {
            $subj = str_replace($search, '', $subj);
        }
        return $subj;
    }

    protected function delete($str, $from, $to) {
        return mb_substr($str, 0, $from).mb_substr($str, $to);
    }

    protected function mkdir($path, $mode, $recursive) {
        if (!mkdir($path, $mode, $recursive)) {
            throw new Exception('Unable to create folder: '.$path);
        }
    }
}
