<?php

namespace Obokaman\PhpAi\Model\Document;

class Section
{
    public function __construct(
        public readonly string $content,
        public readonly array $metadata
    ) {
    }
}
