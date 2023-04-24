<?php

namespace Obokaman\PhpAi\Service\Document;

use League\CommonMark\Extension\FrontMatter\Data\LibYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\Exception\InvalidFrontMatterException;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use Obokaman\PhpAi\Model\Document\Document;
use Obokaman\PhpAi\Model\Document\DocumentFactory;

class MarkdownDocumentParser implements DocumentParser
{
    private ?DocumentParser $next_parser;
    private FrontMatterExtension $parser;

    public function __construct($next_parser = null)
    {
        $this->next_parser = $next_parser;

        $this->parser = new FrontMatterExtension();
    }

    public function parse(string $file_path): ?Document
    {
        if (pathinfo($file_path, PATHINFO_EXTENSION) === 'md') {
            try {
                $parsed_markdown = $this->parser->getFrontMatterParser()->parse(file_get_contents($file_path));
            }
            catch (InvalidFrontMatterException $e)
            {
                dump($file_path);
                dump($e->getMessage());

                return $this->next($file_path);
            }

            return DocumentFactory::fromMarkdown(basename($file_path), $parsed_markdown);
        }

        return $this->next($file_path);
    }

    public function next($file_path): ?Document
    {
        return $this->next_parser?->parse($file_path);
    }
}
