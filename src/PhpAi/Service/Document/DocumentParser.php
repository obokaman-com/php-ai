<?php

namespace Obokaman\PhpAi\Service\Document;

use Obokaman\PhpAi\Model\Document\Document;

interface DocumentParser
{
    public function parse(string $file_path): ?Document;

    public function next($file_path): ?Document;
}
