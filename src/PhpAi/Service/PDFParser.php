<?php

namespace Obokaman\PhpAi\Service;

use Obokaman\PhpAi\Model\Document\Document;
use Obokaman\PhpAi\Model\Document\DocumentFactory;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class PDFParser
{
    private $parser;

    public function __construct()
    {
        $config = new Config();
        $config->setFontSpaceLimit(-60);
        $config->setHorizontalOffset('');
        $this->parser = new Parser([], $config);
    }

    public function parsePDFFile(string $file_path): Document
    {
        $parsed_pdf = $this->parser->parseFile($file_path);

        return DocumentFactory::fromPDF(basename($file_path), $parsed_pdf);
    }

    /**
     * @param string $file_folder
     * @return Document[]
     */
    public function parsePDFFolder(string $file_folder): array
    {
        $docs = [];

        $files = glob($file_folder . '/*.pdf');

        foreach ($files as $file) {
            $docs[] = $this->parsePDFFile($file);
        }

        return $docs;
    }
}
