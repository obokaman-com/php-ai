<?php

namespace Obokaman\PhpAi\Service;

use Obokaman\PhpAi\Model\AiAnswer;
use Obokaman\PhpAi\Model\Document\Document;
use Obokaman\PhpAi\Model\Document\Excerpt;
use OpenAI;

class Ai
{
    /**
     * @var Excerpt[]|null
     */
    private array|null $excerpts;
    private \OpenAI\Responses\Embeddings\CreateResponse|null $vector_excerpts;

    public function __construct(private OpenAI\Client $open_ai, private Embeddings $embeddings)
    {
    }

    /**
     * @param Document[] $documents
     * @return void
     */
    public function memorize(array $documents): void
    {
        $this->excerpts = $this->embeddings->extractExcerpts($documents);
        $this->vector_excerpts = $this->embeddings->vectorExcerpts($this->excerpts);
    }

    public function forgetEverything(): void
    {
        $this->excerpts = null;
        $this->vector_excerpts = null;
    }

    public function answer(string $question): AiAnswer
    {
        $vector_question = $this->embeddings->vectorQuestion($question);

        $answers = $this->embeddings->guessBestAnswers($this->excerpts, $this->vector_excerpts, $vector_question);
        
        $prompt = <<<TEXT
Given the following documents, when a user ask a question about it, create a final answer. 
ALWAYS format your output using JSON, following this template: 

```JSON 
{
    "answer": "[HERE YOU WRITE YOUR ANSWER]",
    "sources": "[HERE YOU WRITE YOUR SOURCES, THE SECTIONS OR PAGES FROM THE DOCUMENT]"
}
```

If you don't know the answer, or the topic is not covered in the document, ONLY answer with "[DONT KNOW]" in the "answer" field. Don't try to make up an answer.

TEXT;

        foreach ($answers as $answer) {
            $prompt .= <<<TEXT
DOCUMENT ({$this->excerpts[$answer['index']]->location}, {$this->excerpts[$answers[0]['index']]->metadata['section']})
=======
{$this->excerpts[$answer['index']]->text}
=======
TEXT;
        }

        $result = $this->open_ai->chat()->create([
            'model' => $_ENV['OPENAI_API_MODEL'],
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
                ['role' => 'user', 'content' => $question],
            ],
            'temperature' => 0
        ]);

        $openai_answer = $result->toArray()['choices'][0]['message']['content'];
        try {
            $openai_answer = json_decode($openai_answer, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new AiAnswer(
                $question,
                $prompt,
                '[DONT KNOW]',
                $openai_answer
            );
        }

        return new AiAnswer(
            $question,
            $prompt,
            $openai_answer->answer,
            $openai_answer->sources
        );
    }
}
