<?php

namespace Obokaman\PhpAi\Model\Document;

class Document
{
    /**
     * @param string $location
     * @param Section[] $sections
     * @param string[] $metadata
     */
    public function __construct(
        public readonly string $location,
        public readonly array $sections,
        public readonly array $metadata
    ) {
    }
}
