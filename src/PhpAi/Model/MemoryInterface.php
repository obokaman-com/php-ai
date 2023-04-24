<?php

namespace Obokaman\PhpAi\Model;

interface MemoryInterface
{
    public function add($data);
    public function get($data);
    public function clear();
    public function getRelevant($data, $numRelevant = 5);
    public function getStats();
}
