<?php

namespace Obokaman\PhpAi\Model\Document;

class Excerpt
{
    public function __construct(
        public readonly string $location,
        public readonly string $text,
        public readonly array $metadata
    ) {
    }
}
