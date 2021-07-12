<?php
/**
 * CLCacheRepository.php
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
 * Interface CLCacheRepository
 * Contract for a Cache repository.
 * Provides basic/common operations for a cache, and access to the Cache object for other,
 * more specialized operations.
 * See: CLMemCacheRepository for an example implementation created to support MemCache
 * @package cl\contract
 */
interface CLCacheRepository {
    /**
     * Adds a server to the cache pool
     * @param string $host
     * @param int $port
     * @param int $weight
     * @return mixed
     */
    public function addServer(string $host, int $port, int $weight);
    /**
     * Creates/Saves entity in the Cache, using its entityName (not its Id) as Key
     * @param CLEntity $entity
     * @param int $expiration
     * @param array $options
     * @return bool
     */
    public function create(CLEntity $entity, int $expiration, array $options) : bool;

    /**
     * Creates/Saves an array of entities in the Cache
     * @param array $entities
     * @param int $expiration
     * @param array $options
     * @return bool
     */
    public function createAll(array $entities, int $expiration, array $options) : bool;

    /**
     * Reads/Get specific Key/EntityName from the Cache
     * @param string $entityName
     * @return CLEntity
     */
    public function read(string $entityName) : CLEntity;

    /**
     * Deletes given Key from the Cache
     * @param string $entityName
     * @return bool
     */
    public function delete(string $entityName) : bool;

    /**
     * Returns the actual Cache object implementation for further operations
     * @return mixed
     */
    public function getCache();
}
