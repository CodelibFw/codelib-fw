<?php
/**
 * CLRedisRepository.php
 */

namespace cl\store\redis;
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
 * Class CLRedisRepository
 * Implementation of a data repository for Redis
 * Takes advantage of existing PHP redis libraries, adapted as CLRepository
 * @package cl\store\redis
 */
class CLRedisRepository implements \cl\contract\CLRepository, \cl\contract\CLRedisInterface
{
    private $redisLib;
    private $connectionDetails;

    public function connect(array $connectionDetails = null): bool
    {
        if ($connectionDetails != null) {
            $this->connectionDetails = $connectionDetails;
        }
        if ($this->connectionDetails == null || !is_array($this->connectionDetails)) {
            _log('Redis Repository not properly configured');
            return false;
        }
        if ($this->connectionDetails['redislib'] == 'predis') {
            unset($this->connectionDetails['redislib']);
            $this->redisLib = new PredisDelegate($this->connectionDetails);
            return true;
        }
        return false;
    }

    public function setConnectionDetails(array $connectionDetails)
    {
        $this->connectionDetails = $connectionDetails;
    }

    public function create(CLEntity $entity): bool
    {
        return $this->redisLib->create($entity);
    }

    public function createAll(array $entities): bool
    {
        foreach ($entities as $entity) {
            $this->create($entity);
        }
        return true;
    }

    public function read(string $entityName, string $cskeys = null, array $keys = null, string $cols = "*", $orderby = ""): ?array
    {
        $params = $keys;
        if ($cskeys != null) {
            $params = $cskeys;
        }
        $response = $this->redisLib->read($entityName, $params);
        if (is_array($response)) { return $response; }
        return [$response];
    }

    public function update(CLEntity $entity): bool
    {
        // TODO: Implement update() method.
    }

    public function updateAll(string $entityName, string $condition, array $conditionValues, array $updatedValues): bool
    {
        // TODO: Implement updateAll() method.
    }

    public function delete(string $entityName, string $condition = null, array $values = null): bool
    {
        // TODO: Implement delete() method.
    }

    public function count(string $entityName, string $condition = null, array $values = null): int
    {
        // TODO: Implement count() method.
    }

    public function startTransaction()
    {
        return $this->redisLib->startTransaction();
    }

    public function cancelTransaction()
    {
        return $this->redisLib->cancelTransaction();
    }

    public function commitTransaction()
    {
        return $this->redisLib->commitTransaction();
    }

    public function executeCommand(string $command, $params)
    {
        return $this->redisLib->executeCommand($command, $params);
    }
}
class PredisDelegate {
    private $connectionDetails;
    private $redisLib;

    public function __construct(array $connectionDetails) {
        $this->connectionDetails = $connectionDetails;
        $this->redisLib = new \Predis\Client($this->connectionDetails);
    }

    public function create($entity) {
        if ($entity->getField('list') != null) {
            $list = $entity->getField('list');
            unset($entity->getData()['list']);
            $response = $this->redisLib->transaction()
                ->sadd($list, $entity->getEntityName())
                ->hmset($entity->getEntityName(), $entity->getData())
                ->execute();
        } else {
            $response = $this->redisLib->hmset($entity->getEntityName(), $entity->getData());
        }
        if (is_array($response) && count($response) == 2) {
            return $response[1]->getPayload() == "OK";
        }
        return $response->getPayload() == "OK";
    }

    public function read(string $entityName, string $key = null) {
        if ($key == null) {
            return $this->redisLib->hgetall($entityName);
        }
        return $this->redisLib->hget($entityName, $key);
    }

    public function executeCommand(string $command, $params)
    {
        if (is_array($params) && isset($params['hash'])) {
            $hash = $params['hash'];
            unset($params['hash']);
            return $this->redisLib->$$command($hash, $params);
        }
        return $this->redisLib->$command($params);
    }

    public function startTransaction()
    {
        return $this->redisLib->multi();
    }

    public function cancelTransaction()
    {
        return $this->redisLib->discard();
    }

    public function commitTransaction()
    {
        return $this->redisLib->exec();
    }
}
