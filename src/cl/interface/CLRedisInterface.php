<?php


namespace cl\contract;


interface CLRedisInterface
{
    public function startTransaction();
    public function cancelTransaction();
    public function commitTransaction();
    public function executeCommand(string $command, $params);
}
