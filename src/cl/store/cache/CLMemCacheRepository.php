<?php
/**
 * CLMemCacheRepository.php
 */
namespace cl\store\cache;
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

use cl\contract\CLCacheRepository;
use cl\contract\CLEntity;
use Memcache;
use Memcached;

/**
 * Class CLMemCacheRepository
 * Implementation of a cache repository for Memcache
 * @package cl\store\cache
 */
class CLMemCacheRepository implements CLCacheRepository {

    private $cache = null;

    public function __construct($host = '127.0.0.1', $port = 11211, $weight = 0)
    {
        if (class_exists('Memcached')) {
            $this->cache = new MemcachedDelegate();
        } else if (class_exists('Memcache')) {
            $this->cache = new MemcacheDelegate();
        } else {
            throw new \Exception('Unable to create cache instance. Memcache[d] not available');
        }
        if ($host != null) { // so, use null to avoid adding a server at object construction time
            $this->addServer($host, $port, $weight);
        }
    }

    public function create(CLEntity $entity, int $expiration, array $options = null): bool
    {
        return $this->cache->create($entity, $expiration, $options);
    }

    public function createAll(array $entities, int $expiration, array $options): bool
    {
        return $this->cache->createAll($entities, $expiration, $options);
    }

    public function read(string $entityName): CLEntity
    {
        $obj = $this->cache->read($entityName);
        return $obj;
    }

    public function delete(string $entityName): bool
    {
        return $this->cache->delete($entityName);
    }

    public function getCache() {
        return $this->cache->getCache();
    }

    /**
     * Adds a server to the cache pool
     * @param string $host
     * @param int $port
     * @param int $weight
     * @return mixed
     */
    public function addServer(string $host, int $port = 11211, int $weight = 0)
    {
        if (!$this->serverExists($host, $port)) {
            return $this->cache->addServer($host, $port, $weight);
        }
        return true;
    }

    private function serverExists(string $host, int $port) {
        $servers = $this->cache->getServerList();
        foreach ($servers as $server) {
            if ($server['host'] == $host && $server['port'] == $port)
                return true;
        }
        return false;
    }
}
class MemcachedDelegate {
    protected $cache;

    public function __construct()
    {
        $this->cache = new Memcached();
    }

    public function create(CLEntity $entity, int $expiration, array $options = null): bool {
        if ($options != null && count($options) > 0) {
            $this->cache->setOptions($options);
        }
        return $this->cache->set($entity->getEntityName(), $entity, $expiration);
    }

    public function createAll(array $entities, int $expiration, array $options = null): bool
    {
        if ($options != null && count($options) > 0) {
            $this->cache->setOptions($options);
        }
        $kv = [];
        foreach ($entities as $entity) {
            $kv[] = [$entity->getEntityName() => $entity];
        }
        if (count($kv) > 0) {
            $this->cache->setMulti($kv, $expiration);
        }
        return true;
    }

    public function read(string $entityName): CLEntity
    {
        return $this->cache->get($entityName);
    }

    public function delete(string $entityName): bool
    {
        return $this->cache->delete($entityName);
    }

    public function getCache() {
        return $this->cache;
    }

    public function addServer(string $host, int $port, int $weight = 0): bool
    {
        return $this->cache->addServer($host, $port, $weight);
    }

    public function getServerList(): array {
        return $this->cache->getServerList() ?? [];
    }
}
class MemcacheDelegate extends MemcachedDelegate{
    static private $servers = [];

    public function __construct()
    {
        $this->cache = new Memcache();
    }

    public function create(CLEntity $entity, int $expiration, array $options = null): bool {
        $usecompression = $this->getCompression($options);
        return $this->cache->set($entity->getEntityName(), $entity, $usecompression, $expiration);
    }

    public function createAll(array $entities, int $expiration, array $options = null): bool
    {
        foreach ($entities as $entity) {
            $this->create($entity, $expiration, $options);
        }
        return true;
    }

    private function getCompression($options) {
        $usecompression = MEMCACHE_COMPRESSED;
        if ($options != null && count($options) > 0) {
            if (isset($options['compression'])) {
                $usecompression = $options['compression'];
            }
        }
        return $usecompression;
    }

    public function getServerList(): array
    {
        return MemcacheDelegate::$servers;
    }

    public function addServer(string $host, int $port, int $weight = 0): bool
    {
        $result = $this->cache->addServer($host, $port, true, $weight);
        if ($result) {
            MemcacheDelegate::$servers[] = ['host' => $host, 'port' => $port];
        }
        return $result;
    }
}
