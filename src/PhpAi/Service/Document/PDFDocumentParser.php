<?php

namespace Obokaman\PhpAi\Service\Document;

use Obokaman\PhpAi\Model\Document\Document;
use Obokaman\PhpAi\Model\Document\DocumentFactory;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser as PDFParser;

class PDFDocumentParser implements DocumentParser
{
    private ?DocumentParser $next_parser;
    private PDFParser $parser;

    public function __construct($next_parser = null)
    {
        $this->next_parser = $next_parser;

        $config = new Config();
        $config->setFontSpaceLimit(-60);
        $config->setHorizontalOffset('');
        $this->parser = new PDFParser([], $config);
    }

    public function parse(string $file_path): ?Document
    {
        if (pathinfo($file_path, PATHINFO_EXTENSION) === 'pdf') {
            $parsed_pdf = $this->parser->parseFile($file_path);

            return DocumentFactory::fromPDF(basename($file_path), $parsed_pdf);
        }

        return $this->next($file_path);
    }

    public function next($file_path): ?Document
    {
        return $this->next_parser?->parse($file_path);
    }
}
