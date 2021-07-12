<?php
/**
 * SchemaMigrationTool.php
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

use cl\util\Util;
use cl\web\CLHtmlApp;
use PDO;

/**
 * Class SchemaMigrationTool
 * Tool to help with Schema migration as an App evolves
 * @package cl\tool
 */
class SchemaMigrationTool
{
    private $schemaPath = BASE_DIR . '/schema';
    private $connectionDetails;
    private $conn, $dbname;

    /**
     * @param string|null $migrateTo (optional) specific version to migrate to
     */
    public function migrate(string $migrateTo = null) {
        Util::ensurePathExists($this->schemaPath);
        $reverse = ($migrateTo == 'reverse');
        error_log('reverse: '.$reverse.' migrateTo: '.$migrateTo);
        if ($reverse) {
            return $this->reverse();
        }
        if ($migrateTo == null) {
            $lastMigration = $this->getLastMigration();
            if ($lastMigration == null) {
                return $this->runMigration($this->findFirstMigration());
            }
            return $this->runMigration($this->findNextMigration($lastMigration), $reverse, $lastMigration['version']);
        } else {
            $previousVersion = null;
            $lastMigration = $this->getLastMigration();
            if ($lastMigration != null) {
                if (!isset($lastMigration['version'])) {
                    throw new \Exception("Previous migration data is missing version information");
                }
                $previousVersion = $lastMigration['version'];
            }
            return $this->runMigration($this->findMigrationByNameOrVersion($migrateTo), false, $previousVersion);
        }
    }

    /**
     * Get last ran migration
     * @return array|null
     */
    private function getLastMigration(): ?array {
        if (file_exists($this->schemaPath.'/lrm.scl')) {
            $lm = unserialize(file_get_contents($this->schemaPath.'/lrm.scl'));
            if ($lm == null || !is_array(($lm))) { return null; }
            return $lm;
        }
        return null;
    }

    /**
     * Get first migration to run
     * @return array|null
     */
    private function findFirstMigration(): ?array {
        $migration = $this->findMigrationByNameOrVersion('1');
        if ($migration != null && !isset($migration['version'])) {
            $migration['version'] = '1';
        }
        return $migration;
    }

    private function runMigration($migration, $reverse = false, $lastVersion = null): bool {
        if ($migration == null) {
            throw new \Exception('Cannot execute an undefined Migration');
        }
        $dbname = $migration['dbname']??null;
        if ($dbname == null) {
            throw new \Exception("Db name missing in migration configuration for migration version: ".($migration['version']??'missing version'));
        }
        $files = $migration['files'];
        if ($reverse) {
            $files = $migration['reverse']??['reverse.sql'];
            $migration['createdb'] = 'false';
        }
        $createdb = $migration['createdb']??'false';

        $connectionDetails = $migration['connDetails']??null;
        if ($connectionDetails == null) {
            $config = CLHtmlApp::$clapp->getAppConfig();
            if ($config == null) {
                throw new \Exception("Database connection details missing from migration, and App config is missing too");
            }
            $connectionDetails = $config->getRepoConnDetails($config->getActiveClRepository());
            if ($connectionDetails == null) {
                throw new \Exception("Database connection details missing from migration and App configuration for repo: ".$config->getActiveClRepository());
            }
        }
        if (!isset($connectionDetails['dbname']) && $createdb === 'false') {
            $connectionDetails['dbname'] = $dbname;
        }
        $resultsf = false;
        $this->connect($connectionDetails);
        if ($createdb == 'true') {
            $resultsf = $this->createDb($dbname);
        }
        if (!empty($files)) {
            $path = $this->schemaPath.'/'. ($migration['path']??'');
            $path = endsWith($path, '/') ? $path : $path.'/';
            foreach ($files as $filename) {
                $filepath = $path.$filename;
                if (file_exists($filepath)) {
                    $data = file_get_contents($filepath);
                    if ($data != null) {
                        $resultsf = $this->execStatement($data);
                    }
                }
            }
        }
        $migration['pv'] = $lastVersion;
        if ($reverse) {
            $migration = $this->findMigrationByNameOrVersion($lastVersion);
        }
        $this->saveRanMigration($migration);
        return $resultsf;
    }

    private function reverse(): bool {
        $migration = $this->getLastMigration();
        if ($migration == null) {
            return false;
        }
        return $this->runMigration($migration, true, $migration['pv']??null);
    }

    private function findNextMigration($lastMigration): ?array {
        $lastVersion = $lastMigration['version']??null;
        if ($lastVersion == null || !is_numeric($lastVersion)) { return null; }
        $vn = intval($lastVersion);
        if ($vn === 0) { return null; }
        $vn+=1;
        $filename = "migration.$vn.cl";
        $migration = $this->findMigrationByNameOrVersion($filename);
        if ($migration != null && !isset($migration['version'])) {
            $migration['version'] = ''.$vn;
        }
        return $migration;
    }

    private function findMigrationByNameOrVersion(string $value): ?array {
        $filename = $value;
        if (mb_strpos($value, 'migration.') === false) {
            $filename = "migration.$value.cl";
        }
        $path = $this->schemaPath . "/$filename";
        if (file_exists($path)) {
            $fm = file_get_contents($path);
            if ($fm == null) { return null; }
            $migration = json_decode($fm, true);
            if ($migration == null) { return null; }
            return $migration;
        }
        return null;
    }

    private function saveRanMigration($migration) {
        if ($migration == null) {
            unlink($this->schemaPath.'/lrm.scl');
            return;
        }
        $lm = serialize($migration);
        file_put_contents($this->schemaPath.'/lrm.scl', $lm);
    }

    public function connect(array $connectionDetails = null): bool
    {
        try {
            if ($connectionDetails != null) {
                $this->connectionDetails = $connectionDetails;
            }
            if ($this->connectionDetails == null || !is_array($this->connectionDetails)) {
                _log('Repository not properly configured');
                return false;
            }
            if (!isset($this->connectionDetails['charset'])) { $this->connectionDetails['charset'] = 'utf8mb4'; }
            $dbnameinfo = '';
            if (!empty($this->connectionDetails['dbname'])) {
                $dbnameinfo = 'dbname=' . $this->connectionDetails['dbname'].';';
            }
            $dsn = 'mysql:host='.$this->connectionDetails['server'].';'.$dbnameinfo.'charset='.$this->connectionDetails['charset'];
            if (isset($connectionDetails['options'])) {
                $this->conn = new PDO($dsn, $this->connectionDetails['user'], $this->connectionDetails['password'], $this->connectionDetails['options']);
            } else {
                $this->conn = new PDO($dsn, $this->connectionDetails['user'], $this->connectionDetails['password']);
            }
            if (!$this->conn) {
                _log("Failed to connect to mysql server:" . $this->connectionDetails['server']);
                return false;
            }
            //$this->dbname = $this->connectionDetails['dbname']??'';
            return true;
        }catch(\PDOException $e) {
            _log("Failed to connect to mysql server:" . $this->connectionDetails['server']);
            _log($e->getMessage());
            return false;
        }
    }

    public function createDb($dbname) {
        if ($this->conn == null) { throw new \Exception('Unable to connect to MySql'); }
        return $this->conn->exec("CREATE DATABASE ".$dbname) !== false;
    }

    public function execStatement(string $stmt) {
        if ($this->conn == null) { throw new \Exception('Unable to connect to MySql'); }
        return $this->conn->exec($stmt) !== false;
    }

    public function getTableInfo($tableName) {
        $sql = 'SHOW COLUMNS FROM '.$tableName;
        if ($this->conn == null) { throw new \Exception('Unable to connect to MySql'); }
        if (($command = $this->execute($sql)) != null) {
            $rows = array();
            $row = $command->fetch(PDO::FETCH_ASSOC);
            while ($row) {
                $rows[] = $row;
                $row = $command->fetch(PDO::FETCH_ASSOC);
            }
            $command = null;
            return $rows;
        }
        return null;
    }

    private function execute($cQuery, $inpParams = null) {
        try {
            $command = $this->conn->prepare($cQuery);
            if ($inpParams != null && is_array($inpParams)) {
                $success = $command->execute($inpParams);
            } else {
                $success = $command->execute();
            }
            if (!$success) return null;
            return $command;
        }catch (\PDOException $e) {
            _log("Failed to execute query:");
            _log($e->getMessage());
            return null;
        }
    }
}
