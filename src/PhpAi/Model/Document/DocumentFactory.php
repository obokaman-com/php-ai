<?php

namespace Obokaman\PhpAi\Model\Document;

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
}
