<?php

namespace Obokaman\PhpAi\Service\Document;

use Obokaman\PhpAi\Model\Document\Document;
use Symfony\Component\Finder\Finder;

class FolderParser
{
    public function __construct(private readonly DocumentParser $parser)
    {
    }

    /**
     * @param string $file_folder
     * @return Document[]
     */
    public function parse(string $file_folder): array
    {
        $docs = [];

        $finder = new Finder();
        $finder->files()->in($file_folder)->depth('>= 0');

        foreach ($finder as $file) {
            $doc = $this->parser->parse($file->getRealPath());
            if (null !== $doc) {
                $docs[] = $doc;
            }
        }

        return $docs;
    }
}
