<?php
/**
 * CLMySqlRepository.php
 */
namespace cl\store\mysql;
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

use cl\contract\CLEntity;
use cl\contract\CLRepository;
use PDO;
use PDOException;
use cl\store\CLBaseEntity;

/**
 * Class CLMySqlRepository
 * Implementation of a data repository for MySql
 * @package cl\store\mysql
 */
class CLMySqlRepository implements CLRepository {
    private $conn, $dbname;
    private $logquery = false;
    private $connectionDetails;

    /**
     * Connects to a MySql repository
     * @param array $connectionDetails associative array which specifies: server, dbname, charset, user, password, and options
     * @return bool
     */
    public function connect(array $connectionDetails = null): bool
    {
        try {
            if ($connectionDetails != null) {
                $this->connectionDetails = $connectionDetails;
            } else {
                if ($this->conn != null) {
                    return true;
                }
            }
            if ($this->connectionDetails == null || !is_array($this->connectionDetails)) {
                _log('Repository not properly configured');
                return false;
            }
            if (!isset($this->connectionDetails['charset'])) { $this->connectionDetails['charset'] = 'utf8mb4'; }
            $dsn = 'mysql:host='.$this->connectionDetails['server'].';dbname='.$this->connectionDetails['dbname'].';charset='.$this->connectionDetails['charset'];
            if (isset($connectionDetails['options'])) {
                $this->conn = new PDO($dsn, $this->connectionDetails['user'], $this->connectionDetails['password'], $this->connectionDetails['options']);
            } else {
                $this->conn = new PDO($dsn, $this->connectionDetails['user'], $this->connectionDetails['password']);
            }
            if (!$this->conn) {
                _log("Failed to connect to mysql server:" . $this->connectionDetails['server']);
                return false;
            }
            $this->dbname = $this->connectionDetails['dbname'];
            return true;
        }catch(PDOException $e) {
            _log("Failed to connect to mysql server:" . $this->connectionDetails['server']);
            _log($e->getMessage());
            return false;
        }
    }

    /**
     * Stores an entity in the repository
     * @param CLEntity $entity
     * @return bool
     */
    public function create(CLEntity $entity): bool
    {
        $sql = $this->prepareInsertStatement($entity, 1);
        return $this->execute($sql, $entity->getValues()) != null;
    }

    /**
     * Stores a group of entities in the repository
     * @param array $entities
     * @return bool
     */
    public function createAll(array $entities): bool
    {
        if (!isset($entities) || count($entities) == 0) return false;
        $sql = $this->prepareInsertStatement($entities[0], count($entities));
        $vals = $entities[0]->getValues();
        $count = count($entities);
        for ($i=1; $i < $count; $i++) {
            $vals = array_merge($vals, $entities[$i]->getValues());
        }
        return $this->execute($sql, $vals) != null;
    }

    /**
     * Reads entities from the repository, that match certain conditions
     * @param string $entityName
     * @param string|null $condition example: product = ?
     * @param array|null $values for condition columns, example: ['laptop']
     * @param string $cols
     * @param string $orderby
     * @return array
     */
    public function read(string $entityName, string $condition = null, array $values = null, string $cols = "*", $orderby = ""): ?array
    {
        $sql = $this->prepareSelectStatement($entityName, $cols, $condition, $orderby);
        if (($command = $this->execute($sql, $values)) != null) {
            $rows = array();
            $row = $command->fetch(PDO::FETCH_ASSOC);
            while ($row) {
                $entity = new CLBaseEntity($entityName, $row['id'] ?? null);
                $entity->setData($row);
                $rows[] = $entity;
                $row = $command->fetch(PDO::FETCH_ASSOC);
            }
            $command = null;
            return $rows;
        }
        return null;
    }

    /**
     * Updates existing entities, which match certain conditions, with the values provided
     * @param CLEntity $entity
     * @param string|null $condition
     * @return bool
     */
    public function update(CLEntity $entity): bool
    {
        $sql = $this->prepareUpdateStatement($entity, "id=".$entity->getId());
        return $this->execute($sql, $entity->getValues()) != null;
    }

    /**
     * @param string $entityName
     * @param string $condition
     * @param array $conditionValues values for columns in the condition
     * @param array $updatedValues
     */
    public function updateAll(string $entityName, string $condition, array $conditionValues, array $updatedValues): bool
    {
        $sql = $this->_prepareUpdateStatement($entityName, $condition, $updatedValues);
        return $this->execute($sql, array_merge(array_values($updatedValues), $conditionValues)) != null;
    }

    /**
     * Deletes existing entities matching certain conditions
     * @param string $entityName
     * @param string|null $condition example: username = ? or email = ?
     * @param array|null $values for the columns specified in the condition
     * @return bool
     */
    public function delete(string $entityName, string $condition = null, array $values = null): bool
    {
        $sql = $this->prepareDeleteStatement($entityName, $condition);
        return $this->execute($sql, $values) != null;
    }

    /**
     * Returns the number of entities in the repository which match the specified condition(s)
     * @param string $entityName
     * @param string|null $condition
     * @param array|null $values
     * @return int
     */
    public function count(string $entityName, string $condition = null, array $values = null): int
    {
        $sql = "select COUNT(*) from " . $entityName;
        if (mb_strlen($condition) > 0) {
            $sql.= " where " . $condition;
        }
        if (($command = $this->execute($sql, $values)) != null) {
            $row = $command->fetch(PDO::FETCH_BOTH);
            return $row[0];
        }
        return 0;
    }

    /**
     * Mostly used internally by this class for CRUD operations on entities,
     * but available for applications to use directly
     * @param String $cQuery query to execute, which can contain placeholders such as ? or :(for named values)
     * @param array $inpParams optional array with values to substitute the placeholders
     * @return null on failure or the command so that any available values can be retrieved
     */
    public function execute($cQuery, $inpParams = null) {
        try {
            if ($this->conn == null) {
                $this->connect();
            }
            $command = $this->conn->prepare($cQuery);
            if ($inpParams != null && is_array($inpParams)) {
                $success = $command->execute($inpParams);
            } else {
                $success = $command->execute();
            }
            if (!$success) return null;
            return $command;
        }catch (PDOException $e) {
            _log("Failed to execute query:");
            _log($e->getMessage());
            return null;
        }
    }

    public function beginTransaction() {
        if ($this->conn) {
            $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT,FALSE);
            $this->conn->beginTransaction();
        }
    }

    public function rollBack() {
        if ($this->conn) {
            $this->conn->rollBack();
            $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT,TRUE);
        }
    }

    public function commit() {
        if ($this->conn) {
            $this->conn->commit();
            $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT,TRUE);
        }
    }

    public function setConnectionDetails(array $connectionDetails) {
        $this->connectionDetails = $connectionDetails;
    }

    private function prepareSelectStatement($entityName, $cols = '*', $condition = '', $orderby = ""): string
    {
        $sql = "select " . $cols . " from " . $entityName;
        if (mb_strlen($condition) > 0) {
            $sql.=" where " . $condition;
        }
        if (mb_strlen($orderby) > 0){
            $sql.=" order by " . $orderby;
        }
        return $sql;
    }

    private function prepareInsertStatement(CLEntity $entity, int $nrows): string
    {
        $count = $entity->getKeyCount();
        $keys = $entity->getKeys();
        $names = $keys[0];
        $vals = '?';
        for ($i = 1; $i < $count - 1; $i++) {
            $names.= "," . $keys[$i];
            $vals .= ", ?";
        }
        if ($count > 1) {
            $names .= "," . $keys[$count - 1];
            $vals .= ", ?";
        }
        $sql = "insert into " . $entity->getEntityName() . "(" . $names . ") values";
        $sep = "";
        for ($i = 0; $i < $nrows; $i++) {
            $sql .=$sep."(" . $vals . ")";
            $sep = ",";
        }
        return $sql;
    }

    private function prepareUpdateStatement(CLEntity $entity, $condition = ''): string
    {
        $count = $entity->getKeyCount();
        $keys = $entity->getKeys();
        $names = $keys[0]. "= ?";
        for ($i = 1; $i < $count - 1; $i++) {
            $names.= "," . $keys[$i]. "= ?";
        }
        if ($count > 1) {
            $names .= "," . $keys[$count - 1]. "= ?";
        }
        $sql = "update " . $entity->getEntityName() . " set " . $names;
        if (mb_strlen($condition) > 0) {
            $sql.=" where " . $condition;
        }
        return $sql;
    }

    private function _prepareUpdateStatement($entityName, $condition = '', $updatedValues): string
    {

        $keys = array_keys($updatedValues);
        $count = count($keys);
        $names = $keys[0]. "= ?";
        for ($i = 1; $i < $count - 1; $i++) {
            $names.= "," . $keys[$i]. "= ?";
        }
        if ($count > 1) {
            $names .= "," . $keys[$count - 1]. "= ?";
        }
        $sql = "update " . $entityName . " set " . $names;
        if (mb_strlen($condition) > 0) {
            $sql.=" where " . $condition;
        }
        return $sql;
    }

    private function prepareDeleteStatement($entityName, $condition = ''): string
    {
        $sql = "delete from " . $entityName;
        if (mb_strlen($condition) > 0) {
            $sql.=" where " . $condition;
        }
        return $sql;
    }
}
