<?php

namespace Obokaman\PhpAi\Infrastructure\Interface\Command;

use Obokaman\PhpAi\Service\Ai;
use Obokaman\PhpAi\Service\Document\FolderParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Question extends Command
{
    public function __construct(private FolderParser $folder_parser, private Ai $ai)
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

        $documents = $this->folder_parser->parse('./public/docs_to_ingest');

        $console->info('Your question will be based in the given ' . count($documents) . ' documents available in /public/docs_to_ingest/ folder.');
        foreach ($documents as $document) {
            $console->writeln(json_encode($document->metadata, JSON_THROW_ON_ERROR), OutputInterface::VERBOSITY_VERBOSE);
        }

        $this->ai->memorize($documents);

        $question = $input->getOption('question') ?? $console->ask('Question');

        $answer = $this->ai->answer($question);

        $console->section('Answer: ' . $answer->answer);
        $console->section('Sources: ' . $answer->sources);
        if ($console->isVerbose()) $console->section('Prompt used: ' . $answer->prompt);

        return Command::SUCCESS;
    }
}
