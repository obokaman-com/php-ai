<?php

namespace Obokaman\PhpAi\Infrastructure\Interface\Command;

use Obokaman\PhpAi\Service\Ai;
use Obokaman\PhpAi\Service\Embeddings;
use Obokaman\PhpAi\Service\PDFParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Flex\Options;

class Question extends Command
{
    public function __construct(private PDFParser $pdf_parser, private Ai $ai)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('ai:question')
             ->setDescription('Ask a question to the AI.')
             ->setHelp('This command allows you to ask a question to the AI.')
             ->addOption('question', 'qu', InputOption::VALUE_REQUIRED, 'Your question');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $console = new SymfonyStyle($input, $output);

        $documents = $this->pdf_parser->parsePDFFolder('./public/docs_to_ingest');

        $console->writeln('This application allow you to ask questions about this documents:');
        foreach ($documents as $document) {
            $console->writeln(json_encode($document->metadata, JSON_THROW_ON_ERROR));
        }

        $this->ai->memorize($documents);

        $question = $input->getOption('question') ?? $console->ask('Question');

        $answer = $this->ai->answer($question);

        dump($answer->answer);
        dump($answer->sources);

        return Command::SUCCESS;
    }
}
