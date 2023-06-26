<?php
/**
 * CLBaseEntity.php
 */

namespace cl\store;
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

/**
 * Class CLBaseEntity
 * Base class for entities that can be stored and retrieved from a repository
 * @package cl\store
 */
class CLBaseEntity implements CLEntity {
    private $id, $entityName;
    private $data = array();

    /**
     * CLBaseEntity constructor.
     * @param string $entityName name of the new entity
     * @param null $id (optional) the id of the entity
     */
    public function __construct(string $entityName, $id = null) {
        $this->entityName = $entityName;
        if (isset($id)) { $this->id = $id;}
    }

    /**
     * sets the entity id
     * @param int $id id of this entity
     * @return mixed|void
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * returns this entity's id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * sets the entity name
     * @param string $entityName
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * returns the entity name
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * sets data for the entity as an associative array
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * returns this entity's data
     * @return array
     */
    public function &getData(): array
    {
        return $this->data;
    }

    /**
     * returns an array with the keys of this entity's data
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->data);
    }

    /**
     * returns an array with the values of this entity's data
     * @return array
     */
    public function getValues(): array
    {
        return array_values($this->data);
    }

    /**
     * returns the number of keys in this entity's data
     * @return int
     */
    public function getKeyCount(): int
    {
        return count($this->getKeys());
    }

    public function setField($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getField($key)
    {
        return $this->data[$key]??null;
    }
}
