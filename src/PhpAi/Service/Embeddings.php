<?php

namespace Obokaman\PhpAi\Service;

use Obokaman\PhpAi\Model\AiAnswer;
use Obokaman\PhpAi\Model\Document\Excerpt;
use OpenAI;

use Obokaman\PhpAi\Model\Document\Document;

class Embeddings
{
    public function __construct(private OpenAI\Client $open_ai)
    {
    }

    /**
     * @param Excerpt[] $excerpts
     * @return OpenAI\Responses\Embeddings\CreateResponse
     */
    public function vectorExcerpts(array $excerpts): OpenAI\Responses\Embeddings\CreateResponse
    {
        return $this->open_ai->embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => array_map(static function (Excerpt $excerpt) {
                return $excerpt->text;
            }, $excerpts)
        ]);
    }

    public function vectorQuestion(string $question): OpenAI\Responses\Embeddings\CreateResponse
    {
        return $this->open_ai->embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => [$question]
        ]);
    }

    /**
     * @param Document[] $documents
     * @param int $chunk_size
     * @param int $chunk_overlap
     * @return Excerpt[]
     */
    public function extractExcerpts(array $documents, int $chunk_size = 1500, int $chunk_overlap = 200): array
    {
        $excerpts = [];

        foreach ($documents as $document) {
            foreach ($document->sections as $section) {
                $text = $section->content;
                $length = mb_strlen($text);

                for ($i = 0; $i < $length; $i += ($chunk_size - $chunk_overlap)) {
                    $excerpts[] = new Excerpt(
                        $document->location,
                        mb_substr($text, $i, $chunk_size),
                        $section->metadata
                    );
                }
            }
        }

        return $excerpts;
    }

    public function guessBestAnswers(
        array $excerpts,
        OpenAI\Responses\Embeddings\CreateResponse $vectorized_excerpts,
        OpenAI\Responses\Embeddings\CreateResponse $vectorized_question
    ): array {
        $results = [];

        for ($i = 0; $i < count($vectorized_excerpts->embeddings); $i++) {
            $similarity = $this->cosineSimilarity($vectorized_excerpts->embeddings[$i]->embedding, $vectorized_question->embeddings[0]->embedding);
            $results[] = [
                'similarity' => $similarity,
                'index' => $i,
                'input' => $excerpts[$i],
            ];
        }

        usort($results, function ($a, $b) {
            return $a['similarity'] <=> $b['similarity'];
        });

        return array_reverse(array_slice($results, -3));
    }

    private function cosineSimilarity(array $embedding, array $embedding1)
    {
        $dotProduct = 0;
        $uLength = 0;
        $vLength = 0;
        for ($i = 0; $i < count($embedding); $i++) {
            $dotProduct += $embedding[$i] * $embedding1[$i];
            $uLength += $embedding[$i] * $embedding[$i];
            $vLength += $embedding1[$i] * $embedding1[$i];
        }
        $uLength = sqrt($uLength);
        $vLength = sqrt($vLength);
        return $dotProduct / ($uLength * $vLength);
    }
}
