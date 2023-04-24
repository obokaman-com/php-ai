<?php

namespace Obokaman\PhpAi\Service;

use Obokaman\PhpAi\Model\Document\Document;
use Obokaman\PhpAi\Model\Document\Excerpt;
use OpenAI;

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
    public function extractExcerpts(array $documents, int $chunk_size = 3000, int $chunk_overlap = 1000): array
    {
        $excerpts = [];

        foreach ($documents as $document) {
            foreach ($document->sections as $section) {
                $text = $section->content;
                $length = mb_strlen($text);

                for ($i = 0; $i < $length; $i += ($chunk_size - $chunk_overlap)) {
                    // If the remaining text is less than $chunk_size, increase the overlap to get a larger excerpt
                    if ($length - $i < $chunk_size && $i > 0) {
                        $new_overlap = $chunk_size - ($length - $i);
                        $i -= $new_overlap - $chunk_overlap;
                    }

                    $excerpts[] = new Excerpt(
                        $document->location,
                        mb_substr($text, $i, $chunk_size),
                        $section->metadata
                    );

                    // Break the loop if the remaining text is less than $chunk_size
                    if ($length - $i < $chunk_size) {
                        break;
                    }
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

    private function euclidianDistance(array $embedding_a, array $embedding_b)
    {
        $distance = 0;
        foreach ($embedding_a as $index => $embedding) {
            $distance = pow($embedding - $embedding_b[$index], 2);
        }

        return sqrt($distance);
    }

    private function cosineSimilarity(array $embedding_a, array $embedding_b)
    {
        $dotProduct = 0;
        $uLength = 0;
        $vLength = 0;
        for ($i = 0; $i < count($embedding_a); $i++) {
            $dotProduct += $embedding_a[$i] * $embedding_b[$i];
            $uLength += $embedding_a[$i] * $embedding_a[$i];
            $vLength += $embedding_b[$i] * $embedding_b[$i];
        }
        $uLength = sqrt($uLength);
        $vLength = sqrt($vLength);
        return $dotProduct / ($uLength * $vLength);
    }
}
