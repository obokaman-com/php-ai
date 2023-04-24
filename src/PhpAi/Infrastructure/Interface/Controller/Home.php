<?php

namespace Obokaman\PhpAi\Infrastructure\Interface\Controller;

use League\CommonMark\CommonMarkConverter;
use Symfony\Component\HttpFoundation\Response;

class Home
{
    public function __construct(private CommonMarkConverter $converter)
    {
    }

    public function main(): Response
    {
        return new Response($this->converter->convert(file_get_contents('./../README.md')));
    }
}
