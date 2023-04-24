<?php

namespace Obokaman\PhpAi\Model;

class AiAnswer
{
    public function __construct(
        public readonly string $question,
        public readonly string $prompt,
        public readonly string $answer,
        public readonly string $sources
    ) {
    }
}
