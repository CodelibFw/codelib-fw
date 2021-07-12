<?php
/**
 * CLRepository.php
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
 * Interface CLRepository
 * Specs for a data repository (sql and no-sql). For an example of implementation, take a look at @CLMySqlRepository
 * @package cl\contract
 */
interface CLRepository {
    public function connect(array $connectionDetails = null) : bool;
    public function setConnectionDetails(array $connectionDetails);
    public function create(CLEntity $entity) : bool;
    public function createAll(array $entities) : bool;
    public function read(string $entityName, string $condition = null, array $values = null, string $cols = "*", $orderby = "") : ?array;
    public function update(CLEntity $entity) : bool;
    public function updateAll(string $entityName, string $condition, array $conditionValues, array $updatedValues) : bool;
    public function delete(string $entityName, string $condition = null, array $values = null) : bool;
    public function count(string $entityName, string $condition = null, array $values = null) : int;
}
