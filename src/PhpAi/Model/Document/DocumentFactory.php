<?php

namespace Obokaman\PhpAi\Model\Document;

use League\CommonMark\Extension\FrontMatter\Input\MarkdownInputWithFrontMatter;
use Smalot\PdfParser\Document as ParsedPDFDocument;

class DocumentFactory
{
    public static function fromPDF(string $location, ParsedPDFDocument $document): Document
    {
        $sections = [];
        $pdf_pages = $document->getPages();
        foreach ($pdf_pages as $pdf_page) {
            $content = $pdf_page->getText();
            $content = trim(
                preg_replace('/\s+/i', ' ', mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1, ISO-8859-15', true)))
            );
            $metadata = [
                'section' => 'page ' . $pdf_page->getPageNumber() + 1,
                'words' => str_word_count($content)
            ];

            $sections[] = new Section($content, $metadata);
        }

        return new Document($location, $sections, $document->getDetails());
    }

    /**
     * @param string $location
     * @param MarkdownInputWithFrontMatter $parsed_markdown
     * @return Document
     */
    public static function fromMarkdown(string $location, MarkdownInputWithFrontMatter $parsed_markdown): Document
    {
        $sections = [];

        $content = $parsed_markdown->getContent();
        $content = trim(
            preg_replace('/\s+/i', ' ', mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1, ISO-8859-15', true)))
        );

        $document_metadata = $parsed_markdown->getFrontMatter();

        $section_metadata = [
            'section' => @json_encode($document_metadata['menu'] ?? []) ?? 'page 1',
            'words' => str_word_count($content)
        ];

        $sections[] = new Section($content, $section_metadata);

        return new Document(
            $location,
            $sections,
            $document_metadata
        );
    }
}
